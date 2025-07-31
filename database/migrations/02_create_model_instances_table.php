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
        Schema::create(config('dynamic-models.table_prefix') . 'model_instances', function (Blueprint $table) {
            $table->bigIncrements('model_instance_id');

            //Reference the id of the form table
            $table->foreignId('model_type_id')->constrained(config('dynamic-models.table_prefix') . 'model_types', 'model_type_id')->cascadeOnDelete()->cascadeOnUpdate();

            //Reference the id of the parent model
            //We cannot constrain the foreignId as we do not know the model
            $table->morphs('parent_model', 'parent_model_index');

            // Form response data
            $table->json('model_instance_data');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_instances');
    }
};
