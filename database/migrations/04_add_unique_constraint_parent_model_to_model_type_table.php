<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Valourite\DynamicModels\Models\ModelType;

return new class extends Migration {
    public function up(): void
    {
        $tableName = config('dynamic-models.table_prefix') . ModelType::BASE_TABLE_NAME;

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->unsignedBigInteger('parent_id')->nullable()->after(ModelType::MODEL_TYPE_PARENT_MODEL);

            $table->index('parent_id', "{$tableName}_parent_idx");

            $table->foreign('parent_id', "{$tableName}_parent_fk")
                ->references(ModelType::PRIMARY_KEY)->on($tableName)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $tableName = config('dynamic-models.table_prefix') . ModelType::BASE_TABLE_NAME;

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->dropForeign(["{$tableName}_parent_fk"]);
        });

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
