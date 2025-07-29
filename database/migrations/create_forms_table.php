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
        Schema::create(config('form-builder.table_prefix') . 'forms', function (Blueprint $table) {
            $table->bigIncrements('form_id');

            // name of form - unique
            $table->string('form_name', 255)->index('index_form_name');

            // the name of the form as a slug
            $table->string('form_slug', 255);

            // description of form
            $table->text('form_description')->nullable();

            // confirmation message to be displayed when form is submitted
            $table->text('form_confirmation_message')->nullable();

            // Indicates if the current form is active
            $table->tinyInteger('is_active')->default(1);

            // Indicates which model this form belongs to
            //We use this to filter forms when we create a model that needs a form
            $table->string('form_model', 255);

            // the content of the form
            $table->json('form_content');

            /*
             * The current version of the form
             * When changes are made to the form, components may be added or removed
             * If components are removed, entities that use this form would lose those values
             * Therefore a new version of the form should be created as a new record
             */
            $table->string('form_version', 10);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('form-builder.table_prefix') . 'forms');
    }
};
