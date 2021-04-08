<?php
//. init
require( __DIR__ . '/_mng.php' );

$dn_lat = DN_FDATA . '/emdbxml';
$dn_old = DN_FDATA . '/emdbxml-old';

$fn_old = []; //- [emdbid][date] = path name
foreach ( glob( "$dn_old/*" ) as $pn ) {
	$fn = basename( $pn, '.xml' );
	$a = explode( '-', $fn );
	$fn_old[ $a[1] ][ $a[2] ] = $pn;
}

$fn = _getpost( 'fn' );
if ( $fn != '' ) {
	die( _t( 'xmp', file_get_contents( $fn )  ) );
}

//. get val
$i = _getpost( 'id' );
$d = _getpost( 'date' );
if ( strlen( $i ) == 8 && $i == _numonly( $i ) ) {
	$i == '';
	$d = $i;
}

define( 'ID', $i );
define( 'DT', $d );

$flg_id_hit = true;
if ( count( (array)$fn_old[ ID ] ) == 0 ) {
	$flg_id_hit = false;
}

//. IDリスト表示して終わり
if ( ! $flg_id_hit || ID == '' ) {
	$out = [];
	$dates = [];
	foreach ( $fn_old as $id => $c ) {
		 //- 日付指定があったら、その日付のみ
		if ( DT != '' && $c[DT] == '' ) continue;
		$out[] = _ab( "?id=$id", "$id (" . count($c) . ')' );
		if ( DT == '' ) foreach ( $c as $d => $f ) {
			$dates[ $d ] = 1;
		}

	}
	$_simple->hdiv( 'ID input', ''
		. ( ID == '' ? '' : _p( 'No old xml for ' . ID ) )
		. _idinput( ID ) 
	);
	if ( $dates != [] ) {
		$dates = array_keys( $dates );
		rsort( $dates );
		$o = [];
		foreach( $dates as $d ) {
			$o[] = _ab( "?date=$d", $d );
		}
		$_simple->hdiv( 'date list', _imp( $o ) );

	}
	$_simple->hdiv( 'ID list' . ( DT == '' ? '' : ' for '.DT )
		, _imp( $out ) );
	die();
}

//. ヒットあり

//.. 他の日
$out = [];
$fn_old = $fn_old[ID];
arsort( $fn_old );
foreach ( $fn_old as $d => $p ) {
	$out[] = ( $d == DT ? "[$d]" : _a( "?date=$d&id=" .ID, $d ) )
		. ' '
		. _ab( "?date=$d", 'all ent for this date' ) 
	;
}
$o_id = ( new cls_entid() )->set_emdb( ID );

//.. ファイル名決定
$fn_target = "$dn_lat/emd-" .ID. '.xml';
foreach ( $fn_old as $d => $n ) {
	$fn_prev = $n;
	if ( $d == DT || DT == '' ) break;
	$fn_target = $n;
}

$cmd = "diff -bu $fn_prev $fn_target ";
exec( $cmd, $diff );
if ( count( $diff ) == 0 ) {
	$diff = [ file_get_contents( $fn_target ) == file_get_contents( $fn_prev )
		? 'identical' : 'not identical but no diff'
	];
}


//.. 
$_simple
->hdiv( 'ID input', _idinput( ID ), [ 'hide' => true] )
->hdiv( ID, ''
	. $o_id->ent_item_list()
	. _ul( $out )
	. _p( 'diff: '
		. _ab( "?fn=$fn_target", basename( $fn_target, '.xml' ) )
		. ' '
		. _ab( "?fn=$fn_prev", basename( $fn_prev, '.xml' ) )
		. _more( _t( 'pre', $cmd ) )
	)
	. _div( 'st: border: 1px solid gray', implode( BR, $diff ) )
)
;


