# Changelog

All notable changes to `laravel-api-scaffold` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.3.1] - 2025-10-11

### Fixed
- Stub file placeholder format: Updated all stub files (Nova, Filament, Entity Documentation) to use correct `{{ placeholder }}` format with spaces
- Test coverage: Added 17 new tests to increase code coverage, now at 100 tests passing with 241 assertions
- Mock expectations: Fixed `determineAdminPanel` test with proper formatter mock setup
- Property initialization: Fixed `generateValidationRulesDoc` test by initializing modelName property

### Improved
- Test suite stability: Resolved test environment caching issues
- Code quality: All 100 tests now passing consistently

## [0.3.0] - 2025-10-11

### Added
- **Admin Panel Resource Generation**: Support for Laravel Nova and Filament admin panel resources (addresses #6)
  - Auto-detection of installed admin panels (Nova/Filament)
  - Command flags: `--nova`, `--filament`, `--admin`
  - Nova resource generation with basic field configuration
  - Filament resource generation with List, Create, and Edit pages
  - Interactive prompts for admin panel selection
  - Configuration for Nova and Filament namespaces and paths
- **Entity Documentation Generation**: Auto-generated markdown documentation for each entity (addresses #6)
  - Command flag: `--docs`
  - Comprehensive entity documentation including:
    - Model overview and database schema
    - Relationships documentation
    - API endpoint documentation
    - Service layer documentation
    - Admin panel integration details
    - Validation rules documentation
  - Configurable documentation path and options
- **New Preset**: "API Complete + Admin Panel" preset for full-stack scaffolding
- **Enhanced Interactive Mode**: Added admin panel and documentation options to component selection

### Improved
- Extended command signature with admin panel and documentation flags
- Enhanced `generateFromOptions` to support admin and docs generation
- Added tracking for model and migration generation for better context
- Updated interactive component selection with admin and docs options

## [0.2.3] - 2025-10-10

### Changed
- Updated SECURITY.md to reflect current supported version (0.2.x)

## [0.2.2] - 2025-10-10

### Fixed
- Laravel 12 compatibility: Updated `laravel/prompts` constraint to support both `^0.1.0` (Laravel 10/11) and `^0.3.0` (Laravel 12)

## [0.2.1] - 2025-10-10

### Added
- **Laravel 11+ API Auto-Setup**: Automatically detects Laravel 11+ and runs `php artisan install:api` if routes/api.php doesn't exist (addresses #4)
  - Intelligent version detection to ensure compatibility
  - Automatic Sanctum installation and API route setup
  - Seamless integration without manual intervention
- **Interactive Route Management**: New workflow for adding API routes after controller generation (addresses #4)
  - Interactive prompt asking if routes should be added
  - Two route organization strategies:
    - Append routes directly to `routes/api.php`
    - Create separate route files in `routes/api/{model}.php` with auto-include
  - Smart conflict detection to prevent duplicate routes
  - Uses Laravel's `Route::apiResource()` for RESTful endpoints

### Fixed
- Controller stub now uses service interface instead of concrete service class for proper dependency injection

### Improved
- Enhanced developer experience with automatic API setup for modern Laravel versions
- Better project organization with flexible route management options
- Reduced manual setup steps for API development
- Documentation updated with comprehensive guides for new features

## [0.2.0] - 2025-10-09

### Added
- **Interactive Mode**: New intuitive wizard-based interface for scaffolding (addresses #1)
  - Automatically launches when no flags are provided
  - Step-by-step guidance through preset selection and component customization
  - Visual preview and confirmation before file generation
- **Preset Templates**: Four predefined scaffolding templates
  - Minimal: Service and Interface only
  - API Complete: Full API scaffold with all components
  - Service Layer: Service, Interface, Model, and Tests
  - Custom: Individual component selection
- **User Preferences Caching**: Remembers last selections for faster subsequent use
- **New Command Flags**:
  - `--interactive`: Force interactive mode even when flags are provided
  - `--no-interactive`: Disable interactive mode and use CLI flags
- **Configuration Options**:
  - `interactive_mode`: Toggle interactive mode on/off globally
  - `presets`: Define custom preset configurations
  - `cache_preferences`: Enable/disable preference caching
  - `preferences_cache_path`: Customize cache file location

### Changed
- Added `laravel/prompts` dependency for enhanced CLI interactions
- Updated README with comprehensive interactive mode documentation
- Enhanced test coverage with interactive mode and preset tests

### Improved
- Developer experience: Reduced learning curve for new users
- Command discoverability: Easier to understand available options through interactive prompts
- Visual feedback: Table-based preview of components to be generated

## [0.1.4] - 2024-10-08

### Fixed
- PHPStan configuration: Removed deprecated `checkMissingIterableValueType` parameter
- PHPUnit configuration: Migrated to PHPUnit 10+ schema using `<source>` element
- Code coverage: Updated configuration structure for better compatibility with modern testing tools

## [0.1.3] - 2024-10-08

### Added
- Laravel 12 compatibility: Support for Laravel 12.x and PHP 8.4
- Extended support for Pest 3.x, PHPUnit 11.x, and PHPStan 2.x

### Fixed
- Test file generation: Ensure test directory exists before creating test files

## [0.1.2] - 2024-10-08

### Fixed
- File generation: Ensure parent directories exist before creating controller, request, and resource files
- Code coverage: Added source configuration to phpunit.xml for proper coverage reporting
- Test suite: Fixed failures when generating files in non-existent directories

## [0.1.1] - 2024-10-08

### Fixed
- PHPStan configuration: Removed invalid parameters (checkOctaneCompatibility, checkModelProperties)
- Code style issues: Fixed class attribute separation and import ordering per Laravel Pint standards

## [0.1.0] - 2024-10-08

### Added
- Initial release of Laravel API Scaffold package
- `make:service-api` artisan command for scaffolding service layer architecture
- Service class generation with constructor injection
- Service interface generation for dependency inversion
- Automatic service binding registration in AppServiceProvider
- Controller generation with service dependency injection
- Form Request validation class generation
- API Resource class generation for response transformation
- Model and migration generation integration
- Pest/PHPUnit test file generation with common test cases
- `--api` flag for generating services with predefined CRUD methods (index, show, store, update, destroy)
- `--all` flag for generating all related files in one command
- Granular options for selective file generation (--model, --migration, --controller, --request, --resource, --test)
- `--force` flag for overwriting existing files with automatic backup
- Configuration file for customizing package behavior
- Support for custom stub templates
- Automatic backup of existing files before modification
- Configurable paths and namespaces
- Safe file operations with conflict detection
- Comprehensive documentation and usage examples
- PHPStan static analysis configuration
- Laravel Pint code formatting configuration
- GitHub Actions CI/CD workflow
- MIT License
- Packagist.org compatibility

### Features
- Battle-tested service layer architecture structure
- Modular and flexible file generation
- Edge case handling for existing files
- Multi-word service name support (camelCase, PascalCase)
- Support for nested service structures
- Automatic route suggestions in command output
- Helpful next steps guidance after scaffolding

### Quality
- Full Pest test coverage for command functionality
- PHPStan level 5 static analysis
- PSR-12 coding standards via Laravel Pint
- Comprehensive README with examples and edge cases
- Contributing guidelines
- Security policy

[Unreleased]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.3.1...HEAD
[0.3.1]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.2.3...0.3.0
[0.2.3]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.2.2...0.2.3
[0.2.2]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.4...0.2.0
[0.1.4]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.3...0.1.4
[0.1.3]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.2...0.1.3
[0.1.2]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/iamgerwin/laravel-api-scaffold/releases/tag/0.1.0
