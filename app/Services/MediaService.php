<?php

namespace App\Services;

use App\Contracts\Services\MediaServiceInterface;
use App\Models\Media;
use App\Support\DocumentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class MediaService implements MediaServiceInterface
{
    public function uploadImage(UploadedFile $file): array
    {
        $disk = $this->resolveDisk();
        $mediaId = (string) Str::ulid();

        $path = $file->store($this->directory(), [
            'disk' => $disk,
            'visibility' => 'private',
        ]);

        $media = Media::create([
            'id' => $mediaId,
            'collection' => 'images',
            'disk' => $disk,
            'path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'content_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'visibility' => 'private',
        ]);

        return [
            'mediaId' => $mediaId,
            'url' => route('media.images.show', ['imageId' => $mediaId]),
            'fileName' => $media->file_name,
            'contentType' => $media->content_type,
            'size' => $media->size,
        ];
    }

    public function streamImage(string $id): StreamedResponse
    {
        $normalizedId = strtoupper(trim($id));

        if (! preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $normalizedId)) {
            throw new NotFoundHttpException('Media file not found');
        }

        $media = Media::query()->findOrFail($normalizedId);

        $disk = $media->disk;
        $path = $media->path;

        if (! Storage::disk($disk)->exists($path)) {
            throw new NotFoundHttpException('Media file not found');
        }

        $headers = [];
        if (! empty($media->content_type)) {
            $headers['Content-Type'] = $media->content_type;
        } else {
            $mimeType = Storage::disk($disk)->mimeType($path);
            if ($mimeType) {
                $headers['Content-Type'] = $mimeType;
            }
        }

        if (! empty($media->size)) {
            $headers['Content-Length'] = $media->size;
        }

        $headers['Content-Disposition'] = 'inline; filename="' . addslashes($media->file_name) . '"';

        return Storage::disk($disk)->response(
            $path,
            null,
            $headers
        );
    }

    private function resolveDisk(): string
    {
        return DocumentStorage::disk(DocumentStorage::S3);
    }

    private function directory(): string
    {
        $datePath = now()->format('Y');

        return trim(config('media.images_directory', 'uploads/images'), '/') . '/' . $datePath;
    }
}
