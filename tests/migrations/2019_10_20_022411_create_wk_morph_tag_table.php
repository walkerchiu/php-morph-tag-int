<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkMorphTagTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.morph-tag.tags'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('host');
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->unsignedBigInteger('order')->nullable();
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('serial');
            $table->index('identifier');
            $table->index('is_enabled');
            $table->index(['host_type', 'host_id', 'is_enabled']);
        });
        if (!config('wk-morph-tag.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.morph-tag.tags_lang'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('morph');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->text('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
        Schema::create(config('wk-core.table.morph-tag.tags_morphs'), function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id')->nullable();
            $table->morphs('morph');

            $table->foreign('tag_id')->references('id')
                  ->on(config('wk-core.table.morph-tag.tags'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['tag_id', 'morph_type', 'morph_id']);
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.morph-tag.tags_morphs'));
        Schema::dropIfExists(config('wk-core.table.morph-tag.tags_lang'));
        Schema::dropIfExists(config('wk-core.table.morph-tag.tags'));
    }
}
