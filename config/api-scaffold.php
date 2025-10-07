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

];
