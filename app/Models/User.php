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

    protected $fillable = [
        'name', 'firstname', 'birthday', 'phone', 'email', 'password', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date:Y-m-d',
    ];

    public function getFullNameAttribute() { return $this->firstname.' '.$this->name; }

    public function getRolesNamesAttribute() { return $this->getRoleNames()->implode(', '); }

    public function avatarUrl()
    {
        return $this->avatar
            ? Storage::disk('avatars')->url($this->avatar)
            : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email)));
    }
}
