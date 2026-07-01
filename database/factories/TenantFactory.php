<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name'      => $name,
            'slug'      => Str::slug($name) . '-' . Str::random(6),
            'email'     => fake()->unique()->companyEmail(),
            'phone'     => fake()->phoneNumber(),
            'address'   => fake()->address(),
            'is_active' => true,
        ];
    }

    /**
     * State for the platform (super-admin) tenant.
     */
    public function platform(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Platform',
            'slug' => 'platform',
        ]);
    }
}
