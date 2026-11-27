<x-admin.content-screen :model="$blog" current="media">
    @include('partials.admin.media-panel', ['mediaItems' => $this->mediaItems])
</x-admin.content-screen>