<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'KW', _getpost( 'kw' ) );
define( 'PAGE', _getpost( 'page' ) );
define( 'RANGE', 50 );
define( 'FLG_AJAX', _getpost( 'ajax' ) );

//. ajax
if ( FLG_AJAX ) {
	die( _search_res() );
}

//. 書き出し
_simple()
->hdiv(
	'Keyword search', 
	_t( 'form', _inpbox( 'kw', KW )  )
)
->hdiv(
	'Search result',
	_div( '#searchres', _search_res() ) 
)
;

//. functions 
//.. search result
function _search_res() {
	$sqlite = new cls_sqlite( DN_PREP. '/taxo/id2name.sqlite' );
	$num_hit = $sqlite->where( _kw2sql( KW, [ 'id', 'name' ] ) )->cnt();
	$res = $sqlite->qobj([
		'select'	=> '*' ,
		'limit'		=> RANGE ,
		'offset'	=> PAGE * RANGE ,
	]);

	$found = '';
	foreach ( $res as $o ) {
		$key = $o->key;
		$found .= TR
			.TD. $o->id
			.TD. $o->name
			.TD. $o->type
		;
	}

	//... pager
	$o_pager = new cls_pager([
		'str'		=> KW == '' ? '':
			_ej( ' for keyword: "' .KW. '"', '検索語「' .KW. '」:  ' ) ,
		'range' 	=> RANGE ,
		'total'		=> $num_hit ,
		'page'		=> PAGE ,
		'pvar'		=> $_GET + [ 'ajax' => 1 ] ,
		'div'		=> '#searchres'
	]);

	return $o_pager->msg()
		. $o_pager->btn()
		. ( $found
			?_t( 'table', TR_TOP.TH. 'ID' .TH. 'name' .TH. 'type'. $found )
			: ''
		)
		. $o_pager->btn()
	;
}
