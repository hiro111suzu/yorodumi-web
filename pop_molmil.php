<?php
//ini_set("display_errors", 1);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

define( 'VIEWER_ID', 'molmil' );
define( 'PRIME', $_GET[ 'prime' ] || $_POST[ 'prime' ] );
define( 'DEFAULT_ID', PRIME ? '2ht7' : '1a00' );
require( __DIR__. '/prime.php' );
require( __DIR__. '/pop_common.php' );
$url = '';

//. prime 対応
$title = '';
if ( PRIME ) _prime_init();
define( 'PRIME_PORTABLE', PRIME && is_dir( 'portable_data' ) );

//. DB別
//.. PDB style
if ( $db == 'pdb' ) {
	$_simple->jsvar([ 'initstyle' => [
		'multic' =>  _inlist( $id, 'multic' ) 
	]]);
}
//- 公開前データをテスト版で見る
if (
	_before_release_time() &&
	in_array( $id, _file( DN_PREP. '/newids/latest_new_pdbid.txt'  ) )
) $url =_url( 'mmcif', $id );

//.. sasbdb
if ( _instr( 'sasbdb', $db ) ) {
	if ( $db == 'sasbdb-model' ) {
		//- model-ID
		$j = _json_load2( _fn( 'sas_json', _sas_info( 'mid2id', $id ) ) )->sas_model;

		//- ダミー原子なら CPK
		$init = 'spacefill only; color CPK;';;
		foreach ( $j as $c ) {
			if ( $c->id != $id ) continue;
			$atomic = $c->type_of_model == 'atomic';
			break;
		}
		$url = URL_DATA. "/sas/splitcif/$id.cif";

	} else {
		//- SASBDB-ID
		$j = _json_load2( _fn( 'sas_json', $id ) )->sas_model[0];	
		$mid = $j->id;
		$url = URL_DATA. "/sas/splitcif/$mid.cif";
		$atomic = $j->type_of_model == 'atomic';
	}
	$_simple->jsvar([ 'initstyle' => [ 'dummy' => ! $atomic ] ]);
}

//.. emdb
if ( $db == 'emdb' ){
	$url = _fn( 'emdb_med', $id ). '/ym/1.obj';
}

//.. bird
if ( $db == 'bird' ) {
	$url = 'txtdisp.php?a=bird_cifcc.'. _numonly( $id );
}

//.. portable
if ( PRIME_PORTABLE ) {
	if ( strlen( $id ) < 4 )
		$url = "portable_data/". strtolower( $id ). ".cif.gz";
	else if ( _numonly( $id ) == $id )
		$url = "portable_data/$id.obj";
	else
		$url = "portable_data/$id.json.gz";
}

//. output
$url_molmil = PRIME_PORTABLE ? 'molmil_src' : '//gjbekker.github.io/molmil';

$_simple->page_conf([
	'id'		=> $o_id->DID ,
	'vw'		=> 'molmil' ,
	'loading'	=> true,
	'icon'		=> PRIME ? 'img/lk-prime.gif':  'img/lk-molmil.gif' ,
	'js'		=> 'pop_molmil' ,
	'jslib'		=> [
		PRIME_PORTABLE ? 'jq_local' : null ,
		_t( 'script', "molmil_settings={src:'$url_molmil/'};" ) ,
		_t( "script|src:$url_molmil/molmil.js", '' ),
	] ,
	'title'		=> $title
])
->add_contents( _div(
	'#molmil_layer| !_gmenu.hide();', 
	_span( '.molmil_UI_container', _t( 'canvas | #molmilViewer' ) ) 
))

//.. jsvar
->jsvar([
	'ent' => [
		'url'	=> $url ,
	] ,
	'app' => 'molmil' ,
])

//.. css
->css( <<<EOD
html, body {
	overflow:hidden; 
}
#molmilViewer {
	width: 100%; height: 100%;
}
#molmil_layer {
	position: fixed; top: 0;
	width: 100%; height: 100%;
}
.molmil_UI_container {
	-webkit-touch-callout: none;
	-webkit-user-select: none;
	-khtml-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
//	position: fixed;

//	border: 1px solid blue;
//	overflow: hidden;
}
.chkbox_label {
	margin-right: 1em;
}

//- Prime
@media screen and ( min-width:641px ) { 
	.prime_img, .prime_img_cur {
		postion: relative; height:120px; width:120px; margin: 4px; border-width: 1px;
		opacity: 0.9
	}
	.prime_img:hover { height:140px; width:140px; margin: -6px; z-index: 300; }
}
@media screen and ( max-width:640px ) { 
	.prime_img, .prime_img_cur {
		postion: relative; height:90px; width:90px; margin: 2px; border-width: 1px;
		opacity: 0.9
	}
	.prime_img:hover { height:102px; width:102px; margin: -4px; z-index: 300; }
}

.prime_img_cur { opacity: 0.4 }
.prime_img_cur:hover { opacity: 0.8 }

.mom_img {max-width:200px; max-height:200px; opacity: 0.8}

EOD
)

//.. end
->popvw_output();

//. gmenuの中身
function _gmenu_items() {
	global $o_id;
	if ( (boolean)_getpost( 'prime' ) ) {
		_gmenu_items_prime();
		return;
	}

	//.. add lang
	_add_lang('molmil');

	//.. assembly
	if ( $o_id->db == 'pdb' ) {
		$asb = [];
		foreach ( (array)$o_id->mainjson()->pdbx_struct_assembly as $c ) {
			if ( in_array( $c, (array)json_decode( _ezsqlite([
				'dbname' => 'pdb' ,
				'where'  => [ 'id', $o_id->id ] ,
				'select' => 'json' ,
			]))->identasb )) continue;
			$asb[] = _btn( "!_mm.asb('". $c->id. "')", '#'. $c->id );
		}
		if ( $asb ) {
			_tab_item( 'data', 'Assembly', ''
				. _btn( "!_mm.asb()", 'AU' )
				. implode( '', $asb )
				. ' '
				. _ab( _url( 'ym', $o_id->did ). '#h_assembly', _l( 'Details' ) )
			);
		}
	}
		
	//.. style
	_tab_item( 'style', [
		_vw_menu([
			'init'		=> 'Default',
			'cartoon'	=> 'Cartoon' ,
			'cpk'		=> 'Spacefill',
			'bs'		=> 'Ball&stick' ,
			'stk'		=> 'Stick'
		],[
			'param1'	=> 'style' ,
			'pretext'	=> _l( 'Style' )
		])
		,
		_vw_menu([
			'init'		=> 'Default' ,
			'jmolchain' => 'by chain' ,
			'cpk'		=> 'CPK' ,
			'grp'		=> 'Group (blue->red)' ,
			'str'		=> 'Structure',
		],[
			'param1' => 'color' ,
			'pretext' => _l( 'Color' )
		])
	]);


	//.. view
	_tab_item( 'view', 'Molmil UI', ''
		. _btn( "!_mm.uiset('molmil_menu');", _l( 'Menus' ) )
		. _btn( "!_mm.uiset('console');", _l( 'Console' ) )
	);
	_tab_item( 'view', 'Viewing', ''
		. _btn( "!_mm.ui.configureSlab($('#uibox')[0]);", _l( 'Slab' ) )
	);
	_tab_item( 'view', ''
		. _vw_chkbox( 'Black BG'	, 'blackbg' , '#chkbox_blackbg' )
		. _vw_chkbox( 'Fog effect'	, 'fog'		, '#chkbox_fog' )
		. _vw_chkbox( 'Stereo'		, 'stereo'	, '#chkbox_stereo_br' )
	);

	//.. -----
	return;
}

//. UI functions

//.. _vw_menu ビューア操作用プルダウンメニュー
//- items: param2 => label
//- $o[ pretext, sel, opt, param1 ]
function _vw_menu( $items, $o = [] ) {
	$pretext = $param1 = '';
	extract( $o );
	$s = '';
	foreach ( $items as $k => $v ) {
		$s .= _t( "option| value:$k", _l( $v ) );
	}
	$s = _t( "select|autocomplete:off|onchange:_vwui.$param1(this)|$opt", $s );
	return $pretext == '' 
		? $s
		: _t( 'span | .nw', "$pretext:$s" )
	;
}
//.. _vw_chkbox ビューア操作用チェックボックス
function _vw_chkbox( $label, $param, $opt = '' ) {
//	$js = "_vwui.$param(this);";
	$opt = "#vwchkbox_$param|$opt|onchange:_vwui.$param(this);"
		. _atr_data( 'param', $param )
	;

	return _span( '.nw chkbox_label', 
		_e( "input| type:checkbox|autocomplete:off| $opt" )
		. _t( "label| for:vwchkbox_$param", _l( $label ) )
	);
}
