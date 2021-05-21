<?php
//. init
require( __DIR__. '/common-web.php' );

//- 検索結果いくつづつ表示するか、生物種、構造データ
define( 'RANGE_TX', 20 );
define( 'RANGE_ST', 50 );

//- 引用
$ref_num = 0;
$refs = [];

//- その他
$dn_taxo = "data/taxo/";

$obj_sqlite  = new cls_sqlite( 'taxo' );
$obj_sql_str = new cls_sqlite( 'taxostr' );
$obj_sql_id  = new cls_sqlite( 'taxoid' );

//. add lang etc.
_add_lang( 'taxo' );
_add_url(  'taxo' );
_add_fn(   'taxo' );

_define_term( <<<EOD
TERM_KW_ETC
	Name, Taxonomy-ID, or Keywords
	名称・Taxonomy-ID・キーワード
TERM_QUERY
	Search query
	検索語
TERM_SRCH_RESULT
	Result of keyword search
	キーワード検索の結果
TERM_LIN_RESULT
	Result of lineage search
	系統検索の結果
TERM_IMG_REL
	Image of closely related species
	画像(近縁種)
TERM_KEYWORD
	 for keyword "_1_"
	 検索語「_1_」の検索結果、
TERM_TAXOICON_SITE
	Taxonomy icon (c) Database Center for Life Science
	生物アイコン (c) ライフサイエンス統合データベースセンター
TERM_TAXON_SEARCH
	Search by taxon - "_1_"
	タクソン「 _1_ 」で検索
TERM_HOST
	Host
	宿主
TERM_OTHER_NAMES
	Other names
	その他の名称
TERM_NAMES_STRDATA
	Names in strucutre data
	構造データでの名称
TERM_REF
	References
	出典
TERM_TAXO_INFO
	Taxonomy info
	生物種情報
TERM_PAGE_TITLE
	Yorodumi Species
	万見生物種
EOD
);

//. getpost
//- キー

//- 名称
$name = ucfirst( strtolower( _getpost( 's' ) ) );

//- 系統
$lin = _getpost( 'l' );


//- order
$o = _getpost( 'order' )
	?: ( $lin . $name == '' ? 'emdb+pdb+sasbdb ' : 'line' )
;

define( 'SORT', $o );
define( 'SORTBY', SORT == 'line' || SORT == 'name' ? 'line' : "$o DESC" );
define( 'PAGE', _getpost( 'page' ) ?: 0 );
define( 'KEY', _getpost('k') );

//. ajax response
$ajax = _getpost( 'ajax' );

if ( $ajax == 'stax' )
	die( _search( $name ) );

if ( $ajax == 'pop' )
	die( $name );

if ( $ajax == 'line' )
	die( _lineage( $lin ) );

if ( in_array( $ajax, [ 'emdb', 'pdb', 'sas' ] ) ) {
	die( _str_ent( $ajax )[0] );
}

//. query
$_simple->hdiv(
	TERM_QUERY ,
	_t( 'form | method:get | action:' ,
		TERM_KW_ETC. ': '
		. _input( 'search', 'name:s| size: 60| .acomp| list:acomp_tx',
			htmlspecialchars( $name ?: KEY ) 
		)
		. _input( 'submit' )
	)
	. _t( 'datalist | #acomp_tx', '' )
	,
	[ 'hide' => $name . $lin != '' ]
);

$data_hit = KEY ? $obj_sqlite->qobj([
	'select' => '*' ,
	'where'	 => 'key='. _quote( KEY )
])[0] : '';
define( 'FLG_KEY_HIT', $data_hit != '' );

//. 検索
//.. キーワード
if ( $lin == '' && ! FLG_KEY_HIT) {
	$r = _search( $name ?: KEY );
	if ( $r ) {
		$_simple->hdiv(
			TERM_SRCH_RESULT ,
			_div( '#searchres', $r )
		);
	}
}

function _search( $name ) {
	global $obj_sqlite;
	//- 検索
	$num = $obj_sqlite->cnt( _kw2sql( $name, 'taxo' ) );
	$hits = $num == 0 ? [] : $obj_sqlite->qobj([
		'select'	=> [ 'key', 'name', 'json1', 'emdb', 'pdb', 'sasbdb' ],
		'order by'	=> SORTBY ,
		'limit'		=> RANGE_TX ,
		'offset'	=> PAGE * RANGE_TX
	]);


	//- 検索語と完全一致一種だけヒット
	if ( count( $hits ) > 0 ) {
		if ( _same_str( $hits[0]->name, $name ) && $num == 1 )
			return;
	}

	$opg = new cls_pager([
		'str'		=> $name == null ? null : [ 'keywords' => $name ] ,
		'range' 	=> RANGE_TX ,
		'total'		=> $num ,
		'page'		=> PAGE ,
		'objname'	=> 'stax' ,
		'pvar'		=> [ 'ajax' => 'stax', 'order' => SORT, 's' => $name ] ,
		'div'		=> '#searchres'
	]);
	return $opg->msg() . _result_table( $hits ) . $opg->btn();
}

//.. 系統
if ( $lin != '' && ! FLG_KEY_HIT) {
	$wp = _obj('wikipe')->taxo( $lin )->show( $lin, 'show' );
	if ( $wp ) $_simple->hdiv( $lin, $wp );
	$_simple->hdiv(
		TERM_LIN_RESULT ,
		_div( '#searchres', _lineage( $lin ) )
	);
}

function _lineage( $lin ) {
	global $obj_sqlite;
	//- 検索
	$num = $obj_sqlite->cnt( _like( 'line', "|%$lin%|" ) );
	$hits = $num == 0 ? (object)[] : $obj_sqlite->qobj([
		'select'	=> [ 'key', 'name', 'json1', 'emdb', 'pdb', 'sasbdb' ],
		'order by'	=> SORTBY ,
		'limit'		=> RANGE_TX ,
		'offset'	=> PAGE * RANGE_TX
	]);

	$opg = new cls_pager([
		'str'		=> ' '. _term_rep( TERM_KEYWORD, $lin ) ,
		'range' 	=> RANGE_TX ,
		'total'		=> $num ,
		'page'		=> PAGE ,
		'objname'	=> 'line' ,
		'pvar'		=> [ 'ajax' => 'line', 'order' => SORT , 'l' => $lin ] ,
		'div'		=> '#searchres'
	]);
	return $opg->msg() . _result_table( $hits ) . $opg->btn();
}

//.. _result_table
//- 結果テーブル
function _result_table( $hits ) {
	if ( count( (array)$hits ) == 0 ) return;

	$table = [];
	foreach ( (array)$hits as $a ) {
		$table[] = [
			_obj('taxo')->from_json( $a->key, $a->name, $a->json1 )->item() ,
			_cell( $a, 'emdb' ) ,
			_cell( $a, 'pdb' ) ,
			_cell( $a, 'sasbdb' ) ,
		];
	}
	$icon = _fa( 'sort-amount-desc' );
	return _table_toph([
		SORT == 'name'
			? $icon. _l( 'Name' )
			: _a( _get_query( [ 'order'=> 'name' ] ), _l( 'Name' ) )
		,
		SORT == 'emdb'
			? $icon. "EMDB"
			: _a( _get_query( [ 'order'=> 'emdb' ] ), 'EMDB' )
		,
		SORT == 'pdb'
			? $icon. "PDB"
			: _a( _get_query( [ 'order'=> 'pdb' ] ) , 'PDB' )
		,
		SORT == 'sasbdb'
			? $icon. "SASBDB"
			: _a( _get_query( [ 'order'=> 'sasbdb' ] ) , 'SASBDB' )
		,
	], $table );
}

function _cell( $obj, $db ) {
	return $obj->$db
		? _ab([ 'k'=> $obj->key, '#'=> 'h_'. $db ], $obj->$db )
		: '0'
	;
}


//. data 個別
if ( ! FLG_KEY_HIT) {
	$data_hit = $obj_sqlite->qobj([
		'select' => '*' ,
		'where'	 => 'name='. _quote( $name )
	])[0];
}
if ( $data_hit ) {
	//.. json extraction
	$ty = $en = $ja = $ic = $th = $id = $ho = $wi = '';
	extract( (array)json_decode( $data_hit->json1 ) );

	$jn = $aj = $ae = $on = '';
	extract( (array)json_decode( $data_hit->json2 ) );

//	_testinfo( json_decode( $data_hit->json1 ), $json1 );
//	_testinfo( json_decode( $data_hit->json2 ), $json2 );
//	_testinfo( $data_hit, 'data_hit' );

	//.. name
	$_out[ 'Name' ] = $name;
	if ( L_JA && $jn )
		$_out[ '和名'. _ref( 'dic' )] = _imp( $jn );

	if ( $on->n )
		$_out[ _l( 'Name' ). _ref('ncbi') ] = _imp2( $on->n );
	$name = $name ?: $on->n[0];

	//.. host
	if ( $ho ) {
		$o = [];
		foreach ( _uniqfilt( $ho ) as $n ) {
			$o[] = _obj('taxo')->item( $n );
		}
		$_out[ TERM_HOST ] = _imp2( $o );
	}

	//.. icon data
	if ( $ic ) {
		$icon_name = strtr( $ic, ['_'=>' '] );
		$icon_id = $icon_j = $icon_e = '';
		extract(
			(array)_json_load( _fn( 'taxo_icon_json' ) )[$icon_name] ,
			EXTR_PREFIX_ALL, 'icon' 
		);
		$_out[ _l( 'Image' ) . _ref( 'icon' ) ] = ''
			. _ab( 'taxo_icon_site', _img( '.ticon', [ 'taxo_icon', $ic ] ) )

			//- 別の種のアイコンだったら、リンク
			. ( !_same_str( $icon_name, $name ) ?
				_p( _kv([
					TERM_IMG_REL => _ab([ 's'=>$icon_name ], "<i>$icon_name</i>" )
				]))
			:'')
			//- 付随情報
			. ( $icon_e || $icon_j ?
				_p( _ej( $icon_e, _imp( $icon_j, $icon_e ) ) )
			:'')
		;
	}

//		$img . 
	//.. other names
	$d = [];
	foreach ([
//		'n'		=> 'scientific name' ,
		'c'		=> 'common name' ,
		'gc'	=> 'GenBank common name' ,
		's'		=> 'synonym' ,
		'gs'	=> 'GenBank synonym' ,
		'eq'	=> 'equivalent name' ,
		'm'		=> 'misspelling' ,
		'i'		=> 'includes' ,
		'x'		=> 'others' ,
	] as $s => $key ) {
		if ( ! $on->$s ) continue;
		$d[ _l( $key ) ] = _long( $on->$s, 2 );
	}
	if ( $d ) 
		$_out[ TERM_OTHER_NAMES. _ref( 'ncbi', 'on' ) ] =  _kv( $d );

	//.. names in strdb
	$str_names = [];
	if ( $id ) {
		foreach ( array_unique( $obj_sql_id->qcol([
			'select' => 'name' ,
			'where' => "id='$id'"
		])) as $n ) {
			if ( $n == $name ) continue;
			$str_names[] = $n;
		}
		$_out[ TERM_NAMES_STRDATA ] = _long( $str_names );
	}

	//.. tax-ID
	if ( $id ) {
		$_out[ 'NCBI Taxonomy ID' ] = _ab([ 'taxo_id', $id ], IC_L . $id );
	}

	//.. linage
	if ( $data_hit->line ) {
		$o = [];
		foreach ( array_filter( explode( '|', $data_hit->line ) ) as $l ) {
			$w = _obj('wikipe')->taxo($l);
			$o[] = _pop(
				L_JA ? ( $w->e2j() ?: _l( $l ) ) : $l ,
				_ul([
					L_JA ? $l : '' ,
					_a( "?l=$l", IC_SEARCH. _term_rep( TERM_TAXON_SEARCH, $l ) ) ,
					$w->show()
				])
			);
		}
		$_out[ _l( 'Lineage' ). _ref( 'ncbi', 'line' ) ] = implode( ' > ', $o );
	}

//.. wikipedia
	$wi2 = $wi ? preg_replace( '/^.+\//', '', $wi ) : '';
	$_out[ 'Wikipedia' ] =
		( $wi ? _div( '.left', _ab(
			_url( _ej( 'wikipe_img_en', 'wikipe_img_ja' ), $wi2 ) ,
			_img( _url( 'wikipe_img', $wi2 ) )
		)): '' )
		._obj('wikipe')->taxo( $id ?: $name )->show()
	;

	//.. ext. link
	$_out[ 'External link' ] = $ty == 'virus' && $id
		? _ab( 'https://www.genome.jp/virushostdb/'. $id, 'Virus-Host DB' )
		: ''
	;

	//.. ym annot
	$_out[ _l( 'Comment' ) ] = _imp2(
		_ej( $ae, $aj ?: $ae ),
		( file_exists( $fn = "img/tx_$ty.gif" )
			? _img( '.txtype_icon', $fn )
			: '' 
		)
		. _l( $ty )
		,
		( $thermo ? _img( 'img/tx_thermo.gif' ). _l( 'thermophilic' ): '' )
	);

	//.. ref
	if ( $refs ) {
		$il = _ab( 'taxo_icon_site2', TERM_TAXOICON_SITE ) 
		. ' licensed under '
		. _ej( 
			_ab( 'cc21en', 'CC Attribution2.1 Japan' ) ,
			_ab( 'cc21ja', 'CC表示2.1 日本' )
		);

		$rs = [
			'icon' => $il ,
			'dic' => _ab( 'lsdb_dic_site' ,
				'DBCLSメタ用語集-翻訳用テーブル-学名と和名の対応' ). ' '
				. _ab( 'lsdb_site', 'ライフサイエンス統合データベースプロジェクト' )
			,
			'ncbi' => _ab( 'ncbi_taxo_site', 'NCBI Taxonomy' ) ,
		];

		$a = [];
		foreach ( $refs as $r => $n )
			$a[] = _span( '.ref2', "*$n" ) . ': ' . $rs[ $r ];

		$_out[ TERM_REF ] = implode( BR, $a );
	}

	//.. 出力
	$_simple->hdiv( TERM_TAXO_INFO, _simple_table( $_out ) );
}

//. PDB/EMDB/SAS
if ( KEY ) foreach ([
	'emdb' => 'EMDB' ,
	'pdb'  => 'PDB' ,
	'sas'  => 'SASBDB' ,
] as $db => $DB ) {
	list( $items, $num ) = _str_ent( $db );
	$_simple->hdiv(
		"$DB ". _l( 'entries' ). _kakko( $num ) ,
		_div( "#$db", $items ) ,
		[ 'id' => strtolower( $DB ) ]
	);
}

function _str_ent( $db ) {
	global $obj_sql_str;
	$num = $obj_sql_str->cnt([
		'db='	. _quote( substr( $db, 0, 1 ) ) ,
		'taxo='	. _quote( KEY ),
	]);
	$ids = $num == 0 ? [] : $obj_sql_str->qcol([
		'select'	=> 'id' ,
		'order by'	=> 'id' ,
		'limit'		=> RANGE_ST ,
		'offset'	=> PAGE * RANGE_ST ,
	]);

	if ( $db != 'sas' ) foreach ( $ids as $k => $v ) {
		$ids[$k] = "$db-$v";
	}

	$opg = new cls_pager([
		'str'		=> '' ,
		'total'		=> $num ,
		'page'		=> PAGE ,
		'range'		=> RANGE_ST ,
		'objname'	=> $db ,
		'pvar'		=> [ 'ajax' => $db, 'k' => KEY ] ,
		'div'		=> "#$db"
	]);
	return [ $opg->msg(). _ent_catalog( $ids ). $opg->btn(), $num ];
}

//. func_homology
if ( KEY && TEST ) {
	$_simple->hdiv( 'Functions & homology of structure data',
		_func()
	);
}
function _func() {
	global $obj_sql_str;
	$num = $obj_sql_str->cnt( "taxo=". _quote( KEY ) );	
	if ( $num > 10000 )
		return ''; //'too many structure data' ;
	$o = $num == 0 ? [] : $obj_sql_str->qobj([
		'select'	=> 'db, id' 
	]);

	//.. 構造データごと
	$items = [];
	foreach ( $o as $c ) {
		foreach ( _obj('dbid')->strid2keys( 
			( $c->db == 'emdb' ? 'e' : '' ) . $c->id 
		) as $i ) {
			++ $items[ $i ];
		}
	}

	//.. dbidアイムごと、構造データ数で重み付け
	foreach ( $items as $key => $num ) {
		$count  = _ezsqlite([
			'dbname'	=> 'dbid' ,
			'where'		=> [ 'db_id', $key ] ,
			'select'	=> 'num' ,
		]);
		if ( $count == 0 ) {
			$items[ $key ] = 0;
		} else {
			$items[ $key ] = $items[ $key ] / pow( $count, 0.5 );
		}
	}
	arsort( $items );

	//.. 集計
	$ret = [];
	foreach ( array_keys( array_slice( array_filter( $items ), 0, 100 )) as $dbid ) {
		$ret[] = _obj('dbid')->pop( $dbid ); 
	}
	return _imp2( $ret );
}


//. output
//.. conf
$_simple->page_conf([
	'title' 	=> TERM_PAGE_TITLE ,
	'icon'		=> 'taxo' ,
	'openabout' => ( $name . $lin == '' ) ,
	'docid'		=> 'about_taxo' ,
	'newstag'	=> 'ym' ,
])

//.. css
->css( <<<EOD
.ticon { float: left; margin: 5px 10px 5px 0; }
.ref, .ref2 { color: red; font-weight: bold; }
.ref { vertical-align: super }
EOD
)->out();

//. function
//.. _ref: 引用
function _ref( $s, $sub = '-' ) {
	global $refs, $ref_num;
	if ( ! $refs[ $s ] ) {
		++ $ref_num;
		$refs[ $s ] = $ref_num;
	}
	return ' ' . _span( '.ref', '*' . $refs[ $s ] );
}
