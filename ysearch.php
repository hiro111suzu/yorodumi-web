<?php

//. init
define( 'COLOR_MODE', 'ym' );
require( __DIR__. '/common-web.php' );

//.. lang / term
_add_lang('ysearch');

_define_term(<<<EOD
TERM_METHOD
	Method
	実験手法
TERM_EQUIP
	Equipment
	設備・装置
TERM_SOFT
	Software
	ソフトウェア
TERM_NAME
	Name
	名称
TERM_CATEG
	Category
	カテゴリ
TERM_FOR_AS
	for / as
	用途・名目
TERM_NUMSTR	
	Number of structure data
	構造データの数
TERM_HIT_ITEM
	Found item by ID search
	IDが一致した項目
TERM_TO_EMN_SEARCH
	Search only for 3DEM data by EM Navigator
	電子顕微鏡データのみをEM Navigatorで検索
TERM_NOT_CONSIDERED
	not considered
	検索条件として利用していません
EOD
//TERM_POW_BY
//	Powered by search engine of _1_
//	_1_の検索エンジンを利用しています
);

//.. get/post
define( 'RANGE'			, 50 );
define( 'G_KW'			, _getpost( 'kw' ) ); //- キーワード
define( 'G_AUTH'		, _getpost( 'auth' ) ); //- オーサー
define( 'G_DISPMODE'	, _getpost( 'mode' ) ?: '10' ); //- 表示モード
define( 'G_PAGE'		, _getpost( 'pagen' ) ?: 0 );
define( 'G_ACTTAB'		, _getpost( 'act_tab' ) ?: 'emdb' );
define( 'G_AJAX'		, _getpost( 'ajax' ) );
//define( 'G_DB'		, _getpost( 'db' ) );
define( 'G_CHEM_SORT'	, _getpost( 'c_sort' ) ?: 'pdbnum-' );
define( 'G_DBID_TYPE'	, _getpost( 'dbid_type' ) ?: 'a' );
define( 'G_MET_TYPE'	, _getpost( 'met_type' ) ?: 'a' );

//.. define
define( 'ICON_METHOD'	,  _fa('hand-paper-o') );
define( 'ICON_EQUIP' 	,  _fa('thermometer') );
define( 'ICON_SOFT'  	,  _fa('desktop') );

define( 'DB_NAMES', [
	'emdb'	=> 'EMDB' ,
	'pdb'	=> 'PDB' ,
	'sas'	=> 'SASBDB' ,
	'dbid'	=> _l( 'Function' ),
	'met'	=> _l( 'Methods'  ),
	'chem'	=> _l( 'Chem'     ),
	'taxo'	=> _l( 'Taxonomy' ),
	'pap'	=> _l( 'Papers'   ),
	'doc'	=> _l( 'Docs'     ),
]);

//. ajax
//.. form
if ( G_AJAX == 'form' ) {
	die( _make_ysform() );
}

//.. hit
if ( G_AJAX == 'hit' ) {
	die( _hit_item() );	
}

//.. res 結果全部 もう使っていない
if ( G_AJAX == 'res' ) {
	die( 'error' );
//	die( _make_result() );	
}

//.. cnt 数
if ( G_AJAX == 'cnt' ) {
	$data = [];
	foreach ( array_keys( DB_NAMES ) as $db )
		$data[ $db ] = (integer)( new cls_search( $db ) )->count();
	die( json_encode( $data, JSON_NUMERIC_CHECK ) );
}

//.. db tab タブ内のみ
if ( G_AJAX == 'page' ) {
	define( 'CSSID_PREFIX', G_ACTTAB ); 
	die( ( new cls_search( G_ACTTAB ) )->search() );
}

//. フルコンテンツ作成
//.. 検索結果エリア
$flg_all = 
	G_KW. G_AUTH == '' && 
	G_DBID_TYPE == 'a' &&
	G_MET_TYPE == 'a'
;

$db2cnt = $flg_all
	? _json_load( _fn( 'binfo' ) )[ 'datacount' ]
	: []
;

$pagenum_params = [];
$result_tabs = [ '#res' ];
foreach ( DB_NAMES as $db => $title ) {
	//- ヒット数ゲット
	$cnt = $flg_all ? (integer)$db2cnt[ $db ] : '--' ;

	//- タブ情報
	$result_tabs[] = [
		'id'	 => $db ,
		'active' => G_ACTTAB == $db ,
		'tab'	 => $title. BR
			. _kakko( _span( "#cnt_ent_$db| .cnt_ent", $cnt ) )
		,
		'div'	 => '' ,
		'js' 	 => "_ystab.set('$db')"
	];

	//- pagenum用データ
	$pagenum_params[ $db ] = [
		'url'	=> '?page=' , 
		'div'	=> "#tabdiv_res_$db" ,
		'pvar'	=> $_GET + [ 'ajax' => $db ] ,
	];
}

//.. 出力
$_simple->page_conf([
	'title'			=> _l( 'Yorodumi search' ) ,
	'icon'			=> 'search' ,
	'openabout'		=> false ,
	'js'			=> [ 'ysearch' ] ,
	'docid'			=> 'about_ysearch' ,
	'newstag'		=> 'ym' ,
	'auth_autocomp' => true ,
])

//.. form
->hdiv( 'Search query' ,
	_t( 'form | #form1', _make_ysform() )
	. _div( '#ent_info' )
	. _div( '#hit_item', LOADING )
//	. _div( '', LOADING_SMALL . 'test' )
)

//.. result
->hdiv( 'Search results', _simple_tabs( $result_tabs ), [ 'id' => 'result' ] )

//.. jsvar
->jsvar([
	'pagenum'	=> $pagenum_params ,
	'actab'		=> G_ACTTAB ,
	'get_cnt'	=> ! $flg_all ,
	'opt_tr'	=> [
		'emdb'  => [ 'disp', 'auth' ] ,
		'pdb'   => [ 'disp', 'auth' ] ,
		'sas'   => [ 'disp', 'auth' ] ,
		'chem'  => [ 'disp', 'c_sort' ] ,
		'dbid'	=> [ 'dbid_type' ] ,
		'met'	=> [ 'met_type' ] ,
		'pap'   => [ 'auth' ] ,
	],
	'tabs'		=> array_keys( DB_NAMES ),
	'loading_anim'	=> LOADING_SMALL ,
])

//.. css
->css( <<<EOD
.qtable th { width: 20% }
.ticon { float: left; margin: 5px 10px 5px 0; }

.met_yearly_s {
	border: 1px solid #ccc; padding: 0; margin: 0;
//	background: $col_bright;
//	vertical-align: bottom;
	white-space: nowrap;
//	box-shadow: 1px 1px 5px #777;
}
.met_ybar_s {
	box-sizing: border-box;
	border: 0px solid $col_dark;
	border-width: 0 0px 1px 0;
	display: inline-block; width: 4px; height: 20px;
	white-space:nowrap;
}
EOD
)

//.. end
->out();

//. function
//.. _listmode: リストモードにするかどうか
function _listmode( &$array ){
	$num = count( $array );
	return is_numeric( G_DISPMODE )
		? ( $num < G_DISPMODE ? 'list' : 'icon' )
		: G_DISPMODE
	;
}

//.. _make_ysform: フォーム作成
function _make_ysform() { return  ''
	. _t( 'table | .maintable qtable', ''
		//- keywords
		.TR.TH. IC_SEARCH. _l( 'Keywords' )
		.TD . _inpbox( 'kw', G_KW, [ 'acomp' => 'kw', 'idbox' => true ] )

		//- authors
		. _opt_tr( 'auth', 'user', 'Author' ,
			_inpbox( 'auth', G_AUTH, [ 'acomp' => 'an' ] )
		)

		//- display mode
		. _opt_tr( 'disp', 'eye', 'Display mode' ,
			_radiobtns( [ 'name' => 'mode', '#disp_mode', 'on' => G_DISPMODE ], [
				'10'	=> 'Auto' ,
				'list'  => [ 'list-ul'  , 'List' ] ,
				'icon'  => [ 'th-large' , 'Images only' ]
			])
		)

		//- sort
		. _opt_tr( 'c_sort', 'sort', 'Sort' ,
			_radiobtns( [ 'name' => 'c_sort', '#c_sort', 'on' => G_CHEM_SORT ], [
				'date-'		=> 'Newer' ,
				'date'		=> 'Older' ,
				'weight-'	=> 'Heavier' ,
				'weight'	=> 'Lighter',
				'pdbnum-'	=> 'More in PDB' ,
				'pdbnum'	=> 'Less in PDB' ,
			])
		)
		
		//- dbidtype
		. _opt_tr( 'dbid_type', 'dbid_type', 'Type',
			_radiobtns( [ 'name' => 'dbid_type', '#c_dbidtype', 'on' => G_DBID_TYPE ], [
				'a' => _l( 'All' ) ,
				'f' => _l( 'Function' )			. ' (GO, EC, etc.)',
				'h' => _l( 'Domain/homology' )	. ' (InterPro, CATH, etc)' ,
				'c' => _l( 'Component' ) 		. ' (UniProt, GenBank, etc)' ,
			])
		)
		//- met_type
		. _opt_tr( 'met_type', 'met_type', 'Type',
			_radiobtns( [ 'name' => 'met_type', '#c_mettype', 'on' => G_MET_TYPE ], [
				'a' => _l( 'All' ) ,
				'm' => ICON_METHOD. TERM_METHOD ,
				'e' => ICON_EQUIP . TERM_EQUIP ,
				's' => ICON_SOFT  . TERM_SOFT ,
			])
		)
	)
	. _input( 'hidden', '#act_tab| name:act_tab', G_ACTTAB )
	. _input( 'hidden', '#pagen  | name:pagen ', G_PAGE )
	. _input( 'submit', 'st: width:30%' )
	. BR
	. _btn( '!_emn_search()', _ic( 'emn' ). TERM_TO_EMN_SEARCH )
	. _doc_pop( 'about_esearch' )
;}

//.. _opt_tr
function _opt_tr( $name, $icon, $th, $td ) {
	return _e( "tr| .opt_tr| #tr_$name" )
		.TH. ( $icon ? _fa( $icon ) : '' ). _l( $th ) 
		.TD. $td
	;
}

//.. _hit_item: IDが直接ヒット
function _hit_item() {
	global $_simple;
	//... met
	$k = strtr( G_KW, [ '"' => '' ] );
	$s = substr( $k, 0, 4 );
	if ( $s == 'm:m:' || $s == 'm:e:' || $s == 'm:s:' ) {
		$k = substr( $k, 2 );
		if ( _ezsqlite([
			'dbname' => 'met' ,
			'where'	 => [ 'key', $k ] ,
			'select' => 'name'
		])) {
			return $_simple->hdiv(
				TERM_HIT_ITEM ,
				_met_data( $k ) ,
				[ 'type' => 'h2' ]
			);
		}
	}

	//... db-id
	if ( _instr( ':', G_KW ) ) {
		$kw = trim( G_KW, '"' );
		$title = $num = '';
		extract( _ezsqlite([ 
			'dbname' => 'dbid' ,
			'select' => [ 'title', 'num' ] ,
			'where'	 => [ 'db_id', $kw ] ,
		]));
		if ( ! $num ) return ;
		return $_simple->hdiv(
			TERM_HIT_ITEM ,
			_obj('dbid')->hit_item( $kw, $title ) ,
			[ 'type' => 'h2' ]
		);
	}

	//... structure-DB
	$o_id = new cls_entid( G_KW );
	if ( $o_id->ex() ) {
		return $_simple->hdiv(
			TERM_HIT_ITEM ,
			$o_id->ent_item_list() ,
			[ 'type' => 'h2' ]
		);
	}
}

/*
//.. _make_result: 検索結果作成 廃止
function _make_result() {
	global $actab, $pagenum_params;

	$tabs = [ '#res' ];
	foreach ( DB_NAMES as $db => $title ) {
		//- ヒット数ゲット
		$cnt = (integer)( new cls_search( $db ) )->count();

		//- 指定されていたらそのタブ
		$act = false;
		if ( G_ACTTAB == $db )
			$actab = $db;

		//- 指定なしなら、最初にヒットしたタブ
		if ( !G_ACTTAB && !$actab && $cnt )
			$actab = $db;

		//- タブ情報
		$tabs[] = [
			'id'	 => $db ,
			'active' => $actab == $db ,
			'tab'	 => $title .BR. "($cnt)" ,
			'div'	 => '' ,
			'js' 	 => "_ystab.set('$db')"
		];

		//- 検索しなかった分の情報も必要なので、ここで定義
		$pagenum_params[ $db ] = [
			'url'	=> '?page=' , 
			'div'	=> "#tabdiv_res_$db" ,
			'pvar'	=> $_GET + [ 'ajax' => $db ] ,
		];
	}

	//- アクティブダブ、全部ゼロなら最初のやつ
	if ( ! $actab ) {
		$actab = 'emdb';
		$tabs[1][ 'active' ] = true;
	}
	return _simple_tabs( $tabs );
}
*/

//. class: cls_search
//- setで DB指定して、countで数、search で検索結果を得る
class cls_search {
	private
		$where	= [] ,
		$sqo	= '' ,
		$dbname = '' ,
		$term	= [] ,
		$total ,
		$range = RANGE 
	;

	function __construct( $db ) {
		if ( $db )
			$this->set( $db );
		return $this;
	}

	//.. set
	function set( $db ) {
//		echo(_p( $db ) );

		$col_auth = [
			'pdb'	=> 'search_auth' ,
			'emdb'	=> 'search_authors' ,
			'sas'	=> 'sauth' ,
			'pap'	=> 'search_auth'  ,
		][ $db ];

		$col_kw = _search_cols( $db );

		//- キーワード クエリ
		foreach ( (array)_kwprep( G_KW ) as $w ) {
			if ( $db == 'met' )
				$w = strtr( $w, [ 'm:m:' => 'm:', 'm:s:' => 's:', 'm:e:' => 'e:' ] );
			$this->where[] = _like( $col_kw, $w );
			$this->term[ 'keywords' ][] = $w;
		}
		
		//- 人名クエリ
		if ( $col_auth ) {
			foreach ( (array)_kwprep( G_AUTH ) as $w ) {
				$this->where[] = _like( $col_auth, $w );
				$this->term[ 'author' ][] = $w;
			}
		} else {
			if ( ! G_AUTH ) {
				$this->term[ 'Author' ] = TERM_NOT_CONSIDERED;
			}
		}

		//- 追加
		if ( $db == 'emdb' )
			$this->where[] = "database is 'EMDB'";
		if ( $db == 'dbid' && G_DBID_TYPE != 'a' )
			$this->where[] = "type is '". G_DBID_TYPE. "'";
		if ( $db == 'met' && G_MET_TYPE != 'a' )
			$this->where[] = "key like '". G_MET_TYPE. ":%'";

		$this->dbname = $db;
		$this->sqo = new cls_sqlite( $db == 'emdb' ? 'main' : $db );
	}

	//.. count
	function count() {
		//- 条件なしなら全数を返す
		if ([
			'emdb'	=> G_KW. G_AUTH == '' ,
			'pdb'	=> G_KW. G_AUTH == '' ,
			'sas'	=> G_KW. G_AUTH == '' ,
			'dbid'	=> G_KW == '' && G_DBID_TYPE == 'a' ,
			'met'	=> G_KW == '' && G_MET_TYPE == 'a' ,
			'chem'	=> G_KW == '' ,
			'taxo'	=> G_KW == '' ,
			'pap'	=> G_KW. G_AUTH == '' ,
			'doc'	=> G_KW == '' ,
		][ $this->dbname ]) {
			$this->total = _json_cache( _fn( 'binfo' ) )->datacount->{ $this->dbname };
			return $this->total;
		}

		//- ヒット数
		$this->total = $this->sqo->cnt( $this->where );
		return $this->total;
	}

	//.. search
	function search() {

		//... 出力
		//- DB名のリンク
		$d = [
			'emdb'	=> 'emdb' ,
			'pdb'	=> 'pdb' ,
			'sas'	=> 'sasbdb' ,
			'dbid'	=> 'func_homology' ,
			'met'	=> 'met_data' ,
			'taxo'	=> 'about_taxo' ,
			'pap'	=> 'about_pap' ,
			'doc'	=> 'about_doc',
		][ $this->dbname ];
		if ( $d )
			$this->term = [ 'DB' => _doc_pop( $d, [ 'noicon' => true ] ) ] + $this->term;

		//- 検索実行
		$result = $this->{$this->dbname}();

		//- ページャオブジェ
		$opg = new cls_pager([
			'str'		=> $this->term ,
			'total'		=> $this->total ,
			'page'		=> G_PAGE ,
			'range'		=> $this->range ,
			'objname'	=> $this->dbname ,
			'func_name' => '_ystab.page' ,
		]);
		return ''
			. $opg->msg()
			. $result
			. $opg->btn()
		;
	}

	//.. PDB
	private function pdb() {
		$ans = $this->search_main([
			'sortby' => 'rdate DESC' ,
			'select' => 'id, title'
		]);
		$ret = '';
		$listmode = _listmode( $ans );
		foreach ( $ans as $a ){
			$ret .= ( new cls_entid() )
				->set_pdb( $a->id )
				->title( $a->title )
				->ent_item( $listmode )
			;
		}
		return $ret;
	}

	//.. EMDB
	private function emdb() {
		$ans = $this->search_main([
			'sortby' => 'release DESC' ,
			'select' => 'db_id, title'
		]);
		$ret = '';
		$listmode = _listmode( $ans );
		foreach ( $ans as $a ){
			$ret .= ( new cls_entid() )
				->set_emdb( $a->db_id )
				->title( $a->title )
				->ent_item( $listmode )
			;
		}
		return $ret;
	}

	//.. SASBDB
	private function sas() {
		$ans = $this->search_main([
			'dbname' => 'sas' ,
			'sortby' => 'id' ,
			'select' => 'id' ,
		]);
		$ret = '';
		$listmode = _listmode( $ans );
		foreach ( $ans as $a ) {
			$ret .= ( new cls_entid() )
				->set_sas( $a->id )
				->ent_item( $listmode )
			;
		}
		return $ret;
	}

	//.. dbid
	private function dbid() {
		$ans = $this->search_main([
			'dbname' => 'dbid' ,
			'sortby' => 'num DESC, db_id' ,
			'select' => '*'
		]);
		if ( ! $ans ) return;
		$table = [];
		foreach ( $ans as $a ) {
			$table[] = [
				_obj('dbid')->link( $a->db_id ) ,
				_obj('dbid')->title( $a->title, $a->db_id ) ,
				_a([ 'kw' => $a->db_id ], $a->num )
			];
		}
		return _table_toph(
			[ 'DB-ID', 'Title', TERM_NUMSTR ],
			$table
		);
	}

	//.. met
	private function met() {
		//... init
		$ans = $this->search_main([
			'dbname'	=> 'met' ,
			'sortby'	=> 'num DESC' ,
			'select'	=> '*'
		]);
		if ( !$ans ) return;

		$icon_dic = [
			'm' => _span( '?'.TERM_METHOD , ICON_METHOD ),
			'e' => _span( '?'.TERM_EQUIP  , ICON_EQUIP ) ,
			's' => _span( '?'.TERM_SOFT   , ICON_SOFT ) ,
		];
		_add_lang( 'met' );

		//... main loop
		$table = [];
		foreach ( $ans as $a ) {
			$key = $name = $for = $num = $yearly = $data = '';
			extract( (array)$a );

			//- for/as
			$for_as = [];
			foreach ( explode( '|', $for ) as $f ) {
				$for_as[] = _a(
					[ 'act_tab' => 'met', 'kw' => _quote( $f, 2 ) ],
					_l( $f )
				);
			}
			
			//- yearly stat
			$yearly = explode( '|', $yearly );
			$max = max( $yearly ) / 20; //- pixel単位
			$stat = '';
			foreach ( $yearly as $n ) {
				$stat .= _div( '.met_ybar_s| st:border-bottom-width: '
					. round( $n/$max + 1) .'px', ''
				);
			}

			//- output
			$table[] = [
				$icon_dic[ substr( $key, 0, 1 ) ] ,
				_pop_ajax(
					_l( explode( '|', $name, 2 )[0] )
					. _country_flag( $data ? json_decode( $data )->place : '' )
					,
					[ 'mode' => 'met2', 'key' => $key ]
				) ,
				_imp2( $for_as ) ,
				_a(
					[ 'kw' => _quote( 'm:'. $key, 2 ) ] ,
					_div( '.met_yearly_s', $stat )
					. number_format( $num )
				)
			];
		}

		//... return
		return _p( _imp2(
			ICON_METHOD . TERM_METHOD ,
			ICON_EQUIP  . TERM_EQUIP ,
			ICON_SOFT   . TERM_SOFT
		)). _table_toph(
			[ TERM_CATEG, TERM_NAME, TERM_FOR_AS, TERM_NUMSTR ] ,
			$table
		);
	}

	//.. chem
	private function chem() {
		_add_lang( 'bird' );
		$sort = trim( G_CHEM_SORT, '-' );
		$ans = $this->search_main([
			'dbname' => 'chem' ,
			'sortby' => $sort. ( G_CHEM_SORT == $sort ? '' : ' DESC' ) ,
			'select' => "id, name, json, inchikey, $sort"
		]);
		$sort = trim( G_CHEM_SORT, '-' );

		$ret = '';
		$listmode = _listmode( $ans );
		
		//... main
		foreach ( $ans as $a ) {
			$id = $name = $pdbnum = $weight = $nikkaji = $date = $json = null;
			extract( (array)$a );
			$syn = $form = $comment = $class = $bird = $img = $wikipe_annot = null;
			extract( (array)json_decode( $json, 1 ) );

			$flg_bird = substr( $id, 0, 4 ) == 'PRD_';

			//... bird情報のあるchemエントリ
			$bird_data = '';
			if ( $bird ) {
				list( $prd_id, $x, $prd_type ) = $bird;
				$o = [];
				foreach ( explode( ', ', $prd_type ) as $s )
					$o[] = _l( $s ). _obj('wikipe')->icon_pop( $s );
				$bird_data = _obj('dbid')->pop( 'BIRD', $prd_id, '.' ). _kakko( _imp( $o ) );
			}
			
			//... birdエントリ
			$bird_wikipe = '';
			if ( $flg_bird ) {
				$d = [];
				foreach ( explode( '|', $class ) as $s )
					$d[] = _l( $s ). _obj('wikipe')->icon_pop( $s );
				$bird_data = _imp2( $d );

				//- wikipe
				$syn = explode( '|', $syn );
				$bird_wikipe = _obj('wikipe')->pop_xx( $name );
				if ( !$bird_wikipe ) foreach ( $syn as $s ) {
					$bird_wikipe = _obj('wikipe')->pop_xx( $s );
					if ( $bird_wikipe ) break;
				}
				$syn = _imp2( array_slice( $syn, 0, 10 ) );
			}

			//... まとめ
			$ret .= ( $flg_bird
				? ( new cls_entid() )->set_bird( $id )
				: ( new cls_entid() )->set_chem( $id )
			)
				->title( _imp2([
					$name ,
					strtr( $syn, [ '|' => SEP ] ),
					$bird_wikipe ,
					$bird_data ,
					( $pdbnum ? "$pdbnum PDB entries": '' ) ,
					( $weight ? "$weight Da" : '' ) ,
					( $date ? _datestr( $date ) : '' ) ,
				]) )
				->ent_item( $listmode )
			;
		}
		return $ret;
	}

	//.. taxo
	private function taxo() {
		$ans = $this->search_main([
			'dbname' => 'taxo' ,
			'sortby' => 'emdb+pdb+sasbdb DESC' ,
			'select' => 'key, name, json1, emdb, pdb, sasbdb'
		]);
		if ( ! $ans ) return;

		$data = [];
		foreach ( $ans as $a ) {
			$data[] = [
				_obj('taxo')->from_json( $a->key, $a->name, $a->json1 )->item() ,
				_ab([ 'taxo', 'k'=>$a->key, '#'=>'h_emdb' ], $a->emdb ) ,
				_ab([ 'taxo', 'k'=>$a->key, '#'=>'h_pdb'  ], $a->pdb ) ,
				_ab([ 'taxo', 'k'=>$a->key, '#'=>'h_sas'  ], $a->sasbdb ) ,
			];
		}
		return _table_toph(
			[ 'Name', 'EMDB',  'PDB', 'SASBDB' ] ,
			$data
		);
	}

	//.. pap
	private function pap() {
		$this->range = 20;
		$ans = $this->search_main([
			'dbname'	=> 'pap' ,
			'select'	=> [ 'pmid', 'journal', 'data' ] , 
			'sortby'	=> 'score DESC' ,
			'range'		=> 20,
		]);
		$ret = '';
		foreach ( $ans as $a )
			$ret .= _pap_item( (array)$a );
		return $ret;
	}

	//.. doc
	private function doc() {
		$this->range = 20;
		$ans = $this->search_main([
			'dbname'	=> 'doc' ,
			'select'	=> [ 'id' ] ,
			'sortby'	=> 'type' ,
			'range'		=> 20,
		]);
		$ret = '';
		foreach ( $ans as $a )
			$ret .= _doc_hdiv( $a->id, [ 'hide' => $this->total  > 20 ] );
		return $ret. _a( 'doc.php', 'Yorodumi Doc' );
	}

	//.. search_main
	private function search_main( $a ) {
		if ( $this->count() == 0 )
			return [];

		$sortby	= 'id';
		$range	= RANGE;
		extract( $a ); //- $select, $sortby, $range;

		//- 本番検索
		return $this->sqo->qobj([
			'select'	=> $select,
			'where'		=> $this->where ,
			'order by'	=> $sortby ,
			'limit'		=> $range ,
			'offset'	=> G_PAGE * $range ,
		]);
	}
}
