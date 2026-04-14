<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DocumentAcceptance;
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

        return view('public.consent-landing', compact('recipient', 'legalDoc'));
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

        $request->validate([
            'accept_data'  => 'required|accepted',
            'accept_terms' => 'required|accepted',
        ], [
            'accept_data.required'  => 'Debes aceptar el tratamiento de datos.',
            'accept_data.accepted'  => 'Debes aceptar el tratamiento de datos.',
            'accept_terms.required' => 'Debes aceptar los términos y condiciones.',
            'accept_terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ]);

        $customer = $recipient->customer;
        $ip       = $request->ip();

        // Registrar consentimiento en el recipient
        $recipient->update([
            'consent_accepted_at' => now(),
            'acceptance_ip'       => $ip,
        ]);

        // Actualizar el cliente con consentimiento de datos
        if ($customer) {
            $customer->update([
                'data_treatment_accepted'    => true,
                'data_treatment_accepted_at' => now(),
                'acceptance_ip'              => $ip,
            ]);

            // Registrar en document_acceptances si existe el documento legal
            $legalDoc = LegalDocument::where('type', 'sms_consent')
                ->where('is_active', true)
                ->latest()
                ->first();

            if ($legalDoc) {
                DocumentAcceptance::firstOrCreate(
                    ['customer_id' => $customer->id, 'legal_document_id' => $legalDoc->id],
                    [
                        'accepted_at' => now(),
                        'ip_address'  => $ip,
                        'user_agent'  => $request->userAgent(),
                        'channel'     => 'sms',
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
