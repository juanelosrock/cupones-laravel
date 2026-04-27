<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;

class LegalController extends Controller
{
    private array $validTypes = ['privacy', 'terms', 'sms_consent', 'commercial'];

    public function show(string $type): JsonResponse
    {
        if (!in_array($type, $this->validTypes)) {
            return $this->invalidType();
        }

        $doc = LegalDocument::getActive($type);

        if (!$doc) {
            return response()->json([
                'error'   => 'not_found',
                'message' => 'No hay un documento de tipo "' . $type . '" publicado actualmente.',
            ], 404);
        }

        return response()->json($this->format($doc));
    }

    public function history(string $type): JsonResponse
    {
        if (!in_array($type, $this->validTypes)) {
            return $this->invalidType();
        }

        $docs = LegalDocument::where('type', $type)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get(['id', 'type', 'version', 'title', 'is_active', 'published_at']);

        return response()->json([
            'type'     => $type,
            'total'    => $docs->count(),
            'versions' => $docs->map(fn($d) => [
                'id'           => $d->id,
                'version'      => $d->version,
                'title'        => $d->title,
                'is_active'    => (bool) $d->is_active,
                'published_at' => $d->published_at?->toIso8601String(),
            ]),
            'meta' => [
                'request_id'   => (string) \Illuminate\Support\Str::uuid(),
                'processed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function version(string $type, string $version): JsonResponse
    {
        if (!in_array($type, $this->validTypes)) {
            return $this->invalidType();
        }

        $doc = LegalDocument::where('type', $type)
            ->where('version', $version)
            ->whereNotNull('published_at')
            ->first();

        if (!$doc) {
            return response()->json([
                'error'   => 'not_found',
                'message' => "No existe versión \"{$version}\" publicada para el documento \"{$type}\".",
            ], 404);
        }

        return response()->json($this->format($doc));
    }

    private function format(LegalDocument $doc): array
    {
        return [
            'type'         => $doc->type,
            'version'      => $doc->version,
            'title'        => $doc->title,
            'content'      => $doc->content,
            'is_active'    => (bool) $doc->is_active,
            'published_at' => $doc->published_at?->toIso8601String(),
            'meta'         => [
                'request_id'   => (string) \Illuminate\Support\Str::uuid(),
                'processed_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function invalidType(): JsonResponse
    {
        return response()->json([
            'error'   => 'invalid_type',
            'message' => 'Tipo de documento inválido. Valores permitidos: ' . implode(', ', $this->validTypes) . '.',
        ], 422);
    }
}
