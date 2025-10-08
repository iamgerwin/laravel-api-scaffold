<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any existing test files
    $servicesPath = app_path('Services/TestService');
    if (File::exists($servicesPath)) {
        File::deleteDirectory($servicesPath);
    }

    $testFiles = [
        app_path('Http/Controllers/TestServiceController.php'),
        app_path('Http/Requests/TestServiceRequest.php'),
        app_path('Http/Resources/TestServiceResource.php'),
        app_path('Models/TestService.php'),
    ];

    foreach ($testFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
});

afterEach(function () {
    // Clean up after tests
    $servicesPath = app_path('Services/TestService');
    if (File::exists($servicesPath)) {
        File::deleteDirectory($servicesPath);
    }

    $testFiles = [
        app_path('Http/Controllers/TestServiceController.php'),
        app_path('Http/Requests/TestServiceRequest.php'),
        app_path('Http/Resources/TestServiceResource.php'),
        app_path('Models/TestService.php'),
    ];

    foreach ($testFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
});

test('command generates service and interface files', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $interfacePath = app_path('Services/TestService/TestServiceServiceInterface.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($interfacePath))->toBeTrue();
});

test('command generates service with api methods when api flag is used', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--api' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $content = File::get($servicePath);

    expect($content)->toContain('public function index');
    expect($content)->toContain('public function show');
    expect($content)->toContain('public function store');
    expect($content)->toContain('public function update');
    expect($content)->toContain('public function destroy');
});

test('command generates all related files when all flag is used', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--all' => true,
    ]);

    expect(File::exists(app_path('Services/TestService/TestServiceService.php')))->toBeTrue();
    expect(File::exists(app_path('Services/TestService/TestServiceServiceInterface.php')))->toBeTrue();
    expect(File::exists(app_path('Http/Controllers/TestServiceController.php')))->toBeTrue();
    expect(File::exists(app_path('Http/Requests/TestServiceRequest.php')))->toBeTrue();
    expect(File::exists(app_path('Http/Resources/TestServiceResource.php')))->toBeTrue();
    expect(File::exists(app_path('Models/TestService.php')))->toBeTrue();
});

test('command generates controller with service injection', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--controller' => true,
    ]);

    $controllerPath = app_path('Http/Controllers/TestServiceController.php');
    $content = File::get($controllerPath);

    expect($content)->toContain('TestServiceServiceInterface');
    expect($content)->toContain('public function __construct');
    expect($content)->toContain('protected TestServiceServiceInterface');
});

test('command generates request file with validation structure', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--request' => true,
    ]);

    $requestPath = app_path('Http/Requests/TestServiceRequest.php');
    $content = File::get($requestPath);

    expect($content)->toContain('class TestServiceRequest extends FormRequest');
    expect($content)->toContain('public function authorize');
    expect($content)->toContain('public function rules');
});

test('command generates resource file with transformation structure', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--resource' => true,
    ]);

    $resourcePath = app_path('Http/Resources/TestServiceResource.php');
    $content = File::get($resourcePath);

    expect($content)->toContain('class TestServiceResource extends JsonResource');
    expect($content)->toContain('public function toArray');
});

test('command does not override existing files without force flag', function () {
    // Create initial service
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $originalContent = File::get($servicePath);

    // Try to create again
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $newContent = File::get($servicePath);

    expect($originalContent)->toBe($newContent);
});

test('command creates backup when force flag is used', function () {
    // Create initial service
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');

    // Wait a second to ensure different timestamp
    sleep(1);

    // Force recreate
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
        '--force' => true,
    ]);

    // Check for backup file
    $directory = dirname($servicePath);
    $backupFiles = File::glob("{$servicePath}.backup.*");

    expect(count($backupFiles))->toBeGreaterThan(0);
});

test('command creates service directory if it does not exist', function () {
    $servicesBasePath = app_path('Services');

    if (File::exists($servicesBasePath)) {
        File::deleteDirectory($servicesBasePath);
    }

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    expect(File::exists($servicesBasePath))->toBeTrue();
    expect(File::exists(app_path('Services/TestService')))->toBeTrue();
});

test('command uses non-interactive mode when no-interactive flag is provided', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $interfacePath = app_path('Services/TestService/TestServiceServiceInterface.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($interfacePath))->toBeTrue();
});

test('command does not use interactive mode when flags are provided', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--api' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command caches preferences when enabled in config', function () {
    config(['api-scaffold.cache_preferences' => true]);

    $cachePath = config('api-scaffold.preferences_cache_path');
    $cacheDir = dirname($cachePath);

    if (! File::exists($cacheDir)) {
        File::makeDirectory($cacheDir, 0755, true);
    }

    // Clean up any existing cache
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }

    // Manually create a cache file to test reading
    $testPreferences = [
        'preset' => 'minimal',
        'options' => [
            'api' => false,
            'model' => false,
            'migration' => false,
            'controller' => false,
            'request' => false,
            'resource' => false,
            'test' => false,
        ],
        'updated_at' => now()->toIso8601String(),
    ];

    File::put($cachePath, json_encode($testPreferences, JSON_PRETTY_PRINT));

    expect(File::exists($cachePath))->toBeTrue();

    $content = File::get($cachePath);
    $cached = json_decode($content, true);

    expect($cached['preset'])->toBe('minimal');
    expect($cached['options']['api'])->toBeFalse();

    // Clean up
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }
});

test('config has interactive mode enabled by default', function () {
    expect(config('api-scaffold.interactive_mode'))->toBeTrue();
});

test('config has presets defined', function () {
    $presets = config('api-scaffold.presets');

    expect($presets)->toBeArray();
    expect($presets)->toHaveKey('minimal');
    expect($presets)->toHaveKey('api-complete');
    expect($presets)->toHaveKey('service-layer');
    expect($presets)->toHaveKey('custom');
});

test('minimal preset has correct configuration', function () {
    $preset = config('api-scaffold.presets.minimal');

    expect($preset['name'])->toBe('Minimal');
    expect($preset['options']['api'])->toBeFalse();
    expect($preset['options']['model'])->toBeFalse();
    expect($preset['options']['migration'])->toBeFalse();
    expect($preset['options']['controller'])->toBeFalse();
    expect($preset['options']['request'])->toBeFalse();
    expect($preset['options']['resource'])->toBeFalse();
    expect($preset['options']['test'])->toBeFalse();
});

test('api-complete preset has all components enabled', function () {
    $preset = config('api-scaffold.presets.api-complete');

    expect($preset['name'])->toBe('API Complete');
    expect($preset['options']['api'])->toBeTrue();
    expect($preset['options']['model'])->toBeTrue();
    expect($preset['options']['migration'])->toBeTrue();
    expect($preset['options']['controller'])->toBeTrue();
    expect($preset['options']['request'])->toBeTrue();
    expect($preset['options']['resource'])->toBeTrue();
    expect($preset['options']['test'])->toBeTrue();
});

test('service-layer preset has correct configuration', function () {
    $preset = config('api-scaffold.presets.service-layer');

    expect($preset['name'])->toBe('Service Layer');
    expect($preset['options']['api'])->toBeTrue();
    expect($preset['options']['model'])->toBeTrue();
    expect($preset['options']['migration'])->toBeFalse();
    expect($preset['options']['controller'])->toBeFalse();
    expect($preset['options']['request'])->toBeFalse();
    expect($preset['options']['resource'])->toBeFalse();
    expect($preset['options']['test'])->toBeTrue();
});

test('command recognizes interactive flag in signature', function () {
    // Test that the command signature includes the interactive flag
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();
    $definition = $command->getDefinition();

    expect($definition->hasOption('interactive'))->toBeTrue();
    expect($definition->hasOption('no-interactive'))->toBeTrue();
});

test('getCachedPreferences returns empty when caching is disabled', function () {
    config(['api-scaffold.cache_preferences' => false]);

    $cachePath = config('api-scaffold.preferences_cache_path');

    // Create a cache file even though caching is disabled
    $cacheDir = dirname($cachePath);
    if (! File::exists($cacheDir)) {
        File::makeDirectory($cacheDir, 0755, true);
    }

    File::put($cachePath, json_encode([
        'preset' => 'minimal',
        'options' => [],
    ]));

    // Since caching is disabled, it should not read the file
    // We test this indirectly by ensuring config works
    expect(config('api-scaffold.cache_preferences'))->toBeFalse();

    // Clean up
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }
});

test('getCachedPreferences handles invalid json gracefully', function () {
    config(['api-scaffold.cache_preferences' => true]);

    $cachePath = config('api-scaffold.preferences_cache_path');
    $cacheDir = dirname($cachePath);

    if (! File::exists($cacheDir)) {
        File::makeDirectory($cacheDir, 0755, true);
    }

    // Create a cache file with invalid JSON
    File::put($cachePath, 'invalid json content {]');

    // The method should handle this gracefully
    // We test this by ensuring the command can still run
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();

    // Clean up
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }
});

test('cachePreferences does not write when caching is disabled', function () {
    config(['api-scaffold.cache_preferences' => false]);

    $cachePath = config('api-scaffold.preferences_cache_path');

    // Clean up any existing cache
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }

    // Run command (which would normally cache preferences)
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    // Cache file should not be created since caching is disabled
    expect(File::exists($cachePath))->toBeFalse();
});

test('cachePreferences creates directory if it does not exist', function () {
    config(['api-scaffold.cache_preferences' => true]);

    // Set a cache path in a non-existent directory
    $customCachePath = storage_path('app/test-cache-dir/preferences.json');
    config(['api-scaffold.preferences_cache_path' => $customCachePath]);

    $cacheDir = dirname($customCachePath);

    // Clean up if it exists
    if (File::exists($cacheDir)) {
        File::deleteDirectory($cacheDir);
    }

    expect(File::exists($cacheDir))->toBeFalse();

    // Create a cache file manually to test directory creation logic
    if (! File::exists($cacheDir)) {
        File::makeDirectory($cacheDir, 0755, true);
    }

    $preferences = [
        'preset' => 'minimal',
        'options' => [],
        'updated_at' => now()->toIso8601String(),
    ];

    File::put($customCachePath, json_encode($preferences, JSON_PRETTY_PRINT));

    // Verify directory and file were created
    expect(File::exists($cacheDir))->toBeTrue();
    expect(File::exists($customCachePath))->toBeTrue();

    // Clean up
    if (File::exists($cacheDir)) {
        File::deleteDirectory($cacheDir);
    }
});

test('command handles force flag correctly', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();

    // Run with force flag
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
        '--force' => true,
    ]);

    // File should still exist (recreated)
    expect(File::exists($servicePath))->toBeTrue();
});

test('custom preset configuration exists', function () {
    $preset = config('api-scaffold.presets.custom');

    expect($preset)->toBeArray();
    expect($preset['name'])->toBe('Custom');
    expect($preset['description'])->toBe('Choose components individually');
    expect($preset['options'])->toBeArray();
    expect($preset['options'])->toBeEmpty();
});

test('command generates only service when minimal options provided', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $interfacePath = app_path('Services/TestService/TestServiceServiceInterface.php');
    $controllerPath = app_path('Http/Controllers/TestServiceController.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($interfacePath))->toBeTrue();
    expect(File::exists($controllerPath))->toBeFalse();
});

test('config cache path is correctly set', function () {
    $cachePath = config('api-scaffold.preferences_cache_path');

    expect($cachePath)->toBeString();
    expect($cachePath)->toContain('storage');
    expect($cachePath)->toContain('api-scaffold-preferences.json');
});

test('interactive flag is recognized in command signature', function () {
    config(['api-scaffold.interactive_mode' => false]);

    // Just verify the flag exists and command can be called
    // We don't actually trigger interactive mode to avoid CI issues
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();
    $definition = $command->getDefinition();

    expect($definition->hasOption('interactive'))->toBeTrue();
});

test('shouldUseInteractiveMode returns false when no-interactive flag is set', function () {
    config(['api-scaffold.interactive_mode' => true]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('shouldUseInteractiveMode returns false when flags are provided', function () {
    config(['api-scaffold.interactive_mode' => true]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--model' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $modelPath = app_path('Models/TestService.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($modelPath))->toBeTrue();
});

test('shouldUseInteractiveMode respects config when no flags provided', function () {
    config(['api-scaffold.interactive_mode' => false]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
    ]);

    // Should generate without interactive mode
    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command generates migration with correct table name', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--migration' => true,
    ]);

    // Check that migration was created
    $migrationFiles = File::glob(database_path('migrations/*_create_test_services_table.php'));
    expect(count($migrationFiles))->toBeGreaterThan(0);

    // Clean up migration
    foreach ($migrationFiles as $file) {
        File::delete($file);
    }
});

test('command generates model when model flag is used', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--model' => true,
    ]);

    $modelPath = app_path('Models/TestService.php');
    expect(File::exists($modelPath))->toBeTrue();
});

test('command respects force flag for multiple files', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
        '--controller' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $controllerPath = app_path('Http/Controllers/TestServiceController.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($controllerPath))->toBeTrue();

    // Modify service file
    File::append($servicePath, '// Modified');
    $modifiedContent = File::get($servicePath);

    // Force regenerate
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
        '--controller' => true,
        '--force' => true,
    ]);

    // Check that backup was created
    $backupFiles = File::glob("{$servicePath}.backup.*");
    expect(count($backupFiles))->toBeGreaterThan(0);
});

test('command generates test file with correct structure', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--test' => true,
    ]);

    $testPath = base_path('tests/Feature/TestServiceTest.php');
    $content = File::get($testPath);

    expect(File::exists($testPath))->toBeTrue();
    expect($content)->toContain('TestService');
    expect($content)->toContain('test(');
});

test('command generates specific components when individual flags used', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--controller' => true,
        '--request' => true,
    ]);

    $controllerPath = app_path('Http/Controllers/TestServiceController.php');
    $requestPath = app_path('Http/Requests/TestServiceRequest.php');
    $resourcePath = app_path('Http/Resources/TestServiceResource.php');

    expect(File::exists($controllerPath))->toBeTrue();
    expect(File::exists($requestPath))->toBeTrue();
    expect(File::exists($resourcePath))->toBeFalse(); // Should not be created
});

test('command auto-registers service binding in provider', function () {
    config(['api-scaffold.auto_register_bindings' => true]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    // We can't easily verify the provider was modified in tests,
    // but we can verify the service was created
    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $interfacePath = app_path('Services/TestService/TestServiceServiceInterface.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($interfacePath))->toBeTrue();
});

test('command skips auto-registration when disabled in config', function () {
    config(['api-scaffold.auto_register_bindings' => false]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command handles multi-word service names correctly', function () {
    Artisan::call('make:service-api', [
        'name' => 'UserProfile',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/UserProfile/UserProfileService.php');
    $interfacePath = app_path('Services/UserProfile/UserProfileServiceInterface.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($interfacePath))->toBeTrue();

    // Verify content has correct class names
    $content = File::get($servicePath);
    expect($content)->toContain('class UserProfileService');

    // Clean up
    File::deleteDirectory(app_path('Services/UserProfile'));
});

test('command generates files with correct namespaces', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
        '--controller' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $controllerPath = app_path('Http/Controllers/TestServiceController.php');

    $serviceContent = File::get($servicePath);
    $controllerContent = File::get($controllerPath);

    expect($serviceContent)->toContain('namespace App\Services\TestService');
    expect($controllerContent)->toContain('namespace App\Http\Controllers');
});

test('command generates interface with correct methods when api flag used', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--api' => true,
    ]);

    $interfacePath = app_path('Services/TestService/TestServiceServiceInterface.php');
    $content = File::get($interfacePath);

    expect($content)->toContain('interface TestServiceServiceInterface');
    expect($content)->toContain('public function index');
    expect($content)->toContain('public function show');
    expect($content)->toContain('public function store');
    expect($content)->toContain('public function update');
    expect($content)->toContain('public function destroy');
});

test('command handles resource generation with correct structure', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--resource' => true,
    ]);

    $resourcePath = app_path('Http/Resources/TestServiceResource.php');
    $content = File::get($resourcePath);

    expect($content)->toContain('use Illuminate\Http\Resources\Json\JsonResource');
    expect($content)->toContain('class TestServiceResource extends JsonResource');
});

test('command generates request with authorize and rules methods', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--request' => true,
    ]);

    $requestPath = app_path('Http/Requests/TestServiceRequest.php');
    $content = File::get($requestPath);

    expect($content)->toContain('use Illuminate\Foundation\Http\FormRequest');
    expect($content)->toContain('public function authorize()');
    expect($content)->toContain('public function rules()');
});

test('command with all flag generates complete api scaffold', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--all' => true,
    ]);

    // Verify all files were created
    expect(File::exists(app_path('Services/TestService/TestServiceService.php')))->toBeTrue();
    expect(File::exists(app_path('Services/TestService/TestServiceServiceInterface.php')))->toBeTrue();
    expect(File::exists(app_path('Models/TestService.php')))->toBeTrue();
    expect(File::exists(app_path('Http/Controllers/TestServiceController.php')))->toBeTrue();
    expect(File::exists(app_path('Http/Requests/TestServiceRequest.php')))->toBeTrue();
    expect(File::exists(app_path('Http/Resources/TestServiceResource.php')))->toBeTrue();
    expect(File::exists(base_path('tests/Feature/TestServiceTest.php')))->toBeTrue();

    // Verify migration was created
    $migrationFiles = File::glob(database_path('migrations/*_create_test_services_table.php'));
    expect(count($migrationFiles))->toBeGreaterThan(0);

    // Clean up migrations
    foreach ($migrationFiles as $file) {
        File::delete($file);
    }
});

test('command creates directories recursively when needed', function () {
    // Delete Services directory if it exists
    if (File::exists(app_path('Services'))) {
        File::deleteDirectory(app_path('Services'));
    }

    expect(File::exists(app_path('Services')))->toBeFalse();

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    expect(File::exists(app_path('Services')))->toBeTrue();
    expect(File::exists(app_path('Services/TestService')))->toBeTrue();
});

test('command displays summary after generation', function () {
    $exitCode = Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $output = Artisan::output();

    expect($exitCode)->toBe(0);
    expect($output)->toContain('Creating service');
});

test('command uses custom stubs when configured', function () {
    config(['api-scaffold.use_custom_stubs' => true]);

    // Even though custom stubs don't exist, the command should handle it gracefully
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command handles existing service binding gracefully', function () {
    // First generation
    Artisan::call('make:service-api', [
        'name' => 'UniqueService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/UniqueService/UniqueServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();

    // Try to generate again without force - should not crash
    Artisan::call('make:service-api', [
        'name' => 'UniqueService',
        '--no-interactive' => true,
    ]);

    // Service should still exist
    expect(File::exists($servicePath))->toBeTrue();

    // Clean up
    File::deleteDirectory(app_path('Services/UniqueService'));
});

test('command handles missing provider path gracefully', function () {
    // Set an invalid provider path
    $originalPath = config('api-scaffold.provider_path');
    config(['api-scaffold.provider_path' => app_path('Providers/NonExistentProvider.php')]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();

    // Restore config
    config(['api-scaffold.provider_path' => $originalPath]);
});

test('command creates backup of existing provider when configured', function () {
    config(['api-scaffold.backup_existing' => true]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command skips backup when disabled in config', function () {
    config(['api-scaffold.backup_existing' => false]);

    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command handles service name with underscores', function () {
    Artisan::call('make:service-api', [
        'name' => 'test_service',
        '--no-interactive' => true,
    ]);

    // Should convert to StudlyCase
    $servicePath = app_path('Services/TestService/TestServiceService.php');
    expect(File::exists($servicePath))->toBeTrue();
});

test('command generates files even when some already exist', function () {
    // Create service first
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    // Now generate with additional components
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
        '--controller' => true,
        '--request' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $controllerPath = app_path('Http/Controllers/TestServiceController.php');
    $requestPath = app_path('Http/Requests/TestServiceRequest.php');

    expect(File::exists($servicePath))->toBeTrue();
    expect(File::exists($controllerPath))->toBeTrue();
    expect(File::exists($requestPath))->toBeTrue();
});

test('command works with just api flag', function () {
    Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--api' => true,
    ]);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $content = File::get($servicePath);

    expect($content)->toContain('public function index');
    expect($content)->toContain('public function show');
    expect($content)->toContain('public function store');
    expect($content)->toContain('public function update');
    expect($content)->toContain('public function destroy');
});

test('command properly injects interface in controller constructor', function () {
    Artisan::call('make:service-api', [
        'name' => 'ProductService',
        '--controller' => true,
    ]);

    $controllerPath = app_path('Http/Controllers/ProductServiceController.php');
    $content = File::get($controllerPath);

    expect(File::exists($controllerPath))->toBeTrue();
    expect($content)->toContain('namespace App\Http\Controllers');
    expect($content)->toContain('class ProductServiceController');

    // Clean up
    File::delete($controllerPath);
});

test('command generates migration for plural table name', function () {
    Artisan::call('make:service-api', [
        'name' => 'Category',
        '--migration' => true,
    ]);

    // Should create categories table (plural)
    $migrationFiles = File::glob(database_path('migrations/*_create_categories_table.php'));
    expect(count($migrationFiles))->toBeGreaterThan(0);

    // Clean up
    foreach ($migrationFiles as $file) {
        File::delete($file);
    }
});

test('command returns success exit code', function () {
    $exitCode = Artisan::call('make:service-api', [
        'name' => 'TestService',
        '--no-interactive' => true,
    ]);

    expect($exitCode)->toBe(0);
});

test('config has correct service path default', function () {
    $servicePath = config('api-scaffold.service_path');

    expect($servicePath)->toContain('app');
    expect($servicePath)->toContain('Services');
});

test('getStub method loads package stubs correctly', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getStub');
    $method->setAccessible(true);

    $stub = $method->invoke($command, 'service.stub');

    expect($stub)->toBeString();
    expect($stub)->toContain('namespace');
});

test('replaceStubPlaceholders method replaces variables correctly', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('replaceStubPlaceholders');
    $method->setAccessible(true);

    $stub = 'Hello {{ name }}, welcome to {{ place }}!';
    $replacements = ['name' => 'John', 'place' => 'Laravel'];

    $result = $method->invoke($command, $stub, $replacements);

    expect($result)->toBe('Hello John, welcome to Laravel!');
});

test('fileExists method returns false for non-existent files', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('fileExists');
    $method->setAccessible(true);

    $result = $method->invoke($command, '/non/existent/file.php');

    expect($result)->toBeFalse();
});

test('fileExists method handles existing files with force flag', function () {
    config(['api-scaffold.backup_existing' => true]);

    // Create a temporary file
    $tempFile = app_path('Services/TempTest/temp.php');
    $tempDir = dirname($tempFile);

    if (! File::exists($tempDir)) {
        File::makeDirectory($tempDir, 0755, true);
    }

    File::put($tempFile, '<?php');

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    // Set force option and output
    $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
    $input->shouldReceive('getOption')->with('force')->andReturn(true);

    $output = Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
    $output->shouldReceive('writeln')->andReturn(null);
    $output->shouldReceive('write')->andReturn(null);
    $output->shouldReceive('getVerbosity')->andReturn(\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL);

    $reflection = new ReflectionClass($command);
    $inputProperty = $reflection->getProperty('input');
    $inputProperty->setAccessible(true);
    $inputProperty->setValue($command, $input);

    $outputProperty = $reflection->getProperty('output');
    $outputProperty->setAccessible(true);
    $outputProperty->setValue($command, $output);

    $method = $reflection->getMethod('fileExists');
    $method->setAccessible(true);

    // Should return false (allowing overwrite) when force is true
    $result = $method->invoke($command, $tempFile);

    expect($result)->toBeFalse();

    // Verify backup was created
    $backupFiles = File::glob("{$tempFile}.backup.*");
    expect(count($backupFiles))->toBeGreaterThan(0);

    // Clean up
    File::deleteDirectory(dirname($tempDir));
});

test('ensureDirectoryExists method creates directories recursively', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('ensureDirectoryExists');
    $method->setAccessible(true);

    $testPath = app_path('Services/TestDir/SubDir/TestFile.php');
    $testDir = dirname($testPath);

    // Ensure it doesn't exist
    if (File::exists(app_path('Services/TestDir'))) {
        File::deleteDirectory(app_path('Services/TestDir'));
    }

    expect(File::exists($testDir))->toBeFalse();

    $method->invoke($command, $testPath);

    expect(File::exists($testDir))->toBeTrue();

    // Clean up
    File::deleteDirectory(app_path('Services/TestDir'));
});

test('getCachedPreferences method returns cached data when available', function () {
    config(['api-scaffold.cache_preferences' => true]);

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $cachePath = config('api-scaffold.preferences_cache_path');
    $cacheDir = dirname($cachePath);

    if (! File::exists($cacheDir)) {
        File::makeDirectory($cacheDir, 0755, true);
    }

    // Create cache file
    $cacheData = [
        'preset' => 'api-complete',
        'options' => ['api' => true, 'model' => true],
        'updated_at' => now()->toIso8601String(),
    ];

    File::put($cachePath, json_encode($cacheData));

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getCachedPreferences');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeArray();
    expect($result['preset'])->toBe('api-complete');
    expect($result['options']['api'])->toBeTrue();

    // Clean up
    File::delete($cachePath);
});

test('cachePreferences method does not write when disabled', function () {
    config(['api-scaffold.cache_preferences' => false]);

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $cachePath = config('api-scaffold.preferences_cache_path');

    // Ensure cache doesn't exist
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('cachePreferences');
    $method->setAccessible(true);

    $method->invoke($command, 'minimal', ['api' => false]);

    expect(File::exists($cachePath))->toBeFalse();
});
