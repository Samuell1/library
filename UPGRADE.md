# Upgrade Guide

## Upgrading to v4.0 from v3.x

### Breaking Changes

#### Removed: Relation `getForeignKey()` and `getOtherKey()` Methods

The deprecated `getForeignKey()` and `getOtherKey()` methods have been removed from relation classes. Use the following replacements:

| Class | Removed Method | Replacement |
|-------|----------------|-------------|
| `AttachOneOrMany` | `getForeignKey()` | `getForeignKeyName()` |
| `AttachOneOrMany` | `getOtherKey()` | `getLocalKeyName()` |
| `HasOneOrMany` | `getForeignKey()` | `getForeignKeyName()` |
| `HasOneOrMany` | `getOtherKey()` | `getLocalKeyName()` |
| `BelongsToMany` | `getForeignKey()` | `getQualifiedForeignPivotKeyName()` |
| `BelongsToMany` | `getOtherKey()` | `getQualifiedRelatedPivotKeyName()` |
| `BelongsTo` | `getOtherKey()` | `getOwnerKeyName()` |

#### Removed: `makeValidationFile()` Method

The `makeValidationFile()` method has been removed from `AttachOneOrMany`. This method was used internally for file validation and is no longer needed.

#### Removed: Relation `count` Option

The `'count' => true` option in relation definitions has been removed. Use Laravel's `withCount()` method instead:

**Before (v3.x):**
```php
public $hasMany = [
    'comments' => [Comment::class, 'count' => true]
];
```

**After (v4.0):**
```php
// In your query
$posts = Post::withCount('comments')->get();

// Access count
$posts->first()->comments_count;
```

The `$countMode` property has also been removed from `BelongsToMany`.

#### Removed: `Model::reloadRelations()` Method

The `reloadRelations()` method has been removed from the Model class. Use `unsetRelation()` or `unsetRelations()` instead:

**Before (v3.x):**
```php
$model->reloadRelations('comments');
$model->reloadRelations(); // all relations
```

**After (v4.0):**
```php
$model->unsetRelation('comments');
$model->unsetRelations(); // all relations
```

#### Removed: `ExtendableTrait::clearExtendedClasses()` Method

The static `clearExtendedClasses()` method has been removed from `ExtendableTrait`. Use `Container::clearExtensions()` instead:

**Before (v3.x):**
```php
Model::clearExtendedClasses();
```

**After (v4.0):**
```php
use October\Rain\Extension\Container;

Container::clearExtensions();
```

### Other Deprecated Items (Still Present)

The following items are deprecated but still functional in v4.0. They will be removed in a future version:

#### `File::getPathAttribute()`
Use `getUrlAttribute()` or `->url` instead of `->path`.

#### `File::output()` and `File::outputThumb()`
The `$returnResponse` parameter is deprecated. Chain with `->send()` instead.

#### `Application::fatal()`
Use `App::error()` with an `Error` exception type.

#### `GeneratorCommand`
Use `GeneratorCommandBase` instead.

#### `SyntaxModelTrait::bootSyntaxModelTrait()`
Replace with `initializeSyntaxModelTrait` in `model.afterFetch` event.

#### `Handler::error()`
Use `renderable()` instead.

#### `ValidationException::getFields()`
Use `->errors()` instead.

#### `Dongle::parseParams()`
Use `parse()` with a second argument instead.

#### `Connection` class
Use `\October\Rain\Database\Connections\ExtendsConnection` trait instead.

#### `Singleton` class
Use `App::singleton()` with a manually built class instead.

#### `SortableScope` and `NestedTreeScope`
Use the classes from `\October\Rain\Database\Scopes\` namespace.

#### `MailServiceProvider` events
Use `mailer.beforeResolve` instead of `mailer.beforeRegister`, and `mailer.resolve` instead of `mailer.register`.

#### `Filesystem\Definitions` config path
Use `media.*` config keys instead of `cms.file_definitions.*`.
