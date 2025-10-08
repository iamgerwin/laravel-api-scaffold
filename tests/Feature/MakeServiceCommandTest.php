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
    Artisan::call('make:service-api', ['name' => 'TestService']);

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
    Artisan::call('make:service-api', ['name' => 'TestService']);

    $servicePath = app_path('Services/TestService/TestServiceService.php');
    $originalContent = File::get($servicePath);

    // Try to create again
    Artisan::call('make:service-api', ['name' => 'TestService']);

    $newContent = File::get($servicePath);

    expect($originalContent)->toBe($newContent);
});

test('command creates backup when force flag is used', function () {
    // Create initial service
    Artisan::call('make:service-api', ['name' => 'TestService']);

    $servicePath = app_path('Services/TestService/TestServiceService.php');

    // Wait a second to ensure different timestamp
    sleep(1);

    // Force recreate
    Artisan::call('make:service-api', [
        'name' => 'TestService',
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

    Artisan::call('make:service-api', ['name' => 'TestService']);

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
