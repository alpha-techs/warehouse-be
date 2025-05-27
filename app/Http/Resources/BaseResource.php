<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class BaseResource extends JsonResource
{

    public static $wrap = null;

    public static ?string $collectionWrap = 'items';

    protected static function newCollection($resource): BaseResourceCollection
    {
        BaseResourceCollection::$wrap = static::$collectionWrap;

        return new BaseResourceCollection($resource, static::class);
    }

    protected function compose(): array
    {
        return $this->resource->toArray();
    }

    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        $array = $this->compose();

        return $this->arrayToCamelCase($array);
    }

    protected function arrayToCamelCase(array $array): array
    {
        $camelArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $camelArray[Str::camel($key)] = $this->arrayToCamelCase($value);
            } elseif ($value instanceof Carbon) {
                $camelArray[Str::camel($key)] = $value->toDateTimeString();
            } elseif ($value instanceof BaseResourceCollection) {
                if (! is_null($value->collection)) {
                    $camelArray[Str::camel($key)] = $this->arrayToCamelCase($value->toArray(request()));
                }
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                $camelArray[Str::camel($key)] = $this->arrayToCamelCase($value->toArray(request()));
            } else {
                $camelArray[Str::camel($key)] = $value;
            }
        }

        return $camelArray;
    }

}
