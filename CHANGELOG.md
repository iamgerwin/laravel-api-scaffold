# Changelog

All notable changes to `laravel-api-scaffold` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.3...HEAD
[0.1.3]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.2...0.1.3
[0.1.2]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/iamgerwin/laravel-api-scaffold/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/iamgerwin/laravel-api-scaffold/releases/tag/0.1.0
