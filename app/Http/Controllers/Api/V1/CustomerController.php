<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DocumentAcceptance;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Registra o actualiza un cliente.
     * POST /api/v1/customers/register
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'lastname'             => 'nullable|string|max:100',
            'phone'                => 'required|string|max:20',
            'email'                => 'nullable|email|max:150',
            'document_type'        => 'nullable|in:CC,CE,PA,TI,RC,NIT,DE',
            'document_number'      => 'nullable|string|max:30',
            'birth_date'           => 'nullable|date',
            'gender'               => 'nullable|in:M,F,O,N',
            'city_code'            => 'nullable|string|max:10',
            'city_name'            => 'nullable|string|max:100',
            'department'           => 'nullable|string|max:100',
            'address'              => 'nullable|string|max:200',
            'accept_privacy'       => 'required|boolean',
            'accept_terms'         => 'required|boolean',
            'accept_sms'           => 'nullable|boolean',
        ]);

        if (!$data['accept_privacy'] || !$data['accept_terms']) {
            return response()->json([
                'error'   => 'consent_required',
                'message' => 'Debe aceptar la política de privacidad y los términos y condiciones (Ley 1581).',
            ], 422);
        }

        // Resolver ciudad — prioridad: código DANE → nombre + departamento → nombre solo
        $cityId = null;
        if ($data['city_code'] ?? null) {
            $cityId = City::where('code', $data['city_code'])->value('id');
        }
        if (!$cityId && ($data['city_name'] ?? null)) {
            $cityId = $this->resolveCityByName($data['city_name'], $data['department'] ?? null);
        }

        $wasNew = !Customer::where('phone', $data['phone'])->exists();

        $customer = Customer::updateOrCreate(
            ['phone' => $data['phone']],
            [
                'name'                      => $data['name'],
                'lastname'                  => $data['lastname'] ?? null,
                'email'                     => $data['email'] ?? null,
                'document_type'             => $data['document_type'] ?? null,
                'document_number'           => $data['document_number'] ?? null,
                'birth_date'                => $data['birth_date'] ?? null,
                'gender'                    => $data['gender'] ?? null,
                'city_id'                   => $cityId,
                'address'                   => $data['address'] ?? null,
                'status'                    => 'active',
                'created_via'               => 'api',
                'data_treatment_accepted'    => true,
                'data_treatment_accepted_at' => now(),
                'acceptance_ip'             => $request->ip(),
            ]
        );

        // Registrar aceptaciones legales
        $docsToAccept = ['privacy', 'terms'];
        if ($data['accept_sms'] ?? false) {
            $docsToAccept[] = 'sms_consent';
        }

        foreach ($docsToAccept as $docType) {
            $doc = LegalDocument::getActive($docType);
            if ($doc) {
                DocumentAcceptance::firstOrCreate(
                    ['customer_id' => $customer->id, 'legal_document_id' => $doc->id],
                    [
                        'accepted_at' => now(),
                        'ip_address'  => $request->ip(),
                        'user_agent'  => $request->userAgent(),
                        'channel'     => 'api',
                    ]
                );
            }
        }

        return response()->json([
            'customer' => $this->formatCustomer($customer),
            'created'  => $wasNew,
            'message'  => $wasNew ? 'Cliente registrado correctamente.' : 'Datos del cliente actualizados.',
            'meta'     => ['request_id' => (string) Str::uuid(), 'processed_at' => now()->toIso8601String()],
        ], $wasNew ? 201 : 200);
    }

    /**
     * Consultar cliente por documento o teléfono.
     * GET /api/v1/customers/{identifier}
     */
    public function show(string $identifier): JsonResponse
    {
        $customer = Customer::where('phone', $identifier)
            ->orWhere('document_number', $identifier)
            ->first();

        if (!$customer) {
            return response()->json([
                'error'   => 'not_found',
                'message' => 'Cliente no encontrado.',
            ], 404);
        }

        return response()->json(array_merge(
            $this->formatCustomer($customer),
            [
                'redemptions_count' => $customer->redemptions()->count(),
                'meta'              => ['request_id' => (string) Str::uuid(), 'processed_at' => now()->toIso8601String()],
            ]
        ));
    }

    /**
     * Registra la aceptación de documentos legales para un cliente.
     * POST /api/v1/customers/accept-terms
     */
    public function acceptTerms(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'          => 'required|string|max:20',
            'document_types' => 'required|array|min:1',
            'document_types.*'=> 'in:privacy,terms,sms_consent',
            'channel'        => 'nullable|in:pos,app,web,api',
        ]);

        $customer = Customer::where('phone', $data['phone'])->first();

        if (!$customer) {
            return response()->json([
                'error'   => 'not_found',
                'message' => 'Cliente no encontrado.',
            ], 404);
        }

        $accepted = [];
        $channel  = $data['channel'] ?? 'api';

        foreach ($data['document_types'] as $docType) {
            $doc = LegalDocument::getActive($docType);
            if (!$doc) {
                continue;
            }

            DocumentAcceptance::firstOrCreate(
                ['customer_id' => $customer->id, 'legal_document_id' => $doc->id],
                [
                    'accepted_at' => now(),
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                    'channel'     => $channel,
                ]
            );

            $accepted[] = $docType;
        }

        // Actualizar flag de tratamiento de datos si se aceptó privacy
        if (in_array('privacy', $accepted)) {
            $customer->update([
                'data_treatment_accepted'    => true,
                'data_treatment_accepted_at' => now(),
            ]);
        }

        return response()->json([
            'accepted' => $accepted,
            'message'  => 'Aceptaciones registradas correctamente.',
            'meta'     => ['request_id' => (string) Str::uuid(), 'processed_at' => now()->toIso8601String()],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formatCustomer(Customer $customer): array
    {
        $customer->loadMissing('city.department');

        return [
            'id'                        => $customer->id,
            'name'                      => $customer->name,
            'lastname'                  => $customer->lastname,
            'full_name'                 => $customer->full_name,
            'phone'                     => $customer->phone,
            'email'                     => $customer->email,
            'document_type'             => $customer->document_type,
            'document_number'           => $customer->document_number,
            'city'                      => $customer->city?->name,
            'department'                => $customer->city?->department?->name,
            'status'                    => $customer->status,
            'data_treatment_accepted'    => (bool) $customer->data_treatment_accepted,
            'data_treatment_accepted_at' => $customer->data_treatment_accepted_at?->toIso8601String(),
        ];
    }

    /** Resuelve ciudad por nombre de texto, opcionalmente acotada a un departamento. */
    private function resolveCityByName(string $cityName, ?string $deptName): ?int
    {
        $deptId = null;
        if ($deptName) {
            $dept = Department::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($deptName)) . '%'])->first();
            $deptId = $dept?->id;
        }

        $q = City::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($cityName)) . '%']);
        if ($deptId) {
            $q->where('department_id', $deptId);
        }

        return $q->value('id');
    }
}
