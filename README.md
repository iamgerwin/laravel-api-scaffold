# Laravel API Scaffold

A comprehensive Laravel package that scaffolds Service layer architecture with API resources, controllers, and tests. This package follows best practices and provides a battle-tested structure for building maintainable Laravel applications.

## Description

Laravel API Scaffold automates the creation of service-oriented architecture components in your Laravel application. It generates well-structured service classes with interfaces, controllers with dependency injection, form requests, API resources, and comprehensive tests, all following Laravel conventions and best practices.

## Features

- 🏗️ **Service Layer Architecture**: Automatically generates service classes with their interfaces
- 🔌 **Dependency Injection**: Auto-registers service bindings in AppServiceProvider
- 🎯 **Complete API Scaffolding**: Creates Models, Migrations, Controllers, Requests, and Resources
- 🧪 **Testing Ready**: Generates Pest/PHPUnit test files with common test cases
- ⚙️ **Highly Configurable**: Customize paths, namespaces, and generation options
- 🔒 **Safe Operations**: Automatic backups of existing files before modifications
- 📦 **Modular Approach**: Generate only what you need with granular options
- 🎨 **Custom Stubs**: Publish and customize stub templates to match your coding style

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Composer

## Installation

You can install the package via composer:

```bash
composer require iamgerwin/laravel-api-scaffold
```

### Publish Configuration (Optional)

Publish the configuration file to customize the package behavior:

```bash
php artisan vendor:publish --tag="api-scaffold-config"
```

### Publish Stubs (Optional)

Publish the stub templates if you want to customize the generated files:

```bash
php artisan vendor:publish --tag="api-scaffold-stubs"
```

## Usage

### Basic Usage

Generate a service with its interface:

```bash
php artisan make:service-api Comment
```

This creates:
- `app/Services/Comment/CommentService.php`
- `app/Services/Comment/CommentServiceInterface.php`
- Automatically registers the binding in `AppServiceProvider`

### Generate with API Methods

Create a service with predefined CRUD methods:

```bash
php artisan make:service-api Comment --api
```

This generates a service with the following methods:
- `index()` - Get paginated list
- `show($id)` - Get single record
- `store(array $data)` - Create new record
- `update($id, array $data)` - Update existing record
- `destroy($id)` - Delete record

### Generate All Related Files

Create a complete API resource with one command:

```bash
php artisan make:service-api Comment --all
```

This generates:
- Service and Interface
- Model
- Migration
- Controller (with service injection)
- Form Request
- API Resource
- Feature Test

### Granular Control

Generate only specific components:

```bash
# Service with controller and request only
php artisan make:service-api Comment --controller --request

# Service with model and migration only
php artisan make:service-api Comment --model --migration

# Service with API methods and tests
php artisan make:service-api Comment --api --test
```

### Force Overwrite

Overwrite existing files (creates backups automatically):

```bash
php artisan make:service-api Comment --force
```

## Configuration

The configuration file allows you to customize various aspects of the package:

```php
return [
    // Where service files will be generated
    'service_path' => app_path('Services'),

    // Which files to generate by default
    'generate' => [
        'model' => true,
        'migration' => true,
        'controller' => true,
        'request' => true,
        'resource' => true,
        'interface' => true,
        'tests' => true,
    ],

    // Backup existing files before modification
    'backup_existing' => true,

    // Auto-register service bindings
    'auto_register_bindings' => true,

    // Path to AppServiceProvider
    'provider_path' => app_path('Providers/AppServiceProvider.php'),

    // Default API methods when using --api flag
    'api_methods' => [
        'index',
        'show',
        'store',
        'update',
        'destroy',
    ],

    // Use custom published stubs
    'use_custom_stubs' => false,

    // Namespace configuration
    'namespaces' => [
        'service' => 'App\\Services',
        'controller' => 'App\\Http\\Controllers',
        'request' => 'App\\Http\\Requests',
        'resource' => 'App\\Http\\Resources',
        'model' => 'App\\Models',
    ],
];
```

## Generated File Structure

When running `php artisan make:service-api Comment --all`, the following structure is created:

```
app/
├── Services/
│   └── Comment/
│       ├── CommentService.php
│       └── CommentServiceInterface.php
├── Http/
│   ├── Controllers/
│   │   └── CommentController.php
│   ├── Requests/
│   │   └── CommentRequest.php
│   └── Resources/
│       └── CommentResource.php
├── Models/
│   └── Comment.php
└── Providers/
    └── AppServiceProvider.php (automatically updated)

database/
└── migrations/
    └── xxxx_xx_xx_create_comments_table.php

tests/
└── Feature/
    └── CommentTest.php
```

## Example Controller

The generated controller automatically injects the service interface:

```php
<?php

namespace App\Http\Controllers;

use App\Services\Comment\CommentServiceInterface;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function __construct(
        protected CommentServiceInterface $commentService
    ) {
    }

    public function index()
    {
        $data = $this->commentService->index();
        return CommentResource::collection($data);
    }

    public function show(int $id)
    {
        $data = $this->commentService->show($id);

        if (!$data) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        return response()->json(new CommentResource($data));
    }

    // ... other methods
}
```

## Edge Cases and Advanced Usage

### Working with Existing Files

The package intelligently handles existing files:

1. **Without --force flag**: Skips existing files and warns you
2. **With --force flag**: Creates timestamped backups before overwriting

```bash
# This will backup existing files with .backup.YmdHis extension
php artisan make:service-api Comment --force
```

### Custom Service Paths

Override the default service path in your configuration:

```php
'service_path' => base_path('src/Services'),
```

### Disabling Auto-Registration

If you prefer manual service registration:

```php
'auto_register_bindings' => false,
```

Then manually register in your `AppServiceProvider`:

```php
use App\Services\Comment\CommentServiceInterface;
use App\Services\Comment\CommentService;

public function register(): void
{
    $this->app->bind(CommentServiceInterface::class, CommentService::class);
}
```

### Multi-Word Service Names

The package handles camelCase and PascalCase correctly:

```bash
php artisan make:service-api BlogPost --all
# Creates: BlogPostService, BlogPostController, etc.

php artisan make:service-api UserProfile --all
# Creates: UserProfileService, UserProfileController, etc.
```

### Nested Services

Create nested service structures:

```bash
# The service name can include subdirectories
php artisan make:service-api Blog/Post --all
```

This creates:
```
app/Services/
└── Blog/
    └── Post/
        ├── PostService.php
        └── PostServiceInterface.php
```

## Testing

Run the package tests:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## Code Quality

This package maintains high code quality standards:

- **PHPStan Level 5**: Static analysis for type safety
- **Laravel Pint**: Code style formatting
- **Pest/PHPUnit**: Comprehensive test coverage
- **GitHub Actions**: Automated CI/CD pipeline

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Submit a pull request

## Security Vulnerabilities

If you discover a security vulnerability within Laravel API Scaffold, please send an email to iamgerwin@example.com. All security vulnerabilities will be promptly addressed.

## Roadmap

- [ ] Support for custom service method templates
- [ ] Interactive mode for selecting which files to generate
- [ ] Support for API versioning structure
- [ ] Repository pattern option
- [ ] GraphQL support
- [ ] OpenAPI/Swagger documentation generation
- [ ] Service layer documentation generator
- [ ] Event and listener scaffolding
- [ ] Job/Queue scaffolding integration

## Credits

- [Gerwin](https://github.com/iamgerwin)
- Inspired by [Spatie's Package Skeleton](https://github.com/spatie/package-skeleton-laravel)
- All [Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
