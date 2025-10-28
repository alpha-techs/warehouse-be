<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\BaseRequest;

final class UploadMediaImageRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'image',
                'max:10240', // 10MB
            ],
        ];
    }
}
