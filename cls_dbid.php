<?php
_define_term( <<<EOD
TERM_NUM_STR_DATA
	_1_ structure data with this property
	_1_個の構造データ
EOD
);

class cls_dbid {

//. vals
//protected $json, $info = [], $key, $name, $cache;
protected $key, $db, $id, $str_id;

//. FHアイテムベース
//.. key
protected function key( $key = null ) {
	if ( $key )
		$this->set_key( $key );
	return $this->key;
}

//.. set_key
function set_key( $db_or_key, $id = '' ) {
	if ( $id ) {
		$d = strtolower( $db_or_key );
		$this->key = ([
			'uniprot'	=> 'un' ,
			'bird'		=> 'bd' ,
			'interpro'	=> 'in' ,
			'smart'		=> 'sm' ,
			'reactome'	=> 'rt' ,
			'pfam'		=> 'pf' ,
			'prosite'	=> 'pr' ,
			'genbank'	=> 'gb' ,
			'cath'		=> 'ct' ,
		][ $d ] ?: $d ) .':'. strtolower( $id );
		$this->db = $d;
	} else {
		$this->key = $db_or_key;
		list( $this->db, $this->id ) = explode( ':', $db_or_key, 2 );
	}
	return $this;
}

//.. get_cls
function get_cls() {
	return [
			'ec' => 'f' ,
			'go' => 'f' ,
			'rt' => 'f' ,
			'pf' => 'h' ,
			'in' => 'h' ,
			'pr' => 'h' ,
			'ct' => 'h' ,
			'sm' => 'h' ,
//			'un' => 'c' ,
//			'gb' => 'c' ,
//			'bd' => 'c' ,
//			'no' => 'c' ,
		][ $this->db ] ?: 'c';
}


//.. title
function title( $title, $key, $flg_array = false ) {
	_add_lang( 'dbid_title' );
	//- $flg_array: Wikipedia情報は配列で別々に返すフラグ
	$key = $this->key( $key );
	list( $t1, $t2 ) = explode( '|', $title, 2 );

	//- bird: $t2がクラス、タイプ、コンマ区切り
	list( $db, $id ) = explode( ':', $key, 2 );

	//... bird
	if ( $db == 'bd' ) {
		_add_lang( 'bird' );
		$t = [];
		foreach ( explode( ', ', $t1 ) as $s )
			$t[] = _l( $s ). _obj('wikipe')->icon_pop($s);
		return $flg_array
			? [ $t2, _obj('wikipe')->show( $t2 ), _imp( $t ) ]
			: $t2. _obj('wikipe')->pop_xx( $t2 ). _kakko( _imp( $t ) )
		;
	}

	//... cath
	if ( $db == 'ct' ) {
		$t2 = $t1;
		$t1 = 'CATH '. _l([
			1 => 'Class' ,
			2 => 'Architecture' ,
			3 => 'Topology' ,
			4 => 'Homologous superfamily' ,
		][ count( explode( '.', $key ) ) ]);
	}

	//... ec 日本語
	if ( L_JA && $db == 'ec' && substr( $id, -1 ) == '-' ) {
		$t = $this->ec_ja( $id ) ?: $t1;
		return $flg_array
			? [ $t, _obj('wikipe')->show( $t1 ) ]
			: $t. _obj('wikipe')->pop_xx( $t1 )
		;
	}

	//... chemcomp
	if ( $db == 'chem' && substr( $t1, 0, 5 ) == 'Chem-' ) {
		$t1 = _ezsqlite([
			'dbname' => 'chem' ,
			'select' => 'name' ,
			'where'  => [ 'id', strtoupper( $id ) ] ,
		]);
	}

	//... $t2がない
	if ( !$t2 ) {
		return $flg_array ? (
			$t1
				? [ $t1, _obj('wikipe')->show( $t1 ) ]
				: [ '-', '' ]
		) : (
			$t1
				? $t1. _obj('wikipe')->pop_xx( $t1 )
				: '-'
		);
	}

	//... uniprot / reactome:  (生物種名がくる)
	if ( $db == 'rt' || $db == 'un' ) {
		return $flg_array
			? [ "$t2 -" . _obj('taxo')->item( $t1 ) ]
			: $t2. _obj('wikipe')->pop_xx( $t2 ). ' - '. _obj('taxo')->item( $t1 )
		;
	}

	//... その他
	return $flg_array
		? [ '<b>'. _l( $t1 ) ."</b>: $t2", _obj('wikipe')->show( $t2 ) ]
		: '<b>'. _l( $t1 ) ."</b>: $t2". _obj('wikipe')->pop_xx($t2)
	;
}

//.. link
function link( $key = '' ) {
	$key = $this->key( $key );
	return _dblink(
		[
			'un' => 'UniProt'   ,
			'bd' => 'BIRD'   ,
			'in' => 'InterPro'	,
			'rt' => 'Reactome'	,
			'pf' => 'Pfam'		,
			'pr' => 'PROSITE'	,
			'gb' => 'GenBank'	,
			'sm' => 'SMART'		,
			'ct' => 'CATH'	,
			'nor' => 'Norine' ,
		][ $this->db ] ?: strtoupper( $this->db )
		,
		strtoupper( $this->id ), 
		[
			'icon' => $this->icon() ,
			//- url CATHは、スーパーファミリーと、親要素で違う
			'url'  => $this->db != 'ct' ? ''
			 : _url(
			 	count( explode( '.', $this->id ) ) == 4 ? 'cath' : 'cath_tree' ,
			 	$this->id
			 )
		]
	);
}

//.. hit_item: ym search用
function hit_item( $key, $title ) {
	$key = $this->key( $key );
	return _ul( array_merge(
		[ $this->link(). $this->db_wikipe() ],
		$this->title( $title, $key, true )
	) );
}

//.. pop
function pop( $db_or_key = '', $id = '', $text = '', $pre_contents = '') {
	if ( $db_or_key )
		$this->set_key( $db_or_key, $id );
	if ( !$text ) {
		$a = explode( '|', _ezsqlite([
			'dbname' => 'dbid' ,
			'select' => 'title' ,
			'where'  => [ 'db_id', $this->key ]  ,
		]));
		$text = $a[1] ?: $a[0];
	}
	if ( $text == '.' ) {
		$text = implode( ': ', array_filter([ $db_or_key, $id ]) ); //- あえてDB-IDで表示
	} else if ( L_JA && $text ) {
		list( $db, $id ) = explode( ':', $this->key );
		$ec_j = '';
		if ( $db == 'ec' ) {
			$ec_j = $this->ec_ja( $id );
		}
		$ja_title = $ec_j ?: _obj('wikipe')->get($text)->e2j(); //- 全一致のみ
	}

	return _pop_ajax( ''
		. $this->icon()
		. _obj('wikipe')->icon( $text ) //- 部分一致あり
		. ( $ja_title ?: $text ?: "$db_or_key: $id" )
		,
		[ 'mode' => 'dbid', 'key' => $this->key, 'pre' => $pre_contents ]
	);
}

//.. pop_pre プレコンテンツあり
function pop_pre( $key, $pre ) {
	return $this->pop( $key, '', '', $pre );
}

//.. pop_contents: ajax.php で利用
function pop_contents( $key, $opt = [] ) {
	$this->set_key( $key );
	//- YM search へのリンク
	$search = '';
	$title = $num = ''; 
	extract( _ezsqlite([
		'dbname' => 'dbid' ,
		'select' => [ 'title', 'num' ] ,
		'where'	 => [ 'db_id', $key ],
	]) );
	if ( $num ) {
		$search = _ab( 
			[ 'ysearch', 'kw' => _quote( $key, 2 ) ] ,
			IC_SEARCH. _term_rep( TERM_NUM_STR_DATA, number_format( $num ) )
		);
	}
	list( $title, $wikipe, $misc ) = $this->title( $title, $key, true );
	$out = [];
	
	//- サブ情報
	$subinfo = '';
	list( $db, $id ) = explode( ':', $key );

	//- ecの場合は親情報
	if ( $db == 'ec' && substr( $id, -1 ) != '-' ) {
		$parent_id = preg_replace( '/\.[0-9]+$/', '.-', $id );
		$subinfo = L_JA 
			? $this->ec_ja( $parent_id )
			: _ezsqlite([
				'dbname' => 'dbid' ,
				'select' => 'title' ,
				'where'  => [ 'db_id', "ec:$parent_id" ]  ,
			])
		;
	}
	//- データベースへのリンク
	return _ul([
		$this->link(). $this->db_wikipe() ,
		$title ,
		$subinfo ,
		$search ,
		$wikipe ,
		$misc
	]);
}

//.. icon
protected function icon() {
	return _fa( 'database', 'large dbid_'. $this->db );
}

//.. db_wikipe
protected function db_wikipe() {
	$n = explode( ':', $this->key )[0];
	return _kakko( $n == 'bd'
		? _ab( 'prd_help', IC_HELP. TERM_ABOUT_PRD )
		: _obj('wikipe')->pop_xx( 'db_'. $n ) )
	;
}

//.. ec_ja
protected function ec_ja( $ecnum ) {
	return _json_cache( DN_DATA. '/ecnum_ja.json.gz' )->$ecnum;
}

//. 構造エントリベース
//.. set_strid
function set_str_id( $id ) {
	$this->str_id = $this->prep_str_id( $id );
	return $this;
}

//.. prep_str_id
protected function prep_str_id( $id ) {
	return is_object( $id ) 
		? ( $id->db == 'emdb' ? 'e' : '' ). $id->id
		: $id
	;
}

//.. strid2keys
function strid2keys( $id = null ) {
	if ( $id )
		$this->set_str_id( $id );
	return array_filter( explode( '|', _ezsqlite( [
		'dbname' => 'strid2dbids' ,
		'where'	 => [ 'strid', $this->str_id ] ,
		'select' => 'dbids' ,
	])));
}


}

