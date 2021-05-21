<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'TODAY', date( 'Y-m-d', time() - 150 * 3600 * 24 ) );
define( 'TSVDATA', _tsv_load2( DN_EDIT. '/pubmed_id.tsv' ) );

//$url_base = 'http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=pubmed&cmd=search&term=';
//'http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=pubmed&cmd=search&term='

//. blacklist
$ign_id = array_fill_keys([
	1906 ,
	'3dg0' ,
	'3dg2' ,
	'3dg4' ,
	'3dg5' ,
], true );

$ign_title = array_fill_keys([
	'to be published' ,
	'cryoem of groel' ,
	'tba' ,
	'N/A' ,
	'Withheld' ,
	'suppressed', 
	'supressed' ,
	'membrane protein' ,
	'tARC6 dimer' ,
], true );

//. table
$clist = [];
$auth = [];
$flg =  [];
$num = 0;

//.. EMDB
$num = 0;
foreach( TSVDATA[ 'emdb' ] as $id => $pmid ) {
	if ( $ign_id[ $id ] || $pmid ) continue;
	$o = new cls_entid();
	$json = $o->set_emdb( $id )->mainjson()->deposition;
	$t = trim( $json->primaryReference->journalArticle->articleTitle, '.' );

	if ( $ign_title[ strtolower( $t ) ] || $t == '' ) continue;

	$clist[ $t ][] = $id;
	$auth[ $t ] = explode( ',',  $json->authors );
	if ( $o->ex_map() )
		$flg[ $t ] = true;
}
$_simple->time( 'emdb' );

//.. PDB
foreach( TSVDATA[ 'pdb' ] as $id => $pmid ) {
	if ( $ign_id[ $id ] || $pmid ) continue;
	$json = _json_load2([ 'epdb_json', $id ]);
	$t = '_';
	//- title
	foreach ( (array)$json->citation as $j ) {
		if ( $j->id != 'primary' ) continue;
		$t = trim( $j->title, '.' );
	}
	if ( $ign_title[ strtolower( $t ) ] || $t == '' ) continue;

	$clist[ $t ][] = $id;

	//- auth
	if ( count( (array)$auth[ $t ] ) == 0 ) {
		foreach ( (array)$json->citation_author as $j ) {
			if ( $j->citation_id != 'primary' ) continue;
			$auth[ $t ][] = trim( $j->name );
		}
	}
	
	$flg[ $t ] = true;
}
$_simple->time( 'pdb' );

//.. clean
foreach ( $clist as $t => $v ) {
	if ( ! $flg[ $t ] )
		unset( $clist[ $t ] );
}

//. main loop
$wait = 0;
$out = [];
$js = '';

$start = PAGE * 10;
foreach ( array_slice( array_keys( $clist ), PAGE * 10, 10 ) as $num => $title ) {
	$ids = '';
	$tt = [];
	foreach ( $clist[ $title ] as $id ) {
		$o = new cls_entid( $id );
		$ids .= $o->ent_item_img();
		$tt[ $o->title() ] = 1;
	}
			
	$url_title = _pubmed_url( $title );
	$str_auth = _imp( $auth[ $title ] );
	$out[] = [
		_span( '.red', '#'. ( $start + $num ) ). BR. $ids ,
		_ul( array_keys( $tt ) ). _ab( _pubmed_url( $str_auth ), $str_auth ) ,
		_ab( $url_title, $title )
	];
	if ( trim( $title ) != 'Supressed' )
		$js .= "setTimeout( function(){ window.open('$url_title'); }, $wait);";
	$wait += 1000;
}
$_simple->time( 'make-result' );

//. 結果まとめ
_out( ''
//	. _p( $date )
	. _btn( '!'.  htmlspecialchars( $js ), 'open all' )
	. _table_toph( [ 'ID', 'title', 'ref' ], $out )
	,
	[ 'total' => count( $clist ), 'range' => 10 ]
);

//. function _pubmed_url
function _pubmed_url( $str ) {
	return "https://www.ncbi.nlm.nih.gov/pubmed?term=("
		. _reg_rep( $str, [
			'/[^a-zA-Z0-9]/' => ' ',
			'/\b(and|or|the|of|as|an|for|to|in|on|with|by)\b/i' => ' ' ,
			'/  +/' => ' ' ,
		])
		. ")%20AND%20(%22". TODAY. "%22%5BDate%20-%20Publication%5D%20%3A%20%223000%22%5BDate%20-%20Publication%5D)"
	;
}
