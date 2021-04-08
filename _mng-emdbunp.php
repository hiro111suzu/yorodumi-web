<?php
//. init
require( __DIR__ . '/_mng.php' );

define( 'RANGE'	, 50 );
define( 'ID', _getpost( 'id' ) );
define( 'VAL', _getpost( 'val' ) );

define( 'FN_TSV', DN_EDIT. '/unpid_emdb_annot.tsv' );
define( 'EMDB_UNP_TSV', _tsv_load( FN_TSV ) );


//. ajax
if ( _getpost( 'ajax' ) ) {
	define( 'TYPE', _getpost( 'type' ) );

	//.. page
	if ( TYPE == 'page' )
		die( _pap_list() );
}

//. フルページ
if ( ID ) {
//.. データ書き込み
	$tsv = EMDB_UNP_TSV;
	if ( VAL == '-' || VAL == '' )
		unset( $tsv[ ID ] );
	else
		$tsv[ ID ] = VAL; 

	if ( EMDB_UNP_TSV == $tsv ) {
		$msg = '変更なし' ;
	} else {
		$msg = _tsv_save( FN_TSV, $tsv ) ? '書き込み成功' : '書き込み失敗';
	}

	$_simple->hdiv( 'command', ''
		. _p( ID. ' => '. VAL )
		. _p( $msg )
		. _p( _a( $_SERVER['SCRIPT_NAME'], 'Pap list' ) )
	);
	
} else {
//.. pap list
	$_simple
	->hdiv( 'Pap items', _div( '#searchres', _pap_list() ) )
	->css(
		'select {font-size: large}'
	);
}

//.. function _pap_list
function _pap_list() {
	$list = [];

	//... EMDB-ID収集
	foreach ( _idlist('emdb') as $emdb_id ) {
		if ( EMDB_UNP_TSV[ $emdb_id ] ) continue;
		if ( ! _flg_emdb_unp( $emdb_id ) ) continue;
		$pmid =_ezsqlite([
			'dbname' => 'main' ,
			'select' => 'pmid' ,
			'where'  => [ 'db_id', "emdb-$emdb_id" ] 
		]);
		if ( EMDB_UNP_TSV[ $pmid ] ) continue;
		$list[ $pmid ][] = $emdb_id;
	}

	//... データ作成
	krsort( $list );
	$out = '';
	foreach ( array_slice(
		$list ,
		PAGE * RANGE, 
		RANGE ,
		true
	) as $pmid => $emdbids ) {
		$item = _ezsqlite([
			'dbname'	=> 'pap' ,
			'select'	=> [ 'pmid', 'journal', 'data' ] ,
			'where'		=> [ 'pmid', $pmid ] 
		]);
		$icons = '';
		$item['data'] = json_decode( $item['data'], true );
		foreach ( $item['data']['ids'] as $i ) {
			$icons .= ( new cls_entid( $i ) )->ent_item_img([
				'add' => in_array( explode( '-', $i )[1], $emdbids )
					? _span( '.red', '@' )
					: ''
			]);
		}
		unset( $item['data']['ids'] );
		$out .= _pap_item( $item, [
			'add' => $icons. BR. _input_emdb_unp( $pmid )
		]);
	}

	//... pager
	$o_pager = new cls_pager([
		'range' => RANGE ,
		'total'	=> count( $list ),
		'page'	=> PAGE ,
		'pvar'	=> $_GET + [ 'ajax' => 1, 'type' => 'page' ] ,
		'div'	=> '#searchres'
	]);
	return $o_pager->msg()
		. $o_pager->btn()
		. $out
		. $o_pager->btn()
	;
}
