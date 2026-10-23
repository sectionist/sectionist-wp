<?php

declare(strict_types=1);

get_template_part('layouts/header');
?>
<main id="content">
	<h1><?php esc_html_e('Page not found', 'sectionist-starter'); ?></h1>
	<?php get_search_form(); ?>
</main>
<?php
get_template_part('layouts/footer');
