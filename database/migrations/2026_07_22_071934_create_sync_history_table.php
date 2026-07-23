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
        Schema::create('sync_history', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 50);
            $table->string('emp_id', 30);
            $table->string('emp_name', 50);
            $table->timestamp('event_time', 6);
            $table->boolean('status')->default(false); // 0 = gagal, 1 = sukses
            $table->text('err_msg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_history');
    }
};
