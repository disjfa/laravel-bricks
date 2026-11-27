<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create());
});

it('creates a form and opens the builder', function () {
    Livewire::test('pages::admin.forms.index')
        ->set('name', 'Newsletter')
        ->call('createForm')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.forms.edit', Form::firstWhere('name', 'Newsletter')));
});

it('deletes a form with its fields and submissions', function () {
    $field = FormField::factory()->create();
    $submission = FormSubmission::factory()->for($field->form)->create();

    Livewire::test('pages::admin.forms.index')
        ->call('deleteForm', $field->form_id);

    $this->assertModelMissing($field->form);
    $this->assertModelMissing($field);
    $this->assertModelMissing($submission);
});

describe('builder', function () {
    it('saves added fields in order with their settings and keys generated from the labels', function () {
        $form = Form::factory()->create();

        Livewire::test('pages::admin.forms.edit', ['form' => $form])
            ->assertSeeLivewire('form-preview')
            ->call('addField', 'email')
            ->call('addField', 'text', 0)
            ->set('fields.0.settings.label', 'Your name')
            ->set('fields.0.settings.required', true)
            ->set('fields.1.settings.label', 'E-mail')
            ->set('fields.1.settings.placeholder', 'you@example.com')
            ->call('save')
            ->assertHasNoErrors();

        expect($form->refresh()->fields)->sequence(
            fn ($field) => $field->type->toBe('text')->name->toBe('your_name')->settings->toMatchArray(['label' => 'Your name', 'required' => true]),
            fn ($field) => $field->type->toBe('email')->name->toBe('email')->settings->toMatchArray(['label' => 'E-mail', 'placeholder' => 'you@example.com']),
        );
    });

    it('keeps keys unique when labels are the same', function () {
        $form = Form::factory()->create();

        Livewire::test('pages::admin.forms.edit', ['form' => $form])
            ->call('addField', 'text')
            ->call('addField', 'text')
            ->call('save')
            ->assertHasNoErrors();

        expect($form->fields()->pluck('name')->all())->toBe(['text', 'text_2']);
    });

    it('lets existing fields swap keys', function () {
        $form = Form::factory()->create();
        FormField::factory()->for($form)->create(['name' => 'first', 'position' => 0]);
        FormField::factory()->for($form)->create(['name' => 'second', 'position' => 1]);

        Livewire::test('pages::admin.forms.edit', ['form' => $form])
            ->set('fields.0.name', 'second')
            ->set('fields.1.name', 'first')
            ->call('save')
            ->assertHasNoErrors();

        expect($form->fields()->pluck('name')->all())->toBe(['second', 'first']);
    });

    it('requires options for a select field', function () {
        Livewire::test('pages::admin.forms.edit', ['form' => Form::factory()->create()])
            ->call('addField', 'select')
            ->call('save')
            ->assertHasErrors(['fields.0.settings.options' => 'The options field is required.']);
    });

    it('rejects an invalid or duplicate key', function () {
        Livewire::test('pages::admin.forms.edit', ['form' => Form::factory()->create()])
            ->call('addField', 'text')
            ->call('addField', 'text')
            ->set('fields.0.name', 'Not A Key')
            ->set('fields.1.name', 'same')
            ->call('addField', 'text')
            ->set('fields.2.name', 'same')
            ->call('save')
            ->assertHasErrors([
                'fields.0.name' => 'The key may only contain lowercase letters, numbers and underscores, and must start with a letter.',
                'fields.1.name' => 'Every field needs its own key.',
            ]);
    });

    it('removes and reorders fields', function () {
        $form = Form::factory()->create();
        $first = FormField::factory()->for($form)->create(['position' => 0]);
        $second = FormField::factory()->for($form)->create(['position' => 1]);
        $third = FormField::factory()->for($form)->create(['position' => 2]);

        $component = Livewire::test('pages::admin.forms.edit', ['form' => $form]);
        $keys = array_column($component->get('fields'), 'key');

        $component
            ->call('sortField', $keys[2], 0)
            ->call('removeField', $keys[1])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertModelMissing($second);
        expect($form->fields()->pluck('id')->all())->toBe([$third->id, $first->id]);
    });

    it('renders the unsaved fields in the preview', function () {
        Livewire::test('form-preview')
            ->dispatch('form-fields-updated', fields: [
                ['key' => 'a', 'type' => 'text', 'settings' => ['label' => 'Favourite colour', 'help' => '', 'required' => false, 'placeholder' => '']],
                ['key' => 'b', 'type' => 'select', 'settings' => ['label' => 'Size', 'help' => '', 'required' => false, 'placeholder' => '', 'options' => '']],
            ])
            ->assertSee('Favourite colour')
            ->assertSee('Select: fill in the required settings to preview this field.');
    });
});

it('updates the form settings', function () {
    $form = Form::factory()->create();

    Livewire::test('pages::admin.forms.settings', ['form' => $form])
        ->set('name', 'Contact')
        ->set('submitLabel', 'Send it')
        ->set('successMessage', 'Thanks!')
        ->call('save')
        ->assertHasNoErrors();

    expect($form->refresh())
        ->name->toBe('Contact')
        ->submitLabel()->toBe('Send it')
        ->successMessage()->toBe('Thanks!');
});

it('saves the addresses that get the submissions', function () {
    $form = Form::factory()->create();

    Livewire::test('pages::admin.forms.settings', ['form' => $form])
        ->set('notificationEmail', 'info@example.com, sales@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect($form->refresh()->notificationRecipients())->toBe(['info@example.com', 'sales@example.com']);
});

it('rejects an address that is not an email address', function () {
    Livewire::test('pages::admin.forms.settings', ['form' => Form::factory()->create()])
        ->set('notificationEmail', 'info@example.com, not-an-address')
        ->call('save')
        ->assertHasErrors(['notificationEmail' => 'Use email addresses separated by commas, like info@example.com, sales@example.com.']);
});

it('lists and deletes submissions', function () {
    $form = Form::factory()->contact()->create();
    $submission = FormSubmission::factory()->for($form)->create(['data' => ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => '']]);

    Livewire::test('pages::admin.forms.submissions', ['form' => $form])
        ->assertSee(['Jane Doe', 'jane@example.com'])
        ->call('deleteSubmission', $submission->id);

    $this->assertModelMissing($submission);
});

it('does not delete a submission of another form', function () {
    $submission = FormSubmission::factory()->create();

    Livewire::test('pages::admin.forms.submissions', ['form' => Form::factory()->create()])
        ->call('deleteSubmission', $submission->id)
        ->assertNotFound();

    $this->assertModelExists($submission);
});
