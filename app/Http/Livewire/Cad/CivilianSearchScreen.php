<?php

namespace App\Http\Livewire\Cad;

use App\Models\Civilian;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class CivilianSearchScreen extends Component
{
    use WithPagination;

    public string $search_name = '';
    public string $search_ssn = '';
    public string $search_status = '';

    protected $queryString = [
        'search_name' => ['except' => '', 'as' => 'name'],
        'search_ssn' => ['except' => '', 'as' => 'ssn'],
    ];

    public function updatedSearchName(): void { $this->resetPage(); }
    public function updatedSearchSsn(): void { $this->resetPage(); }
    public function updatedSearchStatus(): void { $this->resetPage(); }

    public function clearSearch(): void
    {
        $this->search_name = '';
        $this->search_ssn = '';
        $this->search_status = '';
        $this->resetPage();
    }

    public function render()
    {
        $name = trim($this->search_name);
        $id = trim($this->search_ssn);
        $hasSearch = $name !== '' || $id !== '' || $this->search_status !== '';

        $query = Civilian::query()->without(['licenses', 'medical_records', 'vehicles', 'weapons']);

        if ($name !== '') {
            $words = preg_split('/\\s+/', $name);
            foreach ($words as $word) {
                $query->where(function (Builder $q) use ($word) {
                    $q->where('first_name', 'like', '%' . $word . '%')
                        ->orWhere('last_name', 'like', '%' . $word . '%');
                });
            }
        }
        if ($id !== '') {
            $digits = preg_replace('/[^0-9]/', '', $id);
            if ($digits === '') {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('id', 'like', $digits . '%');
            }
        }
        if ($this->search_status !== '' && in_array($this->search_status, ['1','2','3','4','5'], true)) {
            $query->where('status', (int) $this->search_status);
        }

        return view('livewire.cad.civilian-search-screen', [
            'civilians' => $hasSearch
                ? $query->orderBy('last_name')->orderBy('first_name')->paginate(12)
                : collect(),
            'hasSearch' => $hasSearch,
        ]);
    }
}
