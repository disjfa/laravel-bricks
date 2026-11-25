<div class="flex items-center gap-3 rounded-xl border-2 border-dashed border-zinc-300 px-4 py-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
    <flux:icon :name="$type->icon()" variant="mini" />
    {{ __(':type: fill in the required fields to preview this brick.', ['type' => $type->label()]) }}
</div>
