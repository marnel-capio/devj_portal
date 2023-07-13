<?php

namespace Database\Factories;

use App\Models\Laptops;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laptops>
 */
class LaptopsFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Laptops::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'approved_by' => 1,
            'approved_status' => 2,
            'peza_form_number' => $this->faker->regexify('[A-Za-z]{4}-[0-9]{4}'),
            'peza_permit_number' => $this->faker->regexify('[A-Za-z]{3}-[0-9]{3}'),
            'tag_number' => $this->faker->regexify('[A-Za-z]{4}-[0-9]{4}'),
            // 'tag_number' => $this->faker->regexify('[A-Za-z]{4}-12[03456789]{4}'),
            // 'tag_number' => $this->faker->regexify('stat1-as[1-4]{1}'),
            'laptop_make' => $this->faker->word(),
            'laptop_model' => $this->faker->word(),
            'laptop_cpu' => $this->faker->numberBetween(1,20),
            'laptop_clock_speed' => $this->faker->numberBetween(1,20),
            'laptop_ram' => $this->faker->numberBetween(1,20),
            'remarks' => $this->faker->text(),
            'status' => $this->faker->numberBetween(0,1),
            // 'status' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'update_time' => date('Y-m-d H:i:s'),
            'create_time' => date('Y-m-d H:i:s'),
        ];
    }
}
