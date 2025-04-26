<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(protected Order $model)
    {
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Order
    {
        return $this->model->with('items.product')->find($id);
    }

    public function findByUserId(int $userId): Collection
    {
        return $this->model->with('items.product')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function createOrderItem(Order $order, array $itemData): void
    {
        $order->items()->create($itemData);
    }
} 