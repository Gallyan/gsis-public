<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Expense;

class Dashboard extends Component
{
    public function edit( string $type, int $id )
    {
        switch( $type ) {
            case 'Mission':
                return redirect()->route('edit-mission', $id);
            case 'Expenses':
                return redirect()->route('edit-expense', [Expense::findOrFail($id)->mission->id, $id]);
            case 'Order':
                return redirect()->route('edit-order', $id);
            case 'Purchase':
                return redirect()->route('edit-purchase', $id);
            default:
        }
    }

    public function render()
    {
        return view('livewire.dashboard', ['user' => auth()->user()]);
    }
}
