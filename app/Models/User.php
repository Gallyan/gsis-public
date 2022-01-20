<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use HasFactory;
    use HasRoles;

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (User $user) {
            // En cas de changement de l'adresse email, un nouveau mail de vérification est envoyé
            if (in_array('email', array_keys($user->getDirty())) && $user->created_at) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
            }
        });

        static::created(function (User $user) {
            if ( is_null( $user->email_verified_at ) ) {
                $user->sendEmailVerificationNotification();
            }
        });
    }

    public function getFullNameAttribute() { return $this->firstname.' '.$this->name; }

    public function getRolesNamesAttribute() { return $this->getRoleNames()->implode(', '); }

    public function getDateForHumansAttribute() { return $this->created_at->diffForHumans(); }

    public function getVerifiedAttribute() { return !is_null($this->email_verified_at); }

    public function avatarUrl()
    {
        return $this->avatar
            ? Storage::disk('avatars')->url($this->avatar)
            : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email)));
    }
}
