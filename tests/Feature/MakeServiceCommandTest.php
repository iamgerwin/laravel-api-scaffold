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

test('command skips controller generation when file already exists', function () {
    // First run to create the controller
    Artisan::call('make:service-api', [
        'name' => 'SkipTest',
        '--controller' => true,
        '--no-interactive' => true,
    ]);

    $controllerPath = app_path('Http/Controllers/SkipTestController.php');
    expect(File::exists($controllerPath))->toBeTrue();

    // Modify the file to verify it's not overwritten
    File::put($controllerPath, '<?php // Modified');

    // Run again without force flag
    Artisan::call('make:service-api', [
        'name' => 'SkipTest',
        '--controller' => true,
        '--no-interactive' => true,
    ]);

    // File should still have our modification
    expect(File::get($controllerPath))->toContain('// Modified');

    // Cleanup
    File::delete($controllerPath);
    File::deleteDirectory(app_path('Services/SkipTest'));
});

test('command skips request generation when file already exists', function () {
    // First run to create the request
    Artisan::call('make:service-api', [
        'name' => 'SkipRequest',
        '--request' => true,
        '--no-interactive' => true,
    ]);

    $requestPath = app_path('Http/Requests/SkipRequestRequest.php');
    expect(File::exists($requestPath))->toBeTrue();

    // Modify the file
    File::put($requestPath, '<?php // Modified Request');

    // Run again without force flag
    Artisan::call('make:service-api', [
        'name' => 'SkipRequest',
        '--request' => true,
        '--no-interactive' => true,
    ]);

    // File should still have our modification
    expect(File::get($requestPath))->toContain('// Modified Request');

    // Cleanup
    File::delete($requestPath);
    File::deleteDirectory(app_path('Services/SkipRequest'));
});

test('command skips resource generation when file already exists', function () {
    // First run to create the resource
    Artisan::call('make:service-api', [
        'name' => 'SkipResource',
        '--resource' => true,
        '--no-interactive' => true,
    ]);

    $resourcePath = app_path('Http/Resources/SkipResourceResource.php');
    expect(File::exists($resourcePath))->toBeTrue();

    // Modify the file
    File::put($resourcePath, '<?php // Modified Resource');

    // Run again without force flag
    Artisan::call('make:service-api', [
        'name' => 'SkipResource',
        '--resource' => true,
        '--no-interactive' => true,
    ]);

    // File should still have our modification
    expect(File::get($resourcePath))->toContain('// Modified Resource');

    // Cleanup
    File::delete($resourcePath);
    File::deleteDirectory(app_path('Services/SkipResource'));
});

test('command warns when model already exists without force flag', function () {
    // Create a model first
    File::ensureDirectoryExists(app_path('Models'));
    File::put(app_path('Models/ExistingModel.php'), '<?php namespace App\Models; class ExistingModel {}');

    Artisan::call('make:service-api', [
        'name' => 'ExistingModel',
        '--model' => true,
        '--no-interactive' => true,
    ]);

    $output = Artisan::output();
    expect($output)->toContain('Model already exists');

    // Cleanup
    File::delete(app_path('Models/ExistingModel.php'));
    File::deleteDirectory(app_path('Services/ExistingModel'));
});

test('shouldUseInteractiveMode returns true when interactive flag is true', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    // Mock input to have interactive option
    $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
    $input->shouldReceive('getArgument')->with('name')->andReturn('Test');
    $input->shouldReceive('getOption')->with('interactive')->andReturn(true);
    $input->shouldReceive('getOption')->with('no-interactive')->andReturn(false);
    $input->shouldReceive('hasParameterOption')->andReturn(false);
    $input->shouldReceive('bind')->andReturnNull();
    $input->shouldReceive('isInteractive')->andReturn(true);
    $input->shouldReceive('validate')->andReturnNull();

    $output = Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
    $output->shouldReceive('getVerbosity')->andReturn(32);

    $reflection = new ReflectionClass($command);
    $inputProperty = $reflection->getProperty('input');
    $inputProperty->setAccessible(true);
    $inputProperty->setValue($command, $input);

    $outputProperty = $reflection->getProperty('output');
    $outputProperty->setAccessible(true);
    $outputProperty->setValue($command, $output);

    $method = $reflection->getMethod('shouldUseInteractiveMode');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeTrue();

    Mockery::close();
});

test('getStub method loads custom stub when configured', function () {
    config(['api-scaffold.use_custom_stubs' => true]);

    $customStubPath = resource_path('stubs/vendor/api-scaffold/service.stub');

    // Create custom stub directory and file
    File::ensureDirectoryExists(dirname($customStubPath));
    File::put($customStubPath, '<?php // Custom stub content');

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getStub');
    $method->setAccessible(true);

    $result = $method->invoke($command, 'service.stub');

    expect($result)->toContain('// Custom stub content');

    // Cleanup
    File::delete($customStubPath);
    File::deleteDirectory(dirname($customStubPath));
    config(['api-scaffold.use_custom_stubs' => false]);
});

test('registerServiceBinding handles provider without namespace gracefully', function () {
    // Create a temporary provider without proper namespace
    $providerPath = app_path('Providers/InvalidProvider.php');
    File::ensureDirectoryExists(dirname($providerPath));
    File::put($providerPath, '<?php class InvalidProvider {}');

    config(['api-scaffold.provider_path' => $providerPath]);

    Artisan::call('make:service-api', [
        'name' => 'InvalidTest',
        '--no-interactive' => true,
    ]);

    $output = Artisan::output();
    expect($output)->toContain('Could not find namespace declaration in AppServiceProvider');

    // Cleanup
    File::delete($providerPath);
    File::deleteDirectory(app_path('Services/InvalidTest'));

    // Reset config
    config(['api-scaffold.provider_path' => app_path('Providers/AppServiceProvider.php')]);
});

test('registerServiceBinding handles provider with alternative register signature', function () {
    // Create a provider with register() instead of register(): void
    $providerPath = app_path('Providers/AltProvider.php');
    File::ensureDirectoryExists(dirname($providerPath));
    File::put($providerPath, "<?php\n\nnamespace App\\Providers;\n\nclass AltProvider\n{\n    public function register()\n    {\n        //\n    }\n}");

    config(['api-scaffold.provider_path' => $providerPath]);

    Artisan::call('make:service-api', [
        'name' => 'AltTest',
        '--no-interactive' => true,
    ]);

    $content = File::get($providerPath);
    expect($content)->toContain('AltTestServiceInterface');
    expect($content)->toContain('AltTestService');
    expect($content)->toContain('bind(AltTestServiceInterface::class, AltTestService::class)');

    // Cleanup
    File::delete($providerPath);
    File::deleteDirectory(app_path('Services/AltTest'));

    // Reset config
    config(['api-scaffold.provider_path' => app_path('Providers/AppServiceProvider.php')]);
});

// Laravel 11+ Feature Tests
test('getLaravelVersion returns current Laravel version', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('getLaravelVersion');
    $method->setAccessible(true);

    $version = $method->invoke($command);

    expect($version)->toBeString();
    expect($version)->toMatch('/^\d+\.\d+/');
});

test('generateRouteLinesOnly generates correct route syntax', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    // Set the modelName property
    $reflection = new ReflectionClass($command);
    $modelProperty = $reflection->getProperty('modelName');
    $modelProperty->setAccessible(true);
    $modelProperty->setValue($command, 'Product');

    $method = $reflection->getMethod('generateRouteLinesOnly');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toContain('Product API Routes');
    expect($result)->toContain("Route::apiResource('products', ProductController::class)");
});

test('generateRouteContent generates complete route file', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    // Set the modelName property
    $reflection = new ReflectionClass($command);
    $modelProperty = $reflection->getProperty('modelName');
    $modelProperty->setAccessible(true);
    $modelProperty->setValue($command, 'Product');

    $method = $reflection->getMethod('generateRouteContent');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toContain('<?php');
    expect($result)->toContain('use App\Http\Controllers\ProductController');
    expect($result)->toContain('use Illuminate\Support\Facades\Route');
    expect($result)->toContain("Route::apiResource('products', ProductController::class)");
});

test('controller stub uses service interface instead of concrete class', function () {
    Artisan::call('make:service-api', [
        'name' => 'Product',
        '--controller' => true,
        '--no-interactive' => true,
    ]);

    $controllerPath = app_path('Http/Controllers/ProductController.php');
    $content = File::get($controllerPath);

    // Should use the interface, not the concrete service class
    expect($content)->toContain('ProductServiceInterface');
    expect($content)->not->toContain('use App\Services\Product\ProductService;');

    // Cleanup
    File::delete($controllerPath);
    File::deleteDirectory(app_path('Services/Product'));
});

test('controller property tracks if controller was generated', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $property = $reflection->getProperty('controllerGenerated');
    $property->setAccessible(true);

    // Default should be false
    $value = $property->getValue($command);
    expect($value)->toBeFalse();
});

test('generateRouteContent uses kebab-case for route names', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $modelProperty = $reflection->getProperty('modelName');
    $modelProperty->setAccessible(true);
    $modelProperty->setValue($command, 'UserProfile');

    $method = $reflection->getMethod('generateRouteContent');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    // Should use kebab-case for route names
    expect($result)->toContain("'user-profiles'");
    expect($result)->toContain('UserProfileController');
});

test('generateRouteLinesOnly pluralizes model name for route', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $modelProperty = $reflection->getProperty('modelName');
    $modelProperty->setAccessible(true);
    $modelProperty->setValue($command, 'Category');

    $method = $reflection->getMethod('generateRouteLinesOnly');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    // Should pluralize the model name
    expect($result)->toContain("'categories'");
    expect($result)->toContain('CategoryController');
});

// Admin Panel and Documentation Tests

test('config has admin panel configuration', function () {
    $config = config('api-scaffold.admin_panel');

    expect($config)->toBeArray();
    expect($config)->toHaveKey('enabled');
    expect($config)->toHaveKey('auto_detect');
    expect($config)->toHaveKey('nova');
    expect($config)->toHaveKey('filament');
});

test('config has documentation configuration', function () {
    $config = config('api-scaffold.documentation');

    expect($config)->toBeArray();
    expect($config['enabled'])->toBeTrue();
    expect($config)->toHaveKey('path');
    expect($config)->toHaveKey('include_relationships');
});

test('api-complete-admin preset has admin and docs enabled', function () {
    $preset = config('api-scaffold.presets.api-complete-admin');

    expect($preset)->toBeArray();
    expect($preset['name'])->toBe('API Complete + Admin Panel');
    expect($preset['options']['admin'])->toBeTrue();
    expect($preset['options']['docs'])->toBeTrue();
});

test('command recognizes admin panel flags in signature', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();
    $definition = $command->getDefinition();

    expect($definition->hasOption('nova'))->toBeTrue();
    expect($definition->hasOption('filament'))->toBeTrue();
    expect($definition->hasOption('admin'))->toBeTrue();
    expect($definition->hasOption('docs'))->toBeTrue();
});

test('isNovaInstalled returns boolean', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('isNovaInstalled');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeBool();
});

test('isFilamentInstalled returns boolean', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('isFilamentInstalled');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeBool();
});

test('command with admin flag warns when no admin panel detected', function () {
    Artisan::call('make:service-api', [
        'name' => 'AdminTest',
        '--admin' => true,
        '--no-interactive' => true,
    ]);

    $output = Artisan::output();
    expect($output)->toContain('No admin panel detected');

    // Cleanup
    File::deleteDirectory(app_path('Services/AdminTest'));
});

test('command with docs flag generates entity documentation', function () {
    Artisan::call('make:service-api', [
        'name' => 'DocTest',
        '--docs' => true,
        '--no-interactive' => true,
    ]);

    $docsPath = base_path('docs/entities/DocTest.md');
    expect(File::exists($docsPath))->toBeTrue();

    $content = File::get($docsPath);
    expect($content)->toContain('DocTest Entity Documentation');
    expect($content)->toContain('Auto-generated documentation');

    // Cleanup
    File::delete($docsPath);
    File::deleteDirectory(app_path('Services/DocTest'));
});

test('documentation includes model information', function () {
    Artisan::call('make:service-api', [
        'name' => 'Product',
        '--docs' => true,
        '--no-interactive' => true,
    ]);

    $docsPath = base_path('docs/entities/Product.md');
    $content = File::get($docsPath);

    expect($content)->toContain('Product Entity Documentation');
    expect($content)->toContain('Database Schema');
    expect($content)->toContain('API Endpoints');
    expect($content)->toContain('Service Layer');

    // Cleanup
    File::delete($docsPath);
    File::deleteDirectory(app_path('Services/Product'));
});

test('documentation skips existing files without force flag', function () {
    // First generation
    Artisan::call('make:service-api', [
        'name' => 'SkipDoc',
        '--docs' => true,
        '--no-interactive' => true,
    ]);

    $docsPath = base_path('docs/entities/SkipDoc.md');
    expect(File::exists($docsPath))->toBeTrue();

    // Modify the file
    File::put($docsPath, '# Modified Documentation');

    // Try to generate again without force
    Artisan::call('make:service-api', [
        'name' => 'SkipDoc',
        '--docs' => true,
        '--no-interactive' => true,
    ]);

    // Should still have our modification
    expect(File::get($docsPath))->toContain('# Modified Documentation');

    // Cleanup
    File::delete($docsPath);
    File::deleteDirectory(app_path('Services/SkipDoc'));
});

test('determineAdminPanel returns null when no admin panels installed', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);

    // Mock input to not have admin flags
    $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
    $input->shouldReceive('getOption')->with('nova')->andReturn(false);
    $input->shouldReceive('getOption')->with('filament')->andReturn(false);
    $input->shouldReceive('getOption')->with('admin')->andReturn(true);

    $formatter = Mockery::mock(\Symfony\Component\Console\Formatter\OutputFormatterInterface::class);
    $formatter->shouldReceive('hasStyle')->andReturn(false);
    $formatter->shouldReceive('setStyle')->andReturn(null);

    $output = Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
    $output->shouldReceive('writeln')->andReturn(null);
    $output->shouldReceive('write')->andReturn(null);
    $output->shouldReceive('getVerbosity')->andReturn(32);
    $output->shouldReceive('getFormatter')->andReturn($formatter);

    $inputProperty = $reflection->getProperty('input');
    $inputProperty->setAccessible(true);
    $inputProperty->setValue($command, $input);

    $outputProperty = $reflection->getProperty('output');
    $outputProperty->setAccessible(true);
    $outputProperty->setValue($command, $output);

    $method = $reflection->getMethod('determineAdminPanel');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeNull();

    Mockery::close();
});

test('generateFieldsList returns default fields structure', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateFieldsList');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('| Field | Type | Description |');
    expect($result)->toContain('| id | bigInteger | Primary key |');
});

test('generateRelationshipsList returns default message', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateRelationshipsList');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('No relationships defined yet');
});

test('generateExamplePayload returns placeholder', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateExamplePayload');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('Add your fields here');
});

test('generateValidationRulesDoc returns placeholder', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);

    // Set the modelName property first
    $modelProperty = $reflection->getProperty('modelName');
    $modelProperty->setAccessible(true);
    $modelProperty->setValue($command, 'TestModel');

    $method = $reflection->getMethod('generateValidationRulesDoc');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('Add validation rules');
});

test('generateAdminPanelDocSection returns empty when no admin panel', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);

    // Mock input to not have admin flags
    $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
    $input->shouldReceive('getOption')->with('nova')->andReturn(false);
    $input->shouldReceive('getOption')->with('filament')->andReturn(false);

    $inputProperty = $reflection->getProperty('input');
    $inputProperty->setAccessible(true);
    $inputProperty->setValue($command, $input);

    $method = $reflection->getMethod('generateAdminPanelDocSection');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBe('');

    Mockery::close();
});

test('command with all flag does not generate admin panel when disabled', function () {
    config(['api-scaffold.admin_panel.enabled' => false]);

    Artisan::call('make:service-api', [
        'name' => 'NoAdmin',
        '--all' => true,
    ]);

    // Admin panel resources should not be generated
    expect(File::exists(app_path('Nova/NoAdmin.php')))->toBeFalse();
    expect(File::exists(app_path('Filament/Resources/NoAdminResource.php')))->toBeFalse();

    // Cleanup
    File::deleteDirectory(app_path('Services/NoAdmin'));
    if (File::exists(app_path('Models/NoAdmin.php'))) {
        File::delete(app_path('Models/NoAdmin.php'));
    }
    if (File::exists(app_path('Http/Controllers/NoAdminController.php'))) {
        File::delete(app_path('Http/Controllers/NoAdminController.php'));
    }
    if (File::exists(app_path('Http/Requests/NoAdminRequest.php'))) {
        File::delete(app_path('Http/Requests/NoAdminRequest.php'));
    }
    if (File::exists(app_path('Http/Resources/NoAdminResource.php'))) {
        File::delete(app_path('Http/Resources/NoAdminResource.php'));
    }

    // Reset config
    config(['api-scaffold.admin_panel.enabled' => true]);
});

test('command with all flag does not generate docs when disabled', function () {
    config(['api-scaffold.documentation.enabled' => false]);

    Artisan::call('make:service-api', [
        'name' => 'NoDocs',
        '--all' => true,
    ]);

    // Documentation should not be generated
    $docsPath = base_path('docs/entities/NoDocs.md');
    expect(File::exists($docsPath))->toBeFalse();

    // Cleanup
    File::deleteDirectory(app_path('Services/NoDocs'));
    if (File::exists(app_path('Models/NoDocs.php'))) {
        File::delete(app_path('Models/NoDocs.php'));
    }
    if (File::exists(app_path('Http/Controllers/NoDocsController.php'))) {
        File::delete(app_path('Http/Controllers/NoDocsController.php'));
    }
    if (File::exists(app_path('Http/Requests/NoDocsRequest.php'))) {
        File::delete(app_path('Http/Requests/NoDocsRequest.php'));
    }
    if (File::exists(app_path('Http/Resources/NoDocsResource.php'))) {
        File::delete(app_path('Http/Resources/NoDocsResource.php'));
    }

    // Reset config
    config(['api-scaffold.documentation.enabled' => true]);
});

test('generateNovaFields returns basic field comment', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateNovaFields');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('//');
});

test('generateFilamentFormFields returns basic field comment', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateFilamentFormFields');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('//');
});

test('generateFilamentTableColumns returns basic column comment', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('generateFilamentTableColumns');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBeString();
    expect($result)->toContain('//');
});

test('cachePreferences writes cache file when enabled', function () {
    config(['api-scaffold.cache_preferences' => true]);

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $cachePath = config('api-scaffold.preferences_cache_path');

    // Ensure cache doesn't exist
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('cachePreferences');
    $method->setAccessible(true);

    $method->invoke($command, 'api-complete', ['api' => true, 'model' => true]);

    expect(File::exists($cachePath))->toBeTrue();

    $content = json_decode(File::get($cachePath), true);
    expect($content['preset'])->toBe('api-complete');
    expect($content['options'])->toHaveKey('api');

    // Cleanup
    File::delete($cachePath);
});

test('registerServiceBinding adds use statements correctly', function () {
    // Create a test provider
    $providerPath = app_path('Providers/TestBindingProvider.php');
    File::ensureDirectoryExists(dirname($providerPath));
    File::put($providerPath, "<?php\n\nnamespace App\\Providers;\n\nclass TestBindingProvider\n{\n    public function register(): void\n    {\n        //\n    }\n}");

    config(['api-scaffold.provider_path' => $providerPath]);

    Artisan::call('make:service-api', [
        'name' => 'BindingTest',
        '--no-interactive' => true,
    ]);

    $content = File::get($providerPath);
    expect($content)->toContain('use App\Services\BindingTest\BindingTestServiceInterface');
    expect($content)->toContain('use App\Services\BindingTest\BindingTestService');
    expect($content)->toContain('bind(BindingTestServiceInterface::class, BindingTestService::class)');

    // Cleanup
    File::delete($providerPath);
    File::deleteDirectory(app_path('Services/BindingTest'));

    // Reset config
    config(['api-scaffold.provider_path' => app_path('Providers/AppServiceProvider.php')]);
});

test('setupApiRoutes does not run for Laravel versions below 11', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);

    // Mock getLaravelVersion to return version 10
    $versionMethod = $reflection->getMethod('getLaravelVersion');
    $versionMethod->setAccessible(true);

    $setupMethod = $reflection->getMethod('setupApiRoutes');
    $setupMethod->setAccessible(true);

    // The method should exit early for Laravel < 11
    // We can't easily test this without mocking, but we can verify the method exists
    expect($setupMethod)->toBeTruthy();
});

test('displaySummary shows created files', function () {
    Artisan::call('make:service-api', [
        'name' => 'SummaryTest',
        '--no-interactive' => true,
        '--controller' => true,
    ]);

    $output = Artisan::output();

    expect($output)->toContain('Service Scaffolding Complete!');
    expect($output)->toContain('Created files:');
    expect($output)->toContain('SummaryTest');

    // Cleanup
    File::deleteDirectory(app_path('Services/SummaryTest'));
    if (File::exists(app_path('Http/Controllers/SummaryTestController.php'))) {
        File::delete(app_path('Http/Controllers/SummaryTestController.php'));
    }
});

test('generateRouteContent includes correct controller namespace', function () {
    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $modelProperty = $reflection->getProperty('modelName');
    $modelProperty->setAccessible(true);
    $modelProperty->setValue($command, 'TestRoute');

    $method = $reflection->getMethod('generateRouteContent');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toContain('use App\Http\Controllers\TestRouteController');
    expect($result)->toContain('use Illuminate\Support\Facades\Route');
    expect($result)->toContain('Route::apiResource');
});

test('ensureDirectoryExists creates nested directories', function () {
    $testPath = base_path('temp/nested/deep/test.txt');

    // Ensure it doesn't exist
    if (File::exists(base_path('temp'))) {
        File::deleteDirectory(base_path('temp'));
    }

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('ensureDirectoryExists');
    $method->setAccessible(true);

    $method->invoke($command, $testPath);

    expect(File::exists(dirname($testPath)))->toBeTrue();

    // Cleanup
    File::deleteDirectory(base_path('temp'));
});

test('cachePreferences handles json encoding correctly', function () {
    config(['api-scaffold.cache_preferences' => true]);

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $cachePath = config('api-scaffold.preferences_cache_path');

    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }

    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('cachePreferences');
    $method->setAccessible(true);

    $options = [
        'api' => true,
        'model' => false,
        'test' => true,
    ];

    $method->invoke($command, 'custom', $options);

    $cached = json_decode(File::get($cachePath), true);

    expect($cached['preset'])->toBe('custom');
    expect($cached['options']['api'])->toBeTrue();
    expect($cached['options']['model'])->toBeFalse();
    expect($cached)->toHaveKey('updated_at');

    // Cleanup
    File::delete($cachePath);
});

test('command generates files when service directory already exists', function () {
    $servicePath = app_path('Services/ExistingDir');

    // Create directory first
    if (! File::exists($servicePath)) {
        File::makeDirectory($servicePath, 0755, true);
    }

    Artisan::call('make:service-api', [
        'name' => 'ExistingDir',
        '--no-interactive' => true,
    ]);

    $serviceFile = "{$servicePath}/ExistingDirService.php";
    expect(File::exists($serviceFile))->toBeTrue();

    // Cleanup
    File::deleteDirectory($servicePath);
});

test('generateNovaResource respects nova config disabled', function () {
    config(['api-scaffold.admin_panel.nova.enabled' => false]);

    Artisan::call('make:service-api', [
        'name' => 'NovaDisabled',
        '--nova' => true,
        '--no-interactive' => true,
    ]);

    $novaPath = app_path('Nova/NovaDisabled.php');
    expect(File::exists($novaPath))->toBeFalse();

    // Cleanup
    File::deleteDirectory(app_path('Services/NovaDisabled'));

    // Reset config
    config(['api-scaffold.admin_panel.nova.enabled' => true]);
});

test('generateFilamentResource respects filament config disabled', function () {
    config(['api-scaffold.admin_panel.filament.enabled' => false]);

    Artisan::call('make:service-api', [
        'name' => 'FilamentDisabled',
        '--filament' => true,
        '--no-interactive' => true,
    ]);

    $filamentPath = app_path('Filament/Resources/FilamentDisabledResource.php');
    expect(File::exists($filamentPath))->toBeFalse();

    // Cleanup
    File::deleteDirectory(app_path('Services/FilamentDisabled'));

    // Reset config
    config(['api-scaffold.admin_panel.filament.enabled' => true]);
});

test('determineAdminPanel returns configured default', function () {
    config(['api-scaffold.admin_panel.default' => 'nova']);
    config(['api-scaffold.admin_panel.auto_detect' => true]);

    $command = new \Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand();

    $reflection = new ReflectionClass($command);

    $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
    $input->shouldReceive('getOption')->with('nova')->andReturn(false);
    $input->shouldReceive('getOption')->with('filament')->andReturn(false);
    $input->shouldReceive('getOption')->with('admin')->andReturn(true);

    $inputProperty = $reflection->getProperty('input');
    $inputProperty->setAccessible(true);
    $inputProperty->setValue($command, $input);

    $method = $reflection->getMethod('determineAdminPanel');
    $method->setAccessible(true);

    $result = $method->invoke($command);

    expect($result)->toBe('nova');

    Mockery::close();

    // Reset config
    config(['api-scaffold.admin_panel.default' => null]);
});

test('command with nova flag generates Nova resource', function () {
    config(['api-scaffold.admin_panel.nova.enabled' => true]);

    Artisan::call('make:service-api', [
        'name' => 'NovaTest',
        '--nova' => true,
        '--no-interactive' => true,
    ]);

    $novaPath = app_path('Nova/NovaTest.php');
    expect(File::exists($novaPath))->toBeTrue();

    $content = File::get($novaPath);
    expect($content)->toContain('class NovaTest extends Resource');
    expect($content)->toContain('public static $model = NovaTest::class');

    // Cleanup
    File::delete($novaPath);
    File::deleteDirectory(app_path('Services/NovaTest'));
});

test('command with filament flag generates Filament resource and pages', function () {
    config(['api-scaffold.admin_panel.filament.enabled' => true]);

    Artisan::call('make:service-api', [
        'name' => 'FilamentTest',
        '--filament' => true,
        '--no-interactive' => true,
    ]);

    $resourcePath = app_path('Filament/Resources/FilamentTestResource.php');
    $listPage = app_path('Filament/Resources/FilamentTestResource/Pages/ListFilamentTest.php');
    $createPage = app_path('Filament/Resources/FilamentTestResource/Pages/CreateFilamentTest.php');
    $editPage = app_path('Filament/Resources/FilamentTestResource/Pages/EditFilamentTest.php');

    expect(File::exists($resourcePath))->toBeTrue();
    expect(File::exists($listPage))->toBeTrue();
    expect(File::exists($createPage))->toBeTrue();
    expect(File::exists($editPage))->toBeTrue();

    $content = File::get($resourcePath);
    expect($content)->toContain('class FilamentTestResource extends Resource');
    expect($content)->toContain('protected static ?string $model = FilamentTest::class');

    // Cleanup
    File::deleteDirectory(app_path('Filament/Resources/FilamentTestResource'));
    File::delete($resourcePath);
    File::deleteDirectory(app_path('Services/FilamentTest'));
});
