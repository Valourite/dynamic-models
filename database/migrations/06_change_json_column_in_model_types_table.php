<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('dynamic-models.table_prefix') . 'model_types', function (Blueprint $table) {
            //convert the json column to longText to prevent alphanumeric sorting
            $table->longText('model_type_schema')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('dynamic-models.table_prefix') . 'model_types', function (Blueprint $table) {
            $table->json('model_type_schema')->change();
        });
    }
};
