<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Throw exception when code is not optimum outside production
        Model::preventLazyLoading(! app()->isProduction());
        Model::shouldBeStrict(! app()->isProduction());

        // For length to get indexes on string field, spatie permission prerequisite
        Schema::defaultStringLength(125);

        // Renforcement des règles de mot de passe en production
        Password::defaults(function () {
            return App::environment('production')
                    ? Password::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
                    : Password::min(8);
        });

        // Add a validation rule for phone numbers
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $value) && (strlen($value) >= 10 || strlen(trim($value)) == 0);
        });

        Validator::replacer('phone', function ($message, $attribute, $rule, $parameters) {
            return __('Invalid phone number');
        });

        // Add a validation rule for uppercase
        Validator::extend('uppercase', function ($attribute, $value, $parameters, $validator) {
            return $value === strtoupper($value);
        });
        Validator::replacer('uppercase', function ($message, $attribute, $rule, $parameters) {
            return __('String must be uppercase');
        });

        // Add a validation rule for role list
        Validator::extend('validrole', function ($attribute, $value, $parameters, $validator) {
            return Role::all()->contains('name', $value);
        });
        Validator::replacer('validrole', function ($message, $attribute, $rule, $parameters) {
            return __('Unknown role');
        });

        // Add a validator for float numer
        Validator::extend('float', function ($attribute, $value, $parameters, $validator) {
            $thousandsSeparator = config('app.thousands_separator') == '.' ? '\\'.config('app.thousands_separator') : config('app.thousands_separator');
            $decimalSeparator = config('app.decimal_separator') == '.' ? '\\'.config('app.decimal_separator') : config('app.decimal_separator');
            $regex = '~^[+-]?([0-9]{1,3}('.$thousandsSeparator.'?[0-9]{3})*['.$decimalSeparator.'\.]?)?[0-9]{0,2}$~';

            return preg_match($regex, $value) === 1;
        });
        Validator::replacer('float', function ($message, $attribute, $rule, $parameters) {
            return __('Invalid number format');
        });

        // Macros
        Component::macro('notify', function ($message) {
            $this->dispatchBrowserEvent('notify', $message);
        });

        Builder::macro('search', function ($field, $string) {
            return $string ? $this->where($field, 'like', '%'.$string.'%') : $this;
        });
        Builder::macro('orSearch', function ($field, $string) {
            return $string ? $this->orWhere($field, 'like', '%'.$string.'%') : $this;
        });
        Builder::macro('searchBefore', function ($field, $date) {
            return $date ? $this->where($field, '<=', Carbon::parse($date)) : $this;
        });
        Builder::macro('searchAfter', function ($field, $date) {
            return $date ? $this->Where($field, '>=', Carbon::parse($date)) : $this;
        });

        Builder::macro('toCsv', function () {
            $results = $this->get();

            if ($results->count() < 1) {
                return;
            }

            $titles = implode(',', array_keys((array) $results->first()->getAttributes()));

            $values = $results->map(function ($result) {
                return implode(',', collect($result->getAttributes())->map(function ($thing) {
                    return '"'.$thing.'"';
                })->toArray());
            });

            $values->prepend($titles);

            return $values->implode("\n");
        });

        Validator::excludeUnvalidatedArrayKeys();
    }
}
