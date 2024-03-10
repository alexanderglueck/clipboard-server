<?php

namespace Database\Factories;

use App\Models\DeviceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'user_>' => User::factory(),
            'device_type_id' => DeviceType::Android->value
        ];
    }
}
