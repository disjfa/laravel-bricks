<x-admin.form-screen :form="$form" current="revisions" columns>
    @include('partials.admin.revision-list')

    @include('partials.admin.revision-preview-form')
</x-admin.form-screen>
