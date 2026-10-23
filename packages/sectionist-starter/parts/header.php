<?php

declare(strict_types=1);

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'sectionist-starter'); ?></a>
<header class="site-header">
	<a class="site-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
	<?php if (has_nav_menu('primary')) { ?>
		<nav aria-label="<?php esc_attr_e('Primary menu', 'sectionist-starter'); ?>">
			<?php wp_nav_menu(['theme_location' => 'primary', 'container' => false]); ?>
		</nav>
	<?php } ?>
</header>
