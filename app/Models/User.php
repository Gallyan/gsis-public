<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Translation\HasLocalePreference;

class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
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
        'car'       => 'car-registration',
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

    public function getMissingInfoAttribute() {
        $fields = [ 'firstname'  => 'First Name',
                    'lastname'   => 'Last Name',
                    'email'      => 'E-mail',
                    'birthday'   => 'Birthday',
                    'birthplace' => 'Birthplace' ];
        $missing = [];

        foreach( $fields as $field=>$label ) {
            if ( empty($this->$field) ) $missing[] = __($label);
        }

        return $missing;
    }

    public function getNameAttribute() { return $this->firstname.' '.$this->lastname; }

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
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        return $this->locale;
    }

    /**
     * Get all of the user's documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get all of the user's orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all of the user's purchases.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
