<?php

declare(strict_types=1);

namespace Sectionist\Tests\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Admin\SaveHandler;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\PostMetaRepository;
use Sectionist\Tests\Support\WpState;

#[CoversClass(SaveHandler::class)]
final class SaveHandlerTest extends TestCase
{
    private SaveHandler $handler;

    private PostMetaRepository $repository;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry();
        $registry->register(new TextType());
        $this->repository = new PostMetaRepository();
        $this->handler = new SaveHandler($registry, $this->repository);
    }

    /**
     * @return array<string, string>
     */
    private function request(string $json, string $nonce = 'valid:sectionist_save'): array
    {
        return [SaveHandler::NONCE_FIELD => $nonce, SaveHandler::DATA_FIELD => $json];
    }

    public function testSavesSanitizedSectionsAndKeepsUnknownTypes(): void
    {
        $json = (string) json_encode(['sections' => [
            ['id' => 'a', 'type' => 'text', 'data' => ['heading' => '<b>Hi</b>', 'body' => '<script>x</script><p>ok</p>']],
            ['id' => 'b', 'type' => 'custom', 'data' => ['x' => '<i>1</i>']],
        ]]);

        $this->assertTrue($this->handler->process(1, $this->request($json)));

        $sections = $this->repository->load(1)->sections;
        $this->assertSame('Hi', $sections[0]['data']['heading']);
        $this->assertSame('x<p>ok</p>', $sections[0]['data']['body']);
        $this->assertSame(['x' => '<i>1</i>'], $sections[1]['data']);
    }

    public function testAbortsWithoutValidNonce(): void
    {
        $this->assertFalse($this->handler->process(1, $this->request('{"sections":[]}', 'bad')));
        $this->assertFalse($this->handler->process(1, [SaveHandler::DATA_FIELD => '{"sections":[]}']));
        $this->assertSame([], WpState::$meta);
    }

    public function testAbortsWithoutCapability(): void
    {
        WpState::$canEdit = false;

        $this->assertFalse($this->handler->process(1, $this->request('{"sections":[]}')));
        $this->assertSame([], WpState::$meta);
    }

    public function testAbortsOnAutosave(): void
    {
        $this->assertFalse($this->handler->process(1, $this->request('{"sections":[]}'), true));
        $this->assertSame([], WpState::$meta);
    }

    public function testInvalidJsonDoesNotOverwriteExistingData(): void
    {
        $this->handler->process(1, $this->request('{"sections":[{"id":"a","type":"text","data":{}}]}'));
        $before = WpState::$meta;

        $this->assertFalse($this->handler->process(1, $this->request('not json')));
        $this->assertFalse($this->handler->process(1, $this->request('{"foo":1}')));
        $this->assertSame($before, WpState::$meta);
    }

    public function testFailedSaveShowsNoticeOnce(): void
    {
        $_POST = $this->request('not json');
        $this->handler->handle(1);

        ob_start();
        $this->handler->showError();
        $first = (string) ob_get_clean();
        ob_start();
        $this->handler->showError();
        $second = (string) ob_get_clean();
        $_POST = [];

        $this->assertStringContainsString('notice-error', $first);
        $this->assertSame('', $second);
    }
}
