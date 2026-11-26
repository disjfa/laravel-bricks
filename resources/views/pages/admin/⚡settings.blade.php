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

new #[Layout('layouts::admin'), Title('Settings')] class extends Component {
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
}; ?>

<x-admin.screen :title="__('Settings')" save="save">
    <form wire:submit="save" class="space-y-10">
        {{-- Site --}}
        <section class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Site') }}</flux:heading>
                <flux:text class="mt-1">{{ __('The name and logo in the menu bar, and the SEO defaults for pages and blogs that have nothing of their own.') }}</flux:text>
            </div>

            <flux:card class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-6">
                    <flux:input wire:model="siteTitle" :label="__('Title')" :placeholder="config('app.name')" :description:trailing="__('Shown next to the logo and in the footer.')" />

                    <flux:field>
                        <flux:label>{{ __('Logo') }}</flux:label>
                        <flux:description>{{ __('PNG, JPG, WebP, AVIF or GIF up to 2 MB, shown 32 pixels high.') }}</flux:description>

                        <div class="flex items-center gap-3">
                            <div class="flex h-14 min-w-14 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 px-3 dark:border-white/10 dark:bg-white/5">
                                <x-app-logo-icon class="max-h-8 size-auto fill-current text-zinc-800 dark:text-white" />
                            </div>

                            <input type="file" wire:model="logo" accept=".png,.jpg,.jpeg,.webp,.avif,.gif" class="block w-full text-sm text-zinc-600 file:me-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium dark:text-zinc-300 dark:file:bg-zinc-700 dark:file:text-zinc-100" data-test="logo-input">

                            @if (App\Models\Setting::siteLogoUrl())
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="removeLogo" :aria-label="__('Remove logo')" />
                            @endif
                        </div>

                        <div wire:loading wire:target="logo" class="text-sm text-zinc-500">{{ __('Uploading...') }}</div>

                        <flux:error name="logo" />
                    </flux:field>
                </div>

                <x-admin.seo-fields is-default :meta-image="$metaImage" :image-media="$this->imageMedia" :media-url="route('admin.media.index')" />
            </flux:card>
        </section>

        {{-- App --}}
        <section class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('App') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Visitors can install the website as an app on their phone or computer.') }} <flux:link :href="route('manifest')" target="_blank">{{ __('View the manifest') }}</flux:link></flux:text>
            </div>

            <flux:card class="grid gap-6 lg:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('App icon') }}</flux:label>
                    <flux:description>{{ __('A square PNG, JPG or WebP of at least 512 × 512 pixels. It is resized for the home screen, Android, Apple devices and the browser tab.') }}</flux:description>

                    <div class="flex items-center gap-4">
                        @if ($iconUrl = App\Models\Setting::appIconUrl('icon-192.png'))
                            <img src="{{ $iconUrl }}" alt="" class="size-16 rounded-xl" data-test="app-icon-preview">
                            <img src="{{ App\Models\Setting::appIconUrl('maskable-192.png') }}" alt="" class="size-16 rounded-full" title="{{ __('As a maskable icon on Android') }}">
                        @else
                            <img src="/apple-touch-icon.png" alt="" class="size-16 rounded-xl">
                        @endif

                        <input type="file" wire:model="appIcon" accept=".png,.jpg,.jpeg,.webp" class="block w-full text-sm text-zinc-600 file:me-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium dark:text-zinc-300 dark:file:bg-zinc-700 dark:file:text-zinc-100" data-test="app-icon-input">

                        @if ($iconUrl)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="removeAppIcon" :aria-label="__('Remove app icon')" />
                        @endif
                    </div>

                    <div wire:loading wire:target="appIcon" class="text-sm text-zinc-500">{{ __('Creating the icons...') }}</div>

                    <flux:error name="appIcon" />
                </flux:field>

                <div class="space-y-6">
                    <flux:input wire:model="appShortName" :label="__('Short name')" :placeholder="App\Models\Setting::appShortName()" :description:trailing="__('Shown under the icon on a home screen, keep it under 12 characters.')" />

                    <flux:select wire:model="appDisplay" :label="__('Display')" :description:trailing="__('How the installed app opens.')">
                        <flux:select.option value="standalone">{{ __('Standalone, like an app without browser controls') }}</flux:select.option>
                        <flux:select.option value="minimal-ui">{{ __('Minimal, with a few browser controls') }}</flux:select.option>
                        <flux:select.option value="browser">{{ __('In the browser') }}</flux:select.option>
                    </flux:select>
                </div>
            </flux:card>
        </section>

        {{-- Theme --}}
        <section class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <flux:heading size="lg">{{ __('Theme') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Visitors get the light or dark colors from their system setting.') }}</flux:text>
                </div>

                <flux:button size="sm" variant="ghost" icon="arrow-uturn-left" wire:click="resetTheme">{{ __('Reset to defaults') }}</flux:button>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach (App\Enums\ColorScheme::cases() as $scheme)
                    @php($colors = $theme[$scheme->value])

                    <flux:card class="space-y-5" wire:key="scheme-{{ $scheme->value }}">
                        <flux:heading>{{ $scheme->label() }}</flux:heading>

                        {{-- A small preview of the site with these colors --}}
                        <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700" style="background-color: {{ $colors['background'] }}; color: {{ $colors['text'] }}" data-test="theme-preview-{{ $scheme->value }}">
                            <div class="flex items-center gap-4 px-4 py-3 text-sm font-medium" style="background-color: {{ $colors['menubar_background'] }}; color: {{ $colors['menubar_text'] }}">
                                <span>{{ $siteTitle ?: config('app.name') }}</span>
                                <span class="ms-auto">{{ __('Home') }}</span>
                                <span>{{ __('About') }}</span>
                            </div>
                            <div class="space-y-3 px-4 py-5">
                                <p class="text-sm">{{ __('This is how text looks on the website.') }}</p>
                                <span class="inline-block rounded-md px-3 py-1.5 text-sm font-medium" style="background-color: {{ $colors['accent'] }}; color: {{ $colors['accent_foreground'] }}">{{ __('Button') }}</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach (App\Enums\ThemeColor::cases() as $color)
                                @php($path = "theme.{$scheme->value}.{$color->value}")

                                <flux:field wire:key="color-{{ $scheme->value }}-{{ $color->value }}">
                                    <flux:label>{{ $color->label() }}</flux:label>
                                    <flux:description>{{ $color->description() }}</flux:description>

                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            wire:model.live="{{ $path }}"
                                            class="h-10 w-12 shrink-0 cursor-pointer rounded-lg border border-zinc-200 bg-white p-1 dark:border-white/10 dark:bg-white/10"
                                            aria-label="{{ $color->label() }}"
                                        >
                                        <flux:input wire:model.live.debounce.500ms="{{ $path }}" class:input="font-mono" maxlength="7" />
                                        <flux:button size="sm" variant="ghost" icon="arrow-uturn-left" wire:click="resetColor('{{ $scheme->value }}', '{{ $color->value }}')" :aria-label="__('Reset')" />
                                    </div>

                                    <flux:error :name="$path" />
                                </flux:field>
                            @endforeach
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </section>

        {{-- Features --}}
        <section class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Features') }}</flux:heading>
                <flux:text class="mt-1">{{ __('A feature that is turned off disappears from the admin and the website. Its content is kept.') }}</flux:text>
            </div>

            <flux:card class="space-y-6">
                @foreach (App\Features\SiteFeature::All as $featureClass)
                    @php($feature = app($featureClass))

                    <flux:switch
                        wire:key="feature-{{ class_basename($featureClass) }}"
                        data-test="feature-{{ class_basename($featureClass) }}"
                        wire:model="features.{{ class_basename($featureClass) }}"
                        :label="$feature->label()"
                        :description="$feature->description()"
                        align="left"
                    />
                @endforeach
            </flux:card>
        </section>

        {{-- Lets Enter submit the form, the visible save button lives in the header bar. --}}
        <button type="submit" class="hidden"></button>
    </form>
</x-admin.screen>
