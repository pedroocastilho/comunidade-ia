<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alternativa gratuita ao Bunny Stream: link de video externo
     * (YouTube nao listado, Vimeo ou arquivo MP4 direto).
     * Se a aula tiver bunny_video_id, o Bunny continua tendo prioridade.
     */
    public function up(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            $table->string('video_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
