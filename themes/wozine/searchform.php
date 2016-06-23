<?php
/**
 * The template for displaying Search form
 *
 * @package dawn
 */
?>
<form method="GET" id="searchform" class="search-form dt-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="form">
	<label for="s" class="sr-only"><?php esc_html_e( 'Search', 'wozine' ); ?></label>
	<input type="hidden" value="post" name="post_type">
	<input type="search" id="s" name="s" class="form-control" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search&hellip;', 'wozine' ); ?>" />
	<button type="submit" class="search-submit" name="submit"><i class="fa fa-search"></i></button>
</form>
