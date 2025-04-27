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

    public function createOrder(array $data, $items): Order
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            // Process order items and calculate total
            foreach ($items as $item) {
                $product = $item->product;
                $subtotal = $product->price * $item->quantity;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal
                ];

                // Update product stock
                $product->decrement('stock', $item->quantity);
            }

            // Calculate shipping cost based on method (default to standard if not provided)
            $shippingMethod = $data['shipping_method'] ?? 'standard';
            $shippingCost = $shippingMethod === 'express' ? 20.00 : 10.00;

            // Calculate tax (assuming 5% tax rate)
            $taxAmount = $totalAmount * 0.05;

            // Calculate discount if discount code is provided
            $discountAmount = 0;
            if (!empty($data['discount_code'])) {
                // Here you would typically validate the discount code and calculate the discount
                // For now, we'll use a simple 10% discount if a code is provided
                $discountAmount = $totalAmount * 0.10;
            }

            // Calculate final total
            $finalTotal = $totalAmount + $shippingCost + $taxAmount - $discountAmount;

            $order = $this->orderRepository->create([
                'user_id' => $data['user_id'],
                'total_amount' => $finalTotal,
                'shipping_address' => $data['shipping_address'],
                'phone_number' => $data['phone_number'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'],
                'shipping_method' => $shippingMethod,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'discount_code' => $data['discount_code'] ?? null,
                'subtotal' => $totalAmount
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
     * @param \App\Models\Order $order
     * @param string $status
     * @return \App\Models\Order
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        return $order->load('items.product');
    }

    /**
     * Cancel an order and restore product stock.
     *
     * @param \App\Models\Order $order
     * @return \App\Models\Order
     */
    public function cancelOrder(Order $order): Order
    {
        try {
            DB::beginTransaction();

            // Restore product stock
            foreach ($order->items as $item) {
                $product = $item->product;
                $product->increment('stock', $item->quantity);
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            DB::commit();

            return $order->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 