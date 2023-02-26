<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'user_>' => User::factory(),
            'device_type_id' => DeviceType::Android->value
        ];
    }
}
