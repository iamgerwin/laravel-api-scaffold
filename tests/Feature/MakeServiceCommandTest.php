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
