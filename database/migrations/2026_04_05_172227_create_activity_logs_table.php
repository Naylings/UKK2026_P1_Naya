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
        Schema::disableForeignKeyConstraints();

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->integer('user_id')->nullable()->comment('FK ke users yang melakukan aksi. NULL jika aksi otomatis sistem');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('action', 100)->comment('Kode aksi: loan.created  ,   loan.approved  ,   return.recorded  ,   violation.created  ,   settlement.created  ,   appeal.approved  ,   dst');
            $table->string('module', 50)->comment('Modul terkait: loans  ,   returns  ,   violations  ,   settlements  ,   appeals  ,   tools  ,   users');
            $table->text('description')->comment('Penjelasan aksi dalam bahasa natural');
            $table->text('meta')->nullable()->comment('Data konteks tambahan dalam format JSON string. Contoh: ARRAY[\\\\\\\\\"loan_id\\\\\\\\');
            $table->string('ip_address', 45)->nullable()->comment('IP address pelaku aksi');
            $table->timestamp('created_at');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
