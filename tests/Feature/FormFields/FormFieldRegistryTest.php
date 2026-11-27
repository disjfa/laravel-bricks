<?php

use App\FormFields\FormFieldRegistry;

it('discovers every form field type in app/FormFields in menu order', function () {
    expect(app(FormFieldRegistry::class)->keys())
        ->toBe(['text', 'email', 'phone', 'number', 'date', 'textarea', 'select', 'checkbox']);
});

it('has a website view for every form field type and an admin view for every setting', function () {
    foreach (app(FormFieldRegistry::class)->all() as $fieldType) {
        expect(view()->exists($fieldType->view()))->toBeTrue("Missing view {$fieldType->view()}");

        foreach ($fieldType->settings() as $setting) {
            expect(view()->exists($setting->view()))->toBeTrue("Missing view {$setting->view()}");
        }
    }
});

it('validates the submitted value per type', function (string $type, array $settings, mixed $value, bool $passes) {
    $fieldType = app(FormFieldRegistry::class)->find($type);

    expect(validator(['value' => $value], ['value' => $fieldType->rules($settings)])->passes())->toBe($passes);
})->with([
    'optional text left empty' => ['text', ['required' => false], '', true],
    'required text left empty' => ['text', ['required' => true], '', false],
    'valid email' => ['email', [], 'jane@example.com', true],
    'invalid email' => ['email', [], 'not-an-email', false],
    'phone with letters' => ['phone', [], '06-abc', false],
    'number' => ['number', [], 'twelve', false],
    'select option' => ['select', ['options' => "Red\nBlue"], 'Blue', true],
    'unknown select option' => ['select', ['options' => "Red\nBlue"], 'Green', false],
    'required checkbox not ticked' => ['checkbox', ['required' => true], false, false],
    'required checkbox ticked' => ['checkbox', ['required' => true], true, true],
]);
