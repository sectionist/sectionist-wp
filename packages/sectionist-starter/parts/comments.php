<?php

declare(strict_types=1);

if (post_password_required()) {
    return;
}
?>
<section id="comments" class="comments">
<?php if (have_comments()) { ?>
	<h2><?php printf(esc_html(_n('%d comment', '%d comments', get_comments_number(), 'sectionist-starter')), (int) get_comments_number()); ?></h2>
	<ol class="comment-list">
		<?php wp_list_comments(['style' => 'ol', 'short_ping' => true]); ?>
	</ol>
	<?php the_comments_navigation(); ?>
<?php } ?>
<?php if (! comments_open() && have_comments()) { ?>
	<p><?php esc_html_e('Comments are closed.', 'sectionist-starter'); ?></p>
<?php } ?>
<?php comment_form(); ?>
</section>
