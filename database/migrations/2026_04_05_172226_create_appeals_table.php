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

        Schema::create('appeals', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->integer('user_id')->comment('FK ke users   (  User  )   yang mengajukan banding');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('reviewed_by')->nullable()->comment('FK ke users   (  Admin  )   yang mereview banding');
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->text('reason')->comment('Alasan atau refleksi dari user  ,   biasanya diajukan saat penalty_points tinggi sehingga tidak ada alat yang bisa dipinjam');
            $table->enum('status', ["pending","approved","rejected"]);
            $table->integer('credit_changed')->nullable()->comment('Jumlah poin credit yang berubah');
            $table->text('admin_notes')->nullable()->comment('Catatan atau feedback Admin ke user');
            $table->timestamp('created_at');
            $table->timestamp('reviewed_at')->nullable()->comment('Waktu Admin memutuskan. NULL jika masih pending');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeals');
    }
};
