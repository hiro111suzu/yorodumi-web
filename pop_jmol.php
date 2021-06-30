<?php
//. init
define( 'VIEWER_ID', 'jmol' );
require( __DIR__. '/pop_common.php' );

_define_term( <<<EOD
TERM_PROC_SURF
	Processing surface data
	表面データの処理中
TERM_PROC_STR
	Processing structure data
	構造データの処理中
EOD
);


//- 画質
define( 'VHQ', 'set antialiasDisplay ON; set antialiasTranslucent ON;set ribbonBorder ON;' );
define( 'VLQ', 'set antialiasDisplay OFF; set antialiasTranslucent OFF;set ribbonBorder OFF;' );

$size = (integer)_getpost( 'size' ) ?: 300;
$use = _getpost_safe( 'use' ) ?: 'JAVA HTML5';

$o_jmol = new cls_jmol;
$jmol_param = $o_jmol->params( $db, $id, [
	'csmodel' => _getpost_safe( 'csmodel' ) ,
	'asb'	=> _getpost_safe( 'asb' )
]);
//_die( $jmol_param );

//. output
$_simple->page_conf([
	'icon'	=> 'img/lk-jmol.gif' ,
	'jslib'	=> [ 'jmol', 'jmol3' ],
	'js'	=> 'pop_jmol',
])
//.. contents
->add_contents( ''
	. _div( '#jmolinner', '' )

	//- 下の情報
	. _div( '#jmolfoot', ''
		//- 読み込み中
		. _div( '#loading|.appgbar', _l( 'Loading' ). LOADING )

		//- 表面データ
		. _div( '#calcsurf|.appgbar hide', TERM_PROC_SURF. LOADING )

		//- 構造データ
		. _div( '#calcstr|.appgbar hide' , TERM_PROC_STR. LOADING )
	)
)

//.. jsvar
->jsvar([
	'app' => 'jmol' ,
	'jmolcmd' => [
		'reload'	=> $jmol_param[ 'load' ] ,
		'init'		=> $jmol_param[ 'init' ] ,
		'hq'		=> VHQ ,
		'lq'		=> VLQ ,
		'bgblack'	=> 'set ambientPercent 40; set diffusePercent 90;background black' ,
		'bgwhite'	=> 'set ambientPercent 20; set diffusePercent 60;background white'
	] ,
	'jmolconf' => [
		'width'		=> $size ,
		'height'	=> $size ,
		'color'		=> 'white' ,
		'use'		=> $use ?: 'JAVA HTML5' ,
		'isSigned'	=> true ,
		'jarFile'	=> 'JmolAppletSigned.jar' ,
		'j2sPath'	=> JMOLPATH. '/j2s' ,
		'jarPath'	=> JMOLPATH. '/java' ,
		'serverURL' => JMOLPATH. '/php/jsmol.php' ,

		'script'	=> ''
			. 'set ambientPercent 20; set diffusePercent 60;'
			. 'set specular ON; set specularPower 80; set specularExponent 5;'
			. 'set specularPercent 70;'
			. ( $_COOKIE[ 'jmol_hq' ] ? VHQ : VLQ )
			. 'set MessageCallback "_jmolmsg";'
			. 'set picking ident;'
			. 'set PickCallback "_mousepick";'
			. 'set languageTranslation OFF;'
			. $jmol_param[ 'load' ]. ';'
			. $jmol_param[ 'init' ]
 			. 'set zshade on; set zshadepower 1;'

	]
])

//	'idservadr' => IMG_MODE == 'em'
//		? 'ajax.php?mode=id2img&img_mode=em&id='
//		: 'ajax.php?mode=id2img&id='
//		,
//]);

//.. css
->css( <<<EOD
#jmolinner { 
	position: fixed; top: 0;
	border: 1px solid $col_dark;
}
EOD
)
//.. end
->popvw_output();

