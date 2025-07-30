<?php

return [
    /*
     * Determines the step to increment by
     */
    'increment_count' => '0.0.1',

    /*
     * The prefix of the tables for the form builder.
     */
    'table_prefix' => 'form_builder_',

    /*
     * Should add the form builder resources into a navigational group
     */
    'grouped' => true,

    /*
     * The navigational group the resources will fall under
     */
    'group' => 'Form Builder',

    /*
     * A list of all the models on the system that a form can be created for.
     * These models will need to have UseResponse trait as well as have their filament resources configured
     */
    'models' => [
        // \App\Models\User::class,
    ],
];
