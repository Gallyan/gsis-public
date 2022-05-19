<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        'manager' => null,
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
        if( isset($this->filters['user']) && !empty($this->filters['user'])) {
            $filter_users = DB::table('users')
                    ->whereRaw("CONCAT_WS(' ',`firstname`, `name`) like ? ", '%'.$this->filters['user'].'%')
                    ->pluck('id')->toArray();
        } else {
            $filter_users = null;
        }

        $query = Order::query()
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select('orders.*','users.name','users.firstname')
            ->when($this->filters['institution'], fn($query, $institution) => $query->where('orders.institution_id', '=', $institution))
            ->when($this->filters['date-min'], fn($query, $date) => $query->where('orders.created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date-max'], fn($query, $date) => $query->where('orders.created_at', '<=', Carbon::parse($date)))
            ->when($this->filters['manager'], fn($query) => $query->join('managers', function($join) {
                $join->on('orders.id', '=', 'managers.manageable_id')
                    ->where('managers.manageable_type', '=', Order::class)
                    ->where('managers.user_id', '=', $this->filters['manager']);
            }))
            ->when(is_array($filter_users), fn($query) => $query->whereIn('orders.user_id', $filter_users))
            ->when($this->filters['status'], fn($query, $status) => $query->whereIn('orders.status', $status))
            ->when($this->filters['search'], fn($query, $search) => $query->search('orders.subject', $search)
                                                                          ->orSearch('orders.id', $search));

        // Un utilisateur sans droit n'accède qu'à son contenu
        if ( ! auth()->user()->hasPermissionTo('manage-users') )
            $query->where('orders.user_id','=',auth()->user()->id);

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
        $allmanagers = User::role('manager')->get()->mapWithKeys(
            function( $manager ) {
                return [$manager->id => ucwords( $manager->firstname.' '.$manager->name )];
            }
        );

        return view('livewire.orders', [
            'orders' => $this->rows,
            'allmanagers' => User::role('manager')->get()->mapWithKeys(
                                function( $manager ) {
                                    return [$manager->id => ucwords( $manager->firstname.' '.$manager->name )];
                                }
                            ),
        ])->layoutData([
            'pageTitle' => __('Purchase orders'),
        ]);
    }
}