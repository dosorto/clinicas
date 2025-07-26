<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Centros_Medico;
use Illuminate\Support\Facades\Auth;

class CentroSelector extends Component
{
    public $search = '';
    public $selectedCentro;
    public $availableCentros;
    public $showDropdown = false;

    public function mount()
    {
        $this->selectedCentro = session('current_centro_id') ?? auth()->user()?->centro_id;
        $this->loadCentros();
    }

    public function loadCentros()
    {
        $user = auth()->user();
        if (!$user) {
            $this->availableCentros = collect();
            return;
        }

        $this->availableCentros = $user->getAccessibleCentros()
            ->when($this->search, function ($query) {
                return $query->where('nombre_centro', 'like', '%' . $this->search . '%');
            });
    }

    public function updatedSearch()
    {
        $this->loadCentros();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->search = '';
            $this->loadCentros();
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.centro-selector');
    }
}
