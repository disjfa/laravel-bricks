<?php

use App\Bricks\BaseBrick;
use App\Bricks\BrickRegistry;
use App\Fields\Field;
use App\Fields\Image;
use App\Fields\Link;
use App\Fields\Select;
use App\Models\Brick;
use App\Models\Page;

it('discovers every brick type in app/Bricks in menu order', function () {
    expect(app(BrickRegistry::class)->keys())
        ->toBe(['hero', 'heading', 'text', 'image', 'quote', 'button', 'divider', 'blogs', 'form']);
});

it('has a website view for every brick type', function () {
    foreach (app(BrickRegistry::class)->all() as $brickType) {
        expect(view()->exists($brickType->view()))->toBeTrue("Missing view {$brickType->view()}");
    }
});

it('has an admin view for every field and setting of every brick type', function () {
    foreach (app(BrickRegistry::class)->all() as $brickType) {
        foreach ([...$brickType->fields(), ...$brickType->settings()] as $field) {
            expect(view()->exists($field->view()))->toBeTrue("Missing view {$field->view()}");
        }
    }
});

it('builds defaults and validation from the fields', function () {
    $brickType = new class extends BaseBrick
    {
        public function key(): string
        {
            return 'test';
        }

        public function label(): string
        {
            return 'Test';
        }

        public function icon(): string
        {
            return 'star';
        }

        /**
         * @return array<int, Field>
         */
        public function fields(): array
        {
            return [
                Select::make('size')->required()->options(['small' => 'Small', 'large' => 'Large']),
                Link::make('link'),
                Image::make('image')->required(),
            ];
        }
    };

    expect($brickType->defaults())->toBe(['size' => 'small', 'link' => '', 'image' => '']);

    expect($brickType->validator(['size' => 'small', 'link' => '/about', 'image' => '/storage/1/a.jpg'])->passes())->toBeTrue();

    expect($brickType->validator(['size' => 'huge', 'link' => 'javascript:alert(1)', 'image' => ''])->errors()->keys())
        ->toBe(['size', 'link', 'image']);
});

it('skips bricks of an unknown type on the website', function () {
    $page = Page::factory()->published()->create(['slug' => 'legacy']);
    Brick::factory()->for($page, 'brickable')->create(['type' => 'removed-type', 'data' => []]);
    Brick::factory()->for($page, 'brickable')->heading('Still here')->create(['position' => 1]);

    $this->get('/legacy')
        ->assertOk()
        ->assertSee('Still here');
});
