<?php

namespace App\Http\Controllers;

use App\Models\InboundReport;
use App\Models\InventoryReport;
use App\Models\InvoicePrint;
use App\Models\NameChangeReport;
use App\Models\OrderPrint;
use App\Models\OutboundReport;
use App\Support\DocumentStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentDownloadController extends Controller
{
    private const DOCUMENT_MAP = [
        'inventory-report' => InventoryReport::class,
        'outbound-report' => OutboundReport::class,
        'inbound-report' => InboundReport::class,
        'name-change-report' => NameChangeReport::class,
        'invoice-print' => InvoicePrint::class,
        'order-print' => OrderPrint::class,
    ];

    public function show(string $type, string $id): JsonResponse
    {
        $document = $this->resolveDocument($type, $id);

        if (! method_exists($document, 'isCompleted') || ! method_exists($document, 'getDownloadUrl')) {
            abort(404, 'Document does not support downloads');
        }

        if (! $document->isCompleted() || ! $document->file_path) {
            abort(404, 'Document not ready for download');
        }

        $disk = method_exists($document, 'getStorageDisk')
            ? $document->getStorageDisk()
            : config('filesystems.default', 'public');

        if (! Storage::disk($disk)->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        $downloadUrl = $document->getDownloadUrl();

        if (! $downloadUrl) {
            abort(500, 'Unable to generate download URL');
        }

        $expiresAt = data_get($document, 'expires_at');
        if (! $expiresAt && method_exists($document, 'isS3Storage') && $document->isS3Storage()) {
            $expiresAt = now()->addSeconds(DocumentStorage::temporaryUrlTtl());
        }

        return response()->json([
            'type' => $type,
            'id' => $document->getKey(),
            'download_url' => $downloadUrl,
            'storage' => $document->storage ?? null,
            'disk' => $disk,
            'expires_at' => $expiresAt?->toIso8601String(),
        ]);
    }

    private function resolveDocument(string $type, string $id): Model
    {
        $modelClass = self::DOCUMENT_MAP[$type] ?? null;

        if (! $modelClass) {
            abort(404, 'Unknown document type');
        }

        /** @var Model $model */
        $model = $modelClass::query()->findOrFail($id);
        return $model;
    }
}
