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
        // 1. Penyakit (Static References)
        Schema::create('penyakit', function (Blueprint $table) {
            $table->string('kode_penyakit', 5)->primary(); // P01..P05
            $table->string('nama_penyakit', 50);
            $table->text('deskripsi')->nullable();
            $table->text('rekomendasi_penanganan')->nullable();
        });

        // 2. Pohon (Optional tree codes)
        Schema::create('pohon', function (Blueprint $table) {
            $table->uuid('id_pohon')->primary();
            $table->string('kode_pohon', 20);
            $table->string('grup', 50);
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['kode_pohon', 'grup']);
        });

        // 3. Klasifikasi (Classification Results)
        Schema::create('klasifikasi', function (Blueprint $table) {
            $table->uuid('id_klasifikasi')->primary();
            $table->uuid('id_pohon')->nullable();
            $table->string('kode_penyakit', 5);
            $table->string('path_citra', 255);
            $table->string('label_prediksi', 50);
            $table->double('confidence_score');
            $table->timestamp('waktu_klasifikasi')->useCurrent();

            $table->foreign('id_pohon')->references('id_pohon')->on('pohon')->onDelete('set null');
            $table->foreign('kode_penyakit')->references('kode_penyakit')->on('penyakit')->onDelete('cascade');
        });

        // 4. Detail Probabilitas (5-class probabilities)
        Schema::create('detail_probabilitas', function (Blueprint $table) {
            $table->uuid('id_detail')->primary();
            $table->uuid('id_klasifikasi');
            $table->string('kode_penyakit', 5);
            $table->double('nilai_probabilitas');

            $table->foreign('id_klasifikasi')->references('id_klasifikasi')->on('klasifikasi')->onDelete('cascade');
            $table->foreign('kode_penyakit')->references('kode_penyakit')->on('penyakit')->onDelete('cascade');
        });

        // 5. Konsultasi Chat (LLM Conversation)
        Schema::create('konsultasi_chat', function (Blueprint $table) {
            $table->uuid('id_chat')->primary();
            $table->uuid('id_klasifikasi');
            $table->string('role', 10); // 'user' or 'assistant'
            $table->text('pesan');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_klasifikasi')->references('id_klasifikasi')->on('klasifikasi')->onDelete('cascade');
        });

        // 6. Model Metrics Fold
        Schema::create('model_metrics_fold', function (Blueprint $table) {
            $table->integer('fold')->primary(); // 1..5
            $table->double('accuracy');
            $table->double('precision');
            $table->double('recall');
            $table->double('f1');
        });

        // 7. Dataset Distribusi
        Schema::create('dataset_distribusi', function (Blueprint $table) {
            $table->string('kode_penyakit', 5)->primary();
            $table->integer('jumlah_citra');

            $table->foreign('kode_penyakit')->references('kode_penyakit')->on('penyakit')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_distribusi');
        Schema::dropIfExists('model_metrics_fold');
        Schema::dropIfExists('konsultasi_chat');
        Schema::dropIfExists('detail_probabilitas');
        Schema::dropIfExists('klasifikasi');
        Schema::dropIfExists('pohon');
        Schema::dropIfExists('penyakit');
    }
};
