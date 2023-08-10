<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Process::run('rm -rf ' . base_path('storage/app/syncs'));

        Schema::create('syncs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('channel_id')->refereences('id')->on('channels')->onDelete('cascade');
            $table->string('guid');
            $table->text('title');
            $table->text('image')->nullable();
            $table->string('audius_url')->nullable();
            $table->enum('status', ['queued', 'syncing', 'synced', 'failed'])->default('queued');
            $table->boolean('automated')->default(false);
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syncs');
    }
};
