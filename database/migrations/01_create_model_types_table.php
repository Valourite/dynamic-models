<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('dynamic-models.table_prefix') . 'model_types', function (Blueprint $table) {
            $table->bigIncrements('model_type_id');

            // name of mdel_type - unique
            $table->string('model_type_name', 255)->index('index_model_type_name');

            // description of model_type
            $table->text('model_type_description')->nullable();

            // confirmation message to be displayed when model is created
            $table->text('model_type_confirmation_message')->nullable();

            // Indicates if the current model_type can be used in creation
            $table->tinyInteger('can_be_created')->default(1);

            //Indicates the parent model the child models will inherit from
            //Essentially which model we will create this for
            //We use this to filter model_types when we create a model
            $table->string('model_type_parent_model', 255);

            // the schema of this model type
            $table->json('model_type_schema');

            /*
             * The current version of this model type
             * When changes are made to the schema, components may be added or removed
             * If components are removed, entities that use this schema would lose those values
             * Therefore a new version of the model_type should be created as a new record
             */
            $table->string('model_type_version', 10);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('dynamic-models.table_prefix') . 'model_types');
    }
};
