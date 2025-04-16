<?php

namespace App\Twig;

use App\Entity\ImageFile;
use App\Entity\PdfFile;
use App\Repository\FilesystemFileRepository;
use App\Repository\NoteRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

# Source: https://symfony.com/doc/7.3/templates.html#creating-lazy-loaded-twig-extensions
class AppRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private NoteRepository $noteRepository,
        private FilesystemFileRepository $filesystemFileRepository,
        private Security $security,
    )
    {
    }

    public function markdownToHTML(string $markdown): string
    {
        $markdown = $this->convertMarkdownFileLinksToHTML($markdown);
        return $this->convertMarkdownNoteLinksToHTML($markdown);
    }

    public function convertMarkdownNoteLinksToHTML(string $markdown): string
    {
        /**
         * This pattern was generated using ChatGPT o3-mini-high model (https://chat.openai.com/).
         * Prompt:
             * Hello, in my Symfony project I am putting together a custom Twig filter which should convert markdown string to HTML. I need your help putting together a regex, which will find all markdown links to other notes and replace them with HTML anchor element pointing to /note/{slug}.
             *
             * Possible link formats:
             * [[Note1]]
             * [[Note2|AnchorLabel]]
             * [[Note3#Heading]]
             * [[Note4#Heading|AnchorLabel]]
             *
             * Explain the code in detail please.
         * Pattern was also debugged using: https://regex101.com/ (determining which parts of the pattern match which parts of the markdown)
         * This pattern is unit tested in AppRuntimeTest.php
         * Source for preg_replace_callback: https://www.php.net/manual/en/function.preg-replace-callback.php
         */
        $pattern = '/\[\[([^#\|\]]+)(?:#([^|\]]+))?(?:\|([^\]]+))?\]\]/';
        return preg_replace_callback($pattern, function($matches) {
            $noteTitle = $matches[1];
            $heading = empty($matches[2]) ? '' : '#' . $matches[2];
            $label = $matches[3] ?? '';

            // If no label is set, use the note title and (optional) heading
            $anchorText = $noteTitle . $heading;
            if(!empty($label)) {
                $anchorText = $label;
            }

            $note = $this->noteRepository->findOneBy(['title' => $noteTitle, 'owner' => $this->security->getUser()]);

            if (!$note) {
                return sprintf('<span class="link--broken">%s</span>', htmlspecialchars($anchorText));
            }
            $url = '/note/' . $note->getSlug() . $heading;
            return sprintf('<a href="%s">%s</a>', $url, htmlspecialchars($anchorText));
        }, $markdown);
    }

    public function convertMarkdownFileLinksToHTML(string $markdown): string
    {
        /**
         * This pattern was generated using ChatGPT o3-mini-high model (https://chat.openai.com/).
         * Prompt:
             * Hello, in my Symfony project I am putting together a custom Twig filter which should convert markdown string to HTML. I need your help putting together a regex, which will find all markdown links for files.
             *
             * Possible link formats:
             * ![[image.png]]
             * ![[image.png|400]]
             *
             * Explain the code in detail please.
         * Pattern was also debugged using: https://regex101.com/ (determining which parts of the pattern match which parts of the markdown)
         * This pattern is unit tested in AppRuntimeTest.php
         * Source for preg_replace_callback: https://www.php.net/manual/en/function.preg-replace-callback.php
         */
        $pattern = '/!\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        return preg_replace_callback($pattern, function ($matches) {
            $filename = $matches[1];
            $width = isset($matches[2]) ? trim($matches[2]) : null;

            $file = $this->filesystemFileRepository->findOneBy(['owner' => $this->security->getUser(), 'referenceName' => $filename]);
            if(!$file) {
                return sprintf('<span class="link--broken">%s</span>', htmlspecialchars($filename));
            }

            $url = '/files/' . $file->getSafeFilename();

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