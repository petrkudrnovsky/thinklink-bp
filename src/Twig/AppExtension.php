<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

# Source: https://symfony.com/doc/7.3/templates.html#writing-a-twig-extension
# Source: https://symfony.com/doc/7.3/templates.html#creating-lazy-loaded-twig-extensions
# Source: https://twig.symfony.com/doc/3.x/advanced.html#creating-an-extension
class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('thinklink_markdown_to_html', [AppRuntime::class, 'markdownToHTML']),
        ];
    }
}