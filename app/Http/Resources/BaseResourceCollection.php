<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BaseResourceCollection extends AnonymousResourceCollection
{
    public static $wrap = 'items';

    /**
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    public function paginationInformation($request, array $paginated, array $default): array
    {
        return [
            'pagination' => [
                'totalItems' => $paginated['total'],
                'itemsPerPage' => $paginated['per_page'],
                'page' => $paginated['current_page'],
                'totalPages' => $paginated['last_page'],
            ],
        ];
    }
}
