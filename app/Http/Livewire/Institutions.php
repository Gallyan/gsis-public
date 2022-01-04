<?php

namespace App\Http\Livewire;

use App\Models\Institution;
use Livewire\Component;
use Livewire\WithPagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;


class Institutions extends Component
{
    use WithSorting, WithCachedRows, WithPerPagePagination;

    public $showEditModal = false;
    public $search = '';
    public Institution $editing;

    protected $queryString = ['sorts'];

    public function rules() { return [
        'editing.name' => 'required|string|max:255',
        'editing.contract' => 'required|string|max:255',
        'editing.allocation' => 'required|string|max:55',
    ]; }

    public function mount() { $this->editing = $this->makeBlankInstitution(); }

    public function makeBlankInstitution()
    {
        return Institution::make();
    }

    public function create()
    {
        $this->useCachedRows();

        if ($this->editing->getKey()) $this->editing = $this->makeBlankInstitution();

        $this->showEditModal = true;
    }

    public function edit(Institution $institution)
    {
        $this->useCachedRows();

        if ($this->editing->isNot($institution)) $this->editing = $institution;

        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();

        $this->editing->save();

        $this->showEditModal = false;
    }

    public function updatedSearch() { $this->resetPage(); }

    public function getRowsQueryProperty()
    {
        $query = Institution::query()
            ->search('name', $this->search)
            ->orSearch('contract', $this->search)
            ->orSearch('allocation', $this->search);

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
