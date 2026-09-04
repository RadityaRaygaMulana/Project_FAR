<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company().' Official';

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'description' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'ktp_nik' => fake()->numerify('3201############'),
            'ktp_name' => fake()->name(),
            'ktp_photo_path' => 'ktp/test_sample_ktp.jpg',
            'city' => 'Kota Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'address_detail' => fake()->streetAddress(),
            'badge' => 'Official',
            'rating' => 5.0,
            'status' => 'pending',
            'rejection_reason' => null,
            'approved_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(?string $reason = 'Informasi toko kurang lengkap.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }
}
