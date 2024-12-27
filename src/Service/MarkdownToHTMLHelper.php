<?php

namespace App\Service;

use App\Repository\NoteRepository;

class MarkdownToHTMLHelper
{
    public function __construct(
        private SlugGenerator $slugGenerator,
        private NoteRepository $noteRepository,
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
}