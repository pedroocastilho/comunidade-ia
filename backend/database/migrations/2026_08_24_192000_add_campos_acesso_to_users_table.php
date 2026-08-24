<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'aluno'])->default('aluno')->after('phone');
            $table->boolean('tem_acesso')->default(false)->after('role');
            $table->date('acesso_expira_em')->nullable()->after('tem_acesso');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'tem_acesso', 'acesso_expira_em']);
        });
    }
};
