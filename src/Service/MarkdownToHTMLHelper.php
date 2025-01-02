<?php

namespace App\Service;

use App\Repository\ImageRepository;
use App\Repository\NoteRepository;

class MarkdownToHTMLHelper
{
    public function __construct(
        private SlugGenerator $slugGenerator,
        private NoteRepository $noteRepository,
        private ImageRepository $imageRepository,
    )
    {
    }

    public function convertMarkdownHeadingsToHTML(string $markdown): string
    {
        return preg_replace_callback(
            '/^(#+)(.*)/m',
            function ($matches) {
                $level = strlen($matches[1]);
                $heading = trim($matches[2]);

                // match and replace all markdown links in headings
                preg_match_all('/\[\[([^\|\#\]]+)(?:\#([^\|\]]+))?(?:\|[^\]]+)?\]\]/', $heading, $links, PREG_SET_ORDER);

                if (!empty($links)) {
                    foreach ($links as $link) {
                        $noteName = $link[1];
                        $headingAnchor = isset($link[2]) ? '-' . $this->slugGenerator->generateHeadingSlug($link[2]) : '';
                        $heading = str_replace($link[0], $noteName . $headingAnchor, $heading);
                    }
                }

                $slug = $this->slugGenerator->generateHeadingSlug($heading);

                return sprintf('<h%d id="%s">%s</h%d>', $level, $slug, $matches[2], $level);
            },
            $markdown
        );
    }

    public function convertMarkdownLinksToHTML(string $markdown): string
    {
        return preg_replace_callback(
            '/\[\[([^\|\#\]]+)(?:\|([^\|\]]+))?(?:\#([^\|\]]+))?(?:\|([^\]]+))?\]\]/',
            function ($matches) {
                $noteName = $matches[1];
                $heading = isset($matches[3]) ? '#' . $this->slugGenerator->generateHeadingSlug($matches[3]) : '';
                // set label to be 1) original label, 2) note name + heading specification, 3) note name
                if (!empty($matches[2])) {
                    $label = $matches[2];
                } elseif (!empty($matches[3])) {
                    $label = $noteName . '#' . $matches[3];
                } else {
                    $label = $noteName;
                }
                $note = $this->noteRepository->findOneByName($noteName);

                if(!$note) {
                    return sprintf('<span class="broken-link">%s</span>', htmlspecialchars($label));
                }
                return sprintf('<a href="/note/%s%s">%s</a>', htmlspecialchars($note->getSlug()), htmlspecialchars($heading), htmlspecialchars($label));
            },
            $markdown
        );
    }

    public function convertMarkdownImagesToHTML(string $markdown): string
    {
        return preg_replace_callback(
            '/!\[\[([^\|\]]+)(?:\|(\d+))?\]\]/',
            function ($matches) {
                $filename = $matches[1];
                $width = $matches[2] ?? null;

                $image = $this->imageRepository->findOneBy(['filename' => $filename]);

                if(!$image) {
                    return sprintf('<span class="broken-image">%s</span>', $filename);
                }

                if ($width) {
                    return sprintf('<img src="/image/%s" alt="%s" width="%s">', $image->getId(), $filename, $width);
                }
                return sprintf('<img src="/image/%s" alt="%s">', $image->getId(), $filename);
            }, $markdown
        );
    }
}