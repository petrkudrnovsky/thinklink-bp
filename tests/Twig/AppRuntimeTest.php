<?php

namespace App\Tests\Twig;

use App\Entity\ImageFile;
use App\Entity\Note;
use App\Repository\FilesystemFileRepository;
use App\Repository\NoteRepository;
use App\Twig\AppRuntime;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DummyNote extends Note
{
    public function __construct(string $title, string $slug, string $content)
    {
        parent::__construct($title, $slug, $content, new \DateTimeImmutable());
    }
}

class DummyImage extends ImageFile
{
    public function __construct(string $safeFilename, string $referenceName, string $mimeType)
    {
        $this->setSafeFilename($safeFilename);
        $this->setReferenceName($referenceName);
        $this->setMimeType($mimeType);
        $this->setCreatedAt(new \DateTimeImmutable());
    }
}

class AppRuntimeTest extends TestCase
{
    private AppRuntime $appRuntime;

    # Source for setUp(): https://docs.phpunit.de/en/9.6/fixtures.html
    # Why not use tearDown(): https://docs.phpunit.de/en/9.6/fixtures.html#more-setup-than-teardown
    protected function setUp(): void
    {
        # Source: https://docs.phpunit.de/en/10.5/test-doubles.html#test-doubles
        $noteRepositoryStub = $this->createStub(NoteRepository::class);
        $noteRepositoryStub->method('findOneByName')
            # Source: https://docs.phpunit.de/en/10.5/test-doubles.html#willreturncallback
            ->willReturnCallback(function(string $title) {
                // If the title is 'missingNote', return null - simulating a missing note
                if ($title === 'missingNote') {
                    return null;
                }
                return new DummyNote(
                    title: $title,
                    slug: $title . '-slug',
                    content: 'Content of ' . $title,
                );
            });

        $fileRepositoryStub = $this->createStub(FilesystemFileRepository::class);
        $fileRepositoryStub->method('findOneBy')
            ->willReturnCallback(function (array $criteria) {
                if($criteria['referenceName'] === 'missingFile') {
                    return null;
                }

                return new DummyImage('image.png', 'image.png', 'image/png');
            });

        $this->appRuntime = new AppRuntime($noteRepositoryStub, $fileRepositoryStub);
    }

    # Source: https://docs.phpunit.de/en/11.5/writing-tests-for-phpunit.html#data-providers
    public static function markdownNoteLinksProvider(): array
    {
        /**
         * Test case template was written by me, the other test cases were autocompleted by GitHub Copilot plugin (v1.5.32) and proof-checked by me.
         */
        return [
            [
                'input' => 'This is a test [[missingNote]]',
                'expected' => 'This is a test <span class="link--broken">missingNote</span>',
            ],
            [
                'input' => 'This [[Note2|AnchorLabel]] is a test',
                'expected' => 'This <a href="/note/Note2-slug">AnchorLabel</a> is a test',
            ],
            [
                'input' => 'This is a test [[Note3#Heading]]',
                'expected' => 'This is a test <a href="/note/Note3-slug#Heading">Note3#Heading</a>',
            ],
            [
                'input' => '[[Note4#Heading|AnchorLabel]]',
                'expected' => '<a href="/note/Note4-slug#Heading">AnchorLabel</a>',
            ],
        ];
    }

    public static function markdownImageLinksProvider(): array
    {
        /**
         * Test case template was written by me, the other test cases were autocompleted by GitHub Copilot plugin (v1.5.32) and proof-checked by me.
         */
        return [
            [
                'input' => 'This is a test ![[missingFile]]',
                'expected' => 'This is a test <span class="link--broken">missingFile</span>',
            ],
            [
                'input' => 'This is a test ![[image.png]]',
                'expected' => 'This is a test <img src="/files/image.png" alt="image.png">',
            ],
            [
                'input' => 'This is a test ![[image.png|400]]',
                'expected' => 'This is a test <img src="/files/image.png" alt="image.png" width="400">',
            ],
        ];
    }

    #[DataProvider('markdownNoteLinksProvider')]
    public function testConvertMarkdownNoteLinksToHTML(string $input, string $expected): void
    {
        $result = $this->appRuntime->convertMarkdownNoteLinksToHTML($input);
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('markdownImageLinksProvider')]
    public function testConvertMarkdownFileLinksToHTML(string $input, string $expected): void
    {
        $result = $this->appRuntime->convertMarkdownFileLinksToHTML($input);
        $this->assertEquals($expected, $result);
    }

    public function testMarkdownToHTML(): void
    {
        $input = 'We have this note [[Note2|AnchorLabel]] and this image ![[image.png]] (there is smaller version: ![[image.png|400]]). Next notes are [[missingNote]] and [[Note2#Heading]] and [[Note1]].';
        $expected = 'We have this note <a href="/note/Note2-slug">AnchorLabel</a> and this image <img src="/files/image.png" alt="image.png"> (there is smaller version: <img src="/files/image.png" alt="image.png" width="400">). Next notes are <span class="link--broken">missingNote</span> and <a href="/note/Note2-slug#Heading">Note2#Heading</a> and <a href="/note/Note1-slug">Note1</a>.';
        $result = $this->appRuntime->markdownToHTML($input);
        $this->assertEquals($expected, $result);
    }
}