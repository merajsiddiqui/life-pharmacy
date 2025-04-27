<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the role is an admin.
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator role with full access',
            ];
        });
    }

    /**
     * Indicate that the role is a customer.
     */
    public function customer(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Regular customer role',
            ];
        });
    }
} 