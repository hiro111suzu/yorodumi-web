<?php
//. init
require( __DIR__ . '/_mng.php' );

$data = [];
$data2 = [];
$test = [];
foreach ( glob( DN_DATA. '/pubmed/*.json' ) as $fn ) {
	$pmid = basename( $fn, '.json' );
	$afs = [];
	foreach ( (array)_json_load2( $fn )->affi as $c ) {
		if ( ! is_array( $c ) ) continue;
		$afs = array_merge( $afs, $c );
	}
	foreach ( _uniqfilt( $afs ) as $a ) {
		$flg = _country_flag( $a ) ;
		if ( _instr( '<img', $flg ) ) {
			++ $data2[ $flg ];
		} else {
			$data[ $a. $flg ][] = $pmid;
		}
	}
}

$out = [];
foreach ( $data as $affi => $c ){
	$ids = [];
	foreach ( _uniqfilt( $c ) as $i ) {
		$ids[] = _ab([ 'pap', 'id' => $i], $i );
	}
	$out[] = $affi. BR. _imp2( $ids );
}

//. flgs
$out2 =[];
arsort( $data2 );
foreach ( $data2 as $flg => $num ) {
	$out2[] = trim( $flg )
		. " $num: "
		. _reg_rep( $flg, ['/^.+"img\/flg_(.+?)\.gif".+$/' => "$1"] )
	;
}

//. output
_out( ''
	. _ul( $out, 10 )
	. _ul( $out2, 10 )
);

