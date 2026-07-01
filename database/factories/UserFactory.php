<?php

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    private const DEFAULT_PERMISSION_NAMES = [
        'read_quotes',
        'create_quotes',
        'convert_quotes',
        'manage_taxes',
    ];

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterCreating(function ($user) {
            if (! $user->tenant_id) {
                return;
            }

            $tenant = Tenant::query()->find($user->tenant_id);
            if (! $tenant) {
                return;
            }

            $permissions = collect(self::DEFAULT_PERMISSION_NAMES)
                ->map(fn (string $name) => Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]))
                ->filter();

            $user->syncPermissions($permissions->pluck('id')->all());
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => function () {
                return \App\Models\Tenant::firstOrCreate([
                    'slug' => 'platform',
                ], [
                    'name' => 'Plateforme SaaS',
                    'email' => 'contact@platform.local',
                    'is_active' => true,
                ])->id;
            },
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }
}
