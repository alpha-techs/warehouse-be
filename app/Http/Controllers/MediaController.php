<?php

namespace App\Http\Controllers;

use App\Contracts\Services\MediaServiceInterface;
use App\Http\Requests\Media\UploadMediaImageRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MediaController extends Controller
{
    public function uploadImage(
        UploadMediaImageRequest $request,
        MediaServiceInterface $mediaService,
    ): JsonResponse {
        $file = $request->file('file');
        $uploaded = $mediaService->uploadImage($file);

        return response()->json($uploaded);
    }

    public function showImage(
        string $imageId,
        MediaServiceInterface $mediaService,
    ): StreamedResponse {
        return $mediaService->streamImage($imageId);
    }
}
