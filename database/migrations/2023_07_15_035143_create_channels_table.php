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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('auto_sync')->default(false);
            $table->string('youtube_id');
            $table->string('name');
            $table->string('url');
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamp('initial_sync_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
