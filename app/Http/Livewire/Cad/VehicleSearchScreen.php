<?php

namespace App\Http\Livewire\Cad;

use App\Models\Civilian\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class VehicleSearchScreen extends Component
{
    use WithPagination;

    public string $search_plate = '';
    public string $search_model = '';
    public string $search_status = '';

    protected $queryString = [
        'search_plate' => ['except' => '', 'as' => 'plate'],
    ];

    public function updatedSearchPlate(): void { $this->resetPage(); }
    public function updatedSearchModel(): void { $this->resetPage(); }
    public function updatedSearchStatus(): void { $this->resetPage(); }

    public function clearSearch(): void
    {
        $this->search_plate = '';
        $this->search_model = '';
        $this->search_status = '';
        $this->resetPage();
    }

    public function render()
    {
        $plate = trim($this->search_plate);
        $model = trim($this->search_model);
        $hasSearch = $plate !== '' || $model !== '' || $this->search_status !== '';

        $query = Vehicle::query()->with(['civilian', 'business']);
        if ($plate !== '') {
            $query->where('plate', 'like', '%' . $plate . '%');
        }
        if ($model !== '') {
            $query->where('model', 'like', '%' . $model . '%');
        }
        if ($this->search_status !== '' && in_array($this->search_status, ['1','2','3','4','5'], true)) {
            $query->where('vehicle_status', (int) $this->search_status);
        }

        return view('livewire.cad.vehicle-search-screen', [
            'vehicles' => $hasSearch ? $query->orderBy('plate')->paginate(12) : collect(),
            'hasSearch' => $hasSearch,
        ]);
    }
}
