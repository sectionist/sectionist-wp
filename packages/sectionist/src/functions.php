<?php

declare(strict_types=1);

use Sectionist\Plugin;

if (! function_exists('sectionist_render')) {
    /**
     * Template tag: echoes the sections of a post in editor order. Defaults to the current post.
     */
    function sectionist_render(?int $postId = null): void
    {
        echo Plugin::render($postId); // Views escape their own output.
    }
}
