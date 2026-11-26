<?php

use App\Models\Form;
use App\Rules\Turnstile;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    $this->form = Form::factory()->contact()->create();
});

function fillInContactForm(Testable $component): Testable
{
    return $component
        ->set('values.name', 'Jane Doe')
        ->set('values.email', 'jane@example.com');
}

describe('without turnstile', function () {
    beforeEach(function () {
        config(['services.turnstile.site_key' => null, 'services.turnstile.secret_key' => null]);
    });

    it('shows a honeypot instead of turnstile', function () {
        Livewire::test('site-form', ['formId' => $this->form->id])
            ->assertSeeHtml('data-test="honeypot"')
            ->assertDontSeeHtml('data-test="turnstile"');

        expect(Turnstile::isEnabled())->toBeFalse();
    });

    it('acts as if a submission with a filled in honeypot worked, but stores nothing', function () {
        $component = Livewire::test('site-form', ['formId' => $this->form->id]);
        $this->travel(3)->seconds();

        fillInContactForm($component)
            ->set('website', 'https://spam.example.com')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        expect($this->form->submissions()->count())->toBe(0);
    });

    it('refuses a submission that comes in faster than a person can fill in the form', function () {
        fillInContactForm(Livewire::test('site-form', ['formId' => $this->form->id]))
            ->call('submit')
            ->assertHasErrors(['form'])
            ->assertSet('submitted', false);

        expect($this->form->submissions()->count())->toBe(0);
    });
});

describe('with turnstile', function () {
    beforeEach(function () {
        config(['services.turnstile.site_key' => '1x00000000000000000000AA', 'services.turnstile.secret_key' => 'secret']);
    });

    it('shows the turnstile widget instead of the honeypot', function () {
        Livewire::test('site-form', ['formId' => $this->form->id])
            ->assertSeeHtml('data-test="turnstile"')
            ->assertSeeHtml('1x00000000000000000000AA')
            ->assertDontSeeHtml('data-test="honeypot"');
    });

    it('stores a submission with a token cloudflare accepts', function () {
        Http::fake([Turnstile::VerifyUrl => Http::response(['success' => true])]);

        fillInContactForm(Livewire::test('site-form', ['formId' => $this->form->id]))
            ->set('turnstileToken', 'valid-token')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('turnstileToken', '')
            ->assertDispatched('turnstile-reset');

        expect($this->form->submissions()->count())->toBe(1);
        Http::assertSent(fn (Request $request) => $request->url() === Turnstile::VerifyUrl
            && $request['secret'] === 'secret'
            && $request['response'] === 'valid-token');
    });

    it('refuses a token cloudflare rejects and asks for a new one', function () {
        Http::fake([Turnstile::VerifyUrl => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        fillInContactForm(Livewire::test('site-form', ['formId' => $this->form->id]))
            ->set('turnstileToken', 'used-token')
            ->call('submit')
            ->assertHasErrors(['turnstileToken' => 'Please confirm that you are not a robot.'])
            ->assertSet('turnstileToken', '')
            ->assertDispatched('turnstile-reset');

        expect($this->form->submissions()->count())->toBe(0);
    });

    it('requires a token', function () {
        Http::fake();

        fillInContactForm(Livewire::test('site-form', ['formId' => $this->form->id]))
            ->call('submit')
            ->assertHasErrors(['turnstileToken' => 'Please confirm that you are not a robot.']);

        Http::assertNothingSent();
    });

    it('refuses the submission when cloudflare can not be reached', function () {
        Http::fake([Turnstile::VerifyUrl => Http::failedConnection()]);

        fillInContactForm(Livewire::test('site-form', ['formId' => $this->form->id]))
            ->set('turnstileToken', 'token')
            ->call('submit')
            ->assertHasErrors(['turnstileToken' => 'We could not check that you are not a robot. Please try again.']);

        expect($this->form->submissions()->count())->toBe(0);
    });

    it('does not use up the token when a field is wrong', function () {
        Http::fake();

        Livewire::test('site-form', ['formId' => $this->form->id])
            ->set('turnstileToken', 'token')
            ->call('submit')
            ->assertHasErrors(['values.name'])
            ->assertSet('turnstileToken', 'token');

        Http::assertNothingSent();
    });
});
