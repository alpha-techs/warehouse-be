<?php

namespace App\Services;

use App\Contracts\Models\OrderStatus;
use App\Contracts\Services\OrderPrintServiceInterface;
use App\Jobs\GenerateOrderPrintJob;
use App\Models\Order;
use App\Models\OrderPrint;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class OrderPrintService implements OrderPrintServiceInterface
{
    public function getOrderPrints(
        int $itemsPerPage = 20,
        int $page = 1,
        ?string $orderNumber = null,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): Paginator {
        $query = OrderPrint::query()
            ->with(['order.customer'])
            ->orderByDesc('created_at');

        if ($orderNumber) {
            $query->whereHas('order', function ($relation) use ($orderNumber) {
                $relation->where('order_number', 'like', '%' . $orderNumber . '%');
            });
        }

        if ($customerId) {
            $query->whereHas('order', function ($relation) use ($customerId) {
                $relation->where('customer_id', $customerId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getOrderPrint(string $id): OrderPrint
    {
        return OrderPrint::query()
            ->with(['order.customer'])
            ->findOrFail($id);
    }

    /**
     * @throws ValidationException
     */
    public function createOrderPrint(int $orderId, string $format = 'excel'): OrderPrint
    {
        if ($format !== 'excel') {
            throw ValidationException::withMessages([
                'format' => ['目前仅支持生成 Excel 文件。'],
            ]);
        }

        return DB::transaction(function () use ($orderId, $format) {
            $order = Order::query()
                ->with(['items.product', 'customer'])
                ->findOrFail($orderId);

            $status = $order->status instanceof OrderStatus ? $order->status->value : $order->status;
            if (! in_array($status, [OrderStatus::SENT->value, OrderStatus::COMPLETED->value], true)) {
                throw new HttpException(409, '注文状态不支持生成打印版');
            }

            $print = OrderPrint::create([
                'order_id' => $order->id,
                'format' => $format,
                'status' => 'pending',
                'storage' => OrderPrint::defaultStorageType(),
            ]);

            GenerateOrderPrintJob::dispatch($print->id);

            return $print->fresh(['order.customer']);
        });
    }
}
