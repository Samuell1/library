<?php

/**
 * Example Pest tests demonstrating various testing patterns
 *
 * This file serves as a template for writing new Pest tests
 */

test('example: basic assertion', function () {
    expect(true)->toBeTrue();
});

test('example: string assertions', function () {
    $greeting = 'Hello, October';

    expect($greeting)
        ->toBeString()
        ->toStartWith('Hello')
        ->toContain('October')
        ->toEndWith('October');
});

test('example: array assertions', function () {
    $config = [
        'debug' => true,
        'environment' => 'testing',
        'database' => 'sqlite',
    ];

    expect($config)
        ->toBeArray()
        ->toHaveCount(3)
        ->toHaveKey('debug')
        ->toHaveKey('environment', 'testing')
        ->toMatchArray(['debug' => true]);
});

test('example: number assertions', function () {
    $value = 42;

    expect($value)
        ->toBeInt()
        ->toBeGreaterThan(40)
        ->toBeLessThan(50)
        ->toBeBetween(40, 45);
});

test('example: exception handling', function () {
    expect(fn() => throw new InvalidArgumentException('Invalid input'))
        ->toThrow(InvalidArgumentException::class, 'Invalid input');
});

test('example: callback assertions', function () {
    $numbers = [1, 2, 3, 4, 5];

    expect($numbers)->each->toBeInt();
    expect($numbers)->sequence(
        fn($value) => $value->toBe(1),
        fn($value) => $value->toBe(2),
        fn($value) => $value->toBe(3),
        fn($value) => $value->toBe(4),
        fn($value) => $value->toBe(5),
    );
});

describe('grouped tests', function () {
    beforeEach(function () {
        $this->calculator = new class {
            public function add($a, $b) { return $a + $b; }
            public function subtract($a, $b) { return $a - $b; }
            public function multiply($a, $b) { return $a * $b; }
            public function divide($a, $b) {
                if ($b === 0) throw new DivisionByZeroError();
                return $a / $b;
            }
        };
    });

    test('can add numbers', function () {
        expect($this->calculator->add(2, 3))->toBe(5);
    });

    test('can subtract numbers', function () {
        expect($this->calculator->subtract(5, 3))->toBe(2);
    });

    test('can multiply numbers', function () {
        expect($this->calculator->multiply(4, 3))->toBe(12);
    });

    test('can divide numbers', function () {
        expect($this->calculator->divide(10, 2))->toBe(5);
    });

    test('throws on division by zero', function () {
        expect(fn() => $this->calculator->divide(10, 0))
            ->toThrow(DivisionByZeroError::class);
    });
});

// Dataset example
dataset('arithmetic', [
    'positive numbers' => [2, 3, 5],
    'negative numbers' => [-2, -3, -5],
    'mixed numbers' => [-2, 5, 3],
    'with zero' => [0, 5, 5],
]);

test('adds numbers from dataset', function (int $a, int $b, int $expected) {
    expect($a + $b)->toBe($expected);
})->with('arithmetic');
