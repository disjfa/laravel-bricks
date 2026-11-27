<x-admin.screen :title="__('Settings')" icon="cog-6-tooth" save="save">
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

                            @if (app(App\Settings\SiteSettings::class)->logoUrl())
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
                        @if ($iconUrl = app(App\Settings\PwaSettings::class)->iconUrl('icon-192.png'))
                            <img src="{{ $iconUrl }}" alt="" class="size-16 rounded-xl" data-test="app-icon-preview">
                            <img src="{{ app(App\Settings\PwaSettings::class)->iconUrl('maskable-192.png') }}" alt="" class="size-16 rounded-full" title="{{ __('As a maskable icon on Android') }}">
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
                    <flux:input wire:model="appShortName" :label="__('Short name')" :placeholder="app(App\Settings\PwaSettings::class)->shortName()" :description:trailing="__('Shown under the icon on a home screen, keep it under 12 characters.')" />

                    <flux:select wire:model="appDisplay" :label="__('Display')" :description:trailing="__('How the installed app opens.')">
                        <flux:select.option value="standalone">{{ __('Standalone, like an app without browser controls') }}</flux:select.option>
                        <flux:select.option value="minimal-ui">{{ __('Minimal, with a few browser controls') }}</flux:select.option>
                        <flux:select.option value="browser">{{ __('In the browser') }}</flux:select.option>
                    </flux:select>
                </div>
            </flux:card>
        </section>

        {{-- Fonts --}}
        <section class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Fonts') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Loaded from Bunny Fonts for visitors. Without a choice the website uses the system font of the device, so nothing is downloaded.') }}</flux:text>
            </div>

            <flux:card class="grid gap-6 lg:grid-cols-2">
                <flux:select wire:model="fontText" :label="__('Text font')" :description:trailing="__('The body text, buttons and menus.')" data-test="font-text">
                    <flux:select.option value="">{{ __('System font') }}</flux:select.option>
                    @foreach (App\Enums\SiteFont::options() as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="fontHeading" :label="__('Heading font')" :description:trailing="__('Titles and headings.')" data-test="font-heading">
                    <flux:select.option value="">{{ __('Same as the text font') }}</flux:select.option>
                    @foreach (App\Enums\SiteFont::options() as $value => $label)
                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
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

            <flux:card>
                <flux:radio.group wire:model.live="corners" :label="__('Corners')" :description:trailing="__('How rounded the cards, images, buttons and fields on the website are. The previews below show it.')" variant="segmented" data-test="corners">
                    @foreach (App\Enums\SiteCorners::cases() as $cornersOption)
                        <flux:radio :value="$cornersOption->value" :label="$cornersOption->label()" />
                    @endforeach
                </flux:radio.group>
            </flux:card>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach (App\Enums\ColorScheme::cases() as $scheme)
                    @php($colors = $theme[$scheme->value])

                    <flux:card class="space-y-5" wire:key="scheme-{{ $scheme->value }}">
                        <flux:heading>{{ $scheme->label() }}</flux:heading>

                        {{-- A small preview of the site with these colors --}}
                        <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700" style="{{ App\Enums\SiteCorners::tryFrom($corners)?->style() }} background-color: {{ $colors['background'] }}; color: {{ $colors['text'] }}" data-test="theme-preview-{{ $scheme->value }}">
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