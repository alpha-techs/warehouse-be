<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BaseResourceCollection extends AnonymousResourceCollection
{
    public static $wrap = 'items';

    public function paginationInformation($request, array $paginated, array $default): array
    {
        return [
            'pagination' => [
                'total' => $paginated['total'],
                'itemsPerPage' => $paginated['per_page'],
                'currentPage' => $paginated['current_page'],
            ]
        ];
    }
}
