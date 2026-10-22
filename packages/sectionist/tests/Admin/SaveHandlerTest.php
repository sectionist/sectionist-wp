<?php

declare(strict_types=1);

namespace Sectionist\Tests\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Admin\SaveHandler;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\PostContentRepository;
use Sectionist\Tests\Support\WpState;

#[CoversClass(SaveHandler::class)]
final class SaveHandlerTest extends TestCase
{
    private SaveHandler $handler;

    private PostContentRepository $repository;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry;
        $registry->register(new TextType);
        $this->repository = new PostContentRepository;
        $this->handler = new SaveHandler($registry, $this->repository);
    }

    /**
     * @return array<string, string>
     */
    private function request(string $json, string $nonce = 'valid:sectionist_save'): array
    {
        return [SaveHandler::NONCE_FIELD => $nonce, SaveHandler::DATA_FIELD => $json];
    }

    /**
     * @return array<string, mixed>
     */
    private function save(string $json, int $postId = 1, string $type = 'page'): array
    {
        $_POST = $this->request(wp_slash($json));
        $data = $this->handler->filterPostData(['post_type' => $type, 'post_content' => ''], ['ID' => $postId]);
        $_POST = [];
        WpState::$content[$postId] = stripslashes((string) $data['post_content']);

        return $data;
    }

    public function test_saves_sanitized_sections_and_keeps_unknown_types(): void
    {
        $json = (string) json_encode(['sections' => [
            ['id' => 'a', 'type' => 'text', 'data' => ['body' => '<script>x</script><p>ok</p>']],
            ['id' => 'b', 'type' => 'custom', 'data' => ['x' => '<i>1</i>']],
        ]]);

        $this->save($json);

        $sections = $this->repository->load(1)->sections;
        $this->assertSame('x<p>ok</p>', $sections[0]->data['body']);
        $this->assertSame(['x' => '<i>1</i>'], $sections[1]->data);
    }

    public function test_content_survives_backslashes_and_quotes(): void
    {
        $this->save((string) json_encode(['sections' => [['id' => 'a', 'type' => 'custom', 'data' => ['x' => 'C:\\p "q"']]]]));

        $this->assertSame('C:\\p "q"', $this->repository->load(1)->sections[0]->data['x']);
    }

    public function test_other_post_types_are_untouched(): void
    {
        $data = $this->save('{"sections":[]}', 1, 'revision');

        $this->assertSame('', $data['post_content']);
    }

    public function test_aborts_without_valid_nonce(): void
    {
        $this->assertNull($this->handler->encode(1, $this->request('{"sections":[]}', 'bad')));
        $this->assertNull($this->handler->encode(1, [SaveHandler::DATA_FIELD => '{"sections":[]}']));
    }

    public function test_aborts_without_capability(): void
    {
        WpState::$canEdit = false;

        $this->assertNull($this->handler->encode(1, $this->request('{"sections":[]}')));
    }

    public function test_aborts_on_autosave(): void
    {
        $this->assertNull($this->handler->encode(1, $this->request('{"sections":[]}'), true));
    }

    public function test_invalid_json_keeps_existing_content(): void
    {
        $this->save('{"sections":[{"id":"a","type":"text","data":{}}]}');
        $before = WpState::$content[1];

        $this->save('not json');
        $this->assertSame($before, WpState::$content[1]);

        $this->save('{"foo":1}');
        $this->assertSame($before, WpState::$content[1]);
    }

    public function test_request_without_sections_keeps_existing_content(): void
    {
        WpState::$content[1] = '{"schema_version":1,"sections":[]}';

        $data = $this->handler->filterPostData(['post_type' => 'page', 'post_content' => ''], ['ID' => 1]);

        $this->assertSame('{"schema_version":1,"sections":[]}', stripslashes((string) $data['post_content']));
    }

    public function test_external_write_of_foreign_content_is_rejected(): void
    {
        WpState::$content[1] = '{"schema_version":1,"sections":[]}';

        $data = $this->handler->filterPostData(['post_type' => 'page', 'post_content' => '<p>other editor</p>'], ['ID' => 1]);
        $this->assertSame('{"schema_version":1,"sections":[]}', stripslashes((string) $data['post_content']));

        $data = $this->handler->filterPostData(['post_type' => 'page', 'post_content' => '<p>new post</p>'], ['ID' => 0]);
        $this->assertSame('', $data['post_content']);
    }

    public function test_external_write_of_valid_document_is_accepted(): void
    {
        $json = wp_slash('{"schema_version":1,"sections":[{"id":"a","type":"text"}]}');

        $data = $this->handler->filterPostData(['post_type' => 'page', 'post_content' => $json], ['ID' => 1]);

        $this->assertSame($json, $data['post_content']);
    }

    public function test_external_write_cannot_overwrite_newer_schema(): void
    {
        WpState::$content[1] = '{"schema_version":99,"sections":[]}';

        $data = $this->handler->filterPostData(['post_type' => 'page', 'post_content' => '{"sections":[]}'], ['ID' => 1]);

        $this->assertSame('{"schema_version":99,"sections":[]}', stripslashes((string) $data['post_content']));
    }

    public function test_newer_schema_version_is_never_overwritten(): void
    {
        WpState::$content[1] = '{"schema_version":99,"sections":[]}';

        $this->save('{"sections":[]}');

        $this->assertSame('{"schema_version":99,"sections":[]}', WpState::$content[1]);
    }

    public function test_failed_save_shows_notice_once(): void
    {
        $_POST = $this->request('not json');
        $this->handler->filterPostData(['post_type' => 'page', 'post_content' => ''], ['ID' => 1]);

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
