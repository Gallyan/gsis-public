<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Mail\NewAddress;

class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    use Notifiable, HasFactory, HasRoles;

    protected $guarded = ['email_verified_at','roles'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'birthday' => 'date:Y-m-d',
    ];

    const DOCTYPE = [
        'id' => 'id',
        'bank' => 'RIB',
        'passport' => 'passport',
        'driver' => 'driver',
        'insurance' => 'insurance',
        'car-registration' => 'car-registration',
        'loyalty' => 'loyalty',
        'season' => 'season',
        'other' => 'other',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(
            function (User $user) {
                // En cas de changement de l'adresse email, un nouveau mail de vérification est envoyé
                if (in_array('email', array_keys($user->getDirty())) && $user->created_at) {
                    $user->email_verified_at = null;
                    $user->sendEmailVerificationNotification();
                }

                // En cas de changement d'adresse un email est envoyé aux gestionnaires
                if (in_array('hom_', array_map(fn($v): string => substr($v, 0, 4), array_keys($user->getDirty())))
                    || in_array('pro_', array_map(fn($v): string => substr($v, 0, 4), array_keys($user->getDirty())))
                ) {

                    // Send an email to each manager if user is not a manager
                    if (auth()->user() && !auth()->user()->hasRole('manager')) {
                        foreach (User::role('manager')->get() as $dest) {
                            Mail::to($dest)->send(new NewAddress(auth()->user(), $dest->name));
                        }
                    }
                }
            }
        );

        static::created(
            function (User $user) {
                $user->assignRole('user');
            }
        );
    }

    public function getMissingInfoAttribute()
    {
        $fields = ['firstname' => 'First Name',
            'lastname' => 'Last Name',
            'email' => 'E-mail',
            'birthday' => 'Birthday',
            'birthplace' => 'Birthplace'];
        $missing = [];

        foreach ($fields as $field => $label) {
            if (empty($this->$field)) {
                $missing[] = __($label);
            }
        }

        return $missing;
    }

    public function getNameAttribute()
    {
        return ucwords(strtolower($this->firstname.' '.$this->lastname));
    }

    public function getRolesNamesAttribute()
    {
        return $this->getRoleNames()->map(
            function ($item, $key) {
                return __($item);
            }
        )->implode(', ');
    }

    public function getDateForHumansAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getVerifiedAttribute()
    {
        return ! is_null($this->email_verified_at);
    }

    public function getHomeAddressAttribute()
    {
        $adr = trim(
            implode(
                ' ',
                array_filter(
                    $this->toArray(), function ($key) {
                        return str_starts_with($key, 'hom_');
                    }, ARRAY_FILTER_USE_KEY
                )
            )
        );

        return $adr === '' ? null : e($adr);
    }

    public function getWorkAddressAttribute()
    {
        $adr = trim(
            implode(
                ' ',
                array_filter(
                    $this->toArray(), function ($key) {
                        return str_starts_with($key, 'pro_');
                    }, ARRAY_FILTER_USE_KEY
                )
            )
        );

        return $adr === '' ? null : e($adr);
    }

    public function avatarUrl()
    {
        return $this->avatar
            ? Storage::disk('avatars')->url($this->avatar)
            : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email)));
    }

    /**
     * Get the user's preferred locale.
     */
    public function preferredLocale(): string
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

    /**
     * Get all of the user's purchases.
     */
    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    /**
     * Get all of the user's expenses.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get number of users on platform in last $nb minutes.
     */
    public static function sinceMinutes( int $nb = null ) : int {

        return Cache::remember( 'seen_'.($nb??'0'), 30, function() use ($nb) {
            /* By defaut, number of minutes of the current day */
            if ( is_null($nb) ) {
                $nb = Carbon::now()->diffInMinutes(Carbon::now()->startOfDay());
            }

            return count(
                User::where( 'last_seen_at', '>', Carbon::now()->subMinutes( $nb )->toDateTimeString() )
                    ->get()
            );
        });
    }

}
