<?php

namespace Iamgerwin\LaravelApiScaffold\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;

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
                            {--nova : Generate Laravel Nova resource}
                            {--filament : Generate Filament resource}
                            {--admin : Generate admin panel resource (auto-detect)}
                            {--docs : Generate entity documentation}
                            {--force : Overwrite existing files}
                            {--interactive : Force interactive mode}
                            {--no-interactive : Disable interactive mode}';

    protected $description = 'Create a new service class with optional API scaffolding';

    protected string $serviceName;

    protected string $servicePath;

    protected string $modelName;

    protected array $createdFiles = [];

    protected bool $controllerGenerated = false;

    protected bool $modelGenerated = false;

    protected bool $migrationGenerated = false;

    protected ?string $migrationPath = null;

    public function handle(): int
    {
        $this->serviceName = $this->argument('name');
        $this->modelName = Str::studly($this->serviceName);

        // Determine if we should use interactive mode
        if ($this->shouldUseInteractiveMode()) {
            return $this->handleInteractive();
        }

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

        // Generate admin panel resources
        if ($generateAll || $this->option('admin') || $this->option('nova') || $this->option('filament')) {
            $this->generateAdminPanelResource();
        }

        // Generate entity documentation
        if ($generateAll || $this->option('docs')) {
            $this->generateEntityDocumentation();
        }

        $this->displaySummary();

        // Setup API routes for Laravel 11+
        $this->setupApiRoutes();

        // Handle route generation
        $this->handleRouteGeneration();

        return self::SUCCESS;
    }

    protected function shouldUseInteractiveMode(): bool
    {
        // Force interactive if --interactive flag is provided
        if ($this->option('interactive')) {
            return true;
        }

        // Disable interactive if --no-interactive flag is provided
        if ($this->option('no-interactive')) {
            return false;
        }

        // Check if any generation flags are provided
        $hasFlags = $this->option('api')
            || $this->option('model')
            || $this->option('migration')
            || $this->option('controller')
            || $this->option('request')
            || $this->option('resource')
            || $this->option('test')
            || $this->option('nova')
            || $this->option('filament')
            || $this->option('admin')
            || $this->option('docs')
            || $this->option('all');

        // Use interactive mode if no flags and config allows
        return ! $hasFlags && config('api-scaffold.interactive_mode', true);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function handleInteractive(): int
    {
        $this->info('🚀 Laravel API Scaffold - Interactive Mode');
        $this->newLine();

        // Step 1: Select preset
        $preset = $this->selectPreset();

        // Step 2: Select or customize components
        $options = $this->selectComponents($preset);

        // Step 3: Preview and confirm
        if (! $this->confirmGeneration($options)) {
            $this->warn('Operation cancelled.');

            return self::FAILURE;
        }

        // Step 4: Cache preferences if enabled
        $this->cachePreferences($preset, $options);

        // Step 5: Generate files
        return $this->generateFromOptions($options);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function selectPreset(): string
    {
        $presets = config('api-scaffold.presets', []);
        $cachedPreset = $this->getCachedPreferences()['preset'] ?? 'api-complete';

        $choices = [];
        foreach ($presets as $key => $preset) {
            $choices[$key] = $preset['name'].' - '.$preset['description'];
        }

        return select(
            label: 'Select a preset template',
            options: $choices,
            default: $cachedPreset
        );
    }

    /**
     * @codeCoverageIgnore
     */
    protected function selectComponents(string $preset): array
    {
        $presetConfig = config("api-scaffold.presets.{$preset}");
        $options = $presetConfig['options'] ?? [];

        // If custom preset, let user select components
        if ($preset === 'custom' || empty($options)) {
            $components = multiselect(
                label: 'Select components to generate',
                options: [
                    'api' => 'API Methods (CRUD operations)',
                    'model' => 'Model',
                    'migration' => 'Migration',
                    'controller' => 'Controller',
                    'request' => 'Form Request',
                    'resource' => 'API Resource',
                    'test' => 'Feature Test',
                    'admin' => 'Admin Panel Resource (Nova/Filament)',
                    'docs' => 'Entity Documentation',
                ],
                default: ['api', 'model', 'controller', 'request', 'resource', 'test']
            );

            // Convert array to boolean options
            $options = [
                'api' => in_array('api', $components),
                'model' => in_array('model', $components),
                'migration' => in_array('migration', $components),
                'controller' => in_array('controller', $components),
                'request' => in_array('request', $components),
                'resource' => in_array('resource', $components),
                'test' => in_array('test', $components),
                'admin' => in_array('admin', $components),
                'docs' => in_array('docs', $components),
            ];
        } else {
            // For predefined presets, show what will be generated and allow confirmation
            $this->newLine();
            $this->info("The '{$presetConfig['name']}' preset will generate:");
            foreach ($options as $component => $enabled) {
                if ($enabled) {
                    $this->line('  ✓ '.ucfirst($component));
                }
            }
            $this->newLine();

            $customize = confirm(
                label: 'Would you like to customize the components?',
                default: false
            );

            if ($customize) {
                $defaultComponents = [];
                foreach ($options as $component => $enabled) {
                    if ($enabled) {
                        $defaultComponents[] = $component;
                    }
                }

                $components = multiselect(
                    label: 'Select components to generate',
                    options: [
                        'api' => 'API Methods (CRUD operations)',
                        'model' => 'Model',
                        'migration' => 'Migration',
                        'controller' => 'Controller',
                        'request' => 'Form Request',
                        'resource' => 'API Resource',
                        'test' => 'Feature Test',
                        'admin' => 'Admin Panel Resource (Nova/Filament)',
                        'docs' => 'Entity Documentation',
                    ],
                    default: $defaultComponents
                );

                $options = [
                    'api' => in_array('api', $components),
                    'model' => in_array('model', $components),
                    'migration' => in_array('migration', $components),
                    'controller' => in_array('controller', $components),
                    'request' => in_array('request', $components),
                    'resource' => in_array('resource', $components),
                    'test' => in_array('test', $components),
                    'admin' => in_array('admin', $components),
                    'docs' => in_array('docs', $components),
                ];
            }
        }

        return $options;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function confirmGeneration(array $options): bool
    {
        $this->newLine();
        $this->info('📋 Generation Summary');
        $this->newLine();

        $filesToGenerate = [
            ['Component', 'Will Generate'],
            ['Service', '✓'],
            ['Interface', '✓'],
        ];

        foreach ($options as $component => $enabled) {
            if ($enabled) {
                $filesToGenerate[] = [ucfirst($component), '✓'];
            }
        }

        table(
            headers: ['Component', 'Status'],
            rows: array_slice($filesToGenerate, 1)
        );

        $this->newLine();

        return confirm(
            label: 'Proceed with generation?',
            default: true
        );
    }

    protected function getCachedPreferences(): array
    {
        if (! config('api-scaffold.cache_preferences', true)) {
            return [];
        }

        $cachePath = config('api-scaffold.preferences_cache_path');

        if (! File::exists($cachePath)) {
            return [];
        }

        try {
            $content = File::get($cachePath);

            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function cachePreferences(string $preset, array $options): void
    {
        if (! config('api-scaffold.cache_preferences', true)) {
            return;
        }

        $cachePath = config('api-scaffold.preferences_cache_path');
        $cacheDir = dirname($cachePath);

        if (! File::exists($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $preferences = [
            'preset' => $preset,
            'options' => $options,
            'updated_at' => now()->toIso8601String(),
        ];

        File::put($cachePath, json_encode($preferences, JSON_PRETTY_PRINT));
    }

    /**
     * @codeCoverageIgnore
     */
    protected function generateFromOptions(array $options): int
    {
        $this->newLine();
        $this->info("Creating service: {$this->serviceName}");

        // Create service directory structure
        $this->createServiceDirectory();

        // Generate service files
        if ($options['api']) {
            // Temporarily set the api option
            $this->input->setOption('api', true);
        }
        $this->generateService();
        $this->generateInterface();

        // Register service binding
        if (config('api-scaffold.auto_register_bindings', true)) {
            $this->registerServiceBinding();
        }

        // Generate related files based on options
        if ($options['model']) {
            $this->generateModel();
        }

        if ($options['migration']) {
            $this->generateMigration();
        }

        if ($options['controller']) {
            $this->generateController();
        }

        if ($options['request']) {
            $this->generateRequest();
        }

        if ($options['resource']) {
            $this->generateResource();
        }

        if ($options['test']) {
            $this->generateTest();
        }

        // Generate admin panel resources
        if ($options['admin'] ?? false) {
            $this->input->setOption('admin', true);
            $this->generateAdminPanelResource();
        }

        // Generate entity documentation
        if ($options['docs'] ?? false) {
            $this->input->setOption('docs', true);
            $this->generateEntityDocumentation();
        }

        $this->displaySummary();

        // Setup API routes for Laravel 11+
        $this->setupApiRoutes();

        // Handle route generation
        $this->handleRouteGeneration();

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
        $this->controllerGenerated = true;
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

        $this->modelGenerated = true;
        $this->createdFiles[] = $modelPath;
        $this->info("Created model: {$modelPath}");
    }

    protected function generateMigration(): void
    {
        $tableName = Str::snake(Str::pluralStudly($this->modelName));

        Artisan::call('make:migration', [
            'name' => "create_{$tableName}_table",
        ]);

        $this->migrationGenerated = true;

        // Find the most recent migration file for this table
        $migrationFiles = glob(database_path("migrations/*_create_{$tableName}_table.php"));
        if (! empty($migrationFiles)) {
            $this->migrationPath = end($migrationFiles);
        }

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

    protected function generateAdminPanelResource(): void
    {
        if (! config('api-scaffold.admin_panel.enabled', true)) {
            return;
        }

        // Determine which admin panel to generate for
        $adminPanel = $this->determineAdminPanel();

        if (! $adminPanel) {
            return;
        }

        if ($adminPanel === 'nova') {
            $this->generateNovaResource();
        } elseif ($adminPanel === 'filament') {
            $this->generateFilamentResource();
        }
    }

    protected function determineAdminPanel(): ?string
    {
        // Check explicit flags first
        if ($this->option('nova')) {
            return 'nova';
        }

        if ($this->option('filament')) {
            return 'filament';
        }

        // Auto-detect if --admin flag is used or config enables auto-detect
        if (! $this->option('admin') && ! config('api-scaffold.admin_panel.auto_detect', true)) {
            return null;
        }

        // Check default from config
        $default = config('api-scaffold.admin_panel.default');
        if ($default && in_array($default, ['nova', 'filament'])) {
            return $default;
        }

        // Auto-detect installed admin panels
        if ($this->isNovaInstalled()) {
            return 'nova';
        }

        if ($this->isFilamentInstalled()) {
            return 'filament';
        }

        $this->warn('⚠ No admin panel detected. Install Laravel Nova or Filament to generate admin resources.');

        return null;
    }

    protected function isNovaInstalled(): bool
    {
        return class_exists('Laravel\Nova\Nova');
    }

    protected function isFilamentInstalled(): bool
    {
        return class_exists('Filament\Filament');
    }

    protected function generateNovaResource(): void
    {
        if (! config('api-scaffold.admin_panel.nova.enabled', true)) {
            return;
        }

        $resourceName = $this->modelName;
        $namespace = config('api-scaffold.admin_panel.nova.namespace', 'App\\Nova');
        $path = config('api-scaffold.admin_panel.nova.path', app_path('Nova'));
        $filePath = "{$path}/{$resourceName}.php";

        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub('nova.resource.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => $namespace,
            'className' => $resourceName,
            'modelClass' => $this->modelName,
            'modelNamespace' => config('api-scaffold.namespaces.model', 'App\\Models'),
            'fieldImports' => '',
            'fields' => $this->generateNovaFields(),
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created Nova resource: {$filePath}");
    }

    protected function generateNovaFields(): string
    {
        // Basic fields - can be enhanced with migration parsing
        return <<<'PHP'

            //
PHP;
    }

    protected function generateFilamentResource(): void
    {
        if (! config('api-scaffold.admin_panel.filament.enabled', true)) {
            return;
        }

        $resourceName = "{$this->modelName}Resource";
        $namespace = config('api-scaffold.admin_panel.filament.namespace', 'App\\Filament\\Resources');
        $path = config('api-scaffold.admin_panel.filament.path', app_path('Filament/Resources'));
        $filePath = "{$path}/{$resourceName}.php";

        if ($this->fileExists($filePath)) {
            return;
        }

        // Generate resource file
        $stub = $this->getStub('filament.resource.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => $namespace,
            'className' => $resourceName,
            'modelClass' => $this->modelName,
            'modelNamespace' => config('api-scaffold.namespaces.model', 'App\\Models'),
            'formFields' => $this->generateFilamentFormFields(),
            'tableColumns' => $this->generateFilamentTableColumns(),
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created Filament resource: {$filePath}");

        // Generate page files
        $this->generateFilamentPages($resourceName, $namespace, $path);
    }

    protected function generateFilamentPages(string $resourceName, string $namespace, string $path): void
    {
        $pagesPath = "{$path}/{$resourceName}/Pages";
        $pagesNamespace = "{$namespace}\\{$resourceName}\\Pages";

        // List page
        $this->generateFilamentPage(
            'filament.resource.list-page.stub',
            "{$pagesPath}/List{$this->modelName}.php",
            $pagesNamespace,
            $resourceName,
            $namespace
        );

        // Create page
        $this->generateFilamentPage(
            'filament.resource.create-page.stub',
            "{$pagesPath}/Create{$this->modelName}.php",
            $pagesNamespace,
            $resourceName,
            $namespace
        );

        // Edit page
        $this->generateFilamentPage(
            'filament.resource.edit-page.stub',
            "{$pagesPath}/Edit{$this->modelName}.php",
            $pagesNamespace,
            $resourceName,
            $namespace
        );
    }

    protected function generateFilamentPage(
        string $stubName,
        string $filePath,
        string $namespace,
        string $resourceName,
        string $resourceNamespace
    ): void {
        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub($stubName);

        $content = $this->replaceStubPlaceholders($stub, [
            'namespace' => $namespace,
            'className' => $resourceName,
            'modelClass' => $this->modelName,
            'resourceNamespace' => $resourceNamespace,
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
    }

    protected function generateFilamentFormFields(): string
    {
        // Basic fields - can be enhanced with migration parsing
        return <<<'PHP'
                //
PHP;
    }

    protected function generateFilamentTableColumns(): string
    {
        // Basic columns - can be enhanced with migration parsing
        return <<<'PHP'
                //
PHP;
    }

    protected function generateEntityDocumentation(): void
    {
        if (! config('api-scaffold.documentation.enabled', true)) {
            return;
        }

        $docsPath = config('api-scaffold.documentation.path', base_path('docs/entities'));
        $filePath = "{$docsPath}/{$this->modelName}.md";

        if ($this->fileExists($filePath)) {
            return;
        }

        $stub = $this->getStub('entity.documentation.stub');

        $content = $this->replaceStubPlaceholders($stub, [
            'modelClass' => $this->modelName,
            'modelNamespace' => config('api-scaffold.namespaces.model', 'App\\Models'),
            'tableName' => Str::snake(Str::pluralStudly($this->modelName)),
            'modelPlural' => Str::plural($this->modelName),
            'modelKebab' => Str::kebab(Str::plural($this->modelName)),
            'generatedDate' => now()->toDateTimeString(),
            'fieldsList' => $this->generateFieldsList(),
            'relationshipsList' => $this->generateRelationshipsList(),
            'exampleCreatePayload' => $this->generateExamplePayload(),
            'exampleUpdatePayload' => $this->generateExamplePayload(),
            'adminPanelSection' => $this->generateAdminPanelDocSection(),
            'validationRules' => $this->generateValidationRulesDoc(),
        ]);

        $this->ensureDirectoryExists($filePath);
        File::put($filePath, $content);
        $this->createdFiles[] = $filePath;
        $this->info("Created entity documentation: {$filePath}");
    }

    protected function generateFieldsList(): string
    {
        return '| Field | Type | Description |
|-------|------|-------------|
| id | bigInteger | Primary key |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |';
    }

    protected function generateRelationshipsList(): string
    {
        return '*No relationships defined yet. Update this section as you add relationships to the model.*';
    }

    protected function generateExamplePayload(): string
    {
        return '  // Add your fields here';
    }

    protected function generateAdminPanelDocSection(): string
    {
        $sections = [];

        if ($this->isNovaInstalled() && $this->option('nova')) {
            $sections[] = '
## Laravel Nova Admin Panel

**Resource:** `App\Nova\\'.$this->modelName.'`

Nova resource has been generated with basic field configuration. Customize the fields, filters, and actions as needed.';
        }

        if ($this->isFilamentInstalled() && $this->option('filament')) {
            $sections[] = '
## Filament Admin Panel

**Resource:** `App\Filament\Resources\\'.$this->modelName.'Resource`

Filament resource has been generated with:
- Form builder configuration
- Table builder configuration
- List, Create, and Edit pages

Customize the form fields and table columns as needed.';
        }

        return empty($sections) ? '' : implode("\n", $sections);
    }

    protected function generateValidationRulesDoc(): string
    {
        return '*Add validation rules to `App\Http\Requests\\'.$this->modelName.'Request`*';
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

        // @codeCoverageIgnoreStart
        $this->error("Stub file not found: {$name}");
        exit(1);
        // @codeCoverageIgnoreEnd
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

    protected function setupApiRoutes(): void
    {
        // Check if Laravel version is 11 or higher
        $laravelVersion = $this->getLaravelVersion();

        if (version_compare($laravelVersion, '11.0', '<')) {
            return;
        }

        // Check if API routes already exist
        $apiRoutesPath = base_path('routes/api.php');

        if (File::exists($apiRoutesPath)) {
            return;
        }

        $this->newLine();
        $this->info('📦 Laravel 11+ detected - Setting up API routes...');

        try {
            Artisan::call('install:api', [], $this->getOutput());
            $this->info('✓ API routes installed successfully');
        } catch (\Exception $e) {
            $this->warn('⚠ Could not install API routes automatically. Please run: php artisan install:api');
        }
    }

    protected function getLaravelVersion(): string
    {
        return app()->version();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function handleRouteGeneration(): void
    {
        // Only offer route generation if controller was created
        if (! $this->controllerGenerated) {
            return;
        }

        // Check if routes/api.php exists
        $apiRoutesPath = base_path('routes/api.php');

        if (! File::exists($apiRoutesPath)) {
            $this->warn('⚠ routes/api.php not found. Please create API routes first.');

            return;
        }

        $this->newLine();

        // Ask if user wants to add routes
        $addRoutes = confirm(
            label: 'Would you like to add API routes for this resource?',
            default: true
        );

        if (! $addRoutes) {
            return;
        }

        // Ask for route generation method
        $method = select(
            label: 'How would you like to manage the routes?',
            options: [
                'append' => 'Append to routes/api.php',
                'separate' => "Create separate file routes/api/{$this->modelName}.php",
            ],
            default: 'append'
        );

        if ($method === 'separate') {
            $this->createSeparateRouteFile();
        } else {
            $this->appendToApiRoutes();
        }
    }

    protected function createSeparateRouteFile(): void
    {
        $routeFileName = Str::snake($this->modelName);
        $routeFilePath = base_path("routes/api/{$routeFileName}.php");

        // Ensure directory exists
        $this->ensureDirectoryExists($routeFilePath);

        // Generate route content
        $routeContent = $this->generateRouteContent();

        // Write the route file
        File::put($routeFilePath, $routeContent);

        $this->info("✓ Created route file: routes/api/{$routeFileName}.php");

        // Add include statement to api.php
        $this->addRouteInclude($routeFileName);
    }

    protected function appendToApiRoutes(): void
    {
        $apiRoutesPath = base_path('routes/api.php');
        $currentContent = File::get($apiRoutesPath);

        // Generate route lines
        $routeLines = $this->generateRouteLinesOnly();

        // Check if routes already exist
        $controllerName = "{$this->modelName}Controller";
        if (Str::contains($currentContent, $controllerName)) {
            $this->warn("⚠ Routes for {$controllerName} may already exist in routes/api.php");

            return;
        }

        // Append routes
        $newContent = rtrim($currentContent)."\n\n".$routeLines;
        File::put($apiRoutesPath, $newContent);

        $this->info('✓ Routes added to routes/api.php');
    }

    protected function addRouteInclude(string $routeFileName): void
    {
        $apiRoutesPath = base_path('routes/api.php');
        $currentContent = File::get($apiRoutesPath);

        $includeLine = "require __DIR__.'/api/{$routeFileName}.php';";

        // Check if include already exists
        if (Str::contains($currentContent, $includeLine)) {
            return;
        }

        // Add include at the end
        $newContent = rtrim($currentContent)."\n{$includeLine}\n";
        File::put($apiRoutesPath, $newContent);

        $this->info('✓ Added route include to routes/api.php');
    }

    protected function generateRouteContent(): string
    {
        $controllerName = "{$this->modelName}Controller";
        $controllerNamespace = config('api-scaffold.namespaces.controller', 'App\\Http\\Controllers');
        $routeName = Str::kebab(Str::plural($this->modelName));

        return <<<PHP
<?php

use {$controllerNamespace}\\{$controllerName};
use Illuminate\Support\Facades\Route;

{$this->generateRouteLinesOnly()}

PHP;
    }

    protected function generateRouteLinesOnly(): string
    {
        $controllerName = "{$this->modelName}Controller";
        $routeName = Str::kebab(Str::plural($this->modelName));

        return <<<PHP
// {$this->modelName} API Routes
Route::apiResource('{$routeName}', {$controllerName}::class);
PHP;
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
