<?php
//. pre-define
date_default_timezone_set("Asia/Tokyo"); 
mb_internal_encoding( 'UTF-8' );

spl_autoload_register( function( $class_name ) {
	require_once $class_name . '.php';
});

define( 'TIME_START', microtime( TRUE ) );
define( 'SPEED_TEST', true );

if ( ! defined( 'AJAX' ) )
	define( 'AJAX', false );
if ( ! defined( 'MODE_POPVW') )
	define( 'MODE_POPVW', false );

define( 'DN_DATA'	, 'data' ); //- realpathにする予定
define( 'URL_DATA'	, 'data' );

define( 'DN_EMNAVI'	, realpath( __DIR__ ) ); //- mngと共通で使えるように
define( 'DN_PREP'	, realpath( __DIR__ . '/../prepdata' ) );	//- TESTSVにしかない
define( 'DN_EDIT'	, realpath( __DIR__ . '/../edit' ) );		//- TESTSVにしかない
define( 'DN_REPORT'	, realpath( __DIR__ . '/../prepdata/report' ) );	//- TESTSVにしかない
define( 'DN_FDATA'	, realpath( __DIR__ . '/../fdata' ) );		//- TESTSVにしかない

define( 'TEST', $_COOKIE[ 'testhoge' ] );

//.. lang
/*
優先順位
get > cookie > browser

browserとgetが同じで、クッキーがあったら削除
browserとgetが違うなら、クッキー書き込み

*/
$lang = $_COOKIE[ 'lang' ];
$ln_get = $_GET[ 'lang' ] . $_POST[ 'lang' ];
$ln_browser = strtolower( substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2 ) );

if ( $ln_get == 'en' || $ln_get == 'ja' )
	$lang = $ln_get;

if ( $lang == '' )
	$lang = $ln_browser == 'ja' ? 'ja' : 'en' ;

if ( $ln_get == $ln_browser ) {
	if ( $_COOKIE[ 'lang' ] ) 
		setcookie( "lang", '' );
} else {
	setcookie( "lang", $lang, time()+60*60*24*365 );
}

define( 'LANG', $lang );
define( 'L_JA', $lang == 'ja' );
define( 'L_EN', $lang != 'ja' );

//. include
require( __DIR__. '/common-mng-web.php' );
require( __DIR__. '/common-func.php' );

//. 攻撃対策
//.. baidu対策
//- baiduには、5/10の確率で503を返す
//set_time_limit( 1 );

//- robokill なしにしてみるテスト
/*
define( ROBO, $r );

_robokill( 2 ); //- ロボなら2割503
*/

//.. GET/POST
foreach ( $_GET + $_POST as $s ) {
	if ( _instr( '<script', $s ) || _instr( '<iframe', $s ) )
		die();
}

//. misc define

define( 'DN_KF1BU'	, '/home/archive/ftp/pdbj/pub/pdb/data/biounit/coordinates/all/' ); //- bio-unit
//.. term
_define_term(<<<EOD
TERM_POP_VIEWER
	Click this button to start structure viewer
	クリックすると構造ビューアが起動します
TERM_STR_VIEWER
	Structure viewer
	構造ビューア
TERM_WIKIPE
	Wikipedia
	ウィキペディア
TERM_MORE_LI
	Show more _1_ items
	残り_1_件を表示
TERM_LESS_LI
	Show less
	表示を減らす
TERM_RAND_SEL_ENT
	Entries selected randomly
	ランダム選択エントリ
TERM_DOC_NOT_FOUND
	Document not found
	文書が見つかりません

TERM_ICONCAP_NOMAP
	EMDB Unreleased entry
	EMDB 未公開エントリ
TERM_ICONCAP_EMDB_NOIMG
	EMDB entry, No image
	EMDBエントリ 画像なし
TERM_ICONCAP_PDB_UNREL
	PDB Unreleased entry
	PDB 未公開エントリ
TERM_ICONCAP_SASBDB_NOIMG
	SASBDB entry, No image
	SASBDBエントリ 画像なし
TERM_ICONCAP_CHEM_NOIMG
	ChemComp, No image
	化合物 画像なし
TERM_ICONCAP_NOIMG
	No image
	画像なし
TERM_ABOUT_PRD
	about BIRD dictionary
	BIRD辞書について
DOC_RELATED
	<b>Related info.</b>: 
	<b>関連情報</b>: 
DOC_LINK
	<b>External links</b>: 
	<b>外部リンク</b>: 
EOD
);

//.. misc
define( 'BTN_ACTIVE', 'btn_active | disabled' );

define( 'LOADING', _span( '.loadingbar', _img( 'loading.g' ) ) );
define( 'LOADING_SMALL', _div( '.loading_small', _img( 'loading.g' ) ) );
define( 'LOADINGT', _p( LOADING . _ej( 'Loading...', '読み込み中...' ) ) );
define( 'URL_OMOAJAX', 'omo-ajax.php' );

//- hdiv lev2 の一括表示
define( 'BTN_HDIV2_ALL',
	 _btn( '!_hdiv.all2(this)', _ej( 'Show/hide all', 'すべて表示・隠す' ) )
);

//define( 'IC_DB', _fa( 'database' ) );
define( 'IC_SEARCH', _fa( 'search' ) );
define( 'IC_KEY', _fa( 'key' ) );

define( 'TEST_MARK', _span( '.bld red', ' (^o^)' ) );

define( 'LABEL_YM_ANNOT',
	_doc_pop( 'ym_annot', ['label' => _span( '.annot', '*YM' )] )
);

//.. misc html要素

$_ = '&nbsp;';

define( 'SEP', '<span class="sep"> / </span>' ); //- セパレータ

define( 'CHECK'		, 'checked="checked"' );
define( 'DISABLE'	, 'disabled="disabled"' );
define( 'BR'		, '<br>' );
define( 'TR'		, '<tr>' );
define( 'TR_TOP'	, '<tr class="toprow">' );
define( 'TD'		, '<td>' );
define( 'TH'		, '<th>' );
define( 'LI'		, '<li>' );

//.. ホスト名は廃止、テストサーバーかどうかのみ
define( 'TESTSV', is_dir( '../prepdata' )  );
define( 'URL_PDBJ', _instr( 'pdbj.org', $_SERVER[ 'SERVER_NAME' ] ) ? ".." : '//pdbj.org' );

//.. jmol
define( 'JMOLPATH', TESTSV
	? end( glob( '_jmol/jmol*' ) ) . '/jsmol'
	: '../jmol/jsmol'
);

//.. icon

//- icon
define( 'IC_L'		, _ic() );
define( 'IC_DL'		, _ic( 'download' ) );
define( 'IC_OPEN'	, _ic( 'open' ) );
define( 'IC_YM'		, _ic( 'miru' ) );
define( 'IC_HELP'	, _ic( 'help' ) );
define( 'IC_VIEW'	, _ic( 'view' ) );
define( 'IC_WIKIPE'	, _ic( 'wikipe' ) );
define( 'IC_PLUS'	, _fa( 'plus-square', 'large' ) );

//. サブデータ
if ( TEST ) {
	define( 'SUBDATA_TSV' , array_merge(
		[ 'e2j'  => _tsv_load2( 'e2j.tsv' ) ],
		[ 'trep' => _load_trep_tsv()[ _ej( 'en', 'ja' ) ] ] ,
		_tsv_load3( 'subdata.tsv' )
	) );
}
$_e2j_dic = [];
_add_lang( 'common' );
_add_fn( 'common' );
_add_url( 'common' );

define( 'MOV_TASK_INFO', 
	TEST ? _json_load( DN_PREP. '/mov_task_info.json' ) : []
);

//. jsvar 
if ( ! AJAX ) _simple()->jsvar([
	'loading' => LOADINGT ,
	'loadingerror'	=> _span( '.red', _ej( ' error?', ' エラー?' ) ) ,
	'vwurl'	=> [
		'molmil'	=> 'pop_molmil.php?id=' ,
		'jmol'		=> 'pop_jmol.php?id=' ,
		'mov'		=> 'pop_mov.php?id=' ,
		'sview'		=> 'pop_sview.php?id=' ,
	] ,
	'movidx_bar' => _t( 'li | .clearfix hide',
		_btn( '.closebtn| !_pmov.win(0,this)', 'X' )
		. _span( '!_pmov.win(1,this)', 
			_img( '.extcol_listitem_img', 'data/__imgurl__ ' )
			. '__str__'
		)
	) ,
	'vwidx_bar' => _t( 'li | .clearfix hide',
		_btn( '.closebtn| !_vw.win(0,this)', 'X' )
		. _span( '!_vw.win(1,this)',
			_img( '.extcol_listitem_img', 'data/__imgurl__ ' )
			. '__str__'
		)
	) ,
	'imgurl' => [
		'mov' => [
			'e' => 'emdb/media/__id__/snapss__num__.jpg',
			'p' => 'pdb/media/__id__/snapss__num__.jpg',
		],
		'vw' => [
			'e' => 'emdb/media/__id__/snapss2.jpg',
			'p' => 'pdb/img/__id__.jpg',
			's' => 'sas/img/__id__.jpg',
			'c' => 'chem/img/__id__.gif',
			'b' => 'bird/img/__id__.gif',
		]
	]
]);

//. css
//.. simple fw
if ( !defined( 'COLOR_MODE' ) )
	define( 'COLOR_MODE', '' );

list( $col_bright, $col_medium, $col_dark ) = 
	COLOR_MODE == 'ym'  ? [ '#dee'   , '#8bb', '#377' ] : (
	COLOR_MODE == 'emn' ? [ '#d8e8d8', '#9c9', '#585' ] : (
	COLOR_MODE == 'mng' ? [ '#e8d8d8', '#c99', '#855' ] : (
	[ '#eef', '#aad', '#66a' ]
)));

$col_red	= '#800';
$bg_dark   = "background: $col_dark; color: white";
$bg_blight = "background: $col_bright";
$bg_medium = "background: $col_medium";

//- font-size
$fsize = [ 'x-small', 'small', 'medium', 'large', 'x-large' ][ $_COOKIE[ 'vfsize' ] ?: 2 ];

//- ここから
if ( ! AJAX ) _simple()->css( <<<EOD

//- IEが対応していないので使わない
:root {
	--col-dark: $col_dark;
	--col-medium: $col_medium;
	--col-bright: $col_bright;
}

html, body { height: 100%; }
button { font-size: medium }
button img, button { vertical-align: middle }
button, a, select, label, .lk, .clickable {cursor: pointer}

.clickable:hover { color: blue; }

li { margin: 0.3em 0 0.3em 1em; padding: 0 }
p { margin: 0.3em 0  }
ul { margin: 0; padding: 0;}

.hide { display: none; }
.nw { white-space: nowrap; }
.left { float:left }
.right { float:right }
.clboth { clear:both }
.red { color:red }
.blue { color:blue }
.gray{ color: #bbb }
.green { color:green }
.white { color:white }
.dark { color: var(--col-dark) }
.bld { font-weight:bold }
.shine { position: relative; z-index: 100; box-shadow: 0 0 1em 0em #ff0; }
.shine:hover { box-shadow: 0 0 1em 0.5em #ff0; }
.lkicon { padding: 0 2px; margin: 0; border: none; vertical-align: middle; }
.small { font-size: small }
.smaller { font-size: smaller }
.large { font-size: large }
// .shadow { box-shadow: 0 0 10px #000; }

//.. title bar
.ttcol1 {color: #bcf} .ttcol2 {color: #faa }

//.. form
input { max-width: 100% !important; }

input[type=radio]:checked, input[type=checkbox]:checked,
input[type=radio]:checked + label, input[type=checkbox]:checked + label {
	background: #ff9; box-shadow: 0 0 1.5em #ff0;
}
.inpbox { width: 100%; font-size: larger;}

//- float解除用
.left:after, .clearfix:after{
    content: "";
    clear: both;
    display: block;
}

.doc_ul { margin: 0.3em 0.1em 1em 1em; }
pre { border: 1px solid gray; padding: 0.5em }

//.. general
html { font-size: $fsize }
body { background: white; margin: 0; padding: 0; border: none; }

#mainbox, #maininner {
	min-width: 350px;
	border: none; padding: 0 ; margin 0;
}
#maininner { padding: 0.2em 0.3em }

//.. general tabale
table { border-collapse: collapse; border: 1px solid $col_medium;}
	// max-width: 100% !important; }
td, th { padding: 0.1em 0.5em; border: 1px solid $col_medium;  }

.maintable { width: 100%; }
.maintable > tbody > tr > th { width: 15%; }
.maintable > tbody > tr > td { word-wrap: break-word }
//.maintable > tbody > tr > td { word-wrap: break-word; width: 85%; }

th { text-align:right; $bg_blight; }
.toprow th { text-align:center; width: auto; }
.numtable td{ text-align:right }

img { vertical-align: middle; }

//.. general item
button, .submitbtn {
	padding: 0.1em 1em; $bg_blight;
	border: 2px solid $col_medium;
	border-radius: 3px;
	font-size: inherit;
	box-shadow: 0 0 5px rgba(0,0,0,0.3);
}
.minibtn { padding: 0.1em 0.3em; }
button:hover, .submitbtn:hover {
	box-shadow: 0 0 10px rgba(255,255,0,0.5);
}

button:active, .submitbtn:active {
	box-shadow: 0 0 2px rgba(0,0,0,0.7);
}

.submitbtn {
	padding: 0.1em 2em; font-size: larger; font-weight: bold;
	$bg_dark;
}
button:disabled { opacity: 0.5; cursor: default }
.btn_active {
	font-weight: bold; color: $col_red; cursor: default;
}
//- loadingbar
.loadingbar { vertical-align: middle }
.loading_small { display: inline-block; vertical-align: middle; width: 1.5em; overflow: hidden }
.btn_small { padding: 0.1em }

//.. hdiv
h1, h2, h3 { 
	font-size: larger; 
	font-weight:bold; 
	clear: both;
	padding: 0; margin: 3px 0 0;
	border-style: solid; border-width: 2px; border-color: var(--col-dark);
	$bg_blight;
//	height: 1.5em;
//	height: 100%;
	overflow: hidden;
	text-overflow: ellipsis;
	cursor: pointer;
}
h1:hover, h2:hover, h3:hover {
	box-shadow: 0 5px 8px 2px #ff9;
}
h1 p, h2 p, h3 p {
	border: none; padding: 0; margin: 0;
}

h2, h3 { font-size: inherit; border-width: 1px; }

.h_addstr { font-weight: normal;}

.oc_btn {
	$bg_dark;
	float: left; width: 2.5em;
	text-align: center;
	margin: 0 0.5em 0 0; padding: 0;
	font-size: larger;
	font-weight: bolder;
//	height: 100%;
}

.oc_div {
////	margin: 0.2em 0 0.8em 1em;
//	padding: 0.2em 0 0.8em 0.9em;
	margin: 0em 0 0.4em 0em;
	padding: 0.2em 0 0.4em 1em;
	border-left: 2px solid $col_medium;
	word-wrap: break-word;
//	max-width: 100% !important; 
}
.h_sub { font-size: inherit; border:none; border-top: 1px solid $col_medium; padding: 1px 0.2em;
	background: inherit;
	cursor: inherit; }

//- separator
.sep { color: $col_medium }

//- ID input
#id_form { display: inline }
input { font-size: larger; }

//- top / bottom
#simple_top, #simple_bottom, #simple_bottom span {
	$bg_dark; color: white;
	padding: 0.5em 0.5em; margin: 0;
}

//.. simpletop/ bottom
#simple_top a, #simple_bottom a {
	text-decoration: none; color: white; }

#simple_top_title {
	display:block; float: left;
	font-size: x-large; font-weight: bold; 
	padding: 0.5em 0.5em 0.5em 1em;
	text-shadow: 1.5px 1.5px 2px rgba(0,0,0,0.75), 0 0 10px #ff9;
}
#simple_top_title:hover { 
	text-shadow: 1.5px 1.5px 2px rgba(0,0,0,0.8),0 0 5px #ff9, 0 0 15px #ff9;
}

#simple_top_title img { margin-right: 0.3em; vertical-align: middle; }
#simple_top_sub { font-size:small;
	margin: 1.3em 1em 1em 1em;
	text-shadow: 1.5px 1.5px 2px rgba(0,0,0,0.7), 0 0 10px #ff9;
}

#top_opt { float: right; font-size: small; text-align: right;
	margin: 0.1em; }

#simple_bottom {
	font-size: smaller; text-align: center;
}

@media screen and ( max-width:640px ) { 
	#simple_top_sub { clear: both; }
	#simple_top_title { padding: 0 }
	.wide_only {display: none}
}

//- simple_border div
.simple_border { border: 2px solid $col_medium; padding: 0.5em; margin: 0.2em 0.5em; }

//- メニューポップボタン
.btn_menu_fixed {
	position: fixed; top: 0.5em; right: 0.5em; opacity: 1; z-index: 1000;
}

//.. tab
.tabp { margin-bottom: 0; white-space: nowrap; width: auto; }
.tabbtn { margin: 0 2px -2px 2px; padding: 0.1em 0.5em; vertical-align: bottom;
	border-radius: 8px 8px 0 0; position: relative;
	background: $col_bright; border: 2px solid $col_medium; 
	box-shadow: none;
}
.tabbtn:hover { padding-top: 0.3em; z-index: 200; overflow: visible !important }
.tabbtn:disabled { padding-top: 0.5em; 
	margin-bottom: -4px;
	border-bottom-color: white; border-bottom-width: 4px;
	opacity: 1; font-weight: bold; color: $col_red;
	background: white; z-index: 100}
.tabdiv { border: 2px solid $col_medium; padding: 0.5em; margin: 0 }

//.. エントリカタログアイコン
//- マウスオーバーで大きくなる
.enticon { width:100px; height:100px; margin: 1px; padding: none;
	position: relative; z-index: 100; }
.enticon:hover { width:110px; height:110px; margin: -4px; box-shadow: 1px 1px 10px #777; 
	z-index: 200;}
.enticon:active { margin: -2px -6px -6px -2px; }
.enticon_cr { opacity: 0.4 }

//.. キャプション付き
.enticon_cap { width: 100px, height: 100px; position: relative; z-index: 0; display: inline-block; overflow: hidden; }
.enticon_cap img { width: 100%; height: 100% }
.enticon_cap p {
	font-weight: bold;
	position: absolute; z-index: 2;  top: 0; left: 0;
	text-shadow: 1px 1px 2px white, 1px -1px 2px white, -1px -1px 2px white, -1px -1px 2px white;
//	background: rgba( 255, 255, 255, 0.4 )
}
.enticon_cap_add {
//	font-weight: normal;
	font-size: smaller;
}

//.. ポップアップ
.pubox { display: none;
	margin: 0; padding: 5px; border: 1px solid $col_dark; max-width: 30em;
	background: $col_bright; box-shadow: 1px 1px 10px #777;
}
._pu_act {
	box-shadow: 1px 1px 10px #777;
}

//.. クリックポップアップ
.poptrg {
	color: #033; text-decoration: underline; cursor: pointer;
}
.poptrg_act { opacity: 0.5; }
img.poptrg_act { box-shadow: 1px 1px 10px #777; }
.pophide {
	color:white; 
	background: $col_red;

	margin: -3px -3px inherit inherit;
	font-weight: bold;
	border: none; 
	float: right;
}

.popbox {  
	display: none;
	position: absolute;
	margin: 0; padding: 5px; border: 1px solid $col_dark; max-width: 30em;
	background: white;
	box-shadow: 1px 1px 10px #777;
	z-index: 1000;
	max-height: 80vh;
}
.pop_inner {
	font-size: smaller; overflow:auto; width:100%;;max-height:20em
}

//.. 閉じるボタン
.closebtn {
	color:white; 
	background: $col_red;
	font-weight: bold;
	border: none; 
	float: right;
}

//.. sizebox
.sizebtn { height: 1.6em; padding: 0 0.5em; vertical-align: middle; } 
.sizebox_ll { width: 1.1em; height: 1.1em }
.sizebox_l  { width: 0.9em; height: 0.9em }
.sizebox_m  { width: 0.7em; height: 0.7em }
.sizebox_s  { width: 0.5em; height: 0.5em }
.sizebox_ss { width: 0.3em; height: 0.3em }
.sizebtn div { vertical-align: middle; display:table-cell; background: $col_dark }

//- リスト表示の区切り
.topline { border-top: 1px solid $col_medium;}

//.. ext_column
#ext_column {
	position: fixed; z-index: 500; top: 0; right: 0;
	max-width: 90%;
	margin: 0; padding: 0; 
	font-size: smaller;
	height: auto;  overflow-y: auto;
	overflow-x:hidden;
}

#menubox {
	width: 250px; 
}

.extcol_item_outer{
	right:0;
	$bg_dark; margin: 0 0 5px 0; padding: 2px 7px 7px 7px; 
	position: relative;
	border: none;
}

.extcol_item_inner {
	background: white;
	color: black;
}
.extcol_listitem_img {
	width: 75px; height: 75px
}


//.. パンくず
.pankuzu ul { margin-left: 1em }
.pankuzu li { list-style:none; }
.pankuzu li:before { content:"┗"; }

//.. 線なし表
.noborder, .noborder td, .noborder tr { border: none; margin 0; padding: 0;}

//.. Jmol
#cmdhist {
	font-size: x-small;
	height: 10em;
	overflow: scroll;
}

//- アクティブなボタン
// .act, .csel { background: yellow; padding: 2px 8px; }
.act, .csel { background: #ff9; box-shadow: 0 0 10px #ff0; }
.cselx { background: #aaa; padding: 2px 8px; } //- 選択失敗
.picked { background: #ffbbff; padding: 2px 8px; }

//.. doc
.docimg { box-shadow: 0 0 5px rgba(0,0,0,0.8 ); }

//.. dbid
.dbid_ec {color: #911; }
.dbid_go {color: #731; }
.dbid_rt {color: #551; }

.dbid_pf {color: #371; }
.dbid_ct {color: #281; }
.dbid_in {color: #191; }
.dbid_pr {color: #173; }

.dbid_un {color: #119; }
.dbid_gb {color: #218; }
.dbid_bd {color: #416; }
.dbid_chem {color: #416 }
.dbid_poly {color: #317 }

//.. met
.met_yearly, .met_yearly tr, .met_yearly td {
	font-size: small;
	border: none; padding: 0; margin: 0;
	vertical-align: bottom;
}
.met_yearly td { border: 1px solid #ccc; white-space: nowrap; }
.met_ybar {
	box-sizing: border-box;
	border: 0px solid $col_dark;
	border-width: 0 0px 1px 0;
	display: inline-block; width: 10px; height: 50px;
	white-space:nowrap;
}

.met_ybar_last {
	border-color: gray;
}
.met_ybar:hover { border-color: #f55; background: #ffa; }

.momimg_outer {
	width: 105px;
	height: 100px;
	float: left;
	text-align: center;
}
//.. misc 
//- font awesome
.fa { font-size: 1.3em; margin: 0 0.2em; }

//- Yorodumi annotation
.annot {color: white; font-size: small; background: $col_dark; margin: 0 0.2em; padding: 0 0.3em;}

//- txtype_icon
.txtype_icon { width: 24px; height: 24px; }
.txwikipe_icon { height: 24px; }

EOD
);
//.fa-white { font-size: larger; color: white; }
//.fa { font-size: larger; color: $col_dark; margin: 0 0.2em; }

//. その他、共通部
if ( ! AJAX ) {
	if ( ! MODE_POPVW ) _simple()->meta( 'viewport', [
		'width=device-width' ,
		'initial-scale=1.0' ,
		'user-scalable=yes' ,
	]);
	_simple()->meta( 'theme-color', $col_dark );
}
