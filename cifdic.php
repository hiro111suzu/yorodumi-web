<?php
//. init
require( __DIR__. '/common-web.php' );
ini_set( "memory_limit", "256M" );

_define_term( <<<EOD
TERM_REL_WIKIPE
	Related Wikipedia items
	ウィキペディアの関連項目
TERM_MORE
	more
	詳細
EOD
);

_add_url(  'cifdic' );
_add_fn(   'cifdic' );
_add_lang( 'cifdic' );

//- aniso_B11 => aniso_B[1][1]
define( 'QUERY', _aniso_rep( trim( _getpost( 'q' ), ' _.|' ) ) );

list( $categ, $item ) = explode( '.', QUERY );
$wikipe =
	_obj('wikipe')->get( QUERY )->show() ?:
	_obj('wikipe')->get( $item )->show() ?:
	_obj('wikipe')->get( $categ)->show()
;

define( 'SAS' ,
	substr( $q, 0, 4 ) == 'sas_'
	|| 
	_getpost( 'db' ) == 'sas'
);

//define( 'MODE', SAS ? 'sas' : ( MODE_V5 ? 'v5' : 'pdb' ) );
define( 'MODE', SAS ? 'sas' : 'pdb' );
define( 'FLG_AJAX', (boolean)_getpost( 'ajax' ) );
define( 'KW', _getpost_safe( 'kw' ) );
define( 'PAGE', (integer)_getpost( 'page' ) );

//. ajax pop-up
if ( FLG_AJAX && ! KW ) {
	$dic = _dic_json();
	$query = QUERY;
	$desc = $dic[ $query ];
	if ( ! $desc ) foreach ([
		'.'				=> '.beg_' ,
		'vector' 		=> 'vector1' ,
		'matrix' 		=> 'matrix[1][1]' ,
		'sas_scan'		=> 'sas_sample' ,
		'sas_scan.' 	=> 'sas_scan_intensity.' ,
		'sas_beam'		=> 'sas_detc' ,
		'matrix'		=> 'matrix11' ,
		'D_max'			=> 'Dmax' ,
		'resolution'	=> 'd_resolution_high' ,
		'resolution='	=> 'd_res_high' ,
		'resolution=='	=> 'ls_d_res_high' ,
		'resolution==='	=> 'high_resolution' ,

	] as $k => $v ) {
		$q = strtr( $query, [ trim( $k, '=' ) => $v ] );
		$desc = $dic[ $q ];
		if ( ! $desc ) continue;
		$query = $q;
		list( $categ, $item ) = explode( '.', $q );
	}
	if ( ! $desc ) {
		$desc = _l( 'Unknown item'  );
	} else {
/*
		$p1 = '/Data items in the ';
		$p2 = ' category (record|provide the) details (of|about)(| the) /i';
		$q = preg_quote( $query );
		$desc = _reg_rep( $desc, [
			$p1 . $q . $p2 => '' ,
			$p1 . '[A-Z_]+' .  $p2 => '' ,
		]) 
		
		. _div( '.red', '/Data items in the '
			. preg_quote( $query )
			. ' category record details of the/i' );
*/
	}

	die( _t( 'h2| .h_sub', _l([
		'sas' => 'SAS-CIF dictionary' ,
		'pdb' => 'PDBx/mmCIF dictionary',
	][ MODE ] ))
		. _p( _kv([ 'Category' => $categ, 'Item' => _aniso_rev( $item ) ]) )
		. _p( _kv([ 'Description' => $desc ]) )
		. _p( _ab(
			[ 'cifdic', 'q' => $query, 'db' => SAS ? 'sas' :'' ] ,
			TERM_MORE
		))
		. ( $wikipe
			? _t( 'h2| .h_sub', TERM_REL_WIKIPE ). _p( $wikipe ) 
			: '' 
		)
	);
}

//.. query 取得
if ( SAS ) {
	//- SAS
	define( 'U', '?db=sas&' );
} else {
	//- PDB
	define( 'U',  '?' );
	define( 'ITEM_IDS'  , _json_load( _fn( 'dic_item2pdbent' ) ) );
	define( 'ITEM_COUNT', _json_load( _fn( 'dic_item_count'  ) ) );
}


//.. ajax-search result
if ( FLG_AJAX && KW ) {
	die( _kw_search() );
}

//. フルページ、ここから
$query = [ _a( U . 'f=A', 'top' ) ];
$data = '';
$search_res = '';
$links = [];

//$links[ 'others' ][] = _a( U . 'f=A', 'All categories');

$dic = _gzload( _fn( SAS ? 'dic_sas_gz' : 'dic_pdb_gz') );
preg_match( '/_dictionary.version +([0-9\.]+)/', $dic, $a );
$dicver = $a[1];
//$wikipe = [];

//. 内容
//.. 指定なし
if ( !QUERY && !KW ) {
	$init_hit = [];
	$query = [ 'none' ];
	define( 'INITIAL', _getpost( 'f' ) ?: 'A' );
	preg_match_all( '/save_([a-zA-Z\[\]].*)/' , $dic, $match );

	$exf = [];
	foreach ( $match[1] as $categ ) {
		$categ2 = strtr( $categ, [ 'pdbx_' => '' ] );
		$f2 = strtoupper( substr( $categ2, 0, 1 ) );
		$exf[ $f2 ] = true;
		//- 頭文字
		if ( $f2 == INITIAL || SAS ) {
			$init_hit[] = _link_tag( $categ );
		}
	}
	ksort( $exf );
	$link_initials = [];
	foreach ( array_keys( $exf ) as $s ) {
		$link_initials[] = _a( U . "f=$s", $s );
	}
	if ( ! SAS )
		$links = [
			implode( ' ', $link_initials ) ,
			INITIAL . '---' . _ul( $init_hit, 0 )
		];

//.. keyword search
} else if ( KW ) {
	$query[] = 'Keyword search: '. KW;
	$search_res = _kw_search();

//.. header
} else if ( QUERY == 'header' ) {
	$a = [];
	foreach ( explode( "\n", substr( $dic, 0, 5000 ) ) as $line ) {
		if ( _instr( '## DICTIONARY_HISTORY ##', $line ) ) break;
		if ( _instr( '     loop_', $line ) ) break;
		$a[] = $line;
	}
	$data = implode( "\n", $a );

//.. category
} else if ( ! _instr( '.', QUERY ) ) {
	$query[] = "[Category] " . QUERY;

	$qq = preg_quote( QUERY );
	preg_match( "/save_$qq(.*?)save_/s", $dic, $match );
	$data = $match[1];

	preg_match_all( "/save__($qq\..*)/", $dic, $match );
	$links[] = _item_links( $match[1] );
	$links[] = _ab([ 'dic_pdb_cat', QUERY ], 'wwPDB CIF Dictionary' );

//.. item
} else {
	$query[] = _a( U . "q=$categ", "[Category] $categ" );
	$query[] = '[Item] ' . _aniso_rev( $item );

	$qq = preg_quote( $categ. '.'. _aniso_rev( $item ) );
	preg_match( "/save__$qq(.*?)save_/s", $dic, $match );
	$data = $match[1];

	preg_match_all( "/save__($categ\..*)/", $dic, $match );
	$links[] = _item_links( $match[1] );
	$links[] = _ab([ 'dic_pdb_item', QUERY ], 'wwPDB CIF Dictionary' );
}

//.. pdb entries
$pdb_ents = '';
if ( !SAS && QUERY && QUERY != 'header' && ITEM_IDS && !_instr( 'atom_site', QUERY ) ) {
	$cnt = ITEM_COUNT[ QUERY ];
	$a = (array)ITEM_IDS[ QUERY ];
	shuffle( $a );
	$pdb_ents = $a
		? _p( $cnt . ' entries with this this Category/Iterm' )
			. _ent_catalog(
				array_slice( $a, 0, 50 ) ,
				[ 'mode' => 'icon' ]
			)
		: _p( '.red', 'No PDB entry with this Category/Iterm' )
	;
}

//. output
//.. query
_simple()->hdiv( 'Query', _simple_table([
	'Dictionary' => _imp2([
		_a_flg( MODE == 'pdb', '?db='   , 'PDB' ) ,
//		_a_flg( MODE_V5      , '?v5=1'  , 'V5' ) ,
		_a_flg( SAS          , '?db=sas', 'SAS' ) ,
	]),
	'Version'	=> $dicver ,
	'Query'		=> _imp2( $query ) ,
	'Keyword'	=> _t( 'form', _inpbox( 'kw', KW ). _input( 'hidden', 'name:db', MODE ) ),
]) ); 

//.. data
if ( $data != '' )
	_simple()->hdiv( 'Data', _t( 'pre', $data ) );
if ( $search_res ) {
	_simple()->hdiv( 'Search result', _div( '#searchres', $search_res ) );
}

//.. wikipe
if ( $wikipe )
	_simple()->hdiv( TERM_REL_WIKIPE, $wikipe );

//.. pdb
if ( !SAS && $pdb_ents != '' )
	_simple()->hdiv( 'PDB entries', $pdb_ents );

//.. links
if ( QUERY || KW )
	$links[] = _a( U. 'f=A', 'All categories');
if ( QUERY != 'header' )
	$links[] = _a( U. 'q=header', 'Dictionary header' );
$links[] = _ab(
	SAS ? 'dic_text_sas' : 'dic_text_pdb' ,
	'Download dictionry text'
);

_simple()
->page_conf([
	'title' => 'Cif dic view' ,
	'sub'	=> 'simple Cif dictionry viewer' ,
	'icon'	=> 'white'
])
->css( '.notused { text-decoration: line-through; }' )
->hdiv( 'Links', _ul( $links, 0 ) )
->out([
]);

//. func
//.. _item_links
function _item_links( $terms ) {
	$ret = [];
	foreach ( (array)$terms as $term )
		$ret[] = _link_tag( $term );
	return 'Items:'. _ul( $ret, 0 );
}


//.. _link_tag
function _link_tag( $term, $flg_show_both = false ) {
	if ( $term == '' ) return;
	list( $categ, $item ) = explode( '.', $term );
	return _ab(
		[ 'q' => _aniso_rep( $term ), 'db' => SAS ? 'sas' : '' ],
		 _span(
			!SAS && _not_used( $term ) && !_instr( 'atom_site', $term )
				? '.notused' : '.bld' ,
			$flg_show_both ? $term : ( $item ?: $categ )
		)
	);
}

//.. _used
function _not_used( $term )  {
	if ( SAS ) return $term;
	return ITEM_COUNT && ! ITEM_COUNT[ _aniso_rep( $term ) ];
}

//.. _dic_json
function _dic_json() {
	return _json_load( _fn( SAS ? 'dic_sas_json' : 'dic_pdb_json' ) );
}

//.. search
function _kw_search() {
	//- search
	$hit = [];
	foreach ( _dic_json() as $key => $val ) {
		if ( ! _instr( KW, $key ) && ! _instr( KW, $val ) ) continue;
		$hit[] = $key;
	}

	//- pager
	$o_pager = new cls_pager([
		'str'		=> [ 'keywords' => KW ] ,
		'range' 	=> 50 ,
		'total'		=> count( $hit ) ,
		'page'		=> PAGE ,
		'pvar'		=> $_GET + [ 'ajax' => 1 ] ,
		'div'		=> '#searchres'
	]);

	$found = [];
	foreach ( (array)array_slice( $hit, PAGE * 50, 50 ) as $k ) {
		$found[] = _link_tag( $k, true );
	}
	return $o_pager->msg()
		. ( $found ? _ul( $found, 0 ) : 'No item found' )
		. $o_pager->btn()
	;	
}

//.. _aniso_rep
function _aniso_rep( $in ) {
	return strtr( $in, [ '[' => '', ']' => '' ] );
}

//.. _aniso_rev
function _aniso_rev( $in ) {
	return _reg_rep( $in, [
		'/aniso_B([0-3])([0-3])/'	=> 'aniso_B[$1][$2]'  ,
		'/^([TLS])([0-3])([0-3])$/'	=> '$1[$2][$3]' ,
		'/^([TLS])([0-3])([0-3])_/'	=> '$1[$2][$3]_' ,
	]);
}
