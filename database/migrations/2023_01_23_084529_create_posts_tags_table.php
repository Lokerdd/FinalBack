<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('posts_tags', function (Blueprint $table) {
        $table->id();
        $table->integer('post_id');
        $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        $table->integer('tag_id');
        $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      $table->dropForeign(['post_id', 'tag_id']);
      Schema::dropIfExists('postsTags');
    }
};