<?php

namespace App\Services;

use Str;

trait SnakeCaseData
{
    public function snakeCaseData(array $data): array
    {
        $snakeCaseData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $snakeCaseData[Str::snake($key)] = $this->snakeCaseData($value);
            } else {
                $snakeCaseData[Str::snake($key)] = $value;
            }
        }
        return $snakeCaseData;
    }
}
