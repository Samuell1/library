<?php

namespace Tests\Factories;

use October\Rain\Database\Models\Revision;

/**
 * Factory for creating Revision model instances
 *
 * @extends Factory<Revision>
 */
class RevisionFactory extends Factory
{
    /**
     * Get the model class this factory creates
     */
    protected function modelClass(): string
    {
        return Revision::class;
    }

    /**
     * Define the model's default attributes
     */
    public function definition(): array
    {
        return [
            'revisionable_type' => 'TestModel',
            'revisionable_id' => $this->faker->randomNumber(),
            'user_id' => $this->faker->randomNumber(),
            'field' => $this->faker->word(),
            'old_value' => $this->faker->sentence(),
            'new_value' => $this->faker->sentence(),
            'cast' => null,
        ];
    }

    /**
     * Indicate that the revision is for a date field
     */
    public function dateField(): static
    {
        return $this->state([
            'cast' => 'date',
            'old_value' => $this->faker->date(),
            'new_value' => $this->faker->date(),
        ]);
    }

    /**
     * Indicate that the revision is for a boolean field
     */
    public function booleanField(): static
    {
        return $this->state([
            'cast' => 'boolean',
            'old_value' => '0',
            'new_value' => '1',
        ]);
    }

    /**
     * Indicate that the revision is for a specific model
     */
    public function forModel(string $type, int $id): static
    {
        return $this->state([
            'revisionable_type' => $type,
            'revisionable_id' => $id,
        ]);
    }
}
