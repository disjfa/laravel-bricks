<div data-test="site-form">
    @if ($this->form === null)
        {{-- The form was deleted or forms are turned off, render nothing on the website. --}}
    @elseif ($submitted)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-6 text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status">
            {{ $this->form->successMessage() }}
        </div>
    @else
        {{-- novalidate: the server rules of the fields decide, the browser shows no validation popups of its own. --}}
        <form wire:submit="submit" class="space-y-6" novalidate>
            @if (filled($this->form->description))
                <p class="text-lg text-zinc-700 dark:text-zinc-300">{{ $this->form->description }}</p>
            @endif

            @foreach ($this->form->renderableFields() as $field)
                <div wire:key="site-form-field-{{ $field->id }}">
                    @include($field->definition()->view(), [
                        'type' => $field->definition(),
                        'settings' => $field->settings,
                        'model' => "values.{$field->name}",
                    ])
                </div>
            @endforeach

            {{-- Errors about the form as a whole, field errors show under their field. --}}
            <flux:error name="form" />

            <flux:button type="submit" variant="primary">{{ $this->form->submitLabel() }}</flux:button>
        </form>
    @endif
</div>