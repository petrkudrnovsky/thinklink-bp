<?php

namespace App\Twig;

use App\Entity\ImageFile;
use App\Entity\PdfFile;
use App\Repository\FilesystemFileRepository;
use App\Repository\NoteRepository;
use Twig\Extension\RuntimeExtensionInterface;

# Source: https://symfony.com/doc/7.3/templates.html#creating-lazy-loaded-twig-extensions
class AppRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private NoteRepository $noteRepository,
        private FilesystemFileRepository $filesystemFileRepository,
    )
    {
    }

    public function markdownToHTML(string $markdown): string
    {
        $markdown = $this->convertMarkdownFileLinksToHTML($markdown);
        $markdown = $this->convertMarkdownNoteLinksToHTML($markdown);
        return $markdown;
    }

    private function convertMarkdownNoteLinksToHTML(string $markdown): string
    {
        $pattern = '/\[\[([^#\|\]]+)(?:#([^|\]]+))?(?:\|([^\]]+))?\]\]/';
        return preg_replace_callback($pattern, function($matches) {
            $noteTitle = $matches[1];
            $heading = isset($matches[2]) ? '#' . $matches[2] : '';
            $label = $matches[3] ?? '';

            // If no label is set, use the note title and (optional) heading
            $anchorText = $label ?: $noteTitle . $heading;

            $note = $this->noteRepository->findOneByName($noteTitle);

            if (!$note) {
                return sprintf('<span class="link--broken">%s</span>', htmlspecialchars($anchorText));
            }
            $url = '/note/' . $note->getSlug() . $heading;
            return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($anchorText));
        }, $markdown);
    }

    private function convertMarkdownFileLinksToHTML(string $markdown): string
    {
        $pattern = '/!\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        return preg_replace_callback($pattern, function ($matches) {
            $filename = $matches[1];
            $width = isset($matches[2]) ? trim($matches[2]) : null;

            $file = $this->filesystemFileRepository->findOneBy(['referenceName' => $filename]);
            if(!$file) {
                return sprintf('<span class="link--broken">%s</span>', htmlspecialchars($filename));
            }

            $url = '/files/' . $file->getReferenceName();

            if($file instanceof ImageFile) {
                if($width) {
                    return sprintf('<img src="%s" alt="%s" width="%s">', $url, $filename, $width);
                }
                return sprintf('<img src="%s" alt="%s">', $url, $filename);
            } elseif($file instanceof PdfFile) {
                return sprintf('<a href="%s" target="_blank">%s</a>', $url, $filename);
            } else {
                return sprintf('<span class="link--broken">Není možné zpracovat tento soubor: %s</span>', htmlspecialchars($filename));
            }
        }, $markdown);
    }
}