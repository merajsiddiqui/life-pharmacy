<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function findById(int $id): ?Order;
    public function findByUserId(int $userId): Collection;
    public function createOrderItem(Order $order, array $itemData): void;
} 