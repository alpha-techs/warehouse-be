<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\BaseResource;

final class CommonProductResource extends BaseResource
{
    protected function compose(): array
    {
        $resource = $this->resource->toArray();
        $images = collect($this->resource->images ?? []);

        unset(
            $resource['dimension_description'],
            $resource['length'],
            $resource['width'],
            $resource['height'],
            $resource['unit_weight'],
            $resource['total_weight'],
            $resource['length_unit'],
            $resource['weight_unit']
        );
        $resource['images'] = $images
            ->map(static function ($image) {
                return [
                    'id' => $image->id,
                    'url' => route('media.images.show', ['imageId' => $image->media->id]),
                    'order' => $image->order,
                    'alt_text' => $image->alt_text,
                    'media_id' => (string) $image->media->id,
                ];
            })
            ->values()
            ->toArray();

        return $resource;
    }
}
