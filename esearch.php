<?php
/*
get/post
	kw		キーワード
	mode	表示モード
*/

//. init
define( 'COLOR_MODE', 'emn' );
define( 'IMG_MODE', 'em' );
require( __DIR__. '/common-web.php' );

_add_lang( 'esearch' );
_add_fn(   'esearch' );
_define_term( <<<EOD
TERM_NUM_PER_PAGE
	Num. of entries / page
	エントリ数 / 1ページ
TERM_DL_TSV
	TSV format (Tab Separated Values, for Excel, etc.)
	TSV形式 (タブ区切り)
TERM_DL_CSV
	CSV format (Comma-Separated Values, for Excel, etc.)
	CSV形式 (コンマ区切り, Excelなどに)
TERM_DL_JSON
	JSON format
	JSON形式
TERM_FILE_FORMAT
	File format
	ファイル形式
TERM_MAX_NUM
	Max number of data
	データの最大数
TERM_O_FOR_ALL
	0 for all data
	全データの場合は0
TERM_WEEKS_BEFORE
	weeks ago
	週前
TERM_HAVE_EMPIAR
	having EMPIAR entry
	EMPIARに登録がある
EOD
);


define( 'TABLE_DATA', _json_load( _fn( 'table_data' ) ) );
$_ret = [];

define( 'NAME_LANG', L_EN ? 'ename' : 'jname' );

define( 'MET_NAME_LONG' , _subdata( 'trep', 'met_name_long' ) );
define( 'MET_NAME_SHORT', _subdata( 'trep', 'met_name_short' ) );

//. getpost
//- G_HOGE: get/postから来た値
$m = _getpost( 'mode' ) ?: 'list';
define( 'G_DISPMODE' , $m == 10 ? 'list' : $m ); //- 表示モード
define( 'G_KW'		, _getpost( 'kw' ) );
define( 'G_AUTH'	, _getpost( 'author' ) . _getpost( 'auth' ) );
define( 'G_AJAX'	, _getpost( 'ajax' ) );
define( 'G_METHOD'	, _getpost( 'method' ) ?: 'all' );
define( 'G_ACTAB'	, _getpost( 'actab' )  ?: '0' );
define( 'G_PAGE'	, _getpost( 'pagen' )  ?: '0' );
define( 'G_SORTBY'	, _getpost( 'sortby' ) ?: '-rdate' ); 
define( 'G_FORMAT'	, G_AJAX ? '' : _getpost( 'format' ) );
define( 'G_MAXNUM'	, _getpost( 'maxnum' ) ?: 100000000 );
define( 'G_FILT_EMP' , (bool)_getpost( 'filt_emp' ) );

//- テーブルモードのソート条件
define( 'G_TSORT'	, _getpost( 'tsort' )  ?: 'db_id' );
define( 'G_TREV'	, _getpost( 'trev' ) );

//- range
$r = _getpost( 'range' );
define( 'G_RANGE', $r < 10 || 500 < $r ? 50 : $r );

//- db
list( $db, $new ) = explode( '_', _getpost( 'db' ) );
define( 'G_DB', 
	[ 'p' => 'PDB', 'e' => 'EMDB' ][ $db ]
	?: strtoupper( $db )
	?: 'BOTH'
);

//- new
$new = _getpost( 'new' ) ?: $new;
define( 'G_NEW', [
	'newxml'	=> 'new' ,
    'newmap'	=> 'new' ,
    'udmap'		=> 'update' ,
    'new'		=> 'new' ,
    'ud'		=> 'update' ,
][ $new ] ?: $new ?: false );

define( 'G_WEEKS', _getpost( 'weeks' ) ?: 0 );


//.. テーブルモードのカラム
$a = [ 'db_id' ];
foreach ( array_keys( TABLE_DATA ) as $i ) {
	if ( _getpost( "c_$i" ) )
		$a[] = $i;
}
if ( G_FILT_EMP && ! in_array( 'empiar', $a ) )
	$a[] = 'empiar';


//- デフォルトの値
if ( count( $_GET + $_POST ) < 2 && $a == [ 'db_id' ] )
	$a = [ 'db_id', 'title', 'method', 'authors' ];
define( 'TABLE_COLUMNS', $a );

//.. method
$a =[];
foreach ( array_keys( MET_NAME_LONG ) as $n ) {
	if ( _getpost( "m_$n" ) )
		$a[] = $n;
}
define( 'METHODS_CHOSEN', $a );

//. ajax ID hit
if ( G_AJAX == 'h' )
	die( _id_hit() );
if ( G_AJAX == 'f' )
	die( _make_form() );


//. 検索クエリ作成
//.. ソート条件 リストモード
$s = ltrim( G_SORTBY, '-' );
$s = [
	'reso'	 =>	'resolution+0' ,
	'id'	 =>	'db_id' ,
	'ddate'	 =>	'submit' ,
	'rdate'	 =>	'release' ,
][ $s ] ?: $s;
$q_sortby = ( substr( G_SORTBY, 0, 1 ) == '-'
	? "$s DESC"
	: "($s is null), $s"
). ', sort_sub DESC, db_id';

//_die( $q_sortby );

//.. ソート条件 テーブルモード
if ( G_DISPMODE == 'table' ) {
	//- ソート条件
	$s = G_TSORT;
	if ( in_array( TABLE_DATA[ $s ][ 'mode' ], [ 'num', 'INTEGER', 'REAL' ] ) ) {
		//- 数字モード
		$q_sortby = G_TREV
			? "$s+0 DESC"
			: "($s is null), $s"
		;
	} else {
		//- 文字モード
		$q_sortby = G_TREV
			? "$s DESC"
			: "($s is null), $s"
		;
	}
}

//.. キーワード
$where = [];
$term = [];
foreach ( (array)_kwprep( G_KW ) as $word ) {
	list( $key, $val ) = explode( ':', $word );
	if ( is_array( TABLE_DATA[ $key ] ) ) {
		//- キーワード以外検索
		if ( $key == 'categ' ) {
			$where[] = _sql_eq( 'categ', explode( ',', $val ) ) ;
		} else {
			$where[] = strtolower( $val ) == 'n/a'
				? "$key is NULL"
				: ( TABLE_DATA[ $key ][ 'multi' ] 
					? _sql_like( $key, $val, '|' )
					: _sql_eq( $key, $val )
				)
			;
		}
		$term[ TABLE_DATA[ $key ][ NAME_LANG ] ][] = $val;
	} else {
		//- キーワード
		$where[] = _like( 'search_words', $word );
		$term[ 'keywords' ][] = $word;
	}
}

//.. authors
foreach ( (array)_kwprep( G_AUTH ) as $word ) {
	$where[] = _like( 'search_authors', $word );
	$term[ 'author' ][] = $word;
}

//.. method
$c = count( METHODS_CHOSEN );
if ( 0 < $c && $c < 6 ) {
	$where[] = _sql_eq( 'method', METHODS_CHOSEN );
	$t = [];
	foreach ( METHODS_CHOSEN as $met )
		$t[] = MET_NAME_SHORT[ $met ];
	$term[ 'method' ] = implode( '|', $t );
}

//.. dbの種類
if ( G_DB != 'BOTH' ) {
	$where[] = _sql_eq( 'database', G_DB );
	$term[ 'database' ] = G_DB;
}
if ( G_FILT_EMP ) {
	$where[] = 'empiar IS NOT NULL';
}


//.. 新規・更新エントリ
if ( G_NEW ) {
	$term[ 'Data entries' ] = _l( [
		'new'    => 'Latest only' ,
		'update' => 'Updated only'
	][ G_NEW ] );

	$date = _release_date( G_WEEKS );
	$where[] = G_NEW == 'new'
		? _sql_eq( 'release', $date )
		: 'release != '. _quote( $date ).  ' AND udate = ' . _quote( $date )
	;
}

//.. 検索実行
//- ヒット数
$sq = new cls_sqlite();
$num_hit = $sq->cnt( $where );

//- 実行
$result = $num_hit == 0 ? [] : $sq->qar([
	'select'	=> TABLE_COLUMNS ,
	'order by'	=> $q_sortby ,
	'limit'		=> G_FORMAT ? G_MAXNUM : G_RANGE ,
	'offset'	=> G_PAGE * G_RANGE
]);
unset( $sq );

//. 結果作成
//.. '|'区切りを配列化
foreach ( $result as $num => $entry ) {
	foreach ( $entry as $key => $val ) {
		if ( ! TABLE_DATA[ $key ][ 'multi' ] ) continue;
		$result[ $num ][ $key ] = explode( '|', trim( $val, '| ' ) );
	}
}

//.. ダウンロード
if ( G_FORMAT ) {
	if ( in_array( 'method', TABLE_COLUMNS ) ) {
		foreach ( $result as $num => $entry ) {
			$result[ $num ][ 'method' ] = MET_NAME_SHORT[ $entry[ 'method' ] ];
		}
	}
	//- json
	if ( G_FORMAT == 'json' )
		_download_text( 'json', 'emn_search_result', $result );

	//- header
	$data = $row = [];
	foreach ( TABLE_COLUMNS as $col )
		$row[] = TABLE_DATA[ $col ][ NAME_LANG ] ?: 'DB-ID';
	$data[] = $row;

	//- data
	foreach ( $result as $entry ) {
		$row = [];
		foreach ( TABLE_COLUMNS as $col ) {
			$v = $entry[ $col ];
			$row[] = is_array( $v ) ? _imp( $v ) : $v;
		}
		$data[] = $row;
	}
	_download_text( G_FORMAT, 'emn_search_result', $data );
}

	//.. アイコンモード、リストモード
if ( G_DISPMODE == 'icon' || G_DISPMODE == 'list' ) foreach ( $result as $entry ) {
	$o_id = new cls_entid( $entry[ 'db_id' ] );
	$data = [];
	foreach ( $entry as $key => $val ) {
		if ( $key == 'db_id' || $key == 'title' ) continue;

		$icon = '';
		if ( $key == 'method' ) {
			$val = MET_NAME_SHORT[ $val ];
		} else if ( $key == 'resolution' ) {
			$val = $val != '' ? "$val &Aring;" : '';
		} else if ( $key == 'authors' ) {
			foreach ( (array)$val as $num => $name )
				$val[ $num ] = _ab( "?author=$name", $name );
			$icon = _fa( 'user' );
		} else if ( $key == 'empiar' ) {
			$val = _empiar_link( $val );
		}
		$data[
			$icon ?: TABLE_DATA[ $key ][ NAME_LANG ] 
		] = is_array( $val ) ? _imp( $val ) : $val ;
	}
	$_out .= $o_id->ent_item( G_DISPMODE, [ 'data' => $data ]);

	//.. 表モード
} else foreach ( $result as $entry ) {
	$o_id = new cls_entid( $entry[ 'db_id' ] );
	$out = '';
	foreach ( TABLE_COLUMNS as $column ) {
		//- データ加工
		$cell = $entry[ $column ];
		if ( is_array( $cell ) )
			$cell = _imp( $cell );

		if ( $column == 'db_id' ) {
			$cell = _pop( $o_id->DID, $o_id->ent_item_list(), [ 'type' => 'span' ] );
		} else if ( $column == 'title' ) {
			$cell = $o_id->title();
		} else if ( $column == 'method' ) {
			$cell = MET_NAME_LONG[ $cell ];
		} else if ( $column == 'doi' ) {
			$cell = _ab( _url( 'doi', $cell ), $cell  );
		} else if ( $column == 'pmid' ) {
			$cell = _ab( _url( 'pubmed', $cell ), $cell  );
		} else if ( $column == 'empiar' ) {
			$cell = _empiar_link( $cell );
		}

		//- ソートカラムかどうか
		$out .= $column == G_TSORT
			? _e( 'td | .scol' ) . ( $cell ?: ' ' )
			: TD. $cell
		;
	}
	$_out .= TR. $out;
}

//.. 表モードのときのカラムヘッダ等付加
if ( G_DISPMODE == 'table' ) {
	$toprow = '';
	$t = G_TSORT. ( G_TREV ?: '0' );

	//- 各カラムヘッダ
	foreach ( TABLE_COLUMNS as $column ) {
		$toprow .= TH
			. _div( '.srtdiv', ''
				. _img( //- 上向きアイコン
					$t != $column. '1' ? "!_form.tsort('$column',1)" : '.shine' ,
					'img/arup.gif'
				)
				. BR
				. _img( //- 下向きアイコン
					$t != $column. '0' ? "!_form.tsort('$column',0)" : '.shine' ,
					'img/ardown.gif'
				)
			)
			. ( TABLE_DATA[ $column ][ NAME_LANG ] ?: 'DB-ID' )
		;
	}
	$_out = _t( 'table | #rtbl', TR_TOP. $toprow. $_out );
}


//.. pager付加
$opg = new cls_pager([
	'str'		=> $term ,
	'total'		=> $num_hit ,
	'page'		=> G_PAGE ,
	'range'		=> G_RANGE ,
	'func_name' => '_form.pagenum' ,
	'objname'	=> '',
]);
$_out = $opg . $_out . $opg->btn();

//. Ajax 結果ならここで終わり
if ( G_AJAX == 'l' )
	die( $_out );

//. ページ作成
_simple()
->page_conf([
	'title' 	=> _l( 'EMN search' ) ,
	'icon'		=> 'emn' ,
	'openabout'	=> false ,
	'js'		=> [ 'esearch' ] ,
	'docid' 	=> 'about_esearch' ,
	'newstag'	=> 'emn' ,
	'auth_autocomp' => true ,
])

//.. formエリア & resultエリア
->hdiv( 'Search query' ,
	_t( 'form | autocomplete:off | #form1', _make_form() )
	. _div( '#ent_info', '' )
	. _div( '#idhit', _id_hit() ) //- hit id
)
->hdiv( 'Search result', $_out, [ 'id' => 'result' ] )

//.. css
->css( <<<EOD
select {font-size:100%}
.opt_table th { width: 15%; }
.srtdiv { float: left; line-height: 0px }
.srtdiv img { margin: 2px 0; padding: 0; border: none; cursor:pointer; }
.opac, .srtdiv img:hover { opacity:0.6; filter:alpha(opacity=60); -ms-filter: "alpha( opacity=60 )"; }

.srtdiv img:active { margin: 4px -2px 0px 2px; }

.scol { background: #ffe8e8; }
.psel { background: #ffff80; }
EOD
)

//.. output
->out();

//. function form
function _make_form() {

	//.. method
	$met_items = [];
	foreach ( MET_NAME_LONG as $n => $d ) {
		if ( $n == 'e' ) continue;
		$met_items[] = _chkbox(
			$d, 
			".ckb_met| name:m_$n| value:1" ,
			in_array( "$n", METHODS_CHOSEN )
		);
	}

	//.. 検索条件
	$table_srch = [
		'Keywords' =>
			_inpbox( 'kw', G_KW, [ 'acomp' => 'em', 'idbox' => true ] )
		,
		'Database' =>
			_radiobtns( [ 'name' => 'db', 'on' => G_DB ], [
				'BOTH'	=> _l( 'EMDB & PDB' ),
				'EMDB'	=> 'EMDB' ,
				'PDB'	=> 'PDB' ,
			])
			. SEP
			. _chkbox( TERM_HAVE_EMPIAR, "name:filt_emp", G_FILT_EMP )
			. _div( '.right small', _doc_pop( 'emn_source' ) )
		,
		'Data entries' =>
			_radiobtns( [ 'name' => 'new', 'on' => G_NEW ], [
				'0'		 => _l( 'All' ) ,
				'new'	 => _l( 'Latest only' ) ,
				'update' => _l( 'Updated only' ) ,
			])
			. _span( '#weeks|'. ( G_NEW ? '' : '.hide' ) , ''
				. SEP
				. _e( 'input| #weeks| type:number| max:1000| min: 0| name:weeks'
					. '| value:'. G_WEEKS )
				. TERM_WEEKS_BEFORE
			)
			. _div( '.right small', _doc_pop( 'when_update' ) )
		,
		'Author' => _inpbox( 'auth', G_AUTH, [ 'acomp' => 'an_em' ] )
		,
		'Processing method' => 
			implode( ' ', $met_items )
			.' '. _btn( '!_form.allmet()', _l( 'Check all' ) )
	];

	//.. 表示条件
	$table_opt = [
		'Display mode' =>
			_radiobtns( [ 'name' => 'mode', 'on' => G_DISPMODE ], [
				'list'  => [ 'list-ul'  , 'list' ] ,
				'icon'  => [ 'th-large' , 'images only' ] ,
				'table' => [ 'table'    , 'table' ]
			])
		,
		'Sort by' => _selopt( 
			'#sortby| name:sortby'. ( G_DISPMODE == 'table' ? '|' .DISABLE : '' ) ,
			_subdata( 'trep', 'esearch_order' ) ,
			G_SORTBY
		) ,
		TERM_NUM_PER_PAGE =>
			_selopt( 'name:range', [
				'20'	=> '20' ,
				'50'	=> '50'	,
				'100'	=> '100' ,
				'200'	=> '200' ,
				'500'	=> '500'
			], G_RANGE )
	];

	$cat = [];
	//- カテゴリ毎に分類
	foreach ( TABLE_DATA as $n => $c ) {
		if ( ! $c[ 'page' ] ) continue;
		$cat[ $c[ 'categ' ] ][ $n ] = $c;
	}

	//- 表示項目
	foreach ( $cat as $c => $ar ) {
		$out = [];
		foreach ( $ar as $n => $a ) {
			$out[] = _chkbox(
				$a[ NAME_LANG ] , 
				"name:c_$n | value:1" ,
				in_array( "$c_$n", TABLE_COLUMNS )
			);
		}
		$table_opt[ _ic( $c ) . _l( $c ) ] = implode( ' ', $out );
	}

	//.. ダウンロード
	$ic = _fa( 'download' );
	$table_downl = [
		TERM_MAX_NUM => _input( 'number', 'name:maxnum', 100 ). TERM_O_FOR_ALL ,
		TERM_FILE_FORMAT => _ul([
			_btn( "!_form.downld('csv')" , $ic ). TERM_DL_CSV ,
			_btn( "!_form.downld('tsv')" , $ic ). TERM_DL_TSV ,
			_btn( "!_form.downld('json')", $ic ). TERM_DL_JSON ,
		]) ,
	];

	//.. まとめ
	return _simple_tabs([
		'active' => G_ACTAB == 0 ,
		'tab'	 => [ 'search', 'Search options' ] ,
		'div'	 => _table_2col( $table_srch, [ 'opt' => '.opt_table' ] ) ,
		'js'	 => '_form.tab(0);'
	], [
		'active' => G_ACTAB == 1 ,
		'tab'	 => [ 'eye', 'Display options' ] ,
		'div'	 => _table_2col( $table_opt, [ 'opt' => '.opt_table' ] ) ,
		'js'	 => '_form.tab(1);'
	], [
		'active' => G_ACTAB == 2 ,
		'tab'	 => [ 'download', 'Download' ] ,
		'div'	 => _table_2col( $table_downl, [ 'opt' => '.opt_table' ] ) , 
//		'js'	 => '_form.tab(2);'
	])
	. _input( 'submit', 'st: width:20em' ). ' '. _input( 'reset' )

	//- 隠し要素
	. _input( 'hidden', '#pagen| name:pagen', G_PAGE )
	. _input( 'hidden', '#actab| name:actab', G_ACTAB )
	. _input( 'hidden', '#tsort| name:tsort', G_TSORT )
	. _input( 'hidden', '#trev | name:trev ', G_TREV )
	. _input( 'hidden', '#format| name:format ', '' )

	//- ym search
	. _p(
		_btn( '!_ym_search()',
			_ic( 'miru' ). _l( 'Cross search by Yorodumi' ) )
		. _doc_pop( 'about_ysearch' )
	)
	;
}

//. function: _id_hit
function _id_hit() {
	$o_id = new cls_entid( G_KW );
	if ( ! $o_id->ex() || $o_id->db == 'chem' ) return;
	return _simple()->hdiv(
		'Found entry by ID search' ,
		$o_id->ent_item_list() ,
		[ 'type' => 'h2' ]
	);
}

//. function: _empiar_link
function _empiar_link( $in ) {
	$ret = [];
	foreach ( (array)explode( '|', $in ) as $e ) {
		$ret[] = _ab([ 'empiar_j', $e ], $e );
	}
	return _imp( $ret );
}
