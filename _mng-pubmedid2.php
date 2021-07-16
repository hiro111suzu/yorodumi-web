<?php

//. init
require( __DIR__. '/_mng.php' );
define( 'LIM_ID', 32000000 );
//define( 'LIM_ID', 31000000 );
define( 'FN_TSV',   DN_EDIT. '/pubmed_id.tsv' );
define( 'FN_WLIST', DN_EDIT. '/pubmedid_whitelist.txt' );
define( 'FN_BLIST', DN_PREP. '/pubmed/pubmed_blacklist.json' );
define( 'FN_FOUND', DN_PREP. '/pubmed/pubmed_found.json.gz' );

//. get
$reset = BR. _a( '?', 'リセット' );

//.. set
if ( $_GET['set'] ) {
	list( $pmid, $ids ) = explode( ',', $_GET['set'], 2 );
	$ids = explode( ',', $ids );
	_simple()->hdiv( 'set', _imp( $ids ). " = $pmid". $reset );
	$set = [];
	foreach ( $ids as $id ) {
		if ( substr( $id, 0, 1 ) == 'e' )
			$id = _numonly( $id );
		$set[ "/\b$id\t.*/" ] = "$id\t$pmid";
	}
//	_testinfo( $set );
	if ( $set ) {
		file_put_contents( FN_TSV, _reg_rep(
			file_get_contents( FN_TSV ), $set
		) );
	}
}

//.. black list
if ( $_GET['blist'] ) {
//	list( $pmid, $ids ) = explode( '|', $_GET['set'] );
//	$ids = explode( ',', $ids );
	_simple()->hdiv( 'black list', $_GET['blist']. $reset );
	_json_save(
		FN_BLIST,
		_uniqfilt(
			array_merge( (array)_json_load( FN_BLIST ), [ $_GET['blist'] ] 
		) )
	);
}

if ( $_GET['blist_all'] ) {
	list( $pmid_list, $ids ) = explode( ':', $_GET['blist_all'] );
	$o = [];
	foreach ( explode( ',', $pmid_list ) as $p )
		$o[] = "$p,$ids";
	_simple()->hdiv( 'blacklist まとめて', _ul( $o ). $reset );
	_json_save(
		FN_BLIST,
		_uniqfilt(
			array_merge( (array)_json_load( FN_BLIST ), $o
		) )
	);

//	$blist = (array)_json_load( FN_BLIST );
}

//.. whitelist
if ( $_GET['white'] ) {
	file_put_contents( FN_WLIST, "\n". $_GET['white']. "\n", FILE_APPEND );
}

//. 要チェック・pubmed-id
$out = [];
$wlist = _file( FN_WLIST );
foreach ( (array)_json_load( DN_PREP. '/pubmed_tobe_checked.json' ) as $idset => $item ) {
	if ( in_array( $idset, $wlist ) ) continue;
	$out[] = "$idset: ". _a( "?white=$idset", '[ok]' )
		. BR. _table_2col( $item )
	;
}
if ( $out ) 
	_simple()->hdiv( '要チェック Pubmed-ID', _ul( $out, 10000 ) );
unset( $out );

//. data 読み込み
//- tsvから既知のIDを抽出
$known = _tsv_load2( FN_TSV );
foreach ( (array)$known[ 'emdb' ] as $i => $v )
	$known['pdb'][ "e$i" ] = $v;
$known = $known["pdb"];

define( 'BLIST', _json_load( FN_BLIST ) );

//. main
$out = '';
foreach ( _json_load( FN_FOUND ) as $title => $data ) {
	$ids = $auth = $pmids_t = $pmids_a = [];
	extract( $data ); //- $ids, $auth, $pmids_t, $pmids_a

	//- よくわからんが、空白が来る？
	if ( ! is_array( $pmids_a ) )
		$pmids_a = [];
	if ( ! is_array( $pmids_t ) )
		$pmids_t = [];

	//- きまっているやつは除去
	foreach ( $ids as $n => $i ) {
		if ( $known[ $i ] )
			unset( $ids[ $n ] );
	}
	if ( ! $ids ) continue;

	//- ngid
	foreach ( $pmids_t as $n => $i )
		if ( $i < LIM_ID ) unset( $pmids_t[ $n ] );
	foreach ( $pmids_a as $n => $i )
		if ( $i < LIM_ID ) unset( $pmids_a[ $n ] );
	if ( ! $pmids_a && ! $pmids_t ) continue;

	//- 両方一致
	$pmids_b = [];
	foreach ( $pmids_t as $t => $val_t ) foreach ( $pmids_a as $a => $val_a ) {
		if ( $val_t != $val_a ) continue;
		unset( $pmids_t[ $t ], $pmids_a[ $a ] );
		$pmids_b[] = $val_t;
	}

	//- make
	$table = [];
	$pmids_white = [];
	foreach ([
		'title' => $pmids_t,
		'auth'  => $pmids_a ,
		'both'	=> $pmids_b
	] as $type => $pmids ) {
		$a = [];
		foreach ( (array)$pmids as $i ) {
			if ( ! $i ) continue; //- ?
			$label = "$i,". implode( ',', $ids );
			if ( in_array( $label, BLIST ) ) continue;
			$a[] = _ab([ 'pubmed', $i ], $i ). ' '
				. _pop( '▼', _ul([ 
					_a( "?set=$label"   , 'tsv登録' ) ,
					_a( "?blist=$label" , 'ブラックリスト登録' )
				]))
			;
			$pmids_white[] = $i;
		}

		if ( $a ) {
			if ( 2 < count( $pmids_white ) ) {
				$a[] = _pop( 'まとめて▼', _a( 
					'?blist_all='. implode( ',', $pmids_white ). ':'. implode( ',', $ids )
					,
					'まとめてブラックリスト' 
				));
			}
			$table[ $type ] = _imp( $a );
		}
	}
	
	//- output
	if ( ! $table ) continue;
	$out .= _simple()->hdiv( $title,
		''
			. _p( _imp( $auth ) )
			. _ent_catalog(  $ids, [ 'mode' => 'icon' ] )
			. _table_2col( $table )
		, [ 'type' => 'h2' ]
	) ;
}
_simple()->hdiv( 'data', $out ?: 'なし' );

//. blacklistチェック
$all_items = [];
foreach ( _json_load( FN_FOUND ) as $c ) {
	extract( $c ); //- ids, auth, $pmids_t, $pmids_a
	$ids = implode( ',', $ids );
	foreach ( $pmids_a + $pmids_t as $pmid ) {
		$all_items[ "$pmid,$ids" ] = true;
	}
}

$blist_result = [];
$new_list =[];
foreach ( _json_load( FN_BLIST ) as $item ) {
	if ( $all_items[ $item ] ) {
		++ $blist_result[ 'active' ];
		$new_list[] = $item;
	} else {
		++ $blist_result[ 'obso' ];
	}
}
if ( $_GET['clean_blist'] ) {
	_json_save( FN_BLIST, $new_list );
}

_simple()->hdiv(
	'blacklist item',
	_kv( $blist_result )
	.BR. _ab( '?clean_blist=1', 'clean' )
);

