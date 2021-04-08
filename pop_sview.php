<?php
//. init
define( 'VIEWER_ID', 'surfview' );
require( __DIR__ . '/pop_common.php' );

$id = $o_id->id ?: 1003;
$dn = "data/emdb/media/$id/ym";

//. 大きさ計算
foreach ( file( "$dn/pg1.pdb" ) as $n => $l ) {
	$x[ $n ] = substr( $l, 30, 8 );
	$y[ $n ] = substr( $l, 38, 8 );
	$z[ $n ] = substr( $l, 46, 8 );
}

$ix = ( $x[0] + $x[1] ) / 2 * -1;
$iy = ( $y[0] + $y[1] ) / 2 * -1;
$iz = ( $z[0] + $z[1] ) / 2 * -1;
$dep = sqrt( 
	pow( $x[0] - $x[1], 2 ) +
	pow( $y[0] - $y[1], 2 ) +
	pow( $z[0] - $z[1], 2 )
);
$topline = "EMDB-$id - Surfview"; //: x:$ix y:$iy z:$iz dep: $dep ";

$fn_objs = "$dn/s1.obj";
$fn_obj = file_exists( $fn_objs ) ? $fn_objs :  "$dn/1.obj" ;

//. output
$_simple->page_conf([
	'loading' => true ,
	'icon'	=> 'lk-view.gif' ,
	'jslib'	=> [
		'js/three/three.min.js' ,
		'js/three/DDSLoader.js' ,
		'js/three/MTLLoader.js' ,
		'js/three/OBJMTLLoader.js' ,
		'js/three/TrackballControls.js' 
//		'js/three/OrbitControls.js' ,
//		'js/threen/three.min.js' ,
//		'js/three/JSONLoader.js' ,
//		'js/three/OrbitControls.js' 
	],
	'js'	=> 'pop_sview',
])
->add_contents( _div( '#container' ) )

//.. jsvar
->jsvar([
	'filepath' => [
		'obj' => $fn_obj,
		'mtl' => "$dn/1.mtl",
		'json' => "$dn/1.json",
	],
	'init_pos'	=> [

		'x' => $ix,
		'y' => $iy,
		'z' => $iz,
		'dep' => $dep ,
		'fogn' => $dep * 0.25,
		'fogf' => $dep * 0.7,
	]
])

//.. css
->css( <<<EOD
html, body {
	overflow: hidden;
}
#container {
	position: fixed; top: 0;
	width: 100%; height: 100
}
EOD
)

//.. end
->popvw_output();
