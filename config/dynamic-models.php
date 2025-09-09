<?php

return [
    /*
     * The prefix of the tables for the dynamic models.
     */
    'table_prefix' => 'dynamic_models_',

    'navigation' => [
        /*
         * The Dynamic Models Creator resource label
         */
        'label' => null,

        /*
         * The Dynamic Models Creator resource plural label
         */
        'plural_label' => null,

        /*
         * Should add the Dynamic Models Creator resources into a navigational group
         */
        'grouped' => true,

        /*
         * The navigational group the resources will fall under
         */
        'group' => 'Model Builder',

        /*
         * The navigation icon
         */
        'icon' => \Filament\Support\Icons\Heroicon::DocumentText,

        /*
         * The navigation position
         */
        'sort' => 1,
    ],

    'versioning' => [
        /*
         * Enables versioning for dynamic models
         */
        'enabled' => true,

        /*
         * Create a new model type when the schema changes
         */
        'create_new' => true,

        /*
         * Update the version of the model only if schema changes
         */
        'update_only_on_schema_change' => true,

        /*
         * The Versioning Strategy class to use
         * Must implement \Valourite\DynamicModels\Contracts\StrategyInterface
         */
        'strategy' => \Valourite\DynamicModels\Support\DefaultStrategy::class,

        /*
         * Disables all previous versions except the latest one
         */
        'disable_previous_on_new' => false,

        /*
         * Determines the step to increment by
         */
        'increment_count' => '0.0.1',
    ],

    /*
     * The hook classes to be executed during model type create/update
     * Order matters as it determines the sequence of execution
     */
    'hooks' => [
        /*
         * Must implement \Valourite\DynamicModels\Contracts\BeforeSaveEditHookInterface
         */
        'before_save' => [
            // \App\DynamicModels\Hooks\SanitizeData::class,
        ],
        /*
         * Must implement \Valourite\DynamicModels\Contracts\AfterSaveEditHookInterface
         */
        'after_save' => [
            // \App\DynamicModels\Hooks\AuditLog::class,
        ],
    ],

    'deleting' => [
        /*
         * Allow model types to be force deleted
         */
        'allow_force_delete' => false,
    ],

    /*
     * The list of all the models that can be used as based models.
     * When a new model is created using one of the listed models, the new model recieves all the base models attributes
     * The new model will be of type base model, but with extra attributes
     */
    'parent_models' => [
        // \App\Models\User::class,
    ],

    /*
     * Default upload settings for file fields when per-field options are not provided.
     */
    'uploads' => [
        'disk'       => 'public',
        'directory'  => 'dynamic-models/uploads',
        'visibility' => 'public',
    ],
];
