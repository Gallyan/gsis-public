<?php

namespace App\Http\Livewire;

use Hash;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
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
    ];
    public User $editing;

    protected $queryString = ['sorts'];

    public function rules() { return [
        'editing.firstname' => 'required|max:255',
        'editing.name' => 'required|max:255',
        'editing.birthday' => 'required|date',
        'editing.email' => 'required|max:255|email:rfc'.((App::environment('production'))?',dns,spoof':'').'|unique:App\Models\User,email'.($this->editing->id ? ','.$this->editing->id:''),
        'editing.employer' => 'nullable|string',
        'editing.phone' => 'sometimes|phone',
        //'upload' => 'nullable|image|max:1000',
    ]; }

    public function mount() { $this->editing = $this->makeBlankUser(); }

    public function makeBlankUser()
    {
        return User::make();
    }

    public function toggleShowFilters() {

        $this->useCachedRows();

        $this->showFilters = ! $this->showFilters;
    }

    public function create()
    {
        $this->useCachedRows();

        if ($this->editing->getKey()) $this->editing = $this->makeBlankUser();

        $this->showEditModal = true;
    }

    public function edit(User $user)
    {
        $this->useCachedRows();

        if ($this->editing->isNot($user)) $this->editing = $user;

        $this->showEditModal = true;
    }

    public function updated($propertyName) { $this->validateOnly($propertyName); }

    public function save()
    {
        $this->validate();

        // S'il n'y a pas de password, c'est une création, on initialise
        $this->editing->password || $this->editing->password = Hash::make(microtime(true));

        $this->editing->save();

        $this->showEditModal = false;
    }

    public function resetFilters() { $this->reset('filters'); }

    public function updatedFilters() { $this->resetPage(); }

    public function getRowsQueryProperty()
    {
        $query = User::query()
            ->when($this->filters['email'], fn($query, $email) => $query->search('email', $email))
            ->when($this->filters['date-min'], fn($query, $date) => $query->where('created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date-max'], fn($query, $date) => $query->where('created_at', '<=', Carbon::parse($date)))
            ->when($this->filters['search'], fn($query) => $query->where( function($query) {
                $query->search('name', $this->filters['search'])
                      ->orSearch('firstname', $this->filters['search']); }))
            ->when($this->filters['role'], fn($query, $role) => $query->role($role));

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
        ])->layoutData([
            'pageTitle' => __('Users'),
        ]);
    }
}
