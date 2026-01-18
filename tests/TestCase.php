<?php

use PHPUnit\Framework\Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Testing\TestResponse;

/**
 * TestCase base class for all October Rain tests
 *
 * Provides Laravel-compatible testing features including:
 * - HTTP testing helpers (get, post, put, patch, delete)
 * - Database refresh support via RefreshDatabase trait
 * - Factory support for model generation
 * - Session and authentication interaction helpers
 *
 * @package october\tests
 */
class TestCase extends PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Foundation\Application|null The application instance
     */
    protected $app;

    /**
     * @var bool Indicates if the database should be refreshed before each test
     */
    protected $refreshDatabase = false;

    /**
     * @var array Registered test factories
     */
    protected static $factories = [];

    /**
     * setUp the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createApplication();

        if ($this->shouldRefreshDatabase()) {
            $this->refreshDatabase();
        }

        $this->setUpTraits();
    }

    /**
     * tearDown the test environment
     */
    protected function tearDown(): void
    {
        if ($this->app) {
            $this->app->flush();
            $this->app = null;
        }

        parent::tearDown();
    }

    /**
     * createApplication for the test
     * @return \Illuminate\Foundation\Application|null
     */
    public function createApplication()
    {
        return null;
    }

    /**
     * setUpTraits boot the testing helper traits
     */
    protected function setUpTraits(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase();
        }

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }
    }

    /**
     * shouldRefreshDatabase determines if the database should be refreshed
     */
    protected function shouldRefreshDatabase(): bool
    {
        return $this->refreshDatabase ||
            in_array(RefreshDatabase::class, class_uses_recursive(static::class));
    }

    /**
     * refreshDatabase refresh the in-memory database
     */
    protected function refreshDatabase(): void
    {
        if ($this->app && method_exists($this->app, 'make')) {
            $this->artisan('migrate:fresh');
        }
    }

    /**
     * artisan call an Artisan command
     */
    protected function artisan(string $command, array $parameters = []): int
    {
        if (!$this->app) {
            return 0;
        }

        return $this->app->make('Illuminate\Contracts\Console\Kernel')
            ->call($command, $parameters);
    }

    // ==========================================
    // HTTP Testing Helpers
    // ==========================================

    /**
     * get performs a GET request
     */
    public function get(string $uri, array $headers = []): TestResponse
    {
        return $this->call('GET', $uri, [], [], [], $this->transformHeaders($headers));
    }

    /**
     * getJson performs a GET request with JSON headers
     */
    public function getJson(string $uri, array $headers = []): TestResponse
    {
        return $this->json('GET', $uri, [], $headers);
    }

    /**
     * post performs a POST request
     */
    public function post(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('POST', $uri, $data, [], [], $this->transformHeaders($headers));
    }

    /**
     * postJson performs a POST request with JSON data
     */
    public function postJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * put performs a PUT request
     */
    public function put(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('PUT', $uri, $data, [], [], $this->transformHeaders($headers));
    }

    /**
     * putJson performs a PUT request with JSON data
     */
    public function putJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    /**
     * patch performs a PATCH request
     */
    public function patch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('PATCH', $uri, $data, [], [], $this->transformHeaders($headers));
    }

    /**
     * patchJson performs a PATCH request with JSON data
     */
    public function patchJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }

    /**
     * delete performs a DELETE request
     */
    public function delete(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('DELETE', $uri, $data, [], [], $this->transformHeaders($headers));
    }

    /**
     * deleteJson performs a DELETE request with JSON data
     */
    public function deleteJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }

    /**
     * json performs a request with JSON data
     */
    public function json(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        $headers = array_merge([
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);

        return $this->call(
            $method,
            $uri,
            [],
            [],
            [],
            $this->transformHeaders($headers),
            json_encode($data)
        );
    }

    /**
     * call performs an HTTP request
     */
    public function call(
        string $method,
        string $uri,
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): TestResponse {
        if (!$this->app) {
            throw new RuntimeException('Application not bootstrapped for HTTP testing.');
        }

        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');

        $request = \Illuminate\Http\Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );

        $response = $kernel->handle($request);

        $kernel->terminate($request, $response);

        return TestResponse::fromBaseResponse($response);
    }

    /**
     * transformHeaders transforms headers array to server variables
     */
    protected function transformHeaders(array $headers): array
    {
        $server = [];

        foreach ($headers as $name => $value) {
            $name = strtr(strtoupper($name), '-', '_');

            if (!in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $name = 'HTTP_' . $name;
            }

            $server[$name] = $value;
        }

        return $server;
    }

    // ==========================================
    // Factory Support
    // ==========================================

    /**
     * factory creates a model factory builder
     */
    protected function factory(string $class, ?int $count = null): FactoryBuilder
    {
        return new FactoryBuilder($class, $count);
    }

    /**
     * registerFactory registers a factory definition
     */
    public static function registerFactory(string $class, callable $definition): void
    {
        static::$factories[$class] = $definition;
    }

    /**
     * getFactory returns a registered factory definition
     */
    public static function getFactory(string $class): ?callable
    {
        return static::$factories[$class] ?? null;
    }

    /**
     * hasFactory checks if a factory is registered
     */
    public static function hasFactory(string $class): bool
    {
        return isset(static::$factories[$class]);
    }

    // ==========================================
    // Database Assertions
    // ==========================================

    /**
     * assertDatabaseHas asserts that a given record exists in the database
     */
    protected function assertDatabaseHas(string $table, array $data, ?string $connection = null): self
    {
        $database = $this->getConnection($connection);
        $count = $database->table($table)->where($data)->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Failed asserting that a row in the table [%s] matches the attributes %s.',
            $table,
            json_encode($data)
        ));

        return $this;
    }

    /**
     * assertDatabaseMissing asserts that a given record does not exist
     */
    protected function assertDatabaseMissing(string $table, array $data, ?string $connection = null): self
    {
        $database = $this->getConnection($connection);
        $count = $database->table($table)->where($data)->count();

        $this->assertEquals(0, $count, sprintf(
            'Failed asserting that no rows in the table [%s] match the attributes %s.',
            $table,
            json_encode($data)
        ));

        return $this;
    }

    /**
     * assertDatabaseCount asserts the total number of records in a table
     */
    protected function assertDatabaseCount(string $table, int $count, ?string $connection = null): self
    {
        $database = $this->getConnection($connection);
        $actual = $database->table($table)->count();

        $this->assertEquals($count, $actual, sprintf(
            'Failed asserting that table [%s] contains %d records. Found %d.',
            $table,
            $count,
            $actual
        ));

        return $this;
    }

    /**
     * assertModelExists asserts that the given model exists in the database
     */
    protected function assertModelExists($model): self
    {
        return $this->assertDatabaseHas(
            $model->getTable(),
            [$model->getKeyName() => $model->getKey()]
        );
    }

    /**
     * assertModelMissing asserts that the given model does not exist
     */
    protected function assertModelMissing($model): self
    {
        return $this->assertDatabaseMissing(
            $model->getTable(),
            [$model->getKeyName() => $model->getKey()]
        );
    }

    /**
     * getConnection returns the database connection
     */
    protected function getConnection(?string $connection = null)
    {
        if (!$this->app) {
            throw new RuntimeException('Application not bootstrapped for database testing.');
        }

        return $this->app->make('db')->connection($connection);
    }

    // ==========================================
    // Utility Methods
    // ==========================================

    /**
     * callProtectedMethod invokes a protected/private method on an object
     */
    protected static function callProtectedMethod($object, $name, $params = [])
    {
        $className = get_class($object);
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);

        return $method->invokeArgs($object, $params);
    }

    /**
     * getProtectedProperty gets a protected/private property value
     */
    protected static function getProtectedProperty($object, $name)
    {
        $class = new ReflectionClass(get_class($object));
        $property = $class->getProperty($name);

        return $property->getValue($object);
    }

    /**
     * setProtectedProperty sets a protected/private property value
     */
    protected static function setProtectedProperty($object, $name, $value): void
    {
        $class = new ReflectionClass(get_class($object));
        $property = $class->getProperty($name);
        $property->setValue($object, $value);
    }

    // ==========================================
    // PHPUnit Compatibility Wrappers
    // ==========================================

    /**
     * assertFileNotExists allows compatibility with PHPUnit 8 and 9
     */
    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        if (method_exists(Assert::class, 'assertFileDoesNotExist')) {
            Assert::assertFileDoesNotExist($filename, $message);
            return;
        }

        Assert::assertFileNotExists($filename, $message);
    }

    /**
     * assertRegExp allows compatibility with PHPUnit 8 and 9
     */
    public static function assertRegExp(string $pattern, string $string, string $message = ''): void
    {
        if (method_exists(Assert::class, 'assertMatchesRegularExpression')) {
            Assert::assertMatchesRegularExpression($pattern, $string, $message);
            return;
        }

        Assert::assertRegExp($pattern, $string, $message);
    }

    /**
     * assertStringNotContainsString compatibility wrapper
     */
    public static function assertStringNotContainsString(
        string $needle,
        string $haystack,
        string $message = ''
    ): void {
        if (method_exists(Assert::class, 'assertStringNotContainsString')) {
            Assert::assertStringNotContainsString($needle, $haystack, $message);
            return;
        }

        Assert::assertNotContains($needle, $haystack, $message);
    }
}

/**
 * FactoryBuilder builds model instances using registered factories
 */
class FactoryBuilder
{
    protected string $class;
    protected ?int $count;
    protected array $states = [];
    protected array $overrides = [];

    public function __construct(string $class, ?int $count = null)
    {
        $this->class = $class;
        $this->count = $count;
    }

    /**
     * count sets the number of models to create
     */
    public function count(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    /**
     * state applies a state transformation
     */
    public function state(array $state): self
    {
        $this->states[] = $state;
        return $this;
    }

    /**
     * make creates model instances without persisting
     */
    public function make(array $attributes = [])
    {
        $this->overrides = $attributes;

        if ($this->count === null) {
            return $this->makeInstance();
        }

        $instances = [];
        for ($i = 0; $i < $this->count; $i++) {
            $instances[] = $this->makeInstance();
        }

        return collect($instances);
    }

    /**
     * create creates and persists model instances
     */
    public function create(array $attributes = [])
    {
        $this->overrides = $attributes;

        if ($this->count === null) {
            return $this->createInstance();
        }

        $instances = [];
        for ($i = 0; $i < $this->count; $i++) {
            $instances[] = $this->createInstance();
        }

        return collect($instances);
    }

    /**
     * raw returns raw attribute arrays
     */
    public function raw(array $attributes = []): array
    {
        $this->overrides = $attributes;

        if ($this->count === null) {
            return $this->getAttributes();
        }

        $results = [];
        for ($i = 0; $i < $this->count; $i++) {
            $results[] = $this->getAttributes();
        }

        return $results;
    }

    /**
     * makeInstance creates a single model instance
     */
    protected function makeInstance()
    {
        $attributes = $this->getAttributes();
        return new $this->class($attributes);
    }

    /**
     * createInstance creates and persists a single model instance
     */
    protected function createInstance()
    {
        $instance = $this->makeInstance();
        $instance->save();
        return $instance;
    }

    /**
     * getAttributes builds the attributes array
     */
    protected function getAttributes(): array
    {
        $factory = TestCase::getFactory($this->class);

        if (!$factory) {
            throw new RuntimeException("No factory defined for [{$this->class}]");
        }

        $attributes = $factory();

        foreach ($this->states as $state) {
            $attributes = array_merge($attributes, $state);
        }

        return array_merge($attributes, $this->overrides);
    }
}
