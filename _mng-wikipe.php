<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'KW', _getpost( 'kw' ) );
define( 'PAGE', _getpost( 'page' ) );
define( 'RANGE', 50 );
define( 'FLG_AJAX', _getpost( 'ajax' ) );
define( 'KEY', _getpost( 'key' ) );
define( 'NOT_FOUND_LIST', _getpost( 'list' ) );
define( 'FLG_SMALL', _getpost( 'small' ) == 1 );
//define( 'JSON', _json_load( DN_DATA . '/wikipe.json.gz' ) );
$sqlite = new cls_sqlite(
	FLG_SMALL ?  'wikipe' : '/yorodumi/sqlite/wikipe/large.sqlite'
);

//. ajax
if ( FLG_AJAX ) {
	die( _search_res() );
}

//. フルページ
_simple()->hdiv( 'Database', 
	FLG_SMALL
		? '<b>[Small]</b> / '
			. _a( '?'. http_build_query([ 'small' => 0 ] + $_GET ), 'Large' )
		: _a( '?'. http_build_query([ 'small' => 1 ] + $_GET ), 'Small' )
			. ' / <b>[Large]</b> '
);

_simple()->hdiv( 'Keyword search', 
	_t( 'form', _inpbox( 'kw', KW )  )
	. _p( '注意項目' )
	. _imp2([
		_a( '?kw=may+refer+to%3A', 'may refer to:' ) ,
		_a( '?kw=detergent', 'detergent' ) ,
		_a( '?list=chem', 'nflist-chem' ) ,
		_a( '?list=taxo', 'nflist-taxo' ) ,
	])
);

//. 見つからないリスト
if ( NOT_FOUND_LIST == 'chem' ) {
	$fn = DN_PREP. '/wikipedia/not_found_list_chem.txt';
	$ids = array_slice( _file( $fn ), 0, 50 );
	_simple()->hdiv( 'not-found list chem' ,
		_ent_catalog( $ids, [ 'mode' => 'list' ] )
	);
	die();
}

if ( NOT_FOUND_LIST == 'taxo' ) {
	$fn = DN_PREP. '/wikipedia/not_found_list_taxo.txt';
	_testinfo( _file( $fn ) );
	$out = [];
	foreach ( array_slice( _file( $fn ), 0, 50 ) as $term ) {
		$out[] = _ab([ 'taxo', 's' => $term ], $term );
	}
	_simple()->hdiv( 'not-found list taxo' ,
		_ul( $out, 0 )
	);
	die();
}


//. アイテムを表示して終わり
if ( KEY ) {
	$r = $sqlite->qar([
		'select' => '*',
		'where'  => 'key='. _quote( KEY )
	])[0];
	if ( ! $r )  {
		_simple()->hdiv( "Item - " .KEY, 'no item' );
		die();
	}
	extract( $r );
	_simple()->hdiv( "Item - " .KEY, ''
		. _simple()->hdiv( 'Eng: ' .$en_title, $en_abst . _source( $en_abst ), [ 'type' => 'h2' ])
		. _simple()->hdiv( 'Jp: '  .$ja_title, $ja_abst . _source( $ja_abst ), [ 'type' => 'h2' ])
	);
	die();
}

//. 一覧
_simple()->hdiv( 'Search result', _div( '#searchres', _search_res() ) );

//. functions 
//.. search result
function _search_res() {
	global $sqlite;
	$num_hit = $sqlite->where( _kw2sql( KW,
		[ 'key', 'en_title', 'en_abst', 'ja_title', 'ja_abst' ]
	))->cnt();
	$res = $sqlite->qobj([
		'select'	=> [ 'key', 'en_title', 'ja_title' ] ,
		'limit'		=> RANGE ,
		'offset'	=> PAGE * RANGE ,
	]);

	$found = [];
	foreach ( $res as $o ) {
		$key = $o->key;
		list( $cat, $val ) = explode( ':', $key, 2 );
		if ( $cat != 'c' && $cat != 't' ) $cat = 'm';
		$en_term = $o->en_title == '@' ? $key : $o->en_title;
		$found[] = [
			_ab( "?small=" .( FLG_SMALL ? 1: 0 ). "&key=$key", $key ) ,
			_ab([ 'wikipe_en', $en_term ]    , $o->en_title ) ,
			_ab([ 'wikipe_ja', $o->ja_title] , $o->ja_title ) ,
			_ab([
				'c' => 'quick.php?id=chem-' ,
				't' => 'taxo.php?s=' ,
				'm' => 'ysearch.php?kw='
			][ $cat ] . $en_term , [
				'c' => 'Chem' ,
				't' => 'Taxo' ,
				'm' => 'Misc' ,
			][ $cat ])
		];
	}

	//... pager
	$o_pager = new cls_pager([
		'str'		=> KW == '' ? '':
			' for keyword: "' .KW. '"' ,
		'range' 	=> RANGE ,
		'total'		=> $num_hit ,
		'page'		=> PAGE ,
		'pvar'		=> $_GET + [ 'ajax' => 1 ] ,
		'div'		=> '#searchres'
	]);

	return $o_pager->msg()
		. $o_pager->btn()
		. _table_toph(
			[ 'Key', 'EN', 'JP', 'Categ' ] ,
			$found
		)
		. $o_pager->btn()
	;
}
//.. _item
/*
function _item( $key, $array ) {
	return TR
		.TD. _ab( "?key=$key", $key )
		.TD. _ab( "?key=$key", $array[ 'et' ] )
		.TD. _ab( "?key=$key", $array[ 'jt' ] )
		.TD. _ab([
				'c' => 'quick.php?id=chem-' ,
				't' => 'taxo.php?s=' ,
				'm' => 'ysearch.php?kw='
			][ $array[ 'c' ]] . $key ,
			[
				'c' => 'Chem' ,
				't' => 'Taxo' ,
				'm' => 'Misc' ,
			][ $array[ 'c' ]]
		)
	;
}
*/

//.. _source
function _source( $str ) {
	return _div( '.blue', htmlspecialchars( $str ) );
}
