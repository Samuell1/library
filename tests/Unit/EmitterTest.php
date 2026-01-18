<?php

/**
 * Unit tests for the Emitter trait using Pest syntax
 *
 * Demonstrates Pest PHP testing patterns for October Rain
 */

use October\Rain\Support\Traits\Emitter;

beforeEach(function () {
    $this->emitter = new class {
        use Emitter;
    };
});

describe('Emitter Trait', function () {
    test('can bind and fire events', function () {
        $result = false;

        $this->emitter->fireEvent('event.test');
        expect($result)->toBeFalse();

        $this->emitter->bindEvent('event.test', function () use (&$result) {
            $result = true;
        });

        $this->emitter->fireEvent('event.test');
        expect($result)->toBeTrue();
    });

    test('bindEventOnce only fires callback once', function () {
        $result = 1;

        $callback = function () use (&$result) {
            $result++;
        };

        $this->emitter->bindEventOnce('event.test', $callback);
        $this->emitter->fireEvent('event.test');
        $this->emitter->fireEvent('event.test');
        $this->emitter->fireEvent('event.test');

        expect($result)->toBe(2);
    });

    test('can unbind events', function () {
        $result = false;

        $callback = function () use (&$result) {
            $result = true;
        };

        $this->emitter->bindEvent('event.test', $callback);
        $this->emitter->unbindEvent('event.test');
        $this->emitter->fireEvent('event.test');

        expect($result)->toBeFalse();
    });

    test('fires all bound event callbacks', function () {
        $count = 0;

        $callback = function () use (&$count) {
            $count++;
        };

        $this->emitter->bindEvent('event.test', $callback);
        $this->emitter->bindEvent('event.test', $callback);
        $this->emitter->bindEvent('event.test', $callback);
        $this->emitter->fireEvent('event.test');

        expect($count)->toBe(3);
    });

    test('returns results from event callbacks', function () {
        $result = $this->emitter->fireEvent('event.test');
        expect($result)->toBeEmpty();

        $this->emitter->bindEvent('event.test', fn() => 'foo');
        $result = $this->emitter->fireEvent('event.test');

        expect($result)->not->toBeNull();
    });

    test('respects event priority ordering', function () {
        $result = '';

        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'the '; }, 90);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'quick '; }, 80);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'brown '; }, 70);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'fox '; }, 60);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'jumped '; }, 50);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'over '; }, 40);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'the '; }, 30);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'lazy '; }, 20);
        $this->emitter->bindEvent('event.test', function () use (&$result) { $result .= 'dog'; }, 10);

        $this->emitter->fireEvent('event.test');

        expect($result)->toBe('the quick brown fox jumped over the lazy dog');
    });
});
