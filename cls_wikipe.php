<?php
class cls_wikipe {

protected $info = [], $query;

//. find
function find( $query, $flg_upper = false ) {
	$this->info = [];
	$this->query = trim( $flg_upper ? strtoupper( $query) : $query );
	//- $key, $en_title, $en_abst, $ja_title, $ja_abst
	$q = [
		'dbname' => 'wikipe' ,
		'key'	 => 'key' ,
		'select' => [ 'key', 'en_title', 'en_abst', 'ja_title', 'ja_abst' ],
	];

	extract( _ezsqlite( $q, $this->query ) );
	if ( $en_title != '@' ) {
		extract( _ezsqlite( $q, $en_title ) );
	}
	$en_title = $key;

	if (
		_instr( ' may refer to:', $en_abst ) ||
		_instr( '曖昧さ回避', $ja_abst ) ||
		_instr( '曖昧さ回避', $ja_title )
	) {
		$this->info = [ 'bad' => true ];
		$en_title == '';
	} else {
		if ( $en_abst == '' )
			$en_title = '';
		else 
			$this->info = compact( 'en_title', 'en_abst', 'ja_title', 'ja_abst' );
	}
	return $en_title != '';
}

//. get
function get( $query ) {
	if ( $this->find( $query ) ) return $this;
	$this->find( $query, true );
	return $this;
}
//. chem
function chem( $id ) {
	$this->find( "c:$id" );
	return $this;
}

//. taxo
function taxo( $id ) {
	$this->find( "t:$id" );
	return $this;
}
//. term
function term( $query ) {
	$this->get( $query );
	if ( $this->find( $query ) ) return $this;
	if ( $this->find( $query, true ) ) return $this;

	foreach ( _reps_wikipe_terms() as $rep ) {
		$query = trim( _reg_rep( strtolower( $query ), $rep ), ' .,' );
		if ( strlen( $query ) < 3 ) break;
		if ( $this->find( $query ) ) break;
		if ( $this->find( $query, true ) ) break;
	}
	return $this;
}

//. prep
function prep() {
	extract( $this->info ); //- $en_title, $en_abst, $ja_title, $ja_abst
	if ( ! $en_title ) return [];
	if ( L_EN || ! $ja_title ) {
		$title = $en_title;
		$abst  = $en_abst;
		$url   = _url( 'wikipe_en', $en_title );
	} else {
		$title = $ja_title;
		$abst  = $ja_abst;
		$url   = _url( 'wikipe_ja', $ja_title );
	}
	return compact( 'title', 'abst', 'url' );
}

//. show: 概要を出力
function show( $t = '' ) {
	if ( $t )
		$this->term( $t );
	extract( $this->prep() ); //- $tite, $abs, $url
	return $title
		? _ab( $url, IC_WIKIPE. TERM_WIKIPE. ' - '. $title ) 
			. ': ' . $abst
		: ''
	;
}

//. pop: 概要をポップアップ
function pop( $t = '' ) {
	if ( $t )
		$this->term( $t );
	extract( $this->prep() ); //- $tite, $abs, $url
//	if ( ! $title ) return _pop( '[x]', _json_pretty([ $this->query, $this->info ]) );
	if ( ! $title ) return;
	//- キーと同じようなタイトルだったらアイコンだけ
	if ( strtolower( $this->query )
		== _reg_rep( strtolower( $title ), [ '/ *\(.+?\)/' => '' ] ) 
	)
		$title = '';
	return _pop_ajax( IC_WIKIPE. $title, [ 'mode' => 'wp', 'k' => $this->query ] );
}

//. pop_x 見直し予定、とりあえず、単独で使う用
function pop_xx( $t ) {
	return $t ? $this->pop( $t ) : '';
}

//. icon_pop: アイコンだけポップアップ
function icon_pop( $t = '' ) {
	if ( $t )
		$this->term( $t );
	extract( $this->prep() ); //- $tite, $abs, $url
	if ( ! $title ) return;
	return _pop_ajax( IC_WIKIPE, [ 'mode' => 'wp', 'k' => $this->query ] );
}
//. e2j: 日本語化
function e2j() {
	return L_JA
		? ( $this->info['ja_title'] ) /*?: _ezsqlite([
			'dbname' => 'term' ,
			'select' => 'ja' ,
			'where'  => [ 'en', $this->info['en_title'] ]
		]))*/
		: ''
	;
}

//. flg フラグ
function flg( $t = '' ) {
	if ( $t )
		$this->term( $t );
	return $this->info['en_title'] != '' ;
}
//. icon データが有るならアイコン返す
function icon( $t = '' ) {
	if ( $t )
		$this->term( $t );
	return $this->info['en_title']  ? IC_WIKIPE : '';
}

}
