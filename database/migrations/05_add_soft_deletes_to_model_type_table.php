<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Valourite\DynamicModels\Models\ModelType;

return new class () extends Migration {
    public function up(): void
    {
        $tableName = config('dynamic-models.table_prefix') . ModelType::BASE_TABLE_NAME;

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        $tableName = config('dynamic-models.table_prefix') . ModelType::BASE_TABLE_NAME;

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
