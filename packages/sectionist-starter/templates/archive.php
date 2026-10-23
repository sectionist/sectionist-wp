<?php

declare(strict_types=1);

get_template_part('layouts/header');
?>
<main id="content">
	<?php the_archive_title('<h1>', '</h1>'); ?>
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
