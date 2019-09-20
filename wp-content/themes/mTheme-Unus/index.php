<?php
// ?fdxvar1=contact
$queryURL = parse_url( html_entity_decode( esc_url( add_query_arg( $arr_params ) ) ) );
parse_str( $queryURL['query'], $getVar );
$variableNum1 = $getVar['fdxvar1'];

//---------------------------------------
if ($variableNum1 == 'contact') {

get_template_part('inc/p-contact');

//---------------------------------------
} else if ($variableNum1 == 'index') {

get_template_part('inc/p-index');

} else {

get_header();
?>
<div class="fdx_topheading">&nbsp;</div>
<?php
if ( fdx_option('p3_rad1') == "1" ) {
get_template_part('inc/index-mobile');
} else {
get_template_part('inc/index-blog');
}

get_footer();

 }//end

?>

