<?php

declare(strict_types=1);

get_template_part('layouts/header');
?>
<main id="content">
	<h1><?php printf(esc_html__('Search results for: %s', 'sectionist-starter'), esc_html(get_search_query())); ?></h1>
	<?php get_search_form(); ?>
<?php if (have_posts()) { ?>
<?php while (have_posts()) {
	    the_post();
	    get_template_part('layouts/content-excerpt');
	} ?>
<?php the_posts_pagination(); ?>
<?php } else { ?>
	<p><?php esc_html_e('Nothing found.', 'sectionist-starter'); ?></p>
<?php } ?>
</main>
<?php
	get_template_part('layouts/footer');
