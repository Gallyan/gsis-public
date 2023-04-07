<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Order;
use App\Models\Manager;
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
        'search' => null,
        'user' => null,
        'institution' => null,
        'manager' => null,
        'status' => [],
        'date-min' => null,
        'date-max' => null,
    ];

    protected $queryString = ['sorts'=>['as'=>'s'],'filters'=>['as'=>'f']];

    public function mount() {
        if ( empty(array_filter($this->filters)) )
            $initial_status = auth()->user()->hasRole('manager') ? ['on-hold','in-progress'] : [];
        elseif ( !empty(array_diff_key(array_filter($this->filters),['search'=>null])) &&
                ! ( array_keys(array_diff_key(array_filter($this->filters),['search'=>null])) == ['status'] &&
                    $this->filters['status'] == ['on-hold','in-progress'] ) )
            $this->showFilters = true;

        $this->filters = array_merge( [
            'search' => null,
            'user' => null,
            'institution' => null,
            'manager' => null,
            'status' => [],
            'date-min' => null,
            'date-max' => null,
        ], $this->filters);

        if ( isset( $initial_status ) )
            $this->filters['status'] = $initial_status;
    }

    public function toggleShowFilters() {

        $this->useCachedRows();

        $this->showFilters = ! $this->showFilters;
    }

    public function resetFilters() {
        $this->reset('filters');
        if ( auth()->user()->can('manage-users') ) {
            $this->filters['status'] = ['on-hold','in-progress'];
        } else {
            $this->filters['status'] = [];
        }
    }

    public function updatedFilters() { $this->resetPage(); }

    public function edit(Order $order) { return redirect()->route('edit-order',[$order]); }
    public function create() { return redirect()->route('edit-order'); }

    public function getRowsQueryProperty()
    {
        // Sanitize date filters
        $date = date_parse_from_format('Y-m-d', $this->filters['date-min']);
        if (!checkdate($date['month'], $date['day'], $date['year']))
            $this->filters['date-min'] = null;

        $date = date_parse_from_format('Y-m-d', $this->filters['date-max']);
        if (!checkdate($date['month'], $date['day'], $date['year']))
            $this->filters['date-max'] = null;

        $query = Order::query()->with('managers')->with('user')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('institutions', 'institutions.id', '=', 'orders.institution_id')
            ->select('orders.*','users.lastname','users.firstname','institutions.name as ins_name', 'institutions.contract as ins_contract')
            ->when($this->filters['institution'], fn($query, $institution) => $query->whereIn('orders.institution_id', $institution))
            ->when($this->filters['date-min'], fn($query, $date) => $query->where('orders.created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date-max'], fn($query, $date) => $query->where('orders.created_at', '<=', Carbon::parse($date)))
            ->when($this->filters['manager'], fn($query) => $query->join('managers', function($join) {
                $join->on('orders.id', '=', 'managers.manageable_id')
                    ->where('managers.manageable_type', '=', Order::class)
                    ->where('managers.user_id', '=', $this->filters['manager']);
            }))
            ->when($this->filters['user'], function($query) {
                foreach (explode(' ',trim($this->filters['user'])) as $term) {
                    $query->where( function($query) use ($term) {
                        $query->search('users.firstname',$term)
                        ->orSearch('users.lastname', $term)
                        ->orWhere('users.id', $term);
                    });
                }
            })
            ->when($this->filters['status'], fn($query, $status) => $query->whereIn('orders.status', $status))
            ->when($this->filters['search'], function($query) {
                foreach (explode(' ',trim($this->filters['search'])) as $term) {
                    $query->where( function($query) use ($term) {
                        $query->search('orders.subject',trim($term))
                        ->orWhere('orders.id', trim($term));
                    });
                }
            });

        // Un utilisateur sans droit n'accède qu'à son contenu
        if ( ! auth()->user()->hasPermissionTo('manage-users') )
            $query->where('orders.user_id','=',auth()->id());

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
            'allmanagers' => Manager::whereHasMorph( 'manageable', Order::class )
                                ->get()->pluck('name','user_id'),
        ])->layoutData([
            'pageTitle' => __('Purchase orders'),
        ]);
    }
}