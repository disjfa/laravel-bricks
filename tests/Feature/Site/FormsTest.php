<?php

use App\Models\Brick;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Page;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

it('renders a form brick on a page', function () {
    $form = Form::factory()->contact()->create(['submit_label' => 'Send message']);
    $page = Page::factory()->published()->create(['slug' => 'contact']);
    Brick::factory()->for($page, 'brickable')->create(['type' => 'form', 'data' => ['form_id' => (string) $form->id]]);

    $this->get('/contact')
        ->assertOk()
        ->assertSeeLivewire('site-form')
        ->assertSee(['Name', 'Email', 'Message', 'Send message']);
});

it('stores a valid submission and shows the success message', function () {
    $form = Form::factory()->contact()->create(['success_message' => 'We will be in touch.']);

    fillForm($form->id)
        ->set('values.name', 'Jane Doe')
        ->set('values.email', 'jane@example.com')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('We will be in touch.');

    expect($form->submissions()->sole()->data)->toBe(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => '']);
});

it('validates the submission with the rules of the fields', function () {
    $form = Form::factory()->contact()->create();

    fillForm($form->id)
        ->assertSeeHtml('novalidate')
        ->set('values.email', 'not-an-email')
        ->call('submit')
        ->assertHasErrors([
            'values.name' => 'The name field is required.',
            'values.email' => 'The email field must be a valid email address.',
        ])
        ->assertHasNoErrors(['form']);

    expect($form->submissions()->count())->toBe(0);
});

it('only accepts the options of a select field', function () {
    $field = FormField::factory()->select(['Red', 'Blue'])->required()->create(['name' => 'colour']);

    fillForm($field->form_id)
        ->set('values.colour', 'Green')
        ->call('submit')
        ->assertHasErrors(['values.colour' => 'in']);
});

it('ignores values for fields that are not on the form', function () {
    $form = Form::factory()->contact()->create();

    fillForm($form->id)
        ->set('values.name', 'Jane Doe')
        ->set('values.email', 'jane@example.com')
        ->set('values.is_admin', '1')
        ->call('submit')
        ->assertHasNoErrors();

    expect($form->submissions()->sole()->data)->not->toHaveKey('is_admin');
});

it('limits the number of submissions per visitor', function () {
    $form = Form::factory()->contact()->create();

    $component = fillForm($form->id);

    foreach (range(1, 6) as $attempt) {
        $component
            ->set('values.name', 'Jane Doe')
            ->set('values.email', 'jane@example.com')
            ->call('submit');
    }

    $component->assertHasErrors(['form']);
    expect($form->submissions()->count())->toBe(5);
});

/**
 * Show the form brick and take a few seconds to fill it in, like a visitor would.
 */
function fillForm(int $formId): Testable
{
    $component = Livewire::test('site-form', ['formId' => $formId]);

    test()->travel(3)->seconds();

    return $component;
}
