<?php

use App\Enums\PublishStatus;
use App\Models\Blog;
use App\Models\Page;

it('gives new records the next sequence number per model', function () {
    $firstPage = Page::factory()->create();
    $secondPage = Page::factory()->create();
    $firstBlog = Blog::factory()->create();

    expect($firstPage->seqnr)->toBe(1)
        ->and($secondPage->seqnr)->toBe(2)
        ->and($firstBlog->seqnr)->toBe(1);
});

it('keeps a sequence number that is set explicitly', function () {
    expect(Page::factory()->create(['seqnr' => 40])->seqnr)->toBe(40);
});

it('starts as a concept', function () {
    $page = Page::query()->create(['title' => 'New']);

    expect($page->status)->toBe(PublishStatus::Concept)
        ->and($page->isPublished())->toBeFalse();
});

it('only publishes active records inside their publish window', function (array $attributes, bool $isPublished, string $label) {
    $this->freezeTime();
    $blog = Blog::factory()->create($attributes);

    expect($blog->isPublished())->toBe($isPublished)
        ->and(Blog::published()->whereKey($blog->id)->exists())->toBe($isPublished)
        ->and($blog->publicationLabel())->toBe($label);
})->with([
    'concept' => [['status' => PublishStatus::Concept], false, 'Concept'],
    'closed' => [['status' => PublishStatus::Closed, 'published_at' => null], false, 'Closed'],
    'active without dates' => [['status' => PublishStatus::Active], true, 'Published'],
    'active after its publish date' => [fn () => ['status' => PublishStatus::Active, 'published_at' => now()->subMinute()], true, 'Published'],
    'active before its publish date' => [fn () => ['status' => PublishStatus::Active, 'published_at' => now()->addMinute()], false, 'Scheduled'],
    'active before its publish until date' => [fn () => ['status' => PublishStatus::Active, 'published_until' => now()->addMinute()], true, 'Published'],
    'active at its publish until date' => [fn () => ['status' => PublishStatus::Active, 'published_until' => now()], false, 'Expired'],
]);

it('orders by sequence number', function () {
    $last = Page::factory()->create(['seqnr' => 3]);
    $first = Page::factory()->create(['seqnr' => 1]);
    $middle = Page::factory()->create(['seqnr' => 2]);

    expect(Page::ordered()->pluck('id')->all())->toBe([$first->id, $middle->id, $last->id]);
});
