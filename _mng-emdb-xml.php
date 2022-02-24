<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'KW', $_GET['kw'] ?: '<author' );
define( 'NUM', $_GET['num'] ?: 10 );


//. form
_simple()->hdiv(
	"検索キーワード",
	_t( 'form', ''
		. _p( 'キーワード:'. _inpbox( 'kw', KW ) )
		. _p( '数:'. _inpbox( 'num', NUM ) )
		. _input( 'submit', 'st: width:20em' )
	)
);

//. search
$ids = _idlist('emdb');
shuffle( $ids );
$num = 0;
$result = [];
_add_fn([
	'emdb_xml' => DN_FDATA. '/emdb-mirror/structures/EMD-<id>/header/emd-<id>.xml'
]);

foreach ( $ids as $id ) {
//	_die( _fn( 'emdb_xml', $id ) );
	$fn = _fn( 'emdb_xml', $id );
	$xml = file_get_contents( $fn );
	if ( ! _instr( KW, $xml ) ) continue;

	preg_match_all(
		'/^.*'. preg_quote( KW ). '.*$/im' ,
		$xml ,
		$match,
		PREG_PATTERN_ORDER
	);
	$out = [];
	foreach ( $match[0] as $line ) {
		$out[] = htmlspecialchars( trim( $line ) );
	}
	$result[ ''
		. _ab(['ym', $id], $id ). BR
		. _ab("disp.php?path=$fn", 'xml' ). BR
		. _ab(['jsonview', "emdb_json.$id" ], 'json' ). BR
	] = implode( BR, _uniqfilt( $out ) );
	++ $num;
	if ( NUM <= $num ) break;
}
_simple()->hdiv(
	'検索結果' ,
	$result ? _table_2col( $result ) : '見つかりません'
);

