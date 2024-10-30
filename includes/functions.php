<?php

function wpcrm_render_template( $template, $params = array() ) {
    ob_start();
    include( $template );
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
