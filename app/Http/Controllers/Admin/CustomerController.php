<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DocumentAcceptance;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    // ── Listado ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $customers = Customer::with('city')
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->search}%")
                   ->orWhere('lastname', 'like', "%{$request->search}%")
                   ->orWhere('phone', 'like', "%{$request->search}%")
                   ->orWhere('document_number', 'like', "%{$request->search}%")
                   ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->accepted, function ($q) use ($request) {
                $q->where('data_treatment_accepted', $request->accepted === 'yes');
            })
            ->latest()
            ->paginate(25)->withQueryString();

        $stats = [
            'total'    => Customer::count(),
            'active'   => Customer::where('status', 'active')->count(),
            'accepted' => Customer::where('data_treatment_accepted', true)->count(),
            'blocked'  => Customer::where('status', 'blocked')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    // ── Detalle ──────────────────────────────────────────────────────────────

    public function show(Customer $customer)
    {
        $customer->load(['city.department', 'acceptances.legalDocument', 'redemptions.coupon.batch']);
        $redemptions = $customer->redemptions()->with('coupon.batch')->latest('redeemed_at')->paginate(20);
        return view('admin.customers.show', compact('customer', 'redemptions'));
    }

    // ── Crear ─────────────────────────────────────────────────────────────────

    public function create()
    {
        $departments   = Department::orderBy('name')->get(['id', 'name', 'code']);
        $cities        = City::orderBy('name')->get(['id', 'name', 'code', 'department_id']);
        $privacyDoc    = LegalDocument::getActive('privacy');
        $smsConsentDoc = LegalDocument::getActive('sms_consent');
        return view('admin.customers.create', compact('departments', 'cities', 'privacyDoc', 'smsConsentDoc'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'lastname'        => 'nullable|string|max:100',
            'document_type'   => 'nullable|in:CC,CE,PA,NIT,TI,RC,DE',
            'document_number' => 'nullable|string|max:30|unique:customers,document_number',
            'phone'           => 'required|string|max:20|unique:customers,phone',
            'email'           => 'nullable|email|max:150|unique:customers,email',
            'city_id'         => 'nullable|exists:cities,id',
            'birth_date'      => 'nullable|date|before:today',
            'gender'          => 'nullable|in:M,F,O,N',
            'address'         => 'nullable|string|max:255',
            'accept_privacy'  => 'nullable|boolean',
            'accept_sms'      => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $data) {
            $customer = Customer::create([
                'name'            => $data['name'],
                'lastname'        => $data['lastname'] ?? null,
                'document_type'   => $data['document_type'] ?? null,
                'document_number' => $data['document_number'] ?? null,
                'phone'           => $data['phone'],
                'email'           => $data['email'] ?? null,
                'city_id'         => $data['city_id'] ?? null,
                'birth_date'      => $data['birth_date'] ?? null,
                'gender'          => $data['gender'] ?? null,
                'address'         => $data['address'] ?? null,
                'status'          => 'active',
                'created_via'     => 'manual',
                'data_treatment_accepted'    => (bool) ($data['accept_privacy'] ?? false),
                'data_treatment_accepted_at' => ($data['accept_privacy'] ?? false) ? now() : null,
                'acceptance_ip'              => $request->ip(),
            ]);

            // Registrar aceptación de política de privacidad
            if (!empty($data['accept_privacy'])) {
                $doc = LegalDocument::getActive('privacy');
                if ($doc) {
                    DocumentAcceptance::create([
                        'customer_id'       => $customer->id,
                        'legal_document_id' => $doc->id,
                        'channel'           => 'manual',
                        'accepted_at'       => now(),
                        'ip_address'        => $request->ip(),
                        'user_agent'        => $request->userAgent(),
                    ]);
                }
            }

            // Registrar aceptación de consentimiento SMS
            if (!empty($data['accept_sms'])) {
                $doc = LegalDocument::getActive('sms_consent');
                if ($doc) {
                    DocumentAcceptance::create([
                        'customer_id'       => $customer->id,
                        'legal_document_id' => $doc->id,
                        'channel'           => 'manual',
                        'accepted_at'       => now(),
                        'ip_address'        => $request->ip(),
                        'user_agent'        => $request->userAgent(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    // ── Editar ────────────────────────────────────────────────────────────────

    public function edit(Customer $customer)
    {
        $departments   = Department::orderBy('name')->get(['id', 'name', 'code']);
        $cities        = City::orderBy('name')->get(['id', 'name', 'code', 'department_id']);
        $privacyDoc    = LegalDocument::getActive('privacy');
        $smsConsentDoc = LegalDocument::getActive('sms_consent');

        $acceptedTypes = $customer->acceptances()->with('legalDocument')
            ->get()
            ->pluck('legalDocument.type')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return view('admin.customers.edit', compact(
            'customer', 'departments', 'cities', 'privacyDoc', 'smsConsentDoc', 'acceptedTypes'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'lastname'        => 'nullable|string|max:100',
            'document_type'   => 'nullable|in:CC,CE,PA,NIT,TI,RC,DE',
            'document_number' => ['nullable', 'string', 'max:30', Rule::unique('customers', 'document_number')->ignore($customer->id)],
            'phone'           => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'email'           => ['nullable', 'email', 'max:150', Rule::unique('customers', 'email')->ignore($customer->id)],
            'city_id'         => 'nullable|exists:cities,id',
            'birth_date'      => 'nullable|date|before:today',
            'gender'          => 'nullable|in:M,F,O,N',
            'address'         => 'nullable|string|max:255',
            'status'          => 'required|in:active,blocked,unsubscribed',
            'accept_privacy'  => 'nullable|boolean',
            'accept_sms'      => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $data, $customer) {
            $updateData = [
                'name'            => $data['name'],
                'lastname'        => $data['lastname'] ?? null,
                'document_type'   => $data['document_type'] ?? null,
                'document_number' => $data['document_number'] ?? null,
                'phone'           => $data['phone'],
                'email'           => $data['email'] ?? null,
                'city_id'         => $data['city_id'] ?? null,
                'birth_date'      => $data['birth_date'] ?? null,
                'gender'          => $data['gender'] ?? null,
                'address'         => $data['address'] ?? null,
                'status'          => $data['status'],
            ];

            // Si acaba de aceptar datos personales
            if (!empty($data['accept_privacy']) && !$customer->data_treatment_accepted) {
                $updateData['data_treatment_accepted']    = true;
                $updateData['data_treatment_accepted_at'] = now();
                $updateData['acceptance_ip']              = $request->ip();

                $doc = LegalDocument::getActive('privacy');
                if ($doc) {
                    DocumentAcceptance::firstOrCreate(
                        ['customer_id' => $customer->id, 'legal_document_id' => $doc->id],
                        ['channel' => 'manual', 'accepted_at' => now(), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]
                    );
                }
            }

            if (!empty($data['accept_sms'])) {
                $doc = LegalDocument::getActive('sms_consent');
                if ($doc) {
                    DocumentAcceptance::firstOrCreate(
                        ['customer_id' => $customer->id, 'legal_document_id' => $doc->id],
                        ['channel' => 'manual', 'accepted_at' => now(), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]
                    );
                }
            }

            $customer->update($updateData);
        });

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    // ── Importación CSV ───────────────────────────────────────────────────────

    public function import()
    {
        return view('admin.customers.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'Debes seleccionar un archivo CSV.',
            'csv_file.mimes'    => 'El archivo debe ser CSV (.csv o .txt).',
            'csv_file.max'      => 'El archivo no puede superar 10 MB.',
        ]);

        $path   = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        // Eliminar BOM UTF-8 si existe (archivos de Excel)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        // Detectar separador leyendo la primera línea raw
        $firstLine = fgets($handle);
        rewind($handle);
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $separator = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        // Leer encabezados
        $rawHeaders = fgetcsv($handle, 0, $separator);
        if (!$rawHeaders) {
            return back()->withErrors(['csv_file' => 'No se pudo leer el archivo CSV.']);
        }

        $headers = array_map(fn($h) => $this->normalizeHeader($h), $rawHeaders);

        // Verificar columna de teléfono (acepta: telefono, celular, movil, tel)
        $phoneCol = collect($headers)->first(fn($h) => in_array($h, ['telefono', 'celular', 'movil', 'tel', 'phone', 'numero']));
        if (!$phoneCol) {
            return back()->withErrors(['csv_file' => 'Falta la columna de teléfono (telefono, celular o movil).']);
        }

        // Precargar ciudades por código DANE
        $citiesMapByCode = City::whereNotNull('code')->pluck('id', 'code')->toArray();

        // Cachés para búsqueda por nombre
        $deptCache = [];
        $cityCache = [];

        $privacyDoc    = LegalDocument::getActive('privacy');
        $smsConsentDoc = LegalDocument::getActive('sms_consent');

        $created    = 0;
        $updated    = 0;
        $duplicates = 0;
        $errors     = [];
        $rowNum     = 1;

        while (($row = fgetcsv($handle, 0, $separator)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) continue;

            // Mapear fila a array asociativo
            $data = [];
            foreach ($headers as $i => $col) {
                $data[$col] = isset($row[$i]) ? trim($row[$i]) : '';
            }

            // Resolver teléfono (acepta múltiples nombres de columna)
            $phone = preg_replace('/\D/', '', $data[$phoneCol] ?? '');
            $name  = ucwords(strtolower($this->resolveField($data, ['nombre', 'name', 'nombres']) ?? ''));

            if (empty($phone) || strlen($phone) < 7) {
                $errors[] = "Fila {$rowNum}: teléfono inválido.";
                continue;
            }

            $docNumber = $this->resolveField($data, ['numero_documento', 'documento', 'cedula', 'document_number']) ?: null;

            // Detectar duplicado por teléfono o documento
            $exists = Customer::where('phone', $phone)
                ->orWhere(function ($q) use ($docNumber) {
                    if ($docNumber) $q->where('document_number', $docNumber);
                })
                ->exists();

            if ($exists) {
                $duplicates++;
                continue;
            }

            // Resolver ciudad — prioridad: código DANE → nombre + departamento → nombre solo
            $cityId   = null;
            $cityCode = $this->resolveField($data, ['codigo_ciudad', 'ciudad_codigo', 'dane', 'codigo_dane']) ?: null;
            $cityName = $this->resolveField($data, ['ciudad', 'city']) ?: null;
            $deptName = $this->resolveField($data, ['departamento', 'department', 'depto', 'dept']) ?: null;

            if ($cityCode && isset($citiesMapByCode[$cityCode])) {
                $cityId = $citiesMapByCode[$cityCode];
            } elseif ($cityName) {
                $cityId = $this->resolveCityByName($cityName, $deptName, $deptCache, $cityCache);
            }

            $acceptPrivacy = in_array(strtolower($this->resolveField($data, ['acepta_datos', 'aceptacion_datos', 'autoriza_datos']) ?? ''), ['1', 'si', 'sí', 'yes', 'true']);
            $acceptSms     = in_array(strtolower($this->resolveField($data, ['acepta_sms', 'sms']) ?? ''), ['1', 'si', 'sí', 'yes', 'true']);

            try {
                DB::transaction(function () use (
                    $data, $name, $phone, $phoneCol, $docNumber, $cityId,
                    $acceptPrivacy, $acceptSms, $privacyDoc, $smsConsentDoc, &$created
                ) {
                    $customer = Customer::create([
                        'name'            => $name ?: 'Sin nombre',
                        'lastname'        => ucwords(strtolower($this->resolveField($data, ['apellido', 'apellidos', 'lastname']) ?? '')) ?: null,
                        'document_type'   => strtoupper($this->resolveField($data, ['tipo_documento', 'tipo_doc']) ?? '') ?: null,
                        'document_number' => $docNumber ?: null,
                        'phone'           => $phone,
                        'email'           => filter_var($this->resolveField($data, ['correo', 'email', 'mail']) ?? '', FILTER_VALIDATE_EMAIL) ?: null,
                        'city_id'         => $cityId,
                        'status'          => 'active',
                        'created_via'     => 'import',
                        'data_treatment_accepted'    => $acceptPrivacy,
                        'data_treatment_accepted_at' => $acceptPrivacy ? now() : null,
                    ]);

                    if ($acceptPrivacy && $privacyDoc) {
                        DocumentAcceptance::create([
                            'customer_id'       => $customer->id,
                            'legal_document_id' => $privacyDoc->id,
                            'channel'           => 'import',
                            'accepted_at'       => now(),
                        ]);
                    }

                    if ($acceptSms && $smsConsentDoc) {
                        DocumentAcceptance::create([
                            'customer_id'       => $customer->id,
                            'legal_document_id' => $smsConsentDoc->id,
                            'channel'           => 'import',
                            'accepted_at'       => now(),
                        ]);
                    }

                    $created++;
                });
            } catch (\Throwable $e) {
                $errors[] = "Fila {$rowNum}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        $summary = "Importación completada: {$created} clientes nuevos, {$duplicates} duplicados omitidos.";
        if (!empty($errors)) {
            $summary .= ' ' . count($errors) . ' filas con error.';
        }

        return redirect()->route('admin.customers.index')
            ->with('success', $summary)
            ->with('import_errors', array_slice($errors, 0, 20));
    }

    // ── Plantilla CSV ─────────────────────────────────────────────────────────

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_clientes.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // BOM para compatibilidad con Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'nombre', 'apellido', 'tipo_documento', 'numero_documento',
                'celular', 'correo', 'departamento', 'ciudad',
                'acepta_datos', 'acepta_sms',
            ], ';');

            // Filas de ejemplo
            fputcsv($handle, ['María',  'Pérez',  'CC', '1234567890', '3001234567', 'maria@email.com',  'Cundinamarca',    'Bogotá',   'si', 'si'], ';');
            fputcsv($handle, ['Carlos', 'López',  'CC', '9876543210', '3109876543', '',                 'Antioquia',       'Medellín', 'si', 'no'], ';');
            fputcsv($handle, ['Ana',    'García', 'CE', '',           '3201112233', 'ana@email.com',    'Valle del Cauca', 'Cali',     'si', 'no'], ';');
            fputcsv($handle, ['Luis',   'Torres', 'CC', '',           '3151234567', '',                 '',                '',         'no', 'no'], ';');

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Bloqueo ───────────────────────────────────────────────────────────────

    public function block(Customer $customer)
    {
        $customer->update(['status' => 'blocked']);
        return back()->with('success', 'Cliente bloqueado.');
    }

    public function unblock(Customer $customer)
    {
        $customer->update(['status' => 'active']);
        return back()->with('success', 'Cliente desbloqueado.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Devuelve el primer valor no vacío del array $data cuya clave coincida con alguna de $keys. */
    private function resolveField(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!empty($data[$key])) return $data[$key];
        }
        return null;
    }

    /** Resuelve ciudad por nombre de texto, opcionalmente acotada a un departamento. */
    private function resolveCityByName(string $cityName, ?string $deptName, array &$deptCache, array &$cityCache): ?int
    {
        $deptId = null;
        if ($deptName) {
            $deptKey = mb_strtolower(trim($deptName));
            if (!array_key_exists($deptKey, $deptCache)) {
                $dept = Department::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($deptName) . '%'])->first();
                $deptCache[$deptKey] = $dept?->id;
            }
            $deptId = $deptCache[$deptKey];
        }

        $cityKey = mb_strtolower(trim($cityName)) . ($deptId ? "_{$deptId}" : '');
        if (!array_key_exists($cityKey, $cityCache)) {
            $q = City::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($cityName) . '%']);
            if ($deptId) $q->where('department_id', $deptId);
            $cityCache[$cityKey] = $q->first()?->id;
        }
        return $cityCache[$cityKey];
    }

    private function normalizeHeader(string $h): string
    {
        $h = mb_strtolower(trim($h));
        $h = strtr($h, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
        return preg_replace('/\s+/', '_', $h);
    }
}
