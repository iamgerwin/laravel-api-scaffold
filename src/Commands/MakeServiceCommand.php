<?php

namespace Iamgerwin\LaravelApiScaffold\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service-api {name}
                            {--api : Generate service with basic API methods}
                            {--model : Generate model}
                            {--migration : Generate migration}
                            {--controller : Generate controller}
                            {--request : Generate request}
                            {--resource : Generate resource}
                            {--test : Generate test}
                            {--all : Generate all related files}
                            {--force : Overwrite existing files}';

    protected $description = 'Create a new service class with optional API scaffolding';

    protected string $serviceName;

    protected string $servicePath;

    protected string $modelName;

    protected array $createdFiles = [];

    public function handle(): int
    {
        $this->serviceName = $this->argument('name');
        $this->modelName = Str::studly($this->serviceName);

        $this->info("Creating service: {$this->serviceName}");

        $generateAll = $this->option('all');

        // Create service directory structure
        $this->createServiceDirectory();

        // Generate service files
        $this->generateService();
        $this->generateInterface();

        // Register service binding
        if (config('api-scaffold.auto_register_bindings', true)) {
            $this->registerServiceBinding();
        }

        // Generate related files based on options
        if ($generateAll || $this->option('model')) {
            $this->generateModel();
        }

        if ($generateAll || $this->option('migration')) {
            $this->generateMigration();
        }

        if ($generateAll || $this->option('controller')) {
            $this->generateController();
        }

        if ($generateAll || $this->option('request')) {
            $this->generateRequest();
        }

        if ($generateAll || $this->option('resource')) {
            $this->generateResource();
        }

        if ($generateAll || $this->option('test')) {
            $this->generateTest();
        }

        $this->displaySummary();

        return self::SUCCESS;
    }

    protected function createServiceDirectory(): void
    {
        $basePath = config('api-scaffold.service_path', app_path('Services'));
        $this->servicePath = "{$basePath}/{$this->modelName}";

        if (! File::exists($this->servicePath)) {
            File::makeDirectory($this->servicePath, 0755, true);
            $this->info("Created directory: {$this->servicePath}");
        }
    }

    protected function generateService(): void
    {
        $serviceClass = "{$this->modelName}Service";
        $filePath = "{$this->servicePath}/{$serviceClass}.php";

        if ($this->fileExists($filePath)) {
            return;
        }

        $stubType = $this->option('api') ? 'service.api.stub' : 'service.stub';
        $stub = $this->getStub($stubType);

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => config('api-scaffold.namespaces.service', 'App\\Services')."\\{$this->modelName}",
            'class' => $serviceClass,
            'interface' => "{$this->modelName}ServiceInterface",
            'model' => $this->modelName,
            'modelNamespace' => config('api-scaffold.namespaces.model', 'App\\Models'),
            'modelVariable' => Str::camel($this->modelName),
            'methods' => '',
        ]);

        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created service: {$filePath}");
    }

    protected function generateInterface(): void
    {
        $interfaceName = "{$this->modelName}ServiceInterface";
        $filePath = "{$this->servicePath}/{$interfaceName}.php";

        if ($this->fileExists($filePath)) {
            return;
        }

        $stubType = $this->option('api') ? 'interface.api.stub' : 'interface.stub';
        $stub = $this->getStub($stubType);

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => config('api-scaffold.namespaces.service', 'App\\Services')."\\{$this->modelName}",
            'interface' => $interfaceName,
            'model' => $this->modelName,
            'modelNamespace' => config('api-scaffold.namespaces.model', 'App\\Models'),
            'modelVariable' => Str::camel($this->modelName),
            'methods' => '',
        ]);

        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created interface: {$filePath}");
    }

    protected function generateController(): void
    {
        $controllerName = "{$this->modelName}Controller";
        $filePath = app_path("Http/Controllers/{$controllerName}.php");

        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub('controller.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => config('api-scaffold.namespaces.controller', 'App\\Http\\Controllers'),
            'class' => $controllerName,
            'service' => "{$this->modelName}Service",
            'serviceInterface' => "{$this->modelName}ServiceInterface",
            'serviceNamespace' => config('api-scaffold.namespaces.service', 'App\\Services')."\\{$this->modelName}",
            'serviceVariable' => Str::camel($this->modelName).'Service',
            'request' => "{$this->modelName}Request",
            'requestNamespace' => config('api-scaffold.namespaces.request', 'App\\Http\\Requests'),
            'resource' => "{$this->modelName}Resource",
            'resourceNamespace' => config('api-scaffold.namespaces.resource', 'App\\Http\\Resources'),
            'model' => $this->modelName,
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created controller: {$filePath}");
    }

    protected function generateRequest(): void
    {
        $requestName = "{$this->modelName}Request";
        $filePath = app_path("Http/Requests/{$requestName}.php");

        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub('request.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => config('api-scaffold.namespaces.request', 'App\\Http\\Requests'),
            'class' => $requestName,
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created request: {$filePath}");
    }

    protected function generateResource(): void
    {
        $resourceName = "{$this->modelName}Resource";
        $filePath = app_path("Http/Resources/{$resourceName}.php");

        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub('resource.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => config('api-scaffold.namespaces.resource', 'App\\Http\\Resources'),
            'class' => $resourceName,
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created resource: {$filePath}");
    }

    protected function generateModel(): void
    {
        $modelPath = app_path("Models/{$this->modelName}.php");

        if (File::exists($modelPath) && ! $this->option('force')) {
            $this->warn("Model already exists: {$modelPath}");

            return;
        }

        Artisan::call('make:model', [
            'name' => $this->modelName,
        ]);

        $this->createdFiles[] = $modelPath;
        $this->info("Created model: {$modelPath}");
    }

    protected function generateMigration(): void
    {
        $tableName = Str::snake(Str::pluralStudly($this->modelName));

        Artisan::call('make:migration', [
            'name' => "create_{$tableName}_table",
        ]);

        $this->info("Created migration for table: {$tableName}");
    }

    protected function generateTest(): void
    {
        $testName = "{$this->modelName}Test";
        $filePath = base_path("tests/Feature/{$testName}.php");

        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub('test.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'model' => $this->modelName,
            'modelNamespace' => config('api-scaffold.namespaces.model', 'App\\Models'),
            'modelVariable' => Str::camel($this->modelName),
            'modelVariablePlural' => Str::plural(Str::camel($this->modelName)),
            'routeName' => Str::kebab(Str::plural($this->modelName)),
            'tableName' => Str::snake(Str::pluralStudly($this->modelName)),
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created test: {$filePath}");
    }

    protected function registerServiceBinding(): void
    {
        $providerPath = config('api-scaffold.provider_path', app_path('Providers/AppServiceProvider.php'));

        if (! File::exists($providerPath)) {
            $this->warn("AppServiceProvider not found at: {$providerPath}");

            return;
        }

        $content = File::get($providerPath);

        $interfaceClass = "{$this->modelName}ServiceInterface";
        $serviceClass = "{$this->modelName}Service";
        $serviceNamespace = config('api-scaffold.namespaces.service', 'App\\Services')."\\{$this->modelName}";

        // Check if binding already exists
        if (Str::contains($content, $interfaceClass)) {
            $this->warn('Service binding already exists in AppServiceProvider');

            return;
        }

        // Backup if configured
        if (config('api-scaffold.backup_existing', true)) {
            $backupPath = $providerPath.'.backup.'.date('YmdHis');
            File::copy($providerPath, $backupPath);
            $this->info("Backed up AppServiceProvider to: {$backupPath}");
        }

        // Add use statements
        $useStatement = "use {$serviceNamespace}\\{$interfaceClass};\nuse {$serviceNamespace}\\{$serviceClass};";

        if (! Str::contains($content, 'namespace App\\Providers;')) {
            $this->warn('Could not find namespace declaration in AppServiceProvider');

            return;
        }

        // Insert use statements after namespace
        $content = preg_replace(
            '/(namespace App\\\\Providers;)/',
            "$1\n\n{$useStatement}",
            $content,
            1
        );

        // Add binding in register method
        $binding = "\n        \$this->app->bind({$interfaceClass}::class, {$serviceClass}::class);";

        // Find register method and add binding
        if (preg_match('/public function register\(\): void\s*\{/', $content)) {
            $content = preg_replace(
                '/(public function register\(\): void\s*\{)/',
                "$1{$binding}",
                $content,
                1
            );
        } elseif (preg_match('/public function register\(\)\s*\{/', $content)) {
            $content = preg_replace(
                '/(public function register\(\)\s*\{)/',
                "$1{$binding}",
                $content,
                1
            );
        }

        File::put($providerPath, $content);
        $this->info('Registered service binding in AppServiceProvider');
    }

    protected function getStub(string $name): string
    {
        $customStubPath = resource_path("stubs/vendor/api-scaffold/{$name}");
        $packageStubPath = __DIR__."/../../resources/stubs/{$name}";

        if (config('api-scaffold.use_custom_stubs', false) && File::exists($customStubPath)) {
            return File::get($customStubPath);
        }

        if (File::exists($packageStubPath)) {
            return File::get($packageStubPath);
        }

        $this->error("Stub file not found: {$name}");
        exit(1);
    }

    protected function replaceStubPlaceholders(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{ {$key} }}", $value, $stub);
        }

        return $stub;
    }

    protected function fileExists(string $path): bool
    {
        if (! File::exists($path)) {
            return false;
        }

        if ($this->option('force')) {
            if (config('api-scaffold.backup_existing', true)) {
                $backupPath = $path.'.backup.'.date('YmdHis');
                File::copy($path, $backupPath);
                $this->info("Backed up existing file to: {$backupPath}");
            }

            return false;
        }

        $this->warn("File already exists: {$path}");

        return true;
    }

    protected function ensureDirectoryExists(string $filePath): void
    {
        $directory = dirname($filePath);

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('==============================================');
        $this->info('  Service Scaffolding Complete!');
        $this->info('==============================================');
        $this->newLine();

        if (count($this->createdFiles) > 0) {
            $this->info('Created files:');
            foreach ($this->createdFiles as $file) {
                $this->line("  - {$file}");
            }
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Update the migration file with your table schema');
        $this->line('  2. Run: php artisan migrate');
        $this->line('  3. Add validation rules to your Request class');
        $this->line('  4. Customize your Resource class output');
        $this->line('  5. Add routes to your routes/api.php file');
        $this->line('  6. Run tests: php artisan test');
        $this->newLine();
    }
}
