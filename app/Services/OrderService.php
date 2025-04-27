<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service class for handling order-related business logic
 */
class OrderService
{
    /**
     * Create a new OrderService instance.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * Process order items and calculate totals
     *
     * @param mixed $items Collection of order items
     * @return array{totalAmount: float, orderItems: array}
     */
    protected function processOrderItems($items): array
    {
        $totalAmount = 0;
        $orderItems = [];

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

            $product->decrement('stock', $item->quantity);
        }

        return [
            'totalAmount' => $totalAmount,
            'orderItems' => $orderItems
        ];
    }

    /**
     * Calculate shipping cost based on method
     *
     * @param string $shippingMethod
     * @return float
     */
    protected function calculateShippingCost(string $shippingMethod): float
    {
        return $shippingMethod === 'express' ? 20.00 : 10.00;
    }

    /**
     * Calculate tax amount
     *
     * @param float $totalAmount
     * @return float
     */
    protected function calculateTax(float $totalAmount): float
    {
        return $totalAmount * 0.05;
    }

    /**
     * Calculate discount amount
     *
     * @param float $totalAmount
     * @param string|null $discountCode
     * @return float
     */
    protected function calculateDiscount(float $totalAmount, ?string $discountCode): float
    {
        if (empty($discountCode)) {
            return 0;
        }

        // Here you would typically validate the discount code
        // For now, we'll use a simple 10% discount if a code is provided
        return $totalAmount * 0.10;
    }

    /**
     * Prepare order data for creation
     *
     * @param array $data
     * @param float $totalAmount
     * @param float $shippingCost
     * @param float $taxAmount
     * @param float $discountAmount
     * @return array
     */
    protected function prepareOrderData(
        array $data,
        float $totalAmount,
        float $shippingCost,
        float $taxAmount,
        float $discountAmount
    ): array {
        $shippingMethod = $data['shipping_method'] ?? 'standard';
        $finalTotal = $totalAmount + $shippingCost + $taxAmount - $discountAmount;

        return [
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
        ];
    }

    /**
     * Create a new order with the given data and items.
     *
     * @param array $data Order data including user_id, shipping details, etc.
     * @param mixed $items Collection of order items with product and quantity
     * @return Order
     * @throws \Exception If order creation fails
     */
    public function createOrder(array $data, $items): Order
    {
        try {
            DB::beginTransaction();

            // Process order items and calculate totals
            $processedItems = $this->processOrderItems($items);
            $totalAmount = $processedItems['totalAmount'];
            $orderItems = $processedItems['orderItems'];

            // Calculate various costs
            $shippingMethod = $data['shipping_method'] ?? 'standard';
            $shippingCost = $this->calculateShippingCost($shippingMethod);
            $taxAmount = $this->calculateTax($totalAmount);
            $discountAmount = $this->calculateDiscount($totalAmount, $data['discount_code'] ?? null);

            // Prepare and create order
            $orderData = $this->prepareOrderData(
                $data,
                $totalAmount,
                $shippingCost,
                $taxAmount,
                $discountAmount
            );

            $order = $this->orderRepository->create($orderData);

            // Create order items
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

    /**
     * Get all orders for a specific user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserOrders(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId)->toArray();
    }

    /**
     * Get detailed information about a specific order.
     *
     * @param int $orderId
     * @param int $userId
     * @return Order|null
     */
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
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrder(int $orderId, int $userId): Order
    {
        return Order::where('user_id', $userId)
            ->with(['items.product'])
            ->findOrFail($orderId);
    }

    /**
     * Update the status of an order.
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
     * @throws \Exception If order cancellation fails
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