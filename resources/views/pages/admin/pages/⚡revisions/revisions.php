<?php

use App\Livewire\Concerns\ManagesRevisions;
use App\Models\Page;
use App\Revisions\Revisionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
    use ManagesRevisions;

    public Page $page;

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;
    }

    protected function revisionable(): Model&Revisionable
    {
        return $this->page;
    }

    public function render(): View
    {
        return $this->view()->title(__(':title revisions', ['title' => $this->page->title]));
    }
};
