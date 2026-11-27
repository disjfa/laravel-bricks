<?php

namespace App\Enums;

/**
 * The colors of the website that admins set on the settings page (App\Settings\ThemeSettings), for the light
 * and the dark scheme.
 */
enum ThemeColor: string
{
    case Accent = 'accent';
    case AccentForeground = 'accent_foreground';
    case Background = 'background';
    case Text = 'text';
    case MenubarBackground = 'menubar_background';
    case MenubarText = 'menubar_text';

    public const string Pattern = '/^#[0-9a-fA-F]{6}$/';

    public function label(): string
    {
        return match ($this) {
            self::Accent => __('Accent'),
            self::AccentForeground => __('Text on accent'),
            self::Background => __('Background'),
            self::Text => __('Text'),
            self::MenubarBackground => __('Menu bar background'),
            self::MenubarText => __('Menu bar text'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Accent => __('Buttons, links and the active menu item.'),
            self::AccentForeground => __('Text on buttons.'),
            self::Background => __('The page background.'),
            self::Text => __('The body text.'),
            self::MenubarBackground => __('The header with the site menu.'),
            self::MenubarText => __('The logo text and menu items.'),
        };
    }

    /**
     * The color the website has when no color is set.
     */
    public function default(ColorScheme $scheme): string
    {
        return match ([$this, $scheme]) {
            [self::Accent, ColorScheme::Light] => '#262626',
            [self::Accent, ColorScheme::Dark] => '#ffffff',
            [self::AccentForeground, ColorScheme::Light] => '#ffffff',
            [self::AccentForeground, ColorScheme::Dark] => '#262626',
            [self::Background, ColorScheme::Light] => '#ffffff',
            [self::Background, ColorScheme::Dark] => '#171717',
            [self::Text, ColorScheme::Light] => '#262626',
            [self::Text, ColorScheme::Dark] => '#f5f5f5',
            [self::MenubarBackground, ColorScheme::Light] => '#ffffff',
            [self::MenubarBackground, ColorScheme::Dark] => '#171717',
            [self::MenubarText, ColorScheme::Light] => '#525252',
            [self::MenubarText, ColorScheme::Dark] => '#e5e5e5',
        };
    }

    /**
     * The CSS custom properties the color is written to.
     *
     * @return array<int, string>
     */
    public function cssVariables(): array
    {
        return match ($this) {
            self::Accent => ['--color-accent', '--color-accent-content'],
            self::AccentForeground => ['--color-accent-foreground'],
            self::Background => ['--site-background'],
            self::Text => ['--site-text'],
            self::MenubarBackground => ['--site-menubar-background'],
            self::MenubarText => ['--site-menubar-text'],
        };
    }
}
