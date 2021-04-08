<?php 
define( 'COLOR_MODE', 'ym' );
define( 'MODE_POPVW', true );
require( __DIR__. '/common-web.php' );

//. init
$id = _getpost_safe( 'id' ) ?: _getpost_safe( 'prime' );
if ( strlen( $id ) < 3 )
	$id = DEFAULT_ID;
$o_id = new cls_entid( $id );
extract( $o_id->get() );

define( 'VIEWER_NAME', [
	'molmil'	=> 'Molmil',
	'jmol'		=> 'Jmol' ,
	'surfview'	=> 'SurfView' ,
	'movie'		=> 'EMN movie' ,
][ VIEWER_ID ] );

_add_lang( 'pop' );

_define_term( <<<EOD
TERM_SURF_MOD
	The surface model may be simplified or modified to reduce the data size. See the movies for the full-resolution structure.
	データサイズ縮小のため、表示中の表面データは加工されている場合があります。ムービーでは、完全な構造データが見られます。
TERM_ENT_MOVS
	Movies for this entry
	このエントリのムービー
TERM_ABOUT_X
	About _1_
	_1_について
TERM_ABOUT_YORODUMI
	About Yorodumi
	万見(Yorodumi)について
EOD
);

//. ajax - gmenuを出す
if ( _getpost_safe( 'ajax' ) == 'gmenu' ) {

	//.. タブ共通部
	$tabs = [
		'data' => [
			_ab( [ 'ym', $o_id->did ], $o_id->DID ). ': ' . $o_id->title()
			,
			( $o_id->db == 'emdb' && VIEWER_ID != 'movie' ? TERM_SURF_MOD : '' )
		] ,
		'view'  => [],
		'style' => [] ,
		'about' => [ 
			IC_HELP. _ab(
				[ 'doc', 'id' => VIEWER_ID ],
				_term_rep( TERM_ABOUT_X, VIEWER_NAME )
			) ,
			IC_HELP. _ab([ 'doc', 'id' => 'about_ym' ], TERM_ABOUT_YORODUMI )
		]
	];
	$tabs_active = 0;

	//.. 個別コンテンツの処理
	if ( function_exists( '_gmenu_items' ) )
		_gmenu_items( $tabs );

	//.. タブまとめ
	$icons = [
		'data'        => 'entry' ,
		'about'       => 'help' ,
		'explanation' => 'detail' ,
	];

	$out = [];
	foreach ( $tabs as $tab => $inf ) {
		if ( ! $inf ) continue;
		$out[] = [
			'tab' => _ic( $icons[ $tab ] ?: $tab ) . _l( ucfirst( $tab ) ),
			'div' => _btn( ".closebtn | !_gmenu.hide()", 'X' ). _ul( $inf ) ,
		];
	}

	//.. 終了
	die( _simple_tabs( $out ) );
}

//. 共通部、書き出し
//.. meta
$_simple->meta(
	'viewport' ,
	[
		'width=device-width' ,
		'initial-scale=1.0' ,
		'minimum-scale=1.0' ,
		'maximum-scale=1.0' ,
		'user-scalable=no'
	]
)

//.. jsvar
->jsvar([
	'ent' => [
		'db'	=> $db ,
		'id'	=> $id ,
		'url'	=> $url
	] ,
	'postv' => $_GET
])

//.. css
->css( <<<EOD
#ttbar {
	position: relative; z-index: 100;
	background: $col_dark; color: white; margin: 0; padding: 0.2em 1em;
}
#gmenu { position: relative;
	background: transparent;
	z-index: 110; margin: 0; padding: 0.2em 1em;
	max-height: 80%;
	overflow: auto;
}
.gmenu_r {
	float: right !important;
	max-height: 100% !important;
	width: 50% !important;
}

#pmsgbox {
	position: relative; z-index: 100; 
	background: rgba(255,255,255,0.5);
	padding: 0 1em;
}

.tabdiv {
	background: rgba(255,255,255,0.7); 
	max-height: 100%;
	overflow: auto;
}
h1 {
	margin: 1em 0 0 0; padding: 0.1em 1em;
	font-size: medium;
}
EOD
);

//. function
//.. _tab_item: メニュータブのコンテンツ
function _tab_item( $tabname, $item, $item2 = '' ) {
	global $tabs;
	if ( is_array( $item ) )
		$item = implode( ' ', $item );
	$tabs[ $tabname ][] = $item2 == '' ? $item : _kv([ $item => $item2 ]);
}

