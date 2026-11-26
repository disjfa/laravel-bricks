<?php

use App\Actions\Pwa\GenerateAppIcons;
use App\Enums\ColorScheme;
use App\Enums\SiteFont;
use App\Enums\ThemeColor;
use App\Features\SiteFeature;
use App\Livewire\Concerns\EditsSeo;
use App\Settings\FontSettings;
use App\Settings\PwaSettings;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use App\Settings\ThemeSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new #[Layout('layouts::admin'), Title('Settings')] class extends Component
{
    use EditsSeo;
    use WithFileUploads;

    public string $siteTitle = '';

    /**
     * A new logo, stored as soon as it is uploaded.
     */
    public ?TemporaryUploadedFile $logo = null;

    /**
     * A new app icon, turned into all icon sizes as soon as it is uploaded.
     */
    public ?TemporaryUploadedFile $appIcon = null;

    public string $appShortName = '';

    public string $appDisplay = 'standalone';

    /**
     * A SiteFont value, or empty for the system font.
     */
    public string $fontText = '';

    /**
     * A SiteFont value, or empty to use the text font.
     */
    public string $fontHeading = '';

    /**
     * The website colors, scheme => color => hex.
     *
     * @var array<string, array<string, string>>
     */
    public array $theme = [];

    /**
     * Whether each feature is turned on, keyed by the class basename (like "Blogs").
     *
     * @var array<string, bool>
     */
    public array $features = [];

    public function mount(): void
    {
        $this->authorize('manageSettings');

        $pwa = app(PwaSettings::class);
        $fonts = app(FontSettings::class);

        $this->siteTitle = app(SiteSettings::class)->title ?? '';
        $this->fillSeo(app(SeoSettings::class)->toArray());
        $this->appShortName = $pwa->short_name ?? '';
        $this->appDisplay = $pwa->displayMode();
        $this->fontText = $fonts->text->value ?? '';
        $this->fontHeading = $fonts->heading->value ?? '';
        $this->theme = app(ThemeSettings::class)->palette();

        $this->features = collect(SiteFeature::All)
            ->mapWithKeys(fn (string $feature) => [class_basename($feature) => Feature::active($feature)])
            ->all();
    }

    public function updatedLogo(): void
    {
        $this->authorize('manageSettings');

        $this->validate([
            // No svg: it can run scripts when opened from our own domain.
            'logo' => ['image', 'mimes:png,jpg,jpeg,webp,avif,gif', 'max:2048'],
        ]);

        $path = $this->logo->store('branding', 'public');

        $this->deleteLogoFile();
        $this->saveSiteLogo($path ?: null);

        $this->reset('logo');

        Flux::toast(variant: 'success', text: __('Logo uploaded.'));
    }

    public function removeLogo(): void
    {
        $this->authorize('manageSettings');

        $this->deleteLogoFile();
        $this->saveSiteLogo(null);
    }

    protected function deleteLogoFile(): void
    {
        if (filled($oldPath = app(SiteSettings::class)->logo)) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    protected function saveSiteLogo(?string $path): void
    {
        $site = app(SiteSettings::class);
        $site->logo = $path;
        $site->save();
    }

    public function updatedAppIcon(GenerateAppIcons $generateAppIcons): void
    {
        $this->authorize('manageSettings');

        $this->validate([
            // No svg: it can run scripts when opened from our own domain.
            'appIcon' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:5120', 'dimensions:min_width=512,min_height=512'],
        ], [
            'appIcon.dimensions' => __('The app icon must be at least 512 × 512 pixels.'),
        ]);

        $directory = $generateAppIcons->handle($this->appIcon->getRealPath(), $this->iconBackgroundColor());

        $this->deleteAppIcons();
        $this->saveAppIconDirectory($directory);

        $this->reset('appIcon');

        Flux::toast(variant: 'success', text: __('App icon uploaded.'));
    }

    public function removeAppIcon(): void
    {
        $this->authorize('manageSettings');

        $this->deleteAppIcons();
        $this->saveAppIconDirectory(null);
    }

    protected function deleteAppIcons(): void
    {
        if (filled($directory = app(PwaSettings::class)->icon_directory)) {
            Storage::disk('public')->deleteDirectory($directory);
        }
    }

    protected function saveAppIconDirectory(?string $directory): void
    {
        $pwa = app(PwaSettings::class);
        $pwa->icon_directory = $directory;
        $pwa->save();
    }

    /**
     * Maskable and Apple icons get the light background of the theme behind them.
     */
    protected function iconBackgroundColor(): string
    {
        return app(ThemeSettings::class)->color(ThemeColor::Background, ColorScheme::Light);
    }

    /**
     * Generate the icons again from the original upload, after the background color changed.
     */
    protected function regenerateAppIcons(): void
    {
        $directory = app(PwaSettings::class)->icon_directory;
        $sourcePath = filled($directory) ? GenerateAppIcons::sourcePath($directory) : null;

        if ($sourcePath === null) {
            return;
        }

        $newDirectory = app(GenerateAppIcons::class)->handle(Storage::disk('public')->path($sourcePath), $this->iconBackgroundColor());

        $this->deleteAppIcons();
        $this->saveAppIconDirectory($newDirectory);
    }

    /**
     * Images from the media library, offered for the default share image.
     *
     * @return array<int, array{id: int, name: string, url: string, thumb: string}>
     */
    #[Computed]
    public function imageMedia(): array
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest('id')
            ->limit(60)
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])
            ->all();
    }

    public function resetColor(string $scheme, string $color): void
    {
        $this->theme[$scheme][$color] = ThemeColor::from($color)->default(ColorScheme::from($scheme));
    }

    public function resetTheme(): void
    {
        foreach (ColorScheme::cases() as $scheme) {
            foreach (ThemeColor::cases() as $color) {
                $this->theme[$scheme->value][$color->value] = $color->default($scheme);
            }
        }
    }

    public function save(): void
    {
        $this->authorize('manageSettings');

        $rules = [
            'siteTitle' => ['nullable', 'string', 'max:100'],
            'appShortName' => ['nullable', 'string', 'max:30'],
            'appDisplay' => ['required', Rule::in(PwaSettings::DisplayModes)],
            'fontText' => ['nullable', Rule::enum(SiteFont::class)],
            'fontHeading' => ['nullable', Rule::enum(SiteFont::class)],
            'features' => ['array'],
            'features.*' => ['boolean'],
        ];
        $attributes = [];

        foreach (ColorScheme::cases() as $scheme) {
            foreach (ThemeColor::cases() as $color) {
                $path = "theme.{$scheme->value}.{$color->value}";
                $rules[$path] = ['required', 'string', 'regex:'.ThemeColor::Pattern];
                $attributes[$path] = mb_strtolower($scheme->label().' '.$color->label());
            }
        }

        $this->validate($rules, ['theme.*.*.regex' => __('Use a hex color like #1d4ed8.')], $attributes);
        $seo = $this->validatedSeo();

        $previousBackgroundColor = $this->iconBackgroundColor();

        $site = app(SiteSettings::class);
        $site->title = trim($this->siteTitle) ?: null;
        $site->save();

        $seoSettings = app(SeoSettings::class);
        $seoSettings->meta_title = $seo['meta_title'];
        $seoSettings->meta_description = $seo['meta_description'];
        $seoSettings->meta_image = $seo['meta_image'];
        $seoSettings->noindex = $seo['noindex'];
        $seoSettings->save();

        $pwa = app(PwaSettings::class);
        $pwa->short_name = trim($this->appShortName) ?: null;
        $pwa->display = $this->appDisplay;
        $pwa->save();

        $fonts = app(FontSettings::class);
        $fonts->text = SiteFont::tryFrom($this->fontText);
        $fonts->heading = SiteFont::tryFrom($this->fontHeading);
        $fonts->save();

        $theme = app(ThemeSettings::class);
        $theme->light = $this->themeColors(ColorScheme::Light);
        $theme->dark = $this->themeColors(ColorScheme::Dark);
        $theme->save();

        if ($this->iconBackgroundColor() !== $previousBackgroundColor) {
            $this->regenerateAppIcons();
        }

        foreach (SiteFeature::All as $feature) {
            ($this->features[class_basename($feature)] ?? false)
                ? Feature::activate($feature)
                : Feature::deactivate($feature);
        }

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    /**
     * @return array<string, string> color => hex
     */
    protected function themeColors(ColorScheme $scheme): array
    {
        return collect(ThemeColor::cases())
            ->mapWithKeys(fn (ThemeColor $color) => [$color->value => mb_strtolower($this->theme[$scheme->value][$color->value])])
            ->all();
    }
};
