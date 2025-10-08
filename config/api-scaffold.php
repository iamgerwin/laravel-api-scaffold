<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service Layer Path
    |--------------------------------------------------------------------------
    |
    | This value determines where the service layer files will be generated.
    | By default, it will create a Services directory in the app folder.
    |
    */

    'service_path' => app_path('Services'),

    /*
    |--------------------------------------------------------------------------
    | Generate Related Files
    |--------------------------------------------------------------------------
    |
    | Configure which files should be automatically generated when creating
    | a service. Set to false to skip generation of specific file types.
    |
    */

    'generate' => [
        'model' => true,
        'migration' => true,
        'controller' => true,
        'request' => true,
        'resource' => true,
        'interface' => true,
        'tests' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Existing Files
    |--------------------------------------------------------------------------
    |
    | When set to true, existing files will be backed up before being
    | modified. Backups will be stored with a .backup timestamp extension.
    |
    */

    'backup_existing' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-Register Service Bindings
    |--------------------------------------------------------------------------
    |
    | Automatically register service interface bindings in AppServiceProvider.
    | This enables dependency injection of services via their interfaces.
    |
    */

    'auto_register_bindings' => true,

    /*
    |--------------------------------------------------------------------------
    | Service Provider Registration Path
    |--------------------------------------------------------------------------
    |
    | The path to the AppServiceProvider where service bindings will be
    | automatically registered when auto_register_bindings is enabled.
    |
    */

    'provider_path' => app_path('Providers/AppServiceProvider.php'),

    /*
    |--------------------------------------------------------------------------
    | API Methods
    |--------------------------------------------------------------------------
    |
    | Define the default API methods to generate when using the --api flag.
    | These methods will be scaffolded in the service class.
    |
    */

    'api_methods' => [
        'index',
        'show',
        'store',
        'update',
        'destroy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Stubs Path
    |--------------------------------------------------------------------------
    |
    | If you want to customize the stub files used for generation, publish
    | them and set this to true. The package will use stubs from resources.
    |
    */

    'use_custom_stubs' => false,

    /*
    |--------------------------------------------------------------------------
    | Namespace Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the namespaces for generated files. These can be customized
    | based on your application's architecture preferences.
    |
    */

    'namespaces' => [
        'service' => 'App\\Services',
        'controller' => 'App\\Http\\Controllers',
        'request' => 'App\\Http\\Requests',
        'resource' => 'App\\Http\\Resources',
        'model' => 'App\\Models',
    ],

    /*
    |--------------------------------------------------------------------------
    | Interactive Mode
    |--------------------------------------------------------------------------
    |
    | Enable interactive mode by default when no flags are provided.
    | Interactive mode provides a step-by-step wizard for file generation.
    |
    */

    'interactive_mode' => true,

    /*
    |--------------------------------------------------------------------------
    | Presets Configuration
    |--------------------------------------------------------------------------
    |
    | Define preset configurations for common use cases. Users can select
    | a preset in interactive mode to quickly scaffold common patterns.
    |
    */

    'presets' => [
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Service and Interface only',
            'options' => [
                'api' => false,
                'model' => false,
                'migration' => false,
                'controller' => false,
                'request' => false,
                'resource' => false,
                'test' => false,
            ],
        ],
        'api-complete' => [
            'name' => 'API Complete',
            'description' => 'Full API scaffold with all components',
            'options' => [
                'api' => true,
                'model' => true,
                'migration' => true,
                'controller' => true,
                'request' => true,
                'resource' => true,
                'test' => true,
            ],
        ],
        'service-layer' => [
            'name' => 'Service Layer',
            'description' => 'Service, Interface, Model, and Tests',
            'options' => [
                'api' => true,
                'model' => true,
                'migration' => false,
                'controller' => false,
                'request' => false,
                'resource' => false,
                'test' => true,
            ],
        ],
        'custom' => [
            'name' => 'Custom',
            'description' => 'Choose components individually',
            'options' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache User Preferences
    |--------------------------------------------------------------------------
    |
    | Cache the last selected preset and options for faster subsequent use.
    | Cached preferences will be suggested as defaults in interactive mode.
    |
    */

    'cache_preferences' => true,

    /*
    |--------------------------------------------------------------------------
    | Preferences Cache Path
    |--------------------------------------------------------------------------
    |
    | The file path where user preferences will be cached.
    |
    */

    'preferences_cache_path' => storage_path('app/api-scaffold-preferences.json'),

];
