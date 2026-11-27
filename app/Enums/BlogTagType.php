<?php

namespace App\Enums;

use Spatie\Tags\Tag;

/**
 * The kinds of tags on blogs (spatie/laravel-tags): categories, and free tags.
 */
enum BlogTagType: string
{
    case Category = 'category';
    case Tag = 'tag';

    public function label(): string
    {
        return match ($this) {
            self::Category => __('Category'),
            self::Tag => __('Tag'),
        };
    }

    /**
     * The archive page with the blogs of a tag of this type.
     */
    public function url(Tag $tag): string
    {
        return route($this === self::Category ? 'blogs.category' : 'blogs.tag', ['slug' => $tag->slug]);
    }

    /**
     * The tag of this type with the slug, in the current language.
     */
    public function findBySlug(string $slug): ?Tag
    {
        return Tag::query()
            ->withType($this->value)
            ->where('slug->'.Tag::getLocale(), $slug)
            ->first();
    }
}
