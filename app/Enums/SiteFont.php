<?php

namespace App\Enums;

/**
 * The fonts the website can use, loaded from Bunny Fonts (fonts.bunny.net) when chosen on the settings page
 * (App\Settings\FontSettings). Without a choice the website uses the system font, so nothing is downloaded.
 *
 * To add a font: add a case with its Bunny slug as value (the name in the fonts.bunny.net URL),
 * and its label and category below.
 */
enum SiteFont: string
{
    case InstrumentSans = 'instrument-sans';
    case Inter = 'inter';
    case Figtree = 'figtree';
    case DmSans = 'dm-sans';
    case Lora = 'lora';
    case Merriweather = 'merriweather';
    case SourceSerif4 = 'source-serif-4';
    case PlayfairDisplay = 'playfair-display';
    case Fraunces = 'fraunces';
    case SpaceGrotesk = 'space-grotesk';

    /**
     * The weights that are loaded for every font.
     */
    public const array Weights = [400, 500, 600, 700];

    /**
     * The font stack of the device, used when no font is chosen and while a chosen font loads.
     */
    public const string SystemSans = "ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'";

    public const string SystemSerif = "ui-serif, Georgia, Cambria, 'Times New Roman', Times, serif";

    /**
     * The family name, as Bunny Fonts serves it.
     */
    public function label(): string
    {
        return match ($this) {
            self::InstrumentSans => 'Instrument Sans',
            self::Inter => 'Inter',
            self::Figtree => 'Figtree',
            self::DmSans => 'DM Sans',
            self::Lora => 'Lora',
            self::Merriweather => 'Merriweather',
            self::SourceSerif4 => 'Source Serif 4',
            self::PlayfairDisplay => 'Playfair Display',
            self::Fraunces => 'Fraunces',
            self::SpaceGrotesk => 'Space Grotesk',
        };
    }

    public function isSerif(): bool
    {
        return in_array($this, [self::Lora, self::Merriweather, self::SourceSerif4, self::PlayfairDisplay, self::Fraunces], true);
    }

    /**
     * The CSS font-family value, with the matching system fonts as fallback.
     */
    public function cssFamily(): string
    {
        return "'{$this->label()}', ".($this->isSerif() ? self::SystemSerif : self::SystemSans);
    }

    /**
     * @return array<string, string> value => label, for a select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $font) => [$font->value => $font->label()])
            ->all();
    }
}
