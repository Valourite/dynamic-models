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
        Schema::create(config('form-builder.table_prefix') . 'form_responses', function (Blueprint $table) {
            $table->bigIncrements('form_response_id');

            //Reference the id of the form table
            $table->foreignId('form_id')->constrained(config('form-builder.table_prefix') . 'forms', 'form_id')->cascadeOnDelete()->cascadeOnUpdate();

            //Reference the id of the model
            //We cannot constrain the foreignId as we do not know the model
            $table->morphs('model');

            // Form response data
            $table->json('response_data');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_responses');
    }
};
