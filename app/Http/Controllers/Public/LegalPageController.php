<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;

class LegalPageController extends Controller
{
    public function terms()
    {
        $doc = LegalDocument::getActive('terms');
        return view('public.legal', ['doc' => $doc, 'type' => 'terms', 'title' => 'Términos y Condiciones']);
    }

    public function privacy()
    {
        $doc = LegalDocument::getActive('privacy');
        return view('public.legal', ['doc' => $doc, 'type' => 'privacy', 'title' => 'Política de Privacidad']);
    }

    public function smsConsent()
    {
        $doc = LegalDocument::getActive('sms_consent');
        return view('public.legal', ['doc' => $doc, 'type' => 'sms_consent', 'title' => 'Consentimiento SMS']);
    }

    public function accept(string $type)
    {
        $doc = LegalDocument::getActive($type);
        if (!$doc) abort(404);
        return view('public.accept-terms', compact('doc', 'type'));
    }

    public function storeAcceptance(\Illuminate\Http\Request $request, string $type)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'document_number' => 'required|string|max:30',
            'accept' => 'required|accepted',
        ]);

        $doc = LegalDocument::getActive($type);
        if (!$doc) abort(404);

        $customer = \App\Models\Customer::firstOrCreate(
            ['phone' => $data['phone']],
            ['name' => $data['name'], 'document_number' => $data['document_number'],
             'status' => 'active', 'created_via' => 'web',
             'data_treatment_accepted' => true, 'data_treatment_accepted_at' => now(),
             'acceptance_ip' => $request->ip()]
        );

        \App\Models\DocumentAcceptance::firstOrCreate(
            ['customer_id' => $customer->id, 'legal_document_id' => $doc->id],
            ['accepted_at' => now(), 'ip_address' => $request->ip(),
             'user_agent' => $request->userAgent(), 'channel' => 'web']
        );

        return redirect()->route('public.legal.accept', $type)
            ->with('success', '¡Gracias! Tu aceptación ha sido registrada correctamente.');
    }
}