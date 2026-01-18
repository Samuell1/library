<?php

/**
 * Unit tests for Html Helper using Pest syntax
 *
 * Demonstrates Pest PHP testing patterns for October Rain
 */

use October\Rain\Html\Helper as HtmlHelper;

describe('HtmlHelper::nameToId', function () {
    test('converts simple field names', function () {
        expect(HtmlHelper::nameToId('field'))->toBe('field');
    });

    test('converts array notation to dashed format', function () {
        expect(HtmlHelper::nameToId('field[key1]'))->toBe('field-key1');
    });

    test('handles empty array brackets', function () {
        expect(HtmlHelper::nameToId('field[][key1]'))->toBe('field--key1');
    });

    test('converts nested array notation', function () {
        expect(HtmlHelper::nameToId('field[key1][key2][key3]'))->toBe('field-key1-key2-key3');
    });
});

describe('HtmlHelper::nameToArray', function () {
    test('converts simple field to array', function () {
        $result = HtmlHelper::nameToArray('field');

        expect($result)
            ->toBeArray()
            ->toHaveCount(1)
            ->toContain('field');
    });

    test('converts array notation to parts', function () {
        $result = HtmlHelper::nameToArray('field[key1]');

        expect($result)
            ->toBeArray()
            ->toHaveCount(2)
            ->toContain('field')
            ->toContain('key1');
    });

    test('handles empty brackets in array', function () {
        $result = HtmlHelper::nameToArray('field[][key1]');

        expect($result)
            ->toBeArray()
            ->toHaveCount(2)
            ->toContain('field')
            ->toContain('key1');
    });

    test('converts deeply nested array notation', function () {
        $result = HtmlHelper::nameToArray('field[key1][key2][key3]');

        expect($result)
            ->toBeArray()
            ->toHaveCount(4)
            ->toContain('field')
            ->toContain('key1')
            ->toContain('key2')
            ->toContain('key3');
    });
});
