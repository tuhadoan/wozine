<?php
add_action('wozine_breadcrumbs', 'dt_breadcrumbs', 10);

// change the width of an automatic WordPress embed
add_filter( 'dt_embed_defaults', 'dt_embed_size', 10, 2);