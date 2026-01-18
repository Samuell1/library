<?php

namespace Tests\Factories;

use October\Rain\Database\Models\DeferredBinding;

/**
 * Factory for creating DeferredBinding model instances
 *
 * @extends Factory<DeferredBinding>
 */
class DeferredBindingFactory extends Factory
{
    /**
     * Get the model class this factory creates
     */
    protected function modelClass(): string
    {
        return DeferredBinding::class;
    }

    /**
     * Define the model's default attributes
     */
    public function definition(): array
    {
        return [
            'master_type' => 'TestMasterModel',
            'master_field' => $this->faker->word(),
            'slave_type' => 'TestSlaveModel',
            'slave_id' => $this->faker->randomNumber(),
            'session_key' => $this->faker->uuid(),
            'is_bind' => true,
            'sort_order' => null,
            'pivot_data' => null,
        ];
    }

    /**
     * Indicate that this is a bind action
     */
    public function bind(): static
    {
        return $this->state([
            'is_bind' => true,
        ]);
    }

    /**
     * Indicate that this is an unbind action
     */
    public function unbind(): static
    {
        return $this->state([
            'is_bind' => false,
        ]);
    }

    /**
     * Set the session key
     */
    public function withSessionKey(string $key): static
    {
        return $this->state([
            'session_key' => $key,
        ]);
    }

    /**
     * Set pivot data
     */
    public function withPivotData(array $data): static
    {
        return $this->state([
            'pivot_data' => $data,
        ]);
    }

    /**
     * Configure for a specific master model
     */
    public function forMaster(string $type, string $field): static
    {
        return $this->state([
            'master_type' => $type,
            'master_field' => $field,
        ]);
    }

    /**
     * Configure for a specific slave model
     */
    public function forSlave(string $type, int $id): static
    {
        return $this->state([
            'slave_type' => $type,
            'slave_id' => $id,
        ]);
    }

    /**
     * Set sort order
     */
    public function sorted(int $order): static
    {
        return $this->state([
            'sort_order' => $order,
        ]);
    }
}
