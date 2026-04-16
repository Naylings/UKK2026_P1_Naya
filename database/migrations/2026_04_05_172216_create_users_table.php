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
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->string('email', 255)->comment('Email untuk login  ,   harus unik');
            $table->string('password', 255)->comment('Password ter-hash   (  bcrypt)');
            $table->enum('role', ["Admin","Employee","User"]);
            $table->integer('credit_score')->comment('Akumulasi poin pelanggaran  ,   bertambah tiap melanggar. Makin tinggi makin terbatas alat yang bisa dipinjam');
            $table->tinyInteger('is_restricted')->default(0)->comment('1 = sedang ada pinjaman aktif atau belum settlement  ,   tidak bisa ajukan pinjaman baru');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
