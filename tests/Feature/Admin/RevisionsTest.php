<?php

use App\Enums\BlogTagType;
use App\Enums\RevisionAction;
use App\Models\Blog;
use App\Models\Brick;
use App\Models\Form;
use App\Models\Page;
use App\Models\Revision;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

describe('recording', function () {
    it('keeps the state before the first save and a revision after it', function () {
        $page = Page::factory()->create(['title' => 'Before']);
        Brick::factory()->for($page, 'brickable')->heading('Old heading')->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('title', 'After')
            ->call('save')
            ->assertHasNoErrors();

        [$latest, $first] = $page->revisions()->get()->all();

        expect($first->action)->toBe(RevisionAction::Created)
            ->and($first->snapshot['title'])->toBe('Before')
            ->and($first->snapshot['bricks'][0]['data']['text'])->toBe('Old heading')
            ->and($latest->action)->toBe(RevisionAction::Updated)
            ->and($latest->snapshot['title'])->toBe('After')
            ->and($latest->user_id)->toBe($this->admin->id);
    });

    it('does not keep a revision when nothing changed', function () {
        $page = Page::factory()->create();

        $component = Livewire::test('pages::admin.pages.settings', ['page' => $page]);
        $component->call('save');
        $component->call('save');

        expect($page->revisions()->pluck('action')->all())->toBe([RevisionAction::Created]);
    });

    it('keeps a revision of the bricks when the content is saved', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'heading')
            ->set('bricks.0.data.text', 'Welcome')
            ->call('save')
            ->assertHasNoErrors();

        expect($page->revisions()->first()->snapshot['bricks'])->toBe([
            ['type' => 'heading', 'data' => ['text' => 'Welcome', 'level' => 1], 'settings' => ['width' => 'normal', 'align' => 'start']],
        ]);
    });

    it('keeps the categories and tags of a blog', function () {
        $blog = Blog::factory()->create();

        Livewire::test('pages::admin.blogs.settings', ['blog' => $blog])
            ->set('categories', ['News'])
            ->set('tags', ['Laravel'])
            ->call('save')
            ->assertHasNoErrors();

        expect($blog->revisions()->first()->snapshot)
            ->categories->toBe(['News'])
            ->tags->toBe(['Laravel']);
    });

    it('keeps the fields of a form', function () {
        $form = Form::factory()->contact()->create();

        $component = Livewire::test('pages::admin.forms.edit', ['form' => $form]);
        $component->call('removeField', $component->get('fields.2.key'))
            ->call('save')
            ->assertHasNoErrors();

        [$latest, $first] = $form->revisions()->get()->all();

        expect(collect($first->snapshot['fields'])->pluck('name')->all())->toBe(['name', 'email', 'message'])
            ->and(collect($latest->snapshot['fields'])->pluck('name')->all())->toBe(['name', 'email']);
    });

    it('keeps the last state of a deleted page', function () {
        $page = Page::factory()->create(['title' => 'Gone']);

        Livewire::test('pages::admin.pages.index')
            ->call('deletePage', $page->id);

        $this->assertModelMissing($page);

        $revision = Revision::query()->sole();

        expect($revision->action)->toBe(RevisionAction::Deleted)
            ->and($revision->revisionable_id)->toBe($page->id)
            ->and($revision->snapshot['title'])->toBe('Gone');
    });
});

describe('reverting', function () {
    it('puts a page back as it was, as a new revision', function () {
        $page = Page::factory()->create(['title' => 'First', 'slug' => 'first']);
        Brick::factory()->for($page, 'brickable')->heading('First heading')->create();

        $page->recordingRevision(function () use ($page) {
            $page->update(['title' => 'Second', 'slug' => 'second']);
            $page->bricks()->delete();
        });
        $firstVersion = $page->revisions()->where('action', RevisionAction::Created)->sole();

        Livewire::test('pages::admin.pages.revisions', ['page' => $page])
            ->call('revert', $firstVersion->id)
            ->assertHasNoErrors();

        $page->refresh();

        expect($page->title)->toBe('First')
            ->and($page->slug)->toBe('first')
            ->and($page->bricks()->sole()->data)->toBe(['text' => 'First heading', 'level' => 2]);

        $revert = $page->revisions()->first();

        expect($revert->action)->toBe(RevisionAction::Reverted)
            ->and($revert->reverted_from_id)->toBe($firstVersion->id)
            ->and($page->revisions()->count())->toBe(3);
    });

    it('keeps the current slug when another page took the old one', function () {
        $page = Page::factory()->create(['slug' => 'old']);
        $page->recordingRevision(fn () => $page->update(['slug' => 'new']));
        Page::factory()->create(['slug' => 'old']);

        $page->revertTo($page->revisions()->where('action', RevisionAction::Created)->sole());

        expect($page->refresh()->slug)->toBe('new');
    });

    it('puts back the categories and tags of a blog', function () {
        $blog = Blog::factory()->create();
        $blog->syncTagsWithType(['News'], BlogTagType::Category->value);
        $blog->recordingRevision(fn () => $blog->syncTagsWithType(['Events'], BlogTagType::Category->value));

        $blog->revertTo($blog->revisions()->where('action', RevisionAction::Created)->sole());

        expect($blog->refresh()->tagsOfType(BlogTagType::Category)->pluck('name')->all())->toBe(['News']);
    });

    it('puts back the fields of a form', function () {
        $form = Form::factory()->contact()->create();
        $form->recordingRevision(fn () => $form->fields()->where('name', 'message')->delete());

        $form->revertTo($form->revisions()->where('action', RevisionAction::Created)->sole());

        expect($form->fields()->pluck('name')->all())->toBe(['name', 'email', 'message']);
    });

    it('does not revert to a revision of another page', function () {
        $page = Page::factory()->create();
        $otherPage = Page::factory()->create();
        $otherPage->recordRevision();

        Livewire::test('pages::admin.pages.revisions', ['page' => $page])
            ->call('revert', $otherPage->revisions()->sole()->id)
            ->assertNotFound();
    });
});

describe('revisions screen', function () {
    it('shows the newest revision in the preview, and another one when picked', function () {
        $page = Page::factory()->create(['title' => 'Old title']);
        $page->recordingRevision(fn () => $page->update(['title' => 'New title']));
        $oldVersion = $page->revisions()->where('action', RevisionAction::Created)->sole();

        Livewire::test('pages::admin.pages.revisions', ['page' => $page])
            ->assertSeeInOrder(['Current version', 'Revert'])
            ->assertSee('New title')
            ->assertDontSee('Old title')
            ->call('selectRevision', $oldVersion->id)
            ->assertSee('Old title');
    });

    it('shows the fields of a form revision', function () {
        $form = Form::factory()->contact()->create();
        $form->recordRevision();

        Livewire::test('pages::admin.forms.revisions', ['form' => $form])
            ->assertSet('selectedRevisionId', null)
            ->assertSee('Message');
    });
});
