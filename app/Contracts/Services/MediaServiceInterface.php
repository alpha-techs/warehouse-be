<?php

namespace App\Contracts\Services;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface MediaServiceInterface
{
    public function uploadImage(UploadedFile $file): array;

    public function streamImage(string $id): StreamedResponse;
}
