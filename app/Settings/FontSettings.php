<?php

namespace App\Settings;

use App\Enums\SiteFont;
use Spatie\LaravelSettings\Settings;

/**
 * The fonts of the website and the admin, loaded from Bunny Fonts. Without a choice the system font is used.
 */
class FontSettings extends Settings
{
    public ?SiteFont $text;

    /**
     * Null to use the text font for headings too.
     */
    public ?SiteFont $heading;

    public static function group(): string
    {
        return 'fonts';
    }

    public function headingFont(): ?SiteFont
    {
        return $this->heading ?? $this->text;
    }

    /**
     * The CSS font-family of the text.
     */
    public function textFamily(): string
    {
        return $this->text?->cssFamily() ?? SiteFont::SystemSans;
    }

    public function headingFamily(): string
    {
        return $this->headingFont()?->cssFamily() ?? $this->textFamily();
    }

    /**
     * The Bunny Fonts stylesheet with the chosen fonts, or null when the system font is used.
     */
    public function stylesheetUrl(): ?string
    {
        $families = collect([$this->text, $this->heading])
            ->filter()
            ->unique(fn (SiteFont $font) => $font->value)
            ->map(fn (SiteFont $font) => $font->value.':'.implode(',', SiteFont::Weights));

        if ($families->isEmpty()) {
            return null;
        }

        return 'https://fonts.bunny.net/css?family='.$families->implode('|').'&display=swap';
    }
}
