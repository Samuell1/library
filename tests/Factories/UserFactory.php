<?php

namespace Tests\Factories;

use October\Rain\Database\Model;

/**
 * Example User factory for testing purposes
 *
 * This is a template factory that can be used when testing
 * user-related functionality. Extend or modify as needed
 * for your specific User model implementation.
 *
 * @extends Factory<Model>
 */
class UserFactory extends Factory
{
    /**
     * Get the model class this factory creates
     *
     * Override this to return your actual User model class
     */
    protected function modelClass(): string
    {
        // Return your actual User model class, e.g.:
        // return \App\Models\User::class;
        return Model::class;
    }

    /**
     * Define the model's default attributes
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'is_activated' => true,
            'activated_at' => now(),
        ];
    }

    /**
     * Indicate that the user is inactive
     */
    public function inactive(): static
    {
        return $this->state([
            'is_activated' => false,
            'activated_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an administrator
     */
    public function admin(): static
    {
        return $this->state([
            'is_superuser' => true,
        ]);
    }

    /**
     * Indicate that the user has a specific role
     */
    public function withRole(string $role): static
    {
        return $this->state([
            'role' => $role,
        ]);
    }

    /**
     * Set a specific email address
     */
    public function withEmail(string $email): static
    {
        return $this->state([
            'email' => $email,
        ]);
    }

    /**
     * Indicate that the user is unverified
     */
    public function unverified(): static
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set up the user with specific permissions
     */
    public function withPermissions(array $permissions): static
    {
        return $this->afterCreating(function ($user) use ($permissions) {
            if (method_exists($user, 'setPermissions')) {
                $user->setPermissions($permissions);
            }
        });
    }
}
