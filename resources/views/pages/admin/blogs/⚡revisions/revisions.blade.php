<x-admin.content-screen :model="$blog" current="revisions" columns>
    @include('partials.admin.revision-list')

    @include('partials.admin.revision-preview-bricks')
</x-admin.content-screen>
