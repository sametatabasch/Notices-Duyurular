<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

return '
<div id="bar-' . $notice->ID . '" class="bar alert ' . $notice->type . '">
	<button type="button" class="close" >&times;</button>
	' . $title . ' ' . $content . '
</div>';