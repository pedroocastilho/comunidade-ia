<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'phone', 'role', 'tem_acesso', 'acesso_expira_em', 'apelido', 'objetivo_principal', 'objetivo_secundario', 'tempo_disponivel', 'onboarding_completo_em', 'assinatura_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tem_acesso' => 'boolean',
            'acesso_expira_em' => 'date',
            'onboarding_completo_em' => 'datetime',
        ];
    }

    public function progressos()
    {
        return $this->hasMany(ProgressoAula::class);
    }

    public function minhaLista()
    {
        return $this->hasMany(MinhaLista::class);
    }

    public function questionarioRespostas()
    {
        return $this->hasMany(QuestionarioResposta::class);
    }

    public function auraScores()
    {
        return $this->hasMany(AuraScore::class);
    }

    public function jornadas()
    {
        return $this->hasMany(Jornada::class);
    }

    public function jornadaAtiva()
    {
        return $this->hasOne(Jornada::class)->where('status', 'ativa')->latest('iniciada_em');
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function auraConversas()
    {
        return $this->hasMany(AuraConversa::class);
    }

    public function auraMemorias()
    {
        return $this->hasMany(AuraMemoria::class);
    }

    public function progressoAudios()
    {
        return $this->hasMany(ProgressoAudio::class);
    }
}
