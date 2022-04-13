<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasFactory, HasRoles;

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date:Y-m-d',
    ];

    const DOCTYPE = [
        'id'        => 'id',
        'bank'      => 'RIB',
        'passport'  => 'passport',
        'driver'    => 'driver',
        'insurance' => 'insurance',
        'loyalty'   => 'loyalty',
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
    }

    public function getFullNameAttribute() { return $this->firstname.' '.$this->name; }

    public function getRolesNamesAttribute() { return $this->getRoleNames()->map(function ($item, $key) { return __($item); })->implode(', '); }

    public function getDateForHumansAttribute() { return $this->created_at->diffForHumans(); }

    public function getVerifiedAttribute() { return !is_null($this->email_verified_at); }

    public function avatarUrl()
    {
        return $this->avatar
            ? Storage::disk('avatars')->url($this->avatar)
            : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email)));
    }

    /**
     * Get all of the user's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
