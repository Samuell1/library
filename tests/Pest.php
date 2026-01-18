<?php

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| This file configures Pest PHP for the October Rain test suite. It sets up
| the base test case, defines helper functions, and configures expectations.
|
*/

uses(TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Custom expectations that extend Pest's expect() API.
|
*/

expect()->extend('toBeValidModel', function () {
    return $this->toBeInstanceOf(\October\Rain\Database\Model::class);
});

expect()->extend('toBeValidCollection', function () {
    return $this->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

expect()->extend('toHaveAttribute', function (string $attribute) {
    $value = $this->value;

    if (is_object($value) && isset($value->{$attribute})) {
        return $this;
    }

    if (is_array($value) && array_key_exists($attribute, $value)) {
        return $this;
    }

    throw new \PHPUnit\Framework\ExpectationFailedException(
        "Expected object/array to have attribute [{$attribute}]."
    );
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Global helper functions available in all Pest tests.
|
*/

/**
 * Create a fresh in-memory SQLite database connection
 */
function createTestDatabase(): \Illuminate\Database\Capsule\Manager
{
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => ''
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
}

/**
 * Generate a random string for testing
 */
function randomString(int $length = 10): string
{
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

/**
 * Generate a random email for testing
 */
function randomEmail(): string
{
    return randomString(8) . '@' . randomString(5) . '.test';
}

/**
 * Skip test if the given class does not exist
 */
function skipIfClassMissing(string $class): void
{
    if (!class_exists($class)) {
        test()->markTestSkipped("Class [{$class}] does not exist.");
    }
}

/**
 * Skip test if running in CI environment
 */
function skipInCI(): void
{
    if (getenv('CI') || getenv('GITHUB_ACTIONS')) {
        test()->markTestSkipped('Skipped in CI environment.');
    }
}

/*
|--------------------------------------------------------------------------
| Traits
|--------------------------------------------------------------------------
|
| Define test traits that can be used in Pest tests.
|
*/

uses()->group('database')->in('Database');
uses()->group('router')->in('Router');
uses()->group('parse')->in('Parse');
uses()->group('extension')->in('Extension');
uses()->group('halcyon')->in('Halcyon');
uses()->group('html')->in('Html');
