<?php
set_time_limit( 5 );
$s = $_GET[ 's' ] ;
if ( $s == '' ) $s = 5;
echo "sleep $s sec.";
sleep( $s );
