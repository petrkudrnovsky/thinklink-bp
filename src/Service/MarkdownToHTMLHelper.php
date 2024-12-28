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

    public function convertMarkdownLinksToHTML(string $markdown): string
    {
        return preg_replace_callback(
            '/\[\[([^\|\#\]]+)(?:\|([^\|\]]+))?(?:\#([^\|\]]+))?(?:\|([^\]]+))?\]\]/',
            function ($matches) {
                $noteName = $matches[1];
                $label = $matches[2] ?? $matches[4] ?? $noteName;
                $heading = isset($matches[3]) ? '#' . $this->slugGenerator->generateHeadingSlug($matches[3]) : '';

                $note = $this->noteRepository->findOneByName($noteName);

                if(!$note) {
                    return sprintf('<span class="broken-link">%s</span>', $label);
                }

                return sprintf('<a href="/note/%s%s">%s</a>', $note->getSlug(), $heading, $label);
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