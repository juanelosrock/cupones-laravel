<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentAcceptance;
use App\Models\LegalDocument;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LegalDocumentController extends Controller
{
    private array $typeLabels = [
        'terms'       => 'Términos y Condiciones',
        'privacy'     => 'Política de Privacidad',
        'sms_consent' => 'Consentimiento SMS',
        'commercial'  => 'Comunicaciones Comerciales',
    ];

    public function index()
    {
        $documents = LegalDocument::with('createdBy')
            ->latest()
            ->get()
            ->groupBy('type');

        $acceptanceCounts = DocumentAcceptance::selectRaw('legal_document_id, COUNT(*) as total')
            ->groupBy('legal_document_id')
            ->pluck('total', 'legal_document_id');

        $stats = [
            'total'    => LegalDocument::count(),
            'active'   => LegalDocument::where('is_active', true)->count(),
            'accepted' => DocumentAcceptance::count(),
            'types'    => LegalDocument::distinct('type')->count('type'),
        ];

        return view('admin.legal-documents.index', compact('documents', 'acceptanceCounts', 'stats'));
    }

    public function create()
    {
        $typeLabels     = $this->typeLabels;
        $latestVersions = LegalDocument::selectRaw('type, MAX(version) as max_version')
            ->groupBy('type')
            ->pluck('max_version', 'type');

        return view('admin.legal-documents.create', compact('typeLabels', 'latestVersions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|in:terms,privacy,sms_consent,commercial',
            'title'   => 'required|string|max:200',
            'content' => 'required|string',
            'version' => 'required|string|max:20',
        ], [
            'type.in' => 'Tipo de documento no válido.',
        ]);

        $exists = LegalDocument::where('type', $data['type'])
            ->where('version', $data['version'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['version' => "Ya existe la versión {$data['version']} para este tipo de documento."]);
        }

        $data['created_by_user_id'] = auth()->id();
        $doc = LegalDocument::create($data);
        AuditService::log('created', LegalDocument::class, $doc->id, [], $doc->toArray());

        return redirect()->route('admin.legal-documents.show', $doc)
            ->with('success', "Documento \"{$doc->title}\" v{$doc->version} creado. Publícalo para que sea la versión activa.");
    }

    public function show(LegalDocument $legalDocument)
    {
        $legalDocument->load('createdBy');

        $acceptances = DocumentAcceptance::with('customer')
            ->where('legal_document_id', $legalDocument->id)
            ->latest('accepted_at')
            ->paginate(20);

        $acceptanceStats = [
            'total' => DocumentAcceptance::where('legal_document_id', $legalDocument->id)->count(),
            'web'   => DocumentAcceptance::where('legal_document_id', $legalDocument->id)->where('channel', 'web')->count(),
            'sms'   => DocumentAcceptance::where('legal_document_id', $legalDocument->id)->where('channel', 'sms')->count(),
            'api'   => DocumentAcceptance::where('legal_document_id', $legalDocument->id)->where('channel', 'api')->count(),
        ];

        $typeLabel = $this->typeLabels[$legalDocument->type] ?? $legalDocument->type;

        $otherVersions = LegalDocument::where('type', $legalDocument->type)
            ->where('id', '!=', $legalDocument->id)
            ->orderByDesc('created_at')
            ->get(['id', 'version', 'is_active', 'published_at', 'created_at']);

        return view('admin.legal-documents.show', compact(
            'legalDocument', 'acceptances', 'acceptanceStats', 'typeLabel', 'otherVersions'
        ));
    }

    public function publish(LegalDocument $legalDocument)
    {
        LegalDocument::where('type', $legalDocument->type)
            ->where('id', '!=', $legalDocument->id)
            ->update(['is_active' => false]);

        $legalDocument->update(['is_active' => true, 'published_at' => now()]);
        AuditService::log('published', LegalDocument::class, $legalDocument->id, [], ['is_active' => true]);

        return back()->with('success', "Versión {$legalDocument->version} publicada como activa.");
    }

    public function destroy(LegalDocument $legalDocument)
    {
        if ($legalDocument->is_active) {
            return back()->with('error', 'No puedes eliminar el documento activo. Publica otra versión primero.');
        }

        $acceptances = DocumentAcceptance::where('legal_document_id', $legalDocument->id)->count();
        if ($acceptances > 0) {
            return back()->with('error', "No se puede eliminar: tiene {$acceptances} aceptación(es) registrada(s).");
        }

        AuditService::log('deleted', LegalDocument::class, $legalDocument->id, $legalDocument->toArray(), []);
        $legalDocument->delete();

        return redirect()->route('admin.legal-documents.index')
            ->with('success', 'Documento eliminado.');
    }
}
