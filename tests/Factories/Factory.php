<?php

namespace Tests\Factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Collection;

/**
 * Factory base class for generating test model instances
 *
 * Provides Laravel-compatible factory functionality for October Rain models.
 * Extend this class to create custom factories for your models.
 *
 * Usage:
 *   $user = UserFactory::new()->make();
 *   $users = UserFactory::new()->count(5)->create();
 *   $admin = UserFactory::new()->state(['is_admin' => true])->create();
 */
abstract class Factory
{
    /**
     * @var Generator The Faker instance
     */
    protected Generator $faker;

    /**
     * @var int|null Number of models to create
     */
    protected ?int $count = null;

    /**
     * @var array State transformations to apply
     */
    protected array $states = [];

    /**
     * @var array Attribute overrides
     */
    protected array $overrides = [];

    /**
     * @var array Relationships to create with the model
     */
    protected array $with = [];

    /**
     * @var callable|null Callback to run after creating
     */
    protected $afterCreating = null;

    /**
     * @var callable|null Callback to run after making
     */
    protected $afterMaking = null;

    /**
     * Create a new factory instance
     */
    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * Create a new factory instance
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * Get the model class this factory creates
     */
    abstract protected function modelClass(): string;

    /**
     * Define the model's default attributes
     */
    abstract public function definition(): array;

    /**
     * Set the number of models to create
     */
    public function count(int $count): static
    {
        $clone = clone $this;
        $clone->count = $count;

        return $clone;
    }

    /**
     * Apply a state transformation
     */
    public function state(array|callable $state): static
    {
        $clone = clone $this;
        $clone->states[] = $state;

        return $clone;
    }

    /**
     * Specify relationships to create with the model
     */
    public function has(Factory $factory, string $relationship): static
    {
        $clone = clone $this;
        $clone->with[$relationship] = $factory;

        return $clone;
    }

    /**
     * Specify a parent relationship
     */
    public function for($parent, string $relationship): static
    {
        return $this->state(function () use ($parent, $relationship) {
            $model = $parent instanceof Factory ? $parent->create() : $parent;
            $foreignKey = $model->getForeignKey();

            return [$foreignKey => $model->getKey()];
        });
    }

    /**
     * Set a callback to run after creating
     */
    public function afterCreating(callable $callback): static
    {
        $clone = clone $this;
        $clone->afterCreating = $callback;

        return $clone;
    }

    /**
     * Set a callback to run after making
     */
    public function afterMaking(callable $callback): static
    {
        $clone = clone $this;
        $clone->afterMaking = $callback;

        return $clone;
    }

    /**
     * Create model instances without persisting
     */
    public function make(array $attributes = []): mixed
    {
        $this->overrides = $attributes;

        if ($this->count === null) {
            return $this->makeInstance();
        }

        $instances = [];
        for ($i = 0; $i < $this->count; $i++) {
            $instances[] = $this->makeInstance();
        }

        return new Collection($instances);
    }

    /**
     * Create and persist model instances
     */
    public function create(array $attributes = []): mixed
    {
        $this->overrides = $attributes;

        if ($this->count === null) {
            return $this->createInstance();
        }

        $instances = [];
        for ($i = 0; $i < $this->count; $i++) {
            $instances[] = $this->createInstance();
        }

        return new Collection($instances);
    }

    /**
     * Get raw attributes without creating a model
     */
    public function raw(array $attributes = []): array
    {
        $this->overrides = $attributes;

        if ($this->count === null) {
            return $this->buildAttributes();
        }

        $results = [];
        for ($i = 0; $i < $this->count; $i++) {
            $results[] = $this->buildAttributes();
        }

        return $results;
    }

    /**
     * Create a single model instance without persisting
     */
    protected function makeInstance(): mixed
    {
        $class = $this->modelClass();
        $attributes = $this->buildAttributes();
        $instance = new $class($attributes);

        if ($this->afterMaking) {
            call_user_func($this->afterMaking, $instance);
        }

        return $instance;
    }

    /**
     * Create and persist a single model instance
     */
    protected function createInstance(): mixed
    {
        $instance = $this->makeInstance();
        $instance->save();

        $this->createRelationships($instance);

        if ($this->afterCreating) {
            call_user_func($this->afterCreating, $instance);
        }

        return $instance;
    }

    /**
     * Create related models
     */
    protected function createRelationships($instance): void
    {
        foreach ($this->with as $relationship => $factory) {
            $related = $factory->create();

            if ($instance->$relationship() instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                $instance->$relationship()->save($related);
            }
        }
    }

    /**
     * Build the attributes array
     */
    protected function buildAttributes(): array
    {
        $attributes = $this->definition();

        foreach ($this->states as $state) {
            if (is_callable($state)) {
                $state = call_user_func($state, $attributes);
            }

            $attributes = array_merge($attributes, $state);
        }

        return array_merge($attributes, $this->overrides);
    }

    /**
     * Get the Faker instance
     */
    protected function faker(): Generator
    {
        return $this->faker;
    }
}
