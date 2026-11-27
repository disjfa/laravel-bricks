{{-- CommonMark (GitHub flavored) via Str::markdown, with raw HTML escaped and unsafe links removed. --}}
<div class="prose prose-lg prose-site max-w-none prose-img:w-full prose-img:rounded-xl prose-img:object-cover {{ App\Enums\TextAlign::fromSettings($settings)->textClass() }}">
    {!! Str::markdown($data['body'] ?? '', ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
</div>
