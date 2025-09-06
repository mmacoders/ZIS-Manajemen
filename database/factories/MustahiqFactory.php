<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mustahiq;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mustahiq>
 */
class MustahiqFactory extends Factory
{
    protected $model = Mustahiq::class;

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
            'kategori' => $this->faker->randomElement(['fakir', 'miskin', 'amil', 'muallaf', 'riqab', 'gharim', 'fisabilillah', 'ibnu_sabil']),
            'alamat' => $this->faker->address(),
            'telepon' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['aktif', 'nonaktif']),
        ];
    }
}