<?php

class cls_fh_calc {
//. param
protected $id1, $id2;

//. construct
function __construct( $a ) {
	$id = $id2 = $type = null;
	extract( $a );
	$this->id   = $this->prep_id( $id );
	$this->id2  = $this->prep_id( $id2 );
	$this->type = $type;
	$this->score_column = [
		'f'		=> 'score_f' ,
		'h'		=> 'score_h' ,
		'c'		=> 'score_c' ,
		'hc'	=> 'score_h + score_c' ,
	][ G_TYPE ] ?: 'score';
	return $this;
}

//. prep_id
function prep_id( $id ) {
	if ( ! $id ) return;
	$o_id = is_object( $id ) ? $id : new obj_entid( $id );
	$db = $DB = $id = $did = null;
	extract( $o_id->get() );
	return $db == 'emdb' ? "e$id" : $id;
}

//. search
function _search() {

	//... クエリ情報
	list( $key_items, $key_score ) = $this->get_item_score( $this->id );

	//... 検索
	$data = [];
	foreach ( $key_items as $fh_id ) {
		$strids = $score = null;
		extract( _ezsqlite([
			'dbname' => 'dbid2strids' ,
			'select' => [ 'strids', 'score' ] ,
			'where'  => [ 'dbid', $fh_id ]
		]) );
		if ( $score < 0.0001 ) continue;
		foreach ( explode( '|', $strids ) as $i ) {
			if ( !$i || $i == $this->id ) continue;
			$data[ $i ] += $score;
		}
	}

	//... ノーマライズ
	foreach ( array_keys( $data ) as $str_id ) {
		$data[ $str_id ] /= (( _ezsqlite([
			'dbname' => 'strid2dbids' ,
			'select' => $this->score_column ,
			'where'  => [ 'strid', $str_id ]
		]) + $key_score ) / 2 );
		if ( $data[ $str_id ] < 0.0001 )
			unset( $data[ $str_id ] );
	}
	arsort( $data );
	return $data;
}

//.. _get_item_score
function _get_item_score( $id ) {
	list( $items, $score ) = array_values( _ezsqlite([
		'dbname' => 'strid2dbids' ,
		'select' => [ 'dbids', $this->score_column ] ,
		'where'  => [ 'strid', $id ] ,
	]));
	return [ _filter_item( explode( '|', $items ) ), $score ];
}


//.. _similarity
function _similarity() {
//	return 'similarity'. _getpost('ids') ;
	$ids = explode( '|', _getpost('ids') );
	$items = _filter_item( _obj('dbid')->strid2keys( $ids[0] ) );
	$ret = [];
	foreach ( _obj('dbid')->strid2keys( $ids[1] ) as $i ) {
		if ( in_array( $i, $items ) )
			$ret[] = _obj('dbid')->pop( $i );
	}
	return _long( $ret, 10 );
}

//.. _item2categ
function _item2categ( $item ) {
	return [
		'ec' => 'f' ,
		'go' => 'f' ,
		'rt' => 'f' ,
		'pf' => 'h' ,
		'in' => 'h' ,
		'pr' => 'h' ,
		'ct' => 'h' ,
	][ explode( ':', $item, 2 )[0] ] ?: 'c';
}

//.. _filter_item
function _filter_item( $items ) {
	if ( G_TYPE == 'all' ) return $items;
	$ret = [];
	foreach ( $items as $item ) {
		$categ = _item2categ( $item );
		if ( ! _instr( $categ, G_TYPE ) ) continue;
		$ret[] = $item;
	}
	return $ret;
}

//.. _comparison
function _comparison() {
	define( 'ID2', _getpost( 'id2' ) );
	list( $items1, $score1 ) = _get_item_score( ID );
	list( $items2, $score2 ) = _get_item_score( ID2 );
	$share = [];
	$table = TR_TOP.TH. 'Item'
		.TH. ( new cls_entid( ID ) )->ent_item_img(). BR. $score1
		.TH. ( new cls_entid( ID2 ) )->ent_item_img(). BR. $score2
		.TH. 'score'
	;
	$sum = 0;
	foreach ( $items1 as $i ) {
		if ( ! in_array( $i, $items2 ) ) continue;
		$s = _comparison_item_score( $i );
		$sum += $s;
		$table .= _comparison_row( $i, '@', '@', $s );
	}
	foreach ( $items1 as $i ) {
		if ( in_array( $i, $items2 ) ) continue;
		$table .= _comparison_row( $i, '@', '-' );
	}
	foreach ( $items2 as $i ) {
		if ( in_array( $i, $items1 ) ) continue;
		$table .= _comparison_row( $i, '-', '@' );
	}
	$avg = ( $score1 + $score2 ) / 2;
	return _t( 'table', $table )
		.BR. _table_2col([
			'sum' => $sum ,
			'avg' => $avg ,
			'score' => $sum / $avg
		])
	;
}

function _comparison_row( $fh_id, $c1, $c2, $score = false ) {
	return TR.TH. _obj('dbid')->pop( $fh_id )
		.TD. $c1
		.TD. $c2
		.TD. ( $score ?: _comparison_item_score( $fh_id ) )
	;
}
function _comparison_item_score( $fh_id ) {
	return _ezsqlite([
		'dbname' => 'dbid2strids' ,
		'select' =>	'score' ,
		'where'  => [ 'dbid', $fh_id ]
	]);
}

