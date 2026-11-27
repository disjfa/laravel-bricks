<?php

use App\Models\Brick;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Page;
use App\Models\User;
use App\Notifications\FormSubmitted;
use Database\Seeders\RolesSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
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
describe('submission email', function () {
    it('emails a submission to the addresses of the form, with the answers and a reply to the visitor', function () {
        Notification::fake();
        $form = Form::factory()->contact()->create(['notification_email' => 'info@example.com, sales@example.com']);

        fillForm($form->id)
            ->set('values.name', 'Jane Doe')
            ->set('values.email', 'jane@example.com')
            ->set('values.message', 'Hello there')
            ->call('submit')
            ->assertHasNoErrors();

        Notification::assertSentOnDemand(FormSubmitted::class, function (FormSubmitted $notification, array $channels, AnonymousNotifiable $notifiable) {
            $mail = $notification->toMail($notifiable);

            return $notifiable->routes['mail'] === ['info@example.com', 'sales@example.com']
                && $mail->subject === 'New submission: Contact'
                && in_array('**Message:** Hello there', $mail->introLines, true)
                && $mail->replyTo === [['jane@example.com', null]];
        });
    });

    it('emails the admins when the form has no addresses', function () {
        Notification::fake();
        $this->seed(RolesSeeder::class);
        $admin = User::factory()->admin()->create();
        User::factory()->create();
        $form = Form::factory()->contact()->create(['notification_email' => null]);

        fillForm($form->id)
            ->set('values.name', 'Jane Doe')
            ->set('values.email', 'jane@example.com')
            ->call('submit');

        Notification::assertSentTo($admin, FormSubmitted::class);
        Notification::assertCount(1);
    });

    it('does not turn answers into links or formatting in the email', function () {
        $form = Form::factory()->contact()->create();
        $submission = $form->submissions()->create(['data' => ['name' => '[Click](https://evil.example)', 'email' => 'jane@example.com', 'message' => '**bold**']]);

        $mail = (new FormSubmitted($submission))->toMail(new AnonymousNotifiable);

        expect($mail->introLines)->toContain('**Name:** \\[Click\\]\\(https://evil\\.example\\)')
            ->toContain('**Message:** \\*\\*bold\\*\\*');
    });

    it('keeps the submission when the mail server fails', function () {
        Notification::shouldReceive('route')->andThrow(new RuntimeException('Mail server down'));
        $form = Form::factory()->contact()->create(['notification_email' => 'info@example.com', 'success_message' => 'We will be in touch.']);

        fillForm($form->id)
            ->set('values.name', 'Jane Doe')
            ->set('values.email', 'jane@example.com')
            ->call('submit')
            ->assertSee('We will be in touch.');

        expect($form->submissions()->count())->toBe(1);
    });
});

function fillForm(int $formId): Testable
{
    $component = Livewire::test('site-form', ['formId' => $formId]);

    test()->travel(3)->seconds();

    return $component;
}
