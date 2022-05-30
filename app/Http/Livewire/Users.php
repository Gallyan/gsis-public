<?php

namespace App\Http\Livewire;

use Hash;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\App;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;

class Users extends Component
{
    use WithSorting, WithCachedRows, WithPerPagePagination;

    public $showEditModal = false;
    public $showFilters = false;
    public $filters = [
        'search' => '',
        'role' => null,
        'email' => '',
        'date-min' => null,
        'date-max' => null,
        'verified' => null,
    ];
    public User $editing;
    public $selectedroles = [];

    protected $queryString = ['sorts'];

    public function rules() { return [
        'editing.firstname' => 'required|max:255',
        'editing.lastname' => 'required|max:255',
        'editing.birthday' => 'required|date',
        'editing.email' => 'required|max:255|email:rfc'.((App::environment('production'))?',dns,spoof':'').'|unique:App\Models\User,email'.($this->editing->id ? ','.$this->editing->id:''),
        'editing.employer' => 'nullable|string',
        'editing.phone' => 'sometimes|phone',
        'selectedroles' => 'required|array',
        'selectedroles.*' => 'sometimes|boolean',
]; }

    public function mount() {
        $this->editing = $this->makeBlankUser();
        $this->selectedroles = [ 'user' => 1 ]; // Tous utilisateurs par défaut
    }

    public function makeBlankUser()
    {
        return User::make();
    }

    public function toggleShowFilters() {

        $this->useCachedRows();

        $this->showFilters = ! $this->showFilters;
    }

    public function isRoleModified() {
        return
            array_fill_keys( $this->editing->roles->pluck('name')->toArray(), "1" )
            !==
            array_filter( $this->selectedroles );
    }

    public function create()
    {
        $this->useCachedRows();

        if ($this->editing->getKey()) {
            $this->editing = $this->makeBlankUser();
            $this->selectedroles = [ 'user' => 1 ];
        }

        $this->showEditModal = true;
    }

    public function edit(User $user)
    {
        $this->useCachedRows();

        if ($this->editing->isNot($user)) $this->editing = $user;

        $this->selectedroles = array_fill_keys( $this->editing->roles->pluck('name')->toArray(), '1');

        $this->showEditModal = true;
    }

    public function updated($propertyName) { $this->validateOnly($propertyName); }

    public function save()
    {
        $this->validate();

        // S'il n'y a pas de password, c'est une création, on initialise password et rôle
        if ( ! $this->editing->password ) {

            $this->editing->password = Hash::make(microtime(true));

            $this->editing->save();

            $this->editing->sendEmailVerificationNotification();

        } else {

            $this->editing->save();

        }

        if ( $this->isRoleModified() && auth()->user()->can('manage-roles') ) {
            foreach( $this->selectedroles as $role => $assigned ) {
                if ( $role !== "admin" || auth()->user()->can('manage-admin') ) {
                    if ( (bool)$assigned === true && Role::findByName($role) ) {
                        $this->editing->assignRole( $role );
                    } else {
                        $this->editing->removeRole( $role );
                    }
                }
            }
        }

        $this->showEditModal = false;
    }

    public function resetFilters() { $this->reset('filters'); }

    public function updatedFilters() { $this->resetPage(); }

    public function getRowsQueryProperty()
    {
        $query = User::query()
            ->when($this->filters['verified'], fn($query) => $query->whereNotNull('email_verified_at'))
            ->when($this->filters['email'], fn($query, $email) => $query->search('email', $email))
            ->when($this->filters['date-min'], fn($query, $date) => $query->where('created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date-max'], fn($query, $date) => $query->where('created_at', '<=', Carbon::parse($date)))
            ->when($this->filters['search'], fn($query) => $query->where( function($query) {
                $query->whereRaw("CONCAT_WS(' ',`firstname`, `lastname`) like ? ", '%'.$this->filters['search'].'%')
                      ->orWhere('id',$this->filters['search']); }))
            ->when($this->filters['role'], fn($query) => $query->where( function($query) {
                if( $this->filters['role'] === "none" ) {
                    $query->where('id', User::doesntHave('roles')->get()->pluck('id'));
                } else {
                    $query->role($this->filters['role']);
                } }));

        return $this->applySorting($query);
    }

    public function getRowsProperty()
    {
        return $this->cache(function () {
            return $this->applyPagination($this->rowsQuery);
        });
    }

    public function render()
    {
        return view('livewire.users', [
            'users' => $this->rows,
            'Roles' => Role::all()->sortByDesc('id')->pluck('name'),
        ])->layoutData([
            'pageTitle' => __('Users'),
        ]);
    }
}
