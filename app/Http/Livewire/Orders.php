<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;

class Orders extends Component
{
    use WithSorting, WithCachedRows, WithPerPagePagination;

    public $showEditModal = false;
    public $search = '';
    public Order $editing;

    protected $queryString = ['sorts'];

    public function rules() { return [
        'editing.subject' => 'required|string|max:255',
        'editing.institution_id' => 'required|',
        'editing.supplier' => 'nullable|string|max:255',
        'editing.books' => 'nullable|json',
        'editing.comments' => 'nullable|string',
        'editing.status' => 'required|in:'.collect(Order::STATUSES)->keys()->implode(','),
    ]; }

    public function mount() { $this->editing = $this->makeBlankOrder(); }

    public function makeBlankOrder()
    {
        return Order::make();
    }

    public function create()
    {
        $this->useCachedRows();

        if ($this->editing->getKey()) $this->editing = $this->makeBlankOrder();

        $this->showEditModal = true;
    }

    public function edit(Order $order)
    {
        $this->useCachedRows();

        if ($this->editing->isNot($order)) $this->editing = $order;

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
        $query = Order::query()
            ->search('subject', $this->search);

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
        ]);
    }
}