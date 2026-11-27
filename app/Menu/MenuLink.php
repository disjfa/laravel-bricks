<?php

namespace App\Menu;

use Spatie\Menu\Laravel\Link;

/**
 * A spatie/menu link that carries the extra data our Flux navigation needs.
 */
class MenuLink extends Link
{
    protected ?string $icon = null;

    protected bool $matchExactly = false;

    protected bool $opensInNewTab = false;

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function iconName(): ?string
    {
        return $this->icon;
    }

    /**
     * Only mark the link as current on an exact URL match, useful for index or dashboard links.
     */
    public function exact(bool $matchExactly = true): static
    {
        $this->matchExactly = $matchExactly;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->matchExactly
            ? $this->isExactActive()
            : $this->isActive();
    }

    public function newTab(bool $opensInNewTab = true): static
    {
        $this->opensInNewTab = $opensInNewTab;

        return $this;
    }

    public function opensInNewTab(): bool
    {
        return $this->opensInNewTab;
    }
}
