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
        Schema::create(config('dynamic-models.table_prefix') . 'model_instance_values', function (Blueprint $table) {
            $table->bigIncrements('model_instance_value_id');

            $table->foreignId('model_instance_id')
                ->constrained(config('dynamic-models.table_prefix') . 'model_instances', 'model_instance_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            //The name of the field that the user set
            $table->string('name');

            //unique identifer of the field
            //uuid('field-');
            $table->string('field_id');

            //value in field
            $table->text('value');

            //field type
            $table->string('type');
            $table->timestamps();

            $table->unique(['field_id', 'model_instance_id'], 'field_id_model_instance_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_instance_values');
    }
};
