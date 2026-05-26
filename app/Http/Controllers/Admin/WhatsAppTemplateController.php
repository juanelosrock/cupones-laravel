<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    public function index(WhatsAppService $whatsApp)
    {
        $raw       = $whatsApp->getTemplates();
        $templates = collect($raw)
            ->filter(fn($t) => strtolower($t['channel'] ?? '') === 'whatsapp')
            ->map(fn($t) => $this->format($t))
            ->sortBy('name')
            ->values();

        $driver = \App\Models\Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');

        return view('admin.whatsapp-templates.index', compact('templates', 'driver'));
    }

    public function create()
    {
        $driver = \App\Models\Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');
        return view('admin.whatsapp-templates.create', compact('driver'));
    }

    public function store(Request $request, WhatsAppService $whatsApp)
    {
        $data = $request->validate([
            'name'                        => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'locale'                      => 'required|string|max:10',
            'category'                    => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'header_type'                 => 'nullable|in:TEXT,IMAGE,VIDEO,DOCUMENT',
            'header_text'                 => 'nullable|string|max:60',
            'header_media_url'            => 'nullable|url|max:2048',
            'body'                        => 'required|string|max:1024',
            'body_examples'               => 'nullable|array',
            'body_examples.*'             => 'nullable|string|max:100',
            'footer'                      => 'nullable|string|max:60',
            'buttons'                     => 'nullable|array|max:3',
            'buttons.*.type'              => 'required|in:QUICK_REPLY,URL,PHONE_NUMBER',
            'buttons.*.text'              => 'required|string|max:25',
            'buttons.*.url'               => 'nullable|url|max:2000',
            'buttons.*.phone_number'      => 'nullable|string|max:20',
        ]);

        $components = $this->buildComponents($data);

        $result = $whatsApp->createTemplate([
            'name'       => $data['name'],
            'locale'     => $data['locale'],
            'category'   => $data['category'],
            'components' => $components,
        ]);

        if (!$result['success']) {
            return back()->withInput()->with('error', 'Error al crear la plantilla: ' . $result['message']);
        }

        return redirect()->route('admin.whatsapp-templates.index')
            ->with('success', "Plantilla \"{$data['name']}\" enviada a Meta para aprobación. Estado inicial: PENDING.");
    }

    public function destroy(string $templateId, WhatsAppService $whatsApp)
    {
        $result = $whatsApp->deleteTemplate($templateId);

        if (!$result['success']) {
            return back()->with('error', 'Error al eliminar: ' . ($result['message'] ?? 'Error desconocido'));
        }

        return back()->with('success', 'Plantilla eliminada correctamente.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buildComponents(array $data): array
    {
        // Zenvia POST /v2/templates expects components as a JSON *object* keyed by
        // lowercase type name, not an array — despite GET returning an array.
        $components = [];

        // HEADER — type = content format (TEXT | IMAGE | VIDEO | DOCUMENT)
        if (!empty($data['header_type'])) {
            $header = ['type' => $data['header_type']];
            if ($data['header_type'] === 'TEXT') {
                $header['text'] = $data['header_text'] ?? '';
            } elseif (!empty($data['header_media_url'])) {
                $header['example'] = ['header_handle' => [$data['header_media_url']]];
            }
            $components['header'] = $header;
        }

        // BODY — type is always TEXT
        $body = ['type' => 'TEXT', 'text' => $data['body']];
        $examples = array_values(array_filter($data['body_examples'] ?? [], fn($v) => $v !== '' && $v !== null));
        if (!empty($examples)) {
            $body['example'] = ['body_text' => [$examples]];
        }
        $components['body'] = $body;

        // FOOTER — type is always TEXT
        if (!empty($data['footer'])) {
            $components['footer'] = ['type' => 'TEXT', 'text' => $data['footer']];
        }

        // BUTTONS
        $buttons = array_values(array_filter($data['buttons'] ?? [], fn($b) => !empty($b['type']) && !empty($b['text'])));
        if (!empty($buttons)) {
            $built = [];
            foreach ($buttons as $btn) {
                $b = ['type' => $btn['type'], 'text' => $btn['text']];
                if ($btn['type'] === 'URL' && !empty($btn['url'])) {
                    $b['url'] = $btn['url'];
                }
                if ($btn['type'] === 'PHONE_NUMBER' && !empty($btn['phone_number'])) {
                    $b['phone_number'] = $btn['phone_number'];
                }
                $built[] = $b;
            }
            $components['buttons'] = $built;
        }

        return $components;
    }

    private function format(array $t): array
    {
        $body = '';
        $vars = [];
        $header = null;
        $footer = null;
        $buttons = [];

        foreach ($t['components'] ?? [] as $comp) {
            $type = strtoupper($comp['type'] ?? '');
            if ($type === 'BODY') {
                $body = $comp['text'] ?? '';
                preg_match_all('/\{\{(\w+)\}\}/', $body, $m);
                $vars = $m[1] ?? [];
            } elseif ($type === 'HEADER') {
                $header = ['format' => $comp['format'] ?? 'TEXT', 'text' => $comp['text'] ?? ''];
            } elseif ($type === 'FOOTER') {
                $footer = $comp['text'] ?? '';
            } elseif ($type === 'BUTTONS') {
                $buttons = $comp['buttons'] ?? [];
            }
        }

        return [
            'id'        => $t['id'],
            'name'      => $t['name'] ?? $t['id'],
            'category'  => $t['category'] ?? '',
            'status'    => $t['status'] ?? '',
            'locale'    => $t['locale'] ?? '',
            'body'      => $body,
            'variables' => array_values(array_unique($vars)),
            'header'    => $header,
            'footer'    => $footer,
            'buttons'   => $buttons,
            'raw'       => $t,
        ];
    }
}
