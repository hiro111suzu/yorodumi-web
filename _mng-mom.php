<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'KW'	, _getpost( 'kw' ) );
define( 'PAGE'	, _getpost( 'page' ) );
define( 'RANGE'	, 50 );
define( 'MOM_ID', _getpost( 'id' ) ?: '' );

//. ajax
if ( _getpost( 'ajax' ) ) {
	define( 'TYPE', _getpost( 'type' ) );
	//.. PDB items
	if ( TYPE == 'pdb' )
		die( _pdb_ents( _getinfo( 'pdb' ) ) );

	//.. f&h items
	if ( TYPE == 'fh' )
		die( _fh_ents( _getinfo( 'fh' ) ) );

	//.. page
	if ( TYPE == 'page' )
		die( _search_res() );
}

//. フルページ
$_simple->hdiv( 'Keyword search', 
	_t( 'form' , _table_2col([
		'Mom-ID'  => _inpbox( 'id', MOM_ID ) ,
		'keyword' => _inpbox( 'kw', KW )  
	]). _input( 'submit', 'st: width:20em' ) )
);

if ( MOM_ID ) {
	//.. 単独ページ
	$kw = [];
	foreach ( ( new cls_sqlite('id2mom') )->qcol([
		'select' => 'dbid' ,
		'where'  => "dbid LIKE 'kw:%' and mom LIKE '{\"" . MOM_ID. "\":%'"
	]) as $k ) {
		$kw[] = explode( ':', $k, 2 )[1];
	}
	//- $en, $ja, $pdb, $fh, $month
	extract( _getinfo([ 'en', 'ja', 'month', 'pdb', 'fh' ]) );

	$_simple->hdiv( 'MOM entry: '. $month. ' #'. MOM_ID, ''
		. $_simple->hdiv( 'info', _table_2col([
			'Title' => $ja. _kakko( $en ) ,
			'PDB' => _pdb_ents( $pdb ) ,
			'F&H' => _fh_ents( $fh ) ,
			'keywords' => _imp2( $kw ) ,
		]), [ 'type' => 'h2' ] )
		. $_simple->hdiv( 'Doc',
			strtr( 
				file_get_contents( DN_PREP. '/mom/html/'. MOM_ID. '.html' ), [
					'="/mom/' 		=> '="https://numon.pdbj.org/mom/' ,
					'="/momimages/'	=> '="https://numon.pdbj.org/momimages/' ,
					'https://pdbj.org/pdb/' => 'quick.php?id=pdb-'
				]
			)
			, 
			[ 'type' => 'h2' ] 
		)
	);
} else {
	//.. 一覧
	$_simple->hdiv( 'Search result', _div( '#searchres', _search_res() ) );
}
//. functions 
//.. search result
function _search_res() {
	$sqlite = new cls_sqlite( 'mominfo' );
	$num_hit = $sqlite->where( KW == ''
		? ''
		: _kw2sql( KW,
			[ 'id', 'en', 'ja', 'month', 'pdb', 'fh' ]
		)
	)->cnt();
	$res = $sqlite->qar([
		'select'	=> [ 'id', 'month', 'pdb', 'fh' ] ,
		'order by'	=> 'id DESC' ,
		'limit'		=> RANGE ,
		'offset'	=> PAGE * RANGE ,
	]);

	$found = '';
	foreach ( $res as $o ) {
		extract( $o ); //- $id, $pdb, $fh, $month
		$found .= _mom_link( $id, _imp2(
			_pop_ajax( 'PDB', [ '?', 'id' => $id, 'type' => 'pdb' ] ) ,
			_pop_ajax( 'F&H', [ '?', 'id' => $id, 'type' => 'fh' ] )
		));
	}

	//... pager
	$o_pager = new cls_pager([
		'str'		=> KW == '' ? '':
			' for keyword: "' .KW. '"' ,
		'range' 	=> RANGE ,
		'total'		=> $num_hit ,
		'page'		=> PAGE ,
		'pvar'		=> $_GET + [ 'ajax' => 1, 'type' => 'page' ] ,
		'div'		=> '#searchres'
	]);

	return $o_pager->msg()
		. $o_pager->btn()
		. $found
		. $o_pager->btn()
	;
}

//.. _getinfo
function _getinfo( $select ) {
	return _ezsqlite([
		'dbname' => 'mominfo' ,
		'select' => $select ,
		'where'  => [ 'id', MOM_ID ]
	]);
}

//.. _pdb_ents
function _pdb_ents( $in ) {
	return _ent_catalog( explode( '|', $in ) );
}

//.. _fh_ents
function _fh_ents( $in ) {
	$out = [];
	foreach ( explode( '|', $in ) as $i )
		$out[] = _obj('dbid')->pop( $i );
	return _imp( $out );
}

