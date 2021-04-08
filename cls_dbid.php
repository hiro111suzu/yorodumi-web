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
protected $key, $key_ar, $ecnum_ja_tsv;

//. set_key
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
	} else {
		$this->key = $db_or_key;
	}
	return $this->key;
}

//. icon
function icon() {
	return _fa( 'database', 'large dbid_' . explode( ':', $this->key, 2 )[0] );
}

//. title
function title( $title, $key, $flg_array = false ) {
	_add_lang( 'dbid_title' );
	//- $flg_array: Wikipedia情報は配列で別々に返すフラグ
	$this->set_key( $key );
	list( $t1, $t2 ) = explode( '|', $title, 2 );

	//- bird: $t2がクラス、タイプ、コンマ区切り
	list( $db, $id ) = explode( ':', $key, 2 );

	//.. bird
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

	//.. cath
	if ( $db == 'ct' ) {
		$t2 = $t1;
		$t1 = 'CATH '. _l([
			1 => 'Class' ,
			2 => 'Architecture' ,
			3 => 'Topology' ,
			4 => 'Homologous superfamily' ,
		][ count( explode( '.', $key ) ) ]);
	}

	//.. ec 日本語
	if ( L_JA && $db == 'ec' && substr( $id, -1 ) == '-' ) {
		$t = $this->ec_ja( $id ) ?: $t1;
		return $flg_array
			? [ $t, _obj('wikipe')->show( $t1 ) ]
			: $t. _obj('wikipe')->pop_xx( $t1 )
		;
	}

	//.. $t2がない
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

	//.. uniprot / reactome:  (生物種名がくる)
	if ( $db == 'rt' || $db == 'un' ) {
		return $flg_array
			? [ "$t2 -" . _obj('taxo')->item( $t1 ) ]
			: $t2. _obj('wikipe')->pop_xx( $t2 ). ' - '. _obj('taxo')->item( $t1 )
		;
	}

	//.. その他
	return $flg_array
		? [ '<b>'. _l( $t1 ) ."</b>: $t2", _obj('wikipe')->show( $t2 ) ]
		: '<b>'. _l( $t1 ) ."</b>: $t2". _obj('wikipe')->pop_xx($t2)
	;
}

//. link
function link( $key = '' ) {
	if ( $key )
		$this->key = $key;
	$key = $key ?: $this->key ;
	list( $db, $id ) = explode( ':', $key, 2 );
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
		][ $db ] ?: strtoupper( $db )
		,
		strtoupper( $id ), 
		[
			'icon' => $this->icon() ,

			//- url CATHは、スーパーファミリーと、親要素で違う
			'url'  => $db != 'ct' ? ''
			 : _url(
			 	count( explode( '.', $id ) ) == 4 ? 'cath' : 'cath_tree' ,
			 	$id
			 )
		] 
	);
}

//. hit_item
function hit_item( $key, $title ) {
	return _ul( array_merge(
		[ $this->link( $key ). $this->db_wikipe() ],
		$this->title( $title, $key, true )
	) );
}

//. pop
function pop( $db_or_key, $id = '', $text = '', $pre_contents = '') {
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

//. pop プレコンテンツあり
function pop_pre( $key, $pre ) {
	return $this->pop( $key, '', '', $pre );
}

//. pop_contents
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

//. strid2keys
function strid2keys( $id ) {
	return array_filter( explode( '|', _ezsqlite( [
		'dbname' => 'strid2dbids' ,
		'where'	 => [ 'strid', $id ] ,
		'select' => 'dbids' ,
	])));
}

//. db_wikipe
function db_wikipe() {
	$n = explode( ':', $this->key )[0];
	return _kakko( $n == 'bd'
		? _ab( 'prd_help', IC_HELP. TERM_ABOUT_PRD )
		: _obj('wikipe')->pop_xx( 'db_'. $n ) )
	;
}

//. ec_ja
function ec_ja( $ecnum ) {
	return _json_cache( DN_DATA. '/ecnum_ja.json.gz' )->$ecnum;
}

//. end
}

/*
* hit_item
	+ 
* list_item
pop
pop_conttents



*/
