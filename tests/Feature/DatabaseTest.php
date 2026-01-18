<?php

/**
 * Feature tests for database functionality using Pest syntax
 *
 * Demonstrates database testing patterns with Pest PHP
 */

use Illuminate\Database\Capsule\Manager as Capsule;

beforeEach(function () {
    // Create an in-memory SQLite database for testing
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => ''
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $this->db = $capsule;
});

describe('Database Connection', function () {
    test('can establish connection', function () {
        $connection = $this->db->getConnection();

        expect($connection)->not->toBeNull();
        expect($connection->getDatabaseName())->toBe(':memory:');
    });

    test('can execute raw queries', function () {
        $result = $this->db->getConnection()->select('SELECT 1 as value');

        expect($result)->toBeArray();
        expect($result[0]->value)->toBe(1);
    });
});

describe('Schema Builder', function () {
    test('can create tables', function () {
        $schema = $this->db->getConnection()->getSchemaBuilder();

        $schema->create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        expect($schema->hasTable('users'))->toBeTrue();
        expect($schema->hasColumn('users', 'name'))->toBeTrue();
        expect($schema->hasColumn('users', 'email'))->toBeTrue();
    });

    test('can drop tables', function () {
        $schema = $this->db->getConnection()->getSchemaBuilder();

        $schema->create('temp_table', function ($table) {
            $table->increments('id');
        });

        expect($schema->hasTable('temp_table'))->toBeTrue();

        $schema->drop('temp_table');

        expect($schema->hasTable('temp_table'))->toBeFalse();
    });
});

describe('Query Builder', function () {
    beforeEach(function () {
        $schema = $this->db->getConnection()->getSchemaBuilder();

        $schema->create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    });

    test('can insert records', function () {
        $this->db->table('posts')->insert([
            'title' => 'First Post',
            'content' => 'Hello World',
            'published' => true,
        ]);

        $count = $this->db->table('posts')->count();
        expect($count)->toBe(1);
    });

    test('can query records', function () {
        $this->db->table('posts')->insert([
            ['title' => 'Post 1', 'content' => 'Content 1', 'published' => true],
            ['title' => 'Post 2', 'content' => 'Content 2', 'published' => false],
            ['title' => 'Post 3', 'content' => 'Content 3', 'published' => true],
        ]);

        $published = $this->db->table('posts')->where('published', true)->get();

        expect($published)->toHaveCount(2);
    });

    test('can update records', function () {
        $this->db->table('posts')->insert([
            'title' => 'Original Title',
            'content' => 'Content',
        ]);

        $this->db->table('posts')
            ->where('title', 'Original Title')
            ->update(['title' => 'Updated Title']);

        $post = $this->db->table('posts')->first();

        expect($post->title)->toBe('Updated Title');
    });

    test('can delete records', function () {
        $this->db->table('posts')->insert([
            'title' => 'To Delete',
            'content' => 'Content',
        ]);

        expect($this->db->table('posts')->count())->toBe(1);

        $this->db->table('posts')->where('title', 'To Delete')->delete();

        expect($this->db->table('posts')->count())->toBe(0);
    });
});
