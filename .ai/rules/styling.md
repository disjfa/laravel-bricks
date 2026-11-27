---
paths:
  - resources/views/**
  - resources/css/**
---

# Styling

## Use the theme, not fixed colors, on the website

Website colors come from the settings page as custom properties: `--site-background`, `--site-text`, the accent
(`bg-accent`, `text-accent-content`) and the menubar colors. Use those (or neutral zinc grays for borders) so a
changed theme changes the whole site.

## Rich content uses typography

Rendered Markdown and other rich content get `prose prose-site` (Tailwind Typography); `prose-site` maps the prose
colors to the theme. Do not style its elements one by one with `[&_h2]:` classes.

## Corners come from the settings

Round corners with the `rounded-*` classes only. The corners setting scales Tailwind's `--radius-*` sizes, so a
fixed radius (like `rounded-[12px]` or an inline style) ignores it.

## Hover applies on every device

`resources/css/app.css` redefines the `hover` variant without Tailwind 4's `(hover: hover)` media query, so hover
styles also work on touchscreen laptops. Keep that line.

## Clickable and movable elements

Buttons and other clickable roles get the pointer cursor from a base rule in `app.css`. Drag handles for reordering
(`wire:sort:handle`) use `cursor-n-resize`.
