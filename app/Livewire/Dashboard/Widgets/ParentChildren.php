<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Services\Dashboard\DashboardService;
use Livewire\Component;

class ParentChildren extends Component
{
    public array $children = [];

    public function mount(): void
    {
        $schoolId = auth()->user()->school_id;
        $this->children = DashboardService::stats($schoolId)
            ->parentKpis(auth()->id());
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.parent-children');
    }
}