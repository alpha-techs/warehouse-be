<?php

namespace App\Http\Requests\Container;

use App\Contracts\Models\ContainerStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

final class GetContainersRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'itemsPerPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'containerNumber' => ['nullable', 'string', 'max:255'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => [Rule::enum(ContainerStatus::class)],
        ];
    }
}
