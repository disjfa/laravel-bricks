<?php

use App\Livewire\Concerns\ManagesRevisions;
use App\Models\Blog;
use App\Revisions\Revisionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
    use ManagesRevisions;

    public Blog $blog;

    public function mount(Blog $blog): void
    {
        $this->authorize('update', $blog);

        $this->blog = $blog;
    }

    protected function revisionable(): Model&Revisionable
    {
        return $this->blog;
    }

    public function render(): View
    {
        return $this->view()->title(__(':title revisions', ['title' => $this->blog->title]));
    }
};
