<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
} 