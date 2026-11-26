<?php

use App\Actions\Pwa\GenerateAppIcons;
use App\Enums\ColorScheme;
use App\Enums\ThemeColor;
use App\Features\SiteFeature;
use App\Livewire\Concerns\EditsSeo;
use App\Models\Setting;
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

        $this->siteTitle = (string) Setting::get(Setting::SiteTitleKey, '');
        $this->fillSeo(Setting::seoDefaults());
        $this->appShortName = (string) Setting::get(Setting::AppShortNameKey, '');
        $this->appDisplay = Setting::appDisplay();
        $this->theme = ThemeColor::palette();

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
        Setting::put(Setting::SiteLogoKey, $path);

        $this->reset('logo');

        Flux::toast(variant: 'success', text: __('Logo uploaded.'));
    }

    public function removeLogo(): void
    {
        $this->authorize('manageSettings');

        $this->deleteLogoFile();
        Setting::put(Setting::SiteLogoKey, null);
    }

    protected function deleteLogoFile(): void
    {
        if (filled($oldPath = Setting::get(Setting::SiteLogoKey))) {
            Storage::disk('public')->delete($oldPath);
        }
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
        Setting::put(Setting::AppIconKey, $directory);

        $this->reset('appIcon');

        Flux::toast(variant: 'success', text: __('App icon uploaded.'));
    }

    public function removeAppIcon(): void
    {
        $this->authorize('manageSettings');

        $this->deleteAppIcons();
        Setting::put(Setting::AppIconKey, null);
    }

    protected function deleteAppIcons(): void
    {
        if (filled($directory = Setting::get(Setting::AppIconKey))) {
            Storage::disk('public')->deleteDirectory($directory);
        }
    }

    /**
     * Maskable and Apple icons get the light background of the theme behind them.
     */
    protected function iconBackgroundColor(): string
    {
        return ThemeColor::palette()[ColorScheme::Light->value][ThemeColor::Background->value];
    }

    /**
     * Generate the icons again from the original upload, after the background color changed.
     */
    protected function regenerateAppIcons(): void
    {
        $directory = Setting::get(Setting::AppIconKey);
        $sourcePath = "{$directory}/".GenerateAppIcons::SourceFileName;

        if (blank($directory) || ! Storage::disk('public')->exists($sourcePath)) {
            return;
        }

        $newDirectory = app(GenerateAppIcons::class)->handle(Storage::disk('public')->path($sourcePath), $this->iconBackgroundColor());

        $this->deleteAppIcons();
        Setting::put(Setting::AppIconKey, $newDirectory);
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
            'appDisplay' => ['required', Rule::in(Setting::AppDisplayModes)],
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

        Setting::put(Setting::SiteTitleKey, trim($this->siteTitle) ?: null);
        Setting::put(Setting::AppShortNameKey, trim($this->appShortName) ?: null);
        Setting::put(Setting::AppDisplayKey, $this->appDisplay);
        Setting::put(Setting::MetaTitleKey, $seo['meta_title']);
        Setting::put(Setting::MetaDescriptionKey, $seo['meta_description']);
        Setting::put(Setting::MetaImageKey, $seo['meta_image']);
        Setting::put(Setting::NoindexKey, $seo['noindex']);

        Setting::put(ThemeColor::SettingKey, collect(ColorScheme::cases())
            ->mapWithKeys(fn (ColorScheme $scheme) => [
                $scheme->value => collect(ThemeColor::cases())
                    ->mapWithKeys(fn (ThemeColor $color) => [$color->value => mb_strtolower($this->theme[$scheme->value][$color->value])])
                    ->all(),
            ])
            ->all());

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
};
