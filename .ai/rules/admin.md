---
paths:
  - resources/views/pages/admin/**
  - resources/views/components/admin/**
  - resources/views/layouts/admin.blade.php
  - app/Providers/MenuServiceProvider.php
  - app/Features/**
---

# Admin

## Screens use the shared layouts

A screen that edits one thing uses `<x-admin.screen>` (or `<x-admin.content-screen>` / `<x-admin.form-screen>`),
so the header, save button and padding match. List pages start with `<x-admin.heading icon="…">`, using the same
icon as the page's item in the admin menu.

## The admin menu is grouped

`Menu::adminGroups()` in `MenuServiceProvider` returns the sidebar groups: Admin (content), Modules (features that
can be turned off) and Settings. Add a new screen to the right group, behind its policy and, for a module, its
Pennant feature.

## A new module is a site feature

A module that can be turned off is a Pennant feature in `app/Features`, implementing `SiteFeature` and listed in
`SiteFeature::All`, so it shows up on the settings page. Guard its routes with `EnsureFeaturesAreActive` and hide
its menu items and content when it is off.
