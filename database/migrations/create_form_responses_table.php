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
            $table->foreignId('form_id')->constrained(config('form-builder.table_prefix') . 'forms')->onDelete('cascade');

            //Reference the id of the model
            $table->foreignId('model_id'); // ID of the model instance

            //Class name of the model
            $table->string('model_type');

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
