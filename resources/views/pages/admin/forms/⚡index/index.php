<?php

use App\Livewire\Concerns\QueriesWithQueryBuilder;
use App\Models\Form;
use App\Queries\FormQuery;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin'), Title('Forms')] class extends Component
{
    use QueriesWithQueryBuilder;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $name = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createForm(): void
    {
        $this->authorize('create', Form::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $form = Form::query()->create($validated);

        $this->redirectRoute('admin.forms.edit', ['form' => $form], navigate: true);
    }

    public function deleteForm(int $formId): void
    {
        $form = Form::query()->findOrFail($formId);

        $this->authorize('delete', $form);

        $form->deleteWithRevision();

        Flux::modal("delete-form-{$formId}")->close();

        Flux::toast(variant: 'success', text: __('Form deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, Form>
     */
    #[Computed]
    public function forms(): LengthAwarePaginator
    {
        $this->authorize('viewAny', Form::class);

        return FormQuery::for(Form::query()->withCount(['fields', 'submissions']), $this->queryRequest(FormQuery::class, ['search' => $this->search]))
            ->paginate(15);
    }
};
