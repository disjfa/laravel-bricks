<x-admin.content-screen :model="$page" current="revisions" columns>
    @include('partials.admin.revision-list')

    @include('partials.admin.revision-preview-bricks')
</x-admin.content-screen>
