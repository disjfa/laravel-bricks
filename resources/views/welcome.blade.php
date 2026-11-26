<x-site-layout>
    <section class="mx-auto max-w-3xl px-6 py-24 text-center">
        <flux:heading size="xl" level="1" class="text-4xl! font-bold! tracking-tight">
            {{ app(App\Settings\SiteSettings::class)->displayTitle() }}
        </flux:heading>

        <flux:text class="mt-4 text-lg">
            {{ __('Create a published page with the slug "home" in the admin to replace this page.') }}
        </flux:text>
    </section>
</x-site-layout>
