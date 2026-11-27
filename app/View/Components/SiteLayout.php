<?php

namespace App\View\Components;

use App\Models\Blog;
use App\Models\Page;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use Illuminate\View\View;
use romanzipp\Seo\Structs\Meta;
use Spatie\Menu\Laravel\Menu;

/**
 * The layout of the website: <x-site-layout :seo="$page">. Everything the layout shows is prepared here,
 * so resources/views/layouts/site.blade.php only renders.
 */
class SiteLayout extends Component
{
    public Menu $mainMenu;

    public Menu $footerMenu;

    public string $siteTitle;

    /**
     * The title, description and social tags for the head, from romanzipp/laravel-seo.
     */
    public Htmlable $seoTags;

    /**
     * @param  Page|Blog|null  $seo  the page or blog that is shown, its SEO data fills the head
     */
    public function __construct(
        SiteSettings $siteSettings,
        SeoSettings $seoSettings,
        public ?string $title = null,
        public ?string $description = null,
        public Page|Blog|null $seo = null,
    ) {
        $this->mainMenu = Menu::main();
        $this->footerMenu = Menu::footer();
        $this->siteTitle = $siteSettings->displayTitle();
        $this->seoTags = $this->seoTags($siteSettings, $seoSettings);
    }

    /**
     * The tag collection of the package is shared by every request, so it is cleared first.
     */
    protected function seoTags(SiteSettings $siteSettings, SeoSettings $seoSettings): Htmlable
    {
        seo()->clearStructs();

        if ($this->seo) {
            $this->seo->applySeo();
        } else {
            $this->applyDefaultSeo($siteSettings, $seoSettings);
        }

        return new HtmlString(seo()->render()->toHtml());
    }

    /**
     * The tags of a website page without SEO data of its own, like the welcome screen.
     */
    protected function applyDefaultSeo(SiteSettings $siteSettings, SeoSettings $seoSettings): void
    {
        seo()
            ->title(filled($this->title) ? $this->title.' - '.$seoSettings->title() : $seoSettings->title())
            ->og('type', 'website')
            ->og('site_name', $siteSettings->displayTitle());

        if (filled($description = filled($this->description) ? $this->description : $seoSettings->description())) {
            seo()->description($description);
        }

        if ($image = $seoSettings->image()) {
            seo()->image(url($image));
        }

        if ($seoSettings->noindex) {
            seo()->add(Meta::make()->name('robots')->content('noindex, nofollow'));
        }
    }

    public function render(): View
    {
        return view('layouts.site');
    }
}
