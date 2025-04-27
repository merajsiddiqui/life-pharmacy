<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {
    }

    public function createOrder(array $data, array $items): Order
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal
                ];
            }

            $order = $this->orderRepository->create([
                'user_id' => $data['user_id'],
                'total_amount' => $totalAmount,
                'shipping_address' => $data['shipping_address'],
                'phone_number' => $data['phone_number'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending'
            ]);

            foreach ($orderItems as $item) {
                $this->orderRepository->createOrderItem($order, $item);
            }

            DB::commit();

            return $order->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUserOrders(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId)->toArray();
    }

    public function getOrderDetails(int $orderId, int $userId): ?Order
    {
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order || $order->user_id !== $userId) {
            return null;
        }

        return $order;
    }

    /**
     * Get paginated orders for a user.
     *
     * @param int $userId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrders(int $userId): LengthAwarePaginator
    {
        return Order::where('user_id', $userId)
            ->with(['items.product'])
            ->latest()
            ->paginate(10);
    }

    /**
     * Get a specific order.
     *
     * @param int $orderId
     * @param int $userId
     * @return \App\Models\Order
     */
    public function getOrder(int $orderId, int $userId): Order
    {
        return Order::where('user_id', $userId)
            ->with(['items.product'])
            ->findOrFail($orderId);
    }

    /**
     * Update order status.
     *
     * @param int $orderId
     * @param string $status
     * @param int $userId
     * @return \App\Models\Order
     */
    public function updateOrderStatus(int $orderId, string $status, int $userId): Order
    {
        $order = Order::where('user_id', $userId)
            ->findOrFail($orderId);

        $order->update(['status' => $status]);

        return $order->load('items.product');
    }
} 