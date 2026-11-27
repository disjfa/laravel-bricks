<?php

use App\Livewire\Concerns\ManagesRevisions;
use App\Models\Form;
use App\Revisions\Revisionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
    use ManagesRevisions;

    public Form $form;

    public function mount(Form $form): void
    {
        $this->authorize('update', $form);

        $this->form = $form;
    }

    protected function revisionable(): Model&Revisionable
    {
        return $this->form;
    }

    public function render(): View
    {
        return $this->view()->title(__(':title revisions', ['title' => $this->form->name]));
    }
};
