<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set( "memory_limit", "512M" );
define( 'AJAX', true );
require( __DIR__. '/common-web.php' );
require( __DIR__. '/omo-web-common.php' );

//if ( TESTSV ) die('hoge' );
//. init
$num = $argv[2] ?: 100;
$bn_res = $argv[3];
$i = _idrep( $argv[1] );

define( 'DN_RES' , DN_OMO. '/results' . ( TESTSV ? '_pre' : '' ) );

$cmd = DN_GMFIT_BIN. "/pairgmfit.cgi idref=$i idtar=____ text=T";
define( 'FN_OUT', DN_RES . "/$bn_res-gmref-$num.json.gz" );
_json_save( FN_OUT, [ 'status' => 0, 'cnt' => 0, 'num' => $num ] );

$json = _json_load( DN_RES . "/$bn_res.json.gz" );
$res = [];

//- ?i???񍐕p?x
$stat = [];
foreach ( range( 0, 9 ) as $n ) {
//	echo "$n ";
	$stat[ round( $num * $n / 10 ) ] = 100 * $n / 10;
}

//. ?$cnt = 0;
foreach ( array_slice( $json[ 's' ], 0, $num ) as $i => $x ) {
	if ( TESTSV ) {
		//- ?e?X?g?T?[?o?[?p?_?~?[???ʍ쐬
		$res[ $i ] = rand(0,10000) / 10000;
		usleep( 100000 );
	} else {
		$out = [];
		exec( strtr( $cmd, [ '____' => _idrep( $i ) ] ), $out );

		$s = 0;
		foreach ( $out as $l ) {
			$l = explode( ' ', $l ) ;
			echo implode( "\t" , $l ) . "\n";
			if ( $l[0] != 'CorrCoeff' ) continue;
			$s = $l[1];
			break;
		}
		$res[ $i ] = $s;
	}
	$orank[ $i ] = $cnt + 1;


	if ( $stat[ $cnt ] != '' ) {
		_json_save( FN_OUT, [ 'status' => $stat[ $cnt ], 'cnt' => $cnt, 'num' => $num ] );
	}
	echo "$cnt/$num\n";
	
	++ $cnt;
}

_json_save( FN_OUT, [ 'cc' => $res, 'rnk' => $orank ] );
print_r( [ 'cc' => $res, 'rnk' => $orank ] );

function _idrep( $i ) {
	return substr( $i, 0, 1 ) == 'e'
		? "emdb_" . preg_replace( '/[^0-9]/', '',  $i )
		: "PDB-" . strtr( $i, [ '-d' => '' ] )
	;
}

