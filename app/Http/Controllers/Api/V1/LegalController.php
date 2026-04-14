<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\JsonResponse;

class LegalController extends Controller
{
    public function show(string $type): JsonResponse
    {
        $validTypes = ['privacy', 'terms', 'sms_consent', 'commercial'];

        if (!in_array($type, $validTypes)) {
            return response()->json([
                'error'   => 'invalid_type',
                'message' => 'Tipo de documento inválido. Valores permitidos: ' . implode(', ', $validTypes) . '.',
            ], 422);
        }

        $doc = LegalDocument::getActive($type);

        if (!$doc) {
            return response()->json([
                'error'   => 'not_found',
                'message' => 'No hay un documento de tipo "' . $type . '" publicado actualmente.',
            ], 404);
        }

        return response()->json([
            'type'         => $doc->type,
            'version'      => $doc->version,
            'title'        => $doc->title,
            'content'      => $doc->content,
            'published_at' => $doc->published_at?->toIso8601String(),
            'meta'         => [
                'request_id'   => (string) \Illuminate\Support\Str::uuid(),
                'processed_at' => now()->toIso8601String(),
            ],
        ]);
    }
}