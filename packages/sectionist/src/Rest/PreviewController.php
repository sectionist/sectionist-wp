<?php

declare(strict_types=1);

namespace Sectionist\Rest;

use Sectionist\Render\Renderer;
use Sectionist\Sections\Registry;
use WP_REST_Request;

final class PreviewController
{
    public const NAMESPACE = 'sectionist/v1';

    public const ROUTE = '/preview';

    public function __construct(
        private readonly Registry $registry,
        private readonly Renderer $renderer,
    ) {}

    public function register(): void
    {
        add_action('rest_api_init', function (): void {
            register_rest_route(self::NAMESPACE, self::ROUTE, [
                'methods' => 'POST',
                'callback' => [$this, 'handle'],
                'permission_callback' => [$this, 'permission'],
                'args' => [
                    'post_id' => ['type' => 'integer', 'required' => true],
                    'sections' => ['type' => 'array', 'required' => true],
                ],
            ]);
        });
    }

    public function permission(WP_REST_Request $request): bool
    {
        return current_user_can('edit_post', (int) $request->get_param('post_id'));
    }

    /**
     * @return array{html: string}
     */
    public function handle(WP_REST_Request $request): array
    {
        $sections = $request->get_param('sections');

        return ['html' => $this->preview(is_array($sections) ? $sections : [])];
    }

    /**
     * Sanitizes with the section types first, so a preview never renders more than a save would store.
     *
     * @param  array<mixed>  $sections
     */
    public function preview(array $sections): string
    {
        return $this->renderer->renderSections($this->registry->sanitizeSections($sections));
    }
}
