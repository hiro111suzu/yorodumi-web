<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'BRANCH', $_GET['br'] ?: 'admin->title' );
define( 'IDS',  array_filter( preg_split( '/[\s,]+/', $_GET['ids'] )  ));
define( 'LIMIT',  $_GET['limit'] ?: 10 );

//. form
_simple()->hdiv(
	"検索キーワード",
	_t( 'form', ''
		. _p( 'branch:'. _inpbox( 'br', BRANCH ) )
		. _p( 'IDs:'. _inpbox( 'ids', implode( ' ', IDS ) ) )
		. _p( 'limit:'. _inpbox( 'limit', LIMIT ) )
		. _input( 'submit', 'st: width:20em' )
	)
);

//. search
$out = '';
$num = 0;
$ids = [];
if ( ! IDS ) {
	$ids = _idlist('emdb');
	shuffle( $ids );
}
_testinfo( array_slice( $ids, 0, 50 ) );
foreach ( IDS ?: $ids as $id ) {
	$id = _numonly( $id );
	$res = _branch(
		_json_emdb( $id ) ,
		BRANCH
	);
	if ( ! IDS && ! array_filter( $res ) ) continue;
	$out .= TR.TH
		. _ab( "quick.php?id=e$id", $id ). ' '
		. _ab( "jsonview.php?id=e$id", 'json' )
		. TD. _t( 'pre', _json_pretty( $res ))
	;
	++ $num;
	if ( ! IDS && LIMIT <= $num ) break;
}
_simple()->hdiv(
	'data' ,
	$out ? _t( 'table', $out ) : 'no data'
);
