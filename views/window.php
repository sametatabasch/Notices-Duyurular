<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

return '
<div id="window-' . $notice->ID . '" class="alert window ' . $notice->type . ' ' . $noBorder . '" displayTime="' . @$notice->displayTime . '">
    <button type="button" class="close">&times;</button>
    ' . $title . ' ' . $content . '
</div>';