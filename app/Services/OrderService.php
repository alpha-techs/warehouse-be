<?php

namespace App\Services;

use App\Contracts\Models\OrderStatus;
use App\Contracts\Services\OrderServiceInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class OrderService implements OrderServiceInterface
{
    public function getOrders(
        int $itemsPerPage = 20,
        int $page = 1,
        ?string $orderNumber = null,
        ?int $customerId = null,
        ?string $status = null,
        ?Carbon $deliveryDueStart = null,
        ?Carbon $deliveryDueEnd = null,
    ): Paginator {
        $query = Order::query()
            ->with(['customer', 'items.product'])
            ->orderByDesc('created_at');

        if ($orderNumber) {
            $query->where('order_number', 'like', '%' . $orderNumber . '%');
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($deliveryDueStart) {
            $query->whereDate('delivery_due_date', '>=', $deliveryDueStart);
        }

        if ($deliveryDueEnd) {
            $query->whereDate('delivery_due_date', '<=', $deliveryDueEnd);
        }

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    /**
     * @throws ValidationException
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customerId = (int) data_get($data, 'customerId');
            $customer = Customer::query()->findOrFail($customerId);

            $itemsInput = collect(data_get($data, 'items', []));
            if ($itemsInput->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['注文明细不能为空'],
                ]);
            }

            $products = $this->collectProducts($itemsInput);

            $orderData = [
                'customer_id' => $customer->id,
                'delivery_due_date' => data_get($data, 'deliveryDueDate'),
                'delivery_address' => data_get($data, 'deliveryAddress'),
                'contact_name' => data_get($data, 'contactName'),
                'contact_phone' => data_get($data, 'contactPhone'),
                'notes' => data_get($data, 'notes'),
                'currency' => data_get($data, 'currency', 'JPY'),
                'status' => OrderStatus::DRAFT,
            ];

            /** @var Order $order */
            $order = Order::create($orderData);

            $itemsData = $this->buildOrderItems($itemsInput, $products);
            $order->items()->createMany($itemsData);

            $order->update([
                'total_amount' => array_sum(array_column($itemsData, 'line_amount')),
            ]);

            return $order->fresh(['customer', 'items.product']);
        });
    }

    public function getOrder(int $id): Order
    {
        return Order::query()
            ->with(['customer', 'items.product'])
            ->findOrFail($id);
    }

    /**
     * @throws ValidationException
     */
    public function updateOrder(int $id, array $data): Order
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var Order $order */
            $order = Order::query()
                ->with(['items'])
                ->findOrFail($id);

            $status = $order->status instanceof OrderStatus ? $order->status->value : $order->status;
            if (! in_array($status, [OrderStatus::DRAFT->value, OrderStatus::REQUESTED->value], true)) {
                throw new HttpException(409, '订单状态不允许修改');
            }

            $updates = [];
            if (array_key_exists('deliveryDueDate', $data)) {
                $updates['delivery_due_date'] = $data['deliveryDueDate'];
            }
            if (array_key_exists('deliveryAddress', $data)) {
                $updates['delivery_address'] = $data['deliveryAddress'];
            }
            if (array_key_exists('contactName', $data)) {
                $updates['contact_name'] = $data['contactName'];
            }
            if (array_key_exists('contactPhone', $data)) {
                $updates['contact_phone'] = $data['contactPhone'];
            }
            if (array_key_exists('notes', $data)) {
                $updates['notes'] = $data['notes'];
            }

            if ($updates) {
                $order->fill($updates);
                $order->save();
            }

            if (array_key_exists('items', $data)) {
                $itemsInput = collect($data['items']);
                if ($itemsInput->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => ['注文明细不能为空'],
                    ]);
                }

                $products = $this->collectProducts($itemsInput);

                $order->items()->delete();
                $itemsData = $this->buildOrderItems($itemsInput, $products);
                $order->items()->createMany($itemsData);
                $order->update([
                    'total_amount' => array_sum(array_column($itemsData, 'line_amount')),
                ]);
            }

            return $order->fresh(['customer', 'items.product']);
        });
    }

    public function cancelOrder(int $id): Order
    {
        return DB::transaction(function () use ($id) {
            /** @var Order $order */
            $order = Order::query()->findOrFail($id);

            $status = $this->resolveStatus($order);
            if (! in_array($status, [OrderStatus::DRAFT->value, OrderStatus::REQUESTED->value, OrderStatus::SENT->value], true)) {
                throw new HttpException(409, '当前状态不允许取消');
            }

            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $order->fresh(['customer', 'items.product']);
        });
    }

    public function submitOrder(int $id): Order
    {
        return $this->transitionOrder(
            id: $id,
            allowedStatuses: [OrderStatus::DRAFT->value],
            targetStatus: OrderStatus::REQUESTED,
            conflictMessage: '当前状态不允许提交',
        );
    }

    public function sendOrder(int $id): Order
    {
        return $this->transitionOrder(
            id: $id,
            allowedStatuses: [OrderStatus::REQUESTED->value],
            targetStatus: OrderStatus::SENT,
            conflictMessage: '当前状态不允许下发',
        );
    }

    public function completeOrder(int $id): Order
    {
        return $this->transitionOrder(
            id: $id,
            allowedStatuses: [OrderStatus::SENT->value],
            targetStatus: OrderStatus::COMPLETED,
            conflictMessage: '当前状态不允许完成',
        );
    }

    private function transitionOrder(int $id, array $allowedStatuses, OrderStatus $targetStatus, string $conflictMessage): Order
    {
        return DB::transaction(function () use ($id, $allowedStatuses, $targetStatus, $conflictMessage) {
            /** @var Order $order */
            $order = Order::query()->findOrFail($id);

            $currentStatus = $this->resolveStatus($order);
            if (! in_array($currentStatus, $allowedStatuses, true)) {
                throw new HttpException(409, $conflictMessage);
            }

            $updates = [
                'status' => $targetStatus,
            ];

            if ($targetStatus !== OrderStatus::CANCELLED) {
                $updates['cancelled_at'] = null;
            }

            $order->update($updates);

            return $order->fresh(['customer', 'items.product']);
        });
    }

    private function resolveStatus(Order $order): string
    {
        return $order->status instanceof OrderStatus ? $order->status->value : (string) $order->status;
    }

    private function collectProducts(Collection $itemsInput): Collection
    {
        $productIds = $itemsInput
            ->pluck('productId')
            ->filter()
            ->unique();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $missing = $productIds->diff($products->keys());
        if ($missing->isNotEmpty()) {
            throw new HttpException(409, '注文明细不合法');
        }

        return $products;
    }

    private function buildOrderItems(Collection $itemsInput, Collection $products): array
    {
        return $itemsInput->map(function (array $item) use ($products) {
            $productId = data_get($item, 'productId');
            $product = $productId ? $products->get($productId) : null;
            $quantity = (float) data_get($item, 'quantity', 0);
            $unitPrice = $this->castAmount(data_get($item, 'unitPrice', 0));
            $lineAmount = $this->castAmount($quantity * $unitPrice);

            return [
                'product_id' => $product?->id,
                'product_name' => $product?->name,
                'product_sku' => $product?->sku,
                'quantity' => $quantity,
                'unit' => data_get($item, 'unit'),
                'unit_price' => $unitPrice,
                'line_amount' => $lineAmount,
                'currency' => data_get($item, 'currency', 'JPY'),
                'note' => data_get($item, 'note'),
            ];
        })->all();
    }

    private function castAmount(mixed $value): int
    {
        if (! is_numeric($value)) {
            return 0;
        }

        $number = (float) $value;

        if ($number >= 0) {
            return (int) floor($number + 0.5);
        }

        return (int) ceil($number - 0.5);
    }
}
