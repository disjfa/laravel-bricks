<x-admin.content-screen :model="$page" current="media">
    @include('partials.admin.media-panel', ['mediaItems' => $this->mediaItems])
</x-admin.content-screen>