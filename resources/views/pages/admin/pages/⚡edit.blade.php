<?php

use App\Bricks\Brickable;
use App\Livewire\Concerns\EditsBricks;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    use EditsBricks;

    public Page $page;

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;

        $this->loadBricks();
    }

    protected function brickable(): Brickable
    {
        return $this->page;
    }

    public function render()
    {
        return $this->view()->title(__('Edit :title', ['title' => $this->page->title]));
    }
}; ?>

@include('partials.admin.brick-editor', ['model' => $page, 'bricks' => $bricks, 'imageMedia' => $this->imageMedia])
