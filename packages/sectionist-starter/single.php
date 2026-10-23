<?php

declare(strict_types=1);

get_template_part('parts/header');
?>
<main id="content">
<?php while (have_posts()) {
    the_post(); ?>
<?php function_exists('sectionist_render') && sectionist_render(); ?>
<?php comments_template('/parts/comments.php'); ?>
<?php } ?>
</main>
<?php get_template_part('parts/footer'); ?>
