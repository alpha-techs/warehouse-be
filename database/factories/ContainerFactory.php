<?php

namespace Database\Factories;

use App\Contracts\Models\ContainerStatus;
use App\Models\Container;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Container>
 */
class ContainerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'container_number' => $this->faker->unique()->bothify('CONT-####??'),
            'shipping_line' => $this->faker->company,
            'vessel_name' => $this->faker->lastName . '丸',
            'voyage_number' => $this->faker->bothify('VOY-####??'),
            'arrival_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'clearance_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'discharge_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'return_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'status' => $this->faker->randomElement(ContainerStatus::cases()),
        ];
    }
}
