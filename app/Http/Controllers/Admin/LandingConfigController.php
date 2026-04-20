<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingConfigController extends Controller
{
    public function index()
    {
        $configs = LandingPageConfig::latest()->get();
        return view('admin.landing-configs.index', compact('configs'));
    }

    public function create()
    {
        $config = null;
        return view('admin.landing-configs.form', compact('config'));
    }

    public function store(Request $request)
    {
        $data = $this->validateConfig($request);
        $data = $this->handleImageUploads($request, $data);

        if ($data['is_default'] ?? false) {
            LandingPageConfig::where('is_default', true)->update(['is_default' => false]);
        }

        LandingPageConfig::create($data);

        return redirect()->route('admin.landing-configs.index')
            ->with('success', 'Landing page creada correctamente.');
    }

    public function edit(LandingPageConfig $landingConfig)
    {
        $config = $landingConfig;
        return view('admin.landing-configs.form', compact('config'));
    }

    public function update(Request $request, LandingPageConfig $landingConfig)
    {
        $data = $this->validateConfig($request, $landingConfig);
        $data = $this->handleImageUploads($request, $data, $landingConfig);

        if ($data['is_default'] ?? false) {
            LandingPageConfig::where('is_default', true)
                ->where('id', '!=', $landingConfig->id)
                ->update(['is_default' => false]);
        }

        $landingConfig->update($data);

        return redirect()->route('admin.landing-configs.index')
            ->with('success', 'Landing page actualizada correctamente.');
    }

    public function destroy(LandingPageConfig $landingConfig)
    {
        // Don't delete if campaigns are using it
        if ($landingConfig->smsCampaigns()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay campañas SMS que usan esta landing.');
        }

        // Delete uploaded images
        foreach (['logo_url', 'hero_image_url'] as $field) {
            if ($landingConfig->$field && str_starts_with($landingConfig->$field, '/storage/landing-images/')) {
                Storage::disk('public')->delete('landing-images/' . basename($landingConfig->$field));
            }
        }

        $landingConfig->delete();
        return redirect()->route('admin.landing-configs.index')
            ->with('success', 'Landing page eliminada.');
    }

    public function preview(LandingPageConfig $landingConfig)
    {
        $landingConfig->load('smsCampaigns.couponBatch');
        return view('admin.landing-configs.preview', ['landingConfig' => $landingConfig]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validateConfig(Request $request, ?LandingPageConfig $existing = null): array
    {
        return $request->validate([
            'name'            => 'required|string|max:150',
            'template'        => 'required|in:minimal,branded,hero,promo',
            'brand_color'     => 'required|string|max:20',
            'bg_color'        => 'required|string|max:20',
            'heading'         => 'required|string|max:200',
            'subheading'      => 'nullable|string|max:500',
            'body_html'       => 'nullable|string',
            'button_text'     => 'required|string|max:100',
            'success_heading' => 'required|string|max:200',
            'success_text'    => 'required|string|max:500',
            'footer_text'     => 'nullable|string|max:300',
            'is_default'      => 'nullable|boolean',
            // Image URLs (if not uploading a file)
            'logo_url_input'       => 'nullable|url|max:500',
            'hero_image_url_input' => 'nullable|url|max:500',
            // File uploads
            'logo_file'       => 'nullable|image|max:2048',
            'hero_file'       => 'nullable|image|max:5120',
            // Keep existing flag
            'keep_logo'       => 'nullable|boolean',
            'keep_hero'       => 'nullable|boolean',
        ]);
    }

    private function handleImageUploads(Request $request, array $data, ?LandingPageConfig $existing = null): array
    {
        foreach ([
            ['file' => 'logo_file',  'url_input' => 'logo_url_input',  'field' => 'logo_url',       'keep' => 'keep_logo'],
            ['file' => 'hero_file',  'url_input' => 'hero_image_url_input', 'field' => 'hero_image_url', 'keep' => 'keep_hero'],
        ] as $img) {
            if ($request->hasFile($img['file'])) {
                // Delete old file if it was an upload
                if ($existing && $existing->{$img['field']} && str_starts_with($existing->{$img['field']}, '/storage/')) {
                    Storage::disk('public')->delete('landing-images/' . basename($existing->{$img['field']}));
                }
                $path = $request->file($img['file'])->store('landing-images', 'public');
                $data[$img['field']] = '/storage/' . $path;
            } elseif (!empty($data[$img['url_input']])) {
                $data[$img['field']] = $data[$img['url_input']];
            } elseif ($existing && ($data[$img['keep']] ?? false)) {
                $data[$img['field']] = $existing->{$img['field']};
            } else {
                $data[$img['field']] = null;
            }
            unset($data[$img['url_input']], $data[$img['keep']], $data[$img['file']]);
        }

        return $data;
    }
}
