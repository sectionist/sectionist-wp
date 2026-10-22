<?php

declare(strict_types=1);

/*
 * Minimal stand-ins for the WordPress functions used by the plugin, so unit
 * tests run without a WordPress install. Also used as a PHPStan bootstrap.
 */

use Sectionist\Tests\Support\WpState;

if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        WpState::$actions[$hook][] = $callback;

        return true;
    }
}

if (! function_exists('do_action')) {
    function do_action(string $hook, mixed ...$args): void
    {
        foreach (WpState::$actions[$hook] ?? [] as $callback) {
            $callback(...$args);
        }
    }
}

if (! function_exists('get_option')) {
    function get_option(string $name, mixed $default = false): mixed
    {
        return WpState::$options[$name] ?? $default;
    }
}

if (! function_exists('update_option')) {
    function update_option(string $name, mixed $value): bool
    {
        WpState::$options[$name] = $value;

        return true;
    }
}

if (! function_exists('get_transient')) {
    function get_transient(string $name): mixed
    {
        return WpState::$transients[$name] ?? false;
    }
}

if (! function_exists('set_transient')) {
    function set_transient(string $name, mixed $value, int $expiration = 0): bool
    {
        WpState::$transients[$name] = $value;

        return true;
    }
}

if (! function_exists('delete_transient')) {
    function delete_transient(string $name): bool
    {
        unset(WpState::$transients[$name]);

        return true;
    }
}

if (! function_exists('get_post_field')) {
    function get_post_field(string $field, int $postId, string $context = 'display'): string
    {
        return WpState::$content[$postId] ?? '';
    }
}

if (! function_exists('wp_slash')) {
    function wp_slash(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map('wp_slash', $value);
        }

        return is_string($value) ? addslashes($value) : $value;
    }
}

if (! function_exists('wp_json_encode')) {
    function wp_json_encode(mixed $value, int $flags = 0): string|false
    {
        return json_encode($value, $flags);
    }
}

if (! function_exists('sanitize_key')) {
    function sanitize_key(string $key): string
    {
        return (string) preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($value)));
    }
}

if (! function_exists('wp_kses')) {
    /**
     * Approximation of wp_kses(): drops tags and attributes that are not allowed.
     *
     * @param  array<string, array<string, mixed>>  $allowedHtml
     * @param  list<string>  $allowedProtocols
     */
    function wp_kses(string $value, array $allowedHtml, array $allowedProtocols = ['http', 'https', 'mailto', 'tel']): string
    {
        return (string) preg_replace_callback(
            '#<(/?)([a-z0-9]+)([^>]*)>#i',
            static function (array $m) use ($allowedHtml, $allowedProtocols): string {
                $tag = strtolower($m[2]);
                if (! isset($allowedHtml[$tag])) {
                    return '';
                }
                if ($m[1] === '/') {
                    return '</'.$tag.'>';
                }
                $attributes = '';
                preg_match_all('/([a-z\-]+)\s*=\s*"([^"]*)"/i', $m[3], $found, PREG_SET_ORDER);
                foreach ($found as $attribute) {
                    if (strtolower($attribute[1]) === 'href'
                        && preg_match('/^\s*([a-z][a-z0-9+.-]*):/i', $attribute[2], $scheme)
                        && ! in_array(strtolower($scheme[1]), $allowedProtocols, true)) {
                        continue;
                    }
                    if (isset($allowedHtml[$tag][strtolower($attribute[1])])) {
                        $attributes .= ' '.strtolower($attribute[1]).'="'.$attribute[2].'"';
                    }
                }

                return '<'.$tag.$attributes.'>';
            },
            $value,
        );
    }
}

if (! function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return $value;
    }
}

if (! function_exists('wp_verify_nonce')) {
    function wp_verify_nonce(string $nonce, string $action): int|false
    {
        return $nonce === 'valid:'.$action ? 1 : false;
    }
}

if (! function_exists('current_user_can')) {
    function current_user_can(string $capability, mixed ...$args): bool
    {
        return WpState::$canEdit;
    }
}

if (! function_exists('wp_unslash')) {
    function wp_unslash(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map('wp_unslash', $value);
        }

        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (! function_exists('wp_is_post_revision')) {
    function wp_is_post_revision(int $postId): int|false
    {
        return false;
    }
}

if (! function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }
}

if (! function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return esc_html($text);
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }
}

if (! function_exists('wp_nonce_field')) {
    function wp_nonce_field(string $action, string $name): void
    {
        echo '<input type="hidden" name="'.$name.'" value="valid:'.$action.'">';
    }
}

if (! function_exists('add_meta_box')) {
    function add_meta_box(string $id, string $title, callable $callback, string $screen, string $context = 'advanced', string $priority = 'default'): void {}
}

if (! function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle, string $src, array $deps = [], string $ver = '', bool $inFooter = false): void {}
}

if (! function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src, array $deps = [], string $ver = ''): void {}
}

if (! function_exists('plugins_url')) {
    function plugins_url(string $path, string $plugin): string
    {
        return 'http://example.test/'.$path;
    }
}

if (! class_exists('WP_Screen')) {
    class WP_Screen
    {
        public string $base = '';

        public string $post_type = '';
    }
}

if (! function_exists('get_current_screen')) {
    function get_current_screen(): ?WP_Screen
    {
        return WpState::$screen;
    }
}

if (! function_exists('post_type_supports')) {
    function post_type_supports(string $postType, string $feature): bool
    {
        return WpState::$editorSupport;
    }
}

if (! function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $args = 1): bool
    {
        return true;
    }
}

if (! function_exists('remove_post_type_support')) {
    function remove_post_type_support(string $postType, string $feature): void {}
}

if (! function_exists('get_stylesheet_directory')) {
    function get_stylesheet_directory(): string
    {
        return WpState::$themeDir;
    }
}

if (! function_exists('wp_kses_post')) {
    function wp_kses_post(string $text): string
    {
        return $text;
    }
}

if (! function_exists('register_rest_route')) {
    /**
     * @param  array<string, mixed>  $args
     */
    function register_rest_route(string $namespace, string $route, array $args): bool
    {
        return true;
    }
}

if (! class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        /**
         * @param  array<string, mixed>  $params
         */
        public function __construct(private array $params = []) {}

        public function get_param(string $key): mixed
        {
            return $this->params[$key] ?? null;
        }
    }
}

if (! function_exists('rest_url')) {
    function rest_url(string $path = ''): string
    {
        return 'http://example.test/wp-json/'.ltrim($path, '/');
    }
}

if (! function_exists('wp_create_nonce')) {
    function wp_create_nonce(string $action): string
    {
        return 'nonce-'.$action;
    }
}

if (! function_exists('get_current_user_id')) {
    function get_current_user_id(): int
    {
        return 1;
    }
}

if (! function_exists('absint')) {
    function absint(mixed $value): int
    {
        return abs((int) $value);
    }
}

if (! function_exists('esc_url_raw')) {
    function esc_url_raw(string $url): string
    {
        return preg_match('#^(https?://|mailto:|tel:|/|\#)#i', trim($url)) === 1 ? trim($url) : '';
    }
}

if (! function_exists('get_post_status')) {
    function get_post_status(int $id): string|false
    {
        return $GLOBALS['sectionist_test_posts'][$id]['status'] ?? false;
    }
}

if (! function_exists('get_permalink')) {
    function get_permalink(int $id): string|false
    {
        return $GLOBALS['sectionist_test_posts'][$id]['url'] ?? false;
    }
}

if (! function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return esc_url_raw($url);
    }
}

if (! function_exists('get_the_ID')) {
    function get_the_ID(): int|false
    {
        return false;
    }
}
