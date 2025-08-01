<?php

return [
    /*
     * Determines the step to increment by
     */
    'increment_count' => '0.0.1',

    /*
     * The prefix of the tables for the dynamic models.
     */
    'table_prefix' => 'dynamic_models_',

    'navigation' => [

        /**
         * The Dynamic Models Creator resource label
         */
        'label' => null,

        /**
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

        /**
         * The navigation icon
         */
        'icon' => \Filament\Support\Icons\Heroicon::DocumentText,

        /**
         * The navigation position
         */
        'sort' => 1,
    ],

    /*
     * The list of all the models that can be used as based models.
     * When a new model is created using one of the listed models, the new model recieves all the base models attributes
     * The new model will be of type base model, but with extra attributes
     */
    'parent_models' => [
        // \App\Models\User::class,
    ],
];
