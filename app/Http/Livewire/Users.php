<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Carbon;

class Users extends Component
{
    public function render()
    {
        return view('livewire.users', [
            'users' => User::paginate(10),
        ]);
    }
}
