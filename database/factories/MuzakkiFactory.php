<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Muzakki;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Muzakki>
 */
class MuzakkiFactory extends Factory
{
    protected $model = Muzakki::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'nik' => $this->faker->unique()->numerify('################'),
            'jenis' => $this->faker->randomElement(['individu', 'perusahaan']),
            'alamat' => $this->faker->address(),
            'telepon' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}