<?php

namespace App\Tests\Twig;

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
        $this->appRuntime = new AppRuntime($noteRepositoryStub, $fileRepositoryStub);
    }

    # Source: https://docs.phpunit.de/en/11.5/writing-tests-for-phpunit.html#data-providers
    public static function markdownProvider(): array
    {
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

    #[DataProvider('markdownProvider')]
    public function testConvertMarkdownNoteLinksToHTML(string $input, string $expected): void
    {
        $result = $this->appRuntime->convertMarkdownNoteLinksToHTML($input);
        $this->assertEquals($expected, $result);
    }
}