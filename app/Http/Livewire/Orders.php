<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;

class Orders extends Component
{
    use WithSorting, WithCachedRows, WithPerPagePagination;

    public $showFilters = false;
    public $filters = [
        'search' => '',
        'user' => null,
        'institution' => null,
        'status' => ['on-hold','in-progress'],
        'date-min' => null,
        'date-max' => null,
    ];
    public Order $editing;

    protected $queryString = ['sorts'];

    public function toggleShowFilters() {

        $this->useCachedRows();

        $this->showFilters = ! $this->showFilters;
    }

    public function resetFilters() { $this->reset('filters'); }

    public function updatedFilters() { $this->resetPage(); }

    public function edit(Order $order) { return redirect()->route('edit-order',[$order]); }
    public function create() { return redirect()->route('edit-order'); }

    public function getRowsQueryProperty()
    {
        $query = Order::query()
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select('orders.*','users.name','users.firstname')
            ->when($this->filters['institution'], fn($query, $institution) => $query->where('orders.institution_id', '=', $institution))
            ->when($this->filters['date-min'], fn($query, $date) => $query->where('orders.created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date-max'], fn($query, $date) => $query->where('orders.created_at', '<=', Carbon::parse($date)))
            ->when($this->filters['user'], fn($query, $user) => $query->search('users.name', $user)
                                                                      ->orSearch('users.firstname', $user))
            ->when($this->filters['status'], fn($query, $status) => $query->whereIn('orders.status', $status))
            ->when($this->filters['search'], fn($query, $search) => $query->search('orders.subject', $search)
                                                                          ->orSearch('orders.id', $search));

        // Un utilisateur sans droit n'accède qu'à son contenu
        if ( ! auth()->user()->hasPermissionTo('manage-users') )
            $query->where('user_id','=',auth()->user()->id);

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
        return view('livewire.orders', [
            'orders' => $this->rows,
        ])->layoutData([
            'pageTitle' => __('Purchase orders'),
        ]);
    }
}