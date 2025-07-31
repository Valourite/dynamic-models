<?php

namespace Valourite\DynamicModels\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Valourite\DynamicModels\Models\ModelType;

/**
 * @extends Factory<\Valourite\DynamicModels\Models\ModelType>
 */
final class ModelTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ModelType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $formName = fake()->unique()->company();

        return [
            ModelType::MODEL_TYPE_NAME                 => $formName,
            ModelType::MODEL_TYPE_DESCRIPTION          => fake()->text(255),
            ModelType::MODEL_TYPE_CONFIRMATION_MESSAGE => fake()->text(255),
            ModelType::CAN_BE_CREATED                 => 1,
            ModelType::MODEL_TYPE_PARENT_MODEL                => fake()->randomElement(config('dynamic-models.parent_models')),
            //TODO: Create some fake schema here
            ModelType::MODEL_TYPE_SCHEMA              => json_encode(['This is a place holder array' => 'yes']),
            ModelType::MODEL_TYPE_VERSION              => fake()->numberBetween(0, 1) . '.' . fake()->numberBetween(0, 5) . '.' . fake()->numberBetween(0, 5),
        ];
    }
}
