<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DocumentAcceptance;
use App\Models\LandingPageConfig;
use App\Models\LegalDocument;
use App\Models\SmsRecipient;
use Illuminate\Http\Request;

class ConsentLandingController extends Controller
{
    public function show(string $token)
    {
        $recipient = SmsRecipient::with(['campaign.couponBatch', 'campaign.landingConfig', 'customer'])
            ->where('consent_token', $token)
            ->firstOrFail();

        $legalDoc = LegalDocument::where('type', 'sms_consent')
            ->where('is_active', true)
            ->latest()
            ->first();

        return view('public.consent-landing', [
            'recipient' => $recipient,
            'legalDoc'  => $legalDoc,
            'accepted'  => false,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $recipient = SmsRecipient::with(['campaign.couponBatch', 'campaign.landingConfig', 'customer'])
            ->where('consent_token', $token)
            ->firstOrFail();

        // Si ya aceptó, solo mostrar el código
        if ($recipient->hasAcceptedConsent()) {
            return view('public.consent-landing', [
                'recipient' => $recipient,
                'legalDoc'  => null,
                'accepted'  => true,
            ]);
        }

        $lc       = $recipient->campaign?->landingConfig ?? LandingPageConfig::getDefault();
        $isPromo  = ($lc?->template === 'promo');

        if ($isPromo) {
            $request->validate([
                'phone'      => 'required|string|max:20',
                'email'      => 'nullable|email|max:150',
                'accept_all' => 'required|accepted',
            ], [
                'phone.required'      => 'El número de teléfono es obligatorio.',
                'accept_all.required' => 'Debes aceptar las políticas de datos.',
                'accept_all.accepted' => 'Debes aceptar las políticas de datos.',
            ]);

            // Normalizar teléfono (solo dígitos)
            $phone    = preg_replace('/\D/', '', $request->phone);
            $customer = Customer::where('phone', $phone)->first();

            if ($customer) {
                // Actualizar email si se proporcionó
                if ($request->filled('email')) {
                    $customer->update(['email' => $request->email]);
                }
            } else {
                // Crear nuevo cliente con datos mínimos
                $customer = Customer::create([
                    'name'       => 'Cliente',
                    'phone'      => $phone,
                    'email'      => $request->email ?? null,
                    'status'     => 'active',
                    'created_via' => 'landing',
                ]);
            }

            // Vincular cliente al recipient
            $recipient->update(['customer_id' => $customer->id]);
            $recipient->setRelation('customer', $customer);
        } else {
            $request->validate([
                'accept_data'  => 'required|accepted',
                'accept_terms' => 'required|accepted',
            ], [
                'accept_data.required'  => 'Debes aceptar el tratamiento de datos.',
                'accept_data.accepted'  => 'Debes aceptar el tratamiento de datos.',
                'accept_terms.required' => 'Debes aceptar los términos y condiciones.',
                'accept_terms.accepted' => 'Debes aceptar los términos y condiciones.',
            ]);
        }

        $customer = $recipient->customer;
        $ip       = $request->ip();

        // Registrar consentimiento en el recipient
        $recipient->update([
            'consent_accepted_at' => now(),
            'acceptance_ip'       => $ip,
        ]);

        if ($customer) {
            $customer->update([
                'data_treatment_accepted'    => true,
                'data_treatment_accepted_at' => now(),
                'acceptance_ip'              => $ip,
            ]);

            // Promo acepta todos los tipos de documentos legales activos; otros solo sms_consent
            $docTypes = $isPromo ? ['terms', 'privacy', 'sms_consent'] : ['sms_consent'];
            $channel  = $isPromo ? 'web' : 'sms';

            $legalDocs = LegalDocument::whereIn('type', $docTypes)
                ->where('is_active', true)
                ->latest()
                ->get();

            foreach ($legalDocs as $doc) {
                DocumentAcceptance::firstOrCreate(
                    ['customer_id' => $customer->id, 'legal_document_id' => $doc->id],
                    [
                        'accepted_at' => now(),
                        'ip_address'  => $ip,
                        'user_agent'  => $request->userAgent(),
                        'channel'     => $channel,
                    ]
                );
            }
        }

        return view('public.consent-landing', [
            'recipient' => $recipient->fresh(['campaign.couponBatch', 'campaign.landingConfig', 'customer']),
            'legalDoc'  => null,
            'accepted'  => true,
        ]);
    }
}
