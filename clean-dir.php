<?php
$dn = $argv[1];
if ( ! is_dir( $dn ) )
	die();

$num = $argv[2];
if ( $num < 1 )
	$num = 100;

$g = glob( "$dn/*" );
$cnum = count( $g );
//echo "file num: $cnum\n";
foreach ( $g as $fn ) {
	if ( $cnum <= $num ) break;
	unlink( $fn );
//	echo "deleted: $fn\n";
	-- $cnum;
}


