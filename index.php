<?php 
//. init
define( 'COLOR_MODE', 'emn' );
define( 'IMG_MODE', 'em' );
define( 'TOP_PAGE', true );
if ( $_GET['phpinfo'] ) {
	echo phpinfo();
	die();
}

require( __DIR__. '/common-web.php' );
define( 'NAME_SARS_COV_2', 'Severe acute respiratory syndrome coronavirus 2' );
_add_lang( 'index' );

//.. define term
_define_term( <<<EOD
TERM_NO_DATA
	Temporary not available
	ただ今準備中です
TERM_RECENT_REL
	Recently released data
	最近公開されたデータ
TERM_NEW_EMDBXML
	New EMDB meta data
	新規EMDB付随情報
TERM_UPDATE_EMDB
	Updated EMDBs
	EMDB更新
TERM_UPDATE_PDB
	Updated PDBs
	PDB更新
TERM_OTHER_LATESTS
	Other latests
	その他の最新データ
TERM_RECENT_PAPERS
	Recent 3DEM papers
	最近の3次元電子顕微鏡の文献
TERM_ALL_PAPERS
	Show all 3DEM papers
	全文献を見る
TERM_SUB
	3D electron microscopy data navigator
	3次元電子顕微鏡データナビゲーター
TERM_BROWSE_DATA
	Browse 3DEM data
	3DEMデータを見る
TERM_KW_OR_ID
	Keywords / EMDB-ID / PDB-ID
	キーワード / EMDB-ID / PDB-ID
TERM_EMN_SEARCH
	Advanced search, table view, etc.
	詳細な検索、表形式による表示など
TERM_ADV_SEARCH
	Advanced search
	詳細な検索
TERM_NEW
	Latest entry
	最新エントリ
TERM_UPDATE
	Updated entry
	更新エントリ
TERM_OMO_SEARCH
	Omokage search - shape similarity search
	Omokage検索 - 形状が似ている構造データを探す
TERM_YORODUMI
	Yorodumi - integration of EMDB/PDB metadata & structure viewer
	万見 - EMDB/PDBの付随情報と構造ビューアを統合
TERM_EM_PAPERS
	3DEM papers
	3DEM文献
NO_NEW_DATA
	No new strucutre released in this week
	この週に新規公開された構造データはありませんでした
TERM_REL_DATE
	Release date
	公開日
TERM_IMG_UNDER_PREP
	Images and movies for some entries are currently under preparation
	いくつかのエントリの画像及び動画は現在準備中です
EOD
);

//.. getpost
define( 'G_DATE'	, _getpost( 'reldate' ) ?: _release_date() );
define( 'G_TAB'		, _getpost( 'tab' ) ?: 'All' );
define( 'G_PAGE'	, _getpost( 'page' ) ?: 0 );
define( 'G_AJAX'	, _getpost( 'ajax' ) );

//.. 表示最大数（自分用クッキー ）
if ( $n = _getpost( 'maxnum' ) ) {
	if ( $n == 50 ) {
		setcookie( "toppage-maxnum", "", time()-60);
	} else {
		setcookie( "toppage-maxnum", $n, time()+60*60*24*365 );
	}
}
define( 'MAX_NUM', $n ?: $_COOKIE[ 'toppage-maxnum' ] ?: 50 );

//.. カテゴリデータ ロード
define( 'CATEG_KEYS'	, _subdata( 'categ', 'keys' ) );
define( 'CATEG_NAMES'	, _subdata( 'categ', 'names' ) );
define( 'CATEG_CAPTION'	, _subdata( 'categ', _ej( 'caption-e', 'caption-j' ) ) );

//.. 登録数
$o_newent = new cls_sqlite('newent');
define( 'NUM_LIST', json_decode( $o_newent->qcol([
	'select' => 'num' ,
	'where' => _sql_eq( 'date', G_DATE )
])[0], true ) );

$n = [];
foreach ( $o_newent->qar([ 'select' => [ 'date', 'num' ] ]) as $a ) {
	$j = json_decode( $a['num'] );
	$n[ $a[ 'date' ] ] = [ $j->EMDB, $j->PDB ];
}
_simple()->jsvar([ 'ent_num' => $n ]);

//. ajax newdata
if ( G_AJAX == 'outer' )
	die( _newstr_outer() );

if ( G_AJAX == 'tab' )
	die( _newstr_inner( G_TAB ) );

//. browse data
_simple()->hdiv(
	TERM_BROWSE_DATA ,
	_ul([
		TERM_KW_OR_ID. ' '. _idinput( '', [
			'action'	=> 'quick.php' ,
			'acomp' 	=> 'em'
		]). _div( '#idimg', '' ) ,
		_page_link( 'e_search'	, TERM_EMN_SEARCH ) ,
		_page_link( 'omos'		, TERM_OMO_SEARCH ) ,
		_page_link( 'ym'		, TERM_YORODUMI ) ,
		implode( ' ', [
			_page_link( 'gallery'	, _l( 'Gallery' ) ) ,
	 		_page_link( 'e_stat'	, _l( 'Statistics' ) ) ,
			_page_link( 'e_pap'		, TERM_EM_PAPERS )  ,
			_page_link( 'taxo'		, _l( 'Species' ) ) ,
			_page_link( 'covid19' ) ,
		]) ,
		_mng_input() ,
	], 0 )
);

//. new data
//.. 日付メニュー
$menu = [];
foreach ( range( 0, 10 ) as $n ) {
	$d = _release_date( $n );
	$menu[ $d ] = _datestr( $d ). _kakko( $n == 0
		? _ej( 'latest', '最新' )
		: $n. _ej( ' weeks ago', '週間前' )
	);
}

//.. 出力
_simple()->hdiv(
	TERM_RECENT_REL , 
	TERM_REL_DATE. ': '
	. _selopt( '#reldate| name:reldate', $menu, G_DATE )
	. _kakko( ''
		. 'EMDB: '. _span( '#num_emdb', NUM_LIST[ 'EMDB' ] ). ', '
		. 'PDB: ' . _span( '#num_pdb' , NUM_LIST[ 'PDB' ] )
	)
	. ( file_exists( DN_DATA. '/img_under_prep' )
		? _p( '.red', '* '. TERM_IMG_UNDER_PREP ) : ''
	)
	. _div( '#catalog_outer', _newstr_outer() )

	//- テスト用、全部表示
	. _test( _p( '.red', '[test]'. ( MAX_NUM != 50
		? _a( '?maxnum=50', 'default display' )
		: _a( '?maxnum=500', 'Show all items' )
	)))
	. _doc_pop( 'when_update' )
);

//. Papers
$out = '';
foreach ( (array)_json_load( DN_DATA. '/emn/empapers.json' ) as $num => $d ) {
	$out .= _pap_item(
		$d ,
		[ 'hide' => $num > 1 ] ,
	);
}
_simple()->hdiv(
	_ic( 'article' ). TERM_RECENT_PAPERS ,
	( $out ?: MSG_NODATA )
	. _p( _ab([ 'pap', 'em' =>1 ], TERM_ALL_PAPERS ) )
);

//. output
_simple()
->time( 'all' )
//- google webmasters tool
->meta( 'google-site-verification', '73k4FEnUyMEJOFcr30o5izcD2su_NNxmEc9T59fanEg' )
->page_conf([
	'title' 	=> 'EM Navigator' ,
	'icon'		=> 'emn' ,
	'sub'		=> TERM_SUB,
	'openabout'	=> true ,
	'docid'		=> 'about_emn' ,
	'newstag'	=> 'emn' ,
])
//.. css
->css( <<<EOF
select {font-size:100%}
EOF
)

//.. js
->jsvar([ 'postdata' =>[
	'tab' => G_TAB ,
	'reldate' => G_DATE ,
]])
->js( 'index' )

//.. end
->out();

//. func
//.. _newstr_outer
function _newstr_outer() {
	$current_tab = NUM_LIST[ G_TAB ] ? G_TAB : 'All';

	$tabs = [];
	foreach ( array_merge(
		[ 'All', 'Covid-19' ],
		array_keys( CATEG_KEYS ) 
	) as $name ) {
		$num = NUM_LIST[ $name ];
		if ( ! $num ) continue; 

		//- 詳細検索の条件
		foreach ( range( 0, 10 ) as $w )
			if ( G_DATE == _release_date( $w ) ) break;
		$esearch = [ 'esearch', 'weeks' => $w, 'new' => 'new' ];
		if ( $name != 'All' ) {
			$esearch[ 'kw' ] = $name == 'Covid-19'
				? '"spec:'. NAME_SARS_COV_2. '"'
				: '"categ:'. CATEG_KEYS[ $name ]. '"'
			;
		}

		$tabs[] = [
			'active'	=> $name == $current_tab ,
			'tab'		=> _l( CATEG_NAMES[ $name ] ?: $name ). BR. _kakko( $num ) ,
			'js'		=> "_tabsel('". $name. "');" ,
			'div' 		=> ''
				. _p( _imp2(
					CATEG_CAPTION[ $name ] ,
					_ab( _local_link( $esearch ), _ic( 'search' ). TERM_ADV_SEARCH ) ,
					( $name == 'Covid-19' ? _doc_pop( 'about_covid19' ) :'' )
				))
				. _div( "#ct_$name", $name == $current_tab
					? _newstr_inner( $name )
					: ''
				)
		];
	}
	return _simple_tabs( $tabs );
}

//.. _newstr_inner
function _newstr_inner( $tab ) {
	global $o_newent;
	$o_pager = new cls_pager([
		'total'		=> NUM_LIST[ $tab ],
		'page'		=> G_PAGE ,
		'range'		=> MAX_NUM ,
		'func_name' => '_page' ,
		'objname'	=> "ct_$tab" , 
	]);
	return $o_pager. _ent_catalog(
		array_slice(
			explode( ',', $o_newent->qcol([
				'select' => [ 'All' => 'Allent', 'Covid-19' => 'Covid19' ][ $tab ] ?: $tab ,
				'where' => _sql_eq( 'date', G_DATE )
			])[0] ) , 
			G_PAGE * MAX_NUM ,
			MAX_NUM
		) ,
		[ 'mode' => 'auto' ]
	). $o_pager->btn();
}


