<?php

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    private const DEFAULT_PERMISSION_NAMES = [
        'read_quotes',
        'create_quotes',
        'convert_quotes',
        'manage_taxes',
    ];

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

    public function configure(): static
    {
        return $this->afterCreating(function ($tenant) {
            $permissions = collect(self::DEFAULT_PERMISSION_NAMES)
                ->map(fn (string $name) => Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]))
                ->filter();

            $plan = Plan::firstOrCreate(
                ['slug' => 'admin'],
                [
                    'name' => 'Admin',
                    'price' => 0,
                    'duration_days' => 365,
                    'max_users' => 100,
                    'max_storage_mb' => 10240,
                    'is_active' => true,
                    'description' => 'Plan de test créé automatiquement par la factory.',
                ]
            );

            $plan->permissions()->sync($permissions->pluck('id')->all());

            Subscription::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ],
                [
                    'amount_paid' => 0,
                    'payment_method' => 'factory',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addYear(),
                    'is_active' => true,
                ]
            );
        });
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
