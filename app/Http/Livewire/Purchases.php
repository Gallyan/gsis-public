<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;

class Purchases extends Component
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
    public Purchase $editing;

    protected $queryString = ['sorts'];

    public function mount() { $this->resetFilters(); }

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

    public function edit(Purchase $purchase) { return redirect()->route('edit-purchase',[$purchase]); }
    public function create() { return redirect()->route('edit-purchase'); }

    public function getRowsQueryProperty()
    {
        $query = Purchase::query()
            ->join('users', 'users.id', '=', 'purchases.user_id')
            ->join('institutions', 'institutions.id', '=', 'purchases.institution_id')
            ->select('purchases.*','users.lastname','users.firstname','institutions.name as ins_name', 'institutions.contract as ins_contract')
            ->when($this->filters['institution'], fn($query, $institution) => $query->where('purchases.institution_id', '=', $institution))
            ->when($this->filters['date-min'], fn($query, $date) => $query->where('purchases.created_at', '>=', Carbon::parse($date)))
            ->when($this->filters['date-max'], fn($query, $date) => $query->where('purchases.created_at', '<=', Carbon::parse($date)))
            ->when($this->filters['manager'], fn($query) => $query->join('managers', function($join) {
                $join->on('purchases.id', '=', 'managers.manageable_id')
                    ->where('managers.manageable_type', '=', Purchase::class)
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
            ->when($this->filters['status'], fn($query, $status) => $query->whereIn('purchases.status', $status))
            ->when($this->filters['search'], fn($query, $search) => $query->search('purchases.subject', $search)
                                                                          ->orSearch('purchases.id', $search));

        // Un utilisateur sans droit n'accède qu'à son contenu
        if ( ! auth()->user()->hasPermissionTo('manage-users') )
            $query->where('purchases.user_id','=',auth()->id());

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
                return [$manager->id => ucwords( $manager->firstname.' '.$manager->lastname )];
            }
        );

        return view('livewire.purchases', [
            'purchases' => $this->rows,
            'allmanagers' => User::role('manager')->get()->mapWithKeys(
                                function( $manager ) {
                                    return [$manager->id => ucwords( $manager->firstname.' '.$manager->lastname )];
                                }
                            ),
        ])->layoutData([
            'pageTitle' => __('Non-mission purchases'),
        ]);
    }
}
