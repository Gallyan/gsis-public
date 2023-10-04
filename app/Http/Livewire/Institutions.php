<?php

namespace App\Http\Livewire;

use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Institution;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Institutions extends Component
{
    use WithSorting, WithCachedRows, WithPerPagePagination;

    public $showEditModal = false;

    public $search = '';

    public Institution $editing;

    protected $queryString = ['sorts' => ['as' => 's']];

    public function rules()
    {
        return [
            'editing.name' => 'required|string|max:255',
            'editing.contract' => 'required|string|max:255',
            'editing.allocation' => ['required', 'string', 'max:55',
                Rule::unique('institutions', 'allocation')->where(
                    function ($query) {
                        return $query->where(
                            [
                                ['name', '=', $this->editing->name],
                                ['contract', '=', $this->editing->contract],
                            ]
                        );
                    })->ignore($this->editing->id)],
            'editing.wp' => 'boolean',
            'editing.from' => 'nullable|date:Y-m-d',
            'editing.to' => 'nullable|date:Y-m-d'.($this->editing->from ? '|after_or_equal:editing.from' : ''),
        ];
    }

    protected function messages()
    {
        return [
            'editing.allocation.unique' => 'This institution already exists.',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'editing.name' => strtolower(__('Name')),
            'editing.contract' => strtolower(__('Contract')),
            'editing.allocation' => strtolower(__('Allocation')),
            'editing.wp' => 'WP',
            'editing.from' => __('editing-startdate'),
            'editing.to' => __('editing-enddate'),
        ];
    }

    public function mount()
    {
        $this->editing = $this->makeBlankInstitution();
    }

    public function makeBlankInstitution()
    {
        return Institution::make();
    }

    public function create()
    {
        $this->useCachedRows();

        if ($this->editing->getKey()) {
            $this->editing = $this->makeBlankInstitution();
        }

        $this->showEditModal = true;
    }

    public function edit(Institution $institution)
    {
        $this->useCachedRows();

        if ($this->editing->isNot($institution)) {
            $this->editing = $institution;
        }

        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();

        $this->editing->save();

        $this->showEditModal = false;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updated($propertyName)
    {

        if (in_array($propertyName, ['editing.from', 'editing.to'])) {
            $this->validateOnly('editing.from');
            $this->validateOnly('editing.to');
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function getRowsQueryProperty()
    {
        $query = Institution::query();
        foreach (explode(' ', $this->search) as $term) {
            $query = $query->where(function ($query) use ($term) {
                $query->search('name', $term)
                    ->orSearch('contract', $term)
                    ->orSearch('allocation', $term);
            });
        }

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
        return view('livewire.institutions', [
            'institutions' => $this->rows,
        ])->layoutData([
            'pageTitle' => __('Institutions'),
        ]);
    }
}
