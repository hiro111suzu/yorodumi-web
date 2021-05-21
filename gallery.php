<?php 
//. init
define( 'COLOR_MODE', 'emn' );
define( 'IMG_MODE'	, 'em' );
require( __DIR__. '/common-web.php' );

define( 'NUM_IN_PAGE', 50 );
define( 'GRP'		, _getpost( 'grp' ) ?: 'categ' );
define( 'CATEG'		, _getpost( 'categ' ) );

_add_fn( 'gallery' );
_add_lang( 'gallery' );

_define_term( <<<EOD
TERM_GRP_BY
	Grouped by "_1_"
	「_1_」によるグループ分け
EOD
);

//. ajax reply
if ( CATEG != '' ) {
	define( 'PAGE', (integer)_getpost( 'page' ) );
	//.. カテゴリ
	if ( GRP == 'categ' ) {
		$all = explode( ',', _json_load2( DN_DATA. '/emn/categ.json' )->{CATEG}->id );
		$num = count( $all );
		$ids = array_slice( $all, NUM_IN_PAGE * PAGE, NUM_IN_PAGE );
	} else {

	//.. その他
		$sq = new cls_sqlite();
		$num = $sq->cnt( GRP. ' = '. _quote( CATEG ) );

		//- ID
		$ids = $sq->qcol([
			'select'	=> 'db_id' ,
			'order by'	=> 'release DESC, db_id' ,
			'limit'		=> NUM_IN_PAGE ,
			'offset'	=> PAGE * NUM_IN_PAGE
		]);
		unset( $sq );
	}

	//.. 集計
	$opg = new cls_pager([
		'str'		=> '' ,
		'range' 	=> NUM_IN_PAGE ,
		'total'		=> $num ,
		'page'		=> PAGE ,
		'objname'	=> CATEG
	]);

	die( ''
		. $opg->msg()
		. _ent_catalog( $ids, [ 'mode' => 'icon', 'tip' => true ] )
		. $opg->btn()
	);
}

//. main
//.. グルーピング選択
$out = [];
$group_names = _subdata( 'trep', 'gallery-grp' );
foreach ( $group_names as $key => $txt ) {
	$out[] = GRP == $key
		? _span( '.bld', $txt )
		: _a( "?grp=$key", $txt )
	;
}
$_simple->hdiv( 'Classification', _ul( $out ) );
define( 'SUB_TITLE', _term_rep(
	TERM_GRP_BY,
	$group_names[ GRP ]
));

//.. ギャラリー
$grp = [];
if ( GRP == 'categ' ) {
	define( 'E_OR_J', _ej( 'e', 'j' ) );
	//... カテゴリ
	foreach ( _json_load( _fn( 'categ_json' ) ) as $name => $ar ) {
		$grp[ $name ] = [
			'str'   => $ar[ E_OR_J ]  ,
			'count' => count( explode( ',', $ar[ 'id' ] ) ) ,
		];
	}
} else {

	//... その他
	define( 'MET_NAME', _subdata( 'trep', 'met_name_long' ) );
	foreach ( _json_load2( _fn( 'datacount_json' ) )->{GRP} as $name => $a ) {
		$n = $name;
		if ( GRP == 'method' )
			$n = MET_NAME[ strtolower( $n ) ];
		else if ( GRP == 'reso_seg' )
			$n = "~ $n A";
		$grp[ $name ] = [
			'str'	=> $n ,
			'count' => $a->b ,
		];
		ksort( $grp );
	}
}

//.. 出力
$out = '';
$pagenum = [];
foreach ( $grp as $name => $ar ) {
	extract( $ar ); //- $str, $count
	$out .= $_simple->hdiv(
		"$str ($count)" ,
		LOADING ,
		[ 'hide' => true, 'js' => "_gal.get('$name')", 'type' => 'h2', 'id' => $name ]
	);
	$pagenum[ $name ] = [
		'url'	=> "?page=" ,
		'div'	=> "#oc_div_$name" ,
		'pvar'	=> [ 'categ' => $name, 'grp' => GRP ]
	];
}

//. output
$_simple->page_conf([
	'title' 	=> 'EMN Gallery', 
	'icon'		=> 'emn' ,
	'sub'		=> SUB_TITLE ,
	'openabout'	=> false ,
	'docid'		=> 'about_gallery' ,
	'newstag'	=> 'emn',
])
->hdiv(
	'Gallery',
//	BTN_HDIV2_ALL . $out
	$out
)

//.. js
->js( <<<EOD
var _gal = {
	loaded: {},
	loading: false,

	get: function( s ) {
		if ( this.loaded[ s ] ) return;		//- 既読？
		if ( this.loading ) {
			setTimeout(function(){ _this.get(s); }.bind(this), 500 );
			return;
		}
		this.loading = true;
		$( '#oc_div_' + s ).load( '?categ=' + s, phpvar.postv, function() {
			//- 画像サイズ再設定
			this.loaded[ s ] = true;
			this.loading = false;
		}.bind(this) );
	}
};
EOD
)

//.. jsvar
->jsvar([
	'pagenum'	=> $pagenum ,
	'postv'		=> [ 'lang' => LANG, 'grp' => GRP ] ,
])

//.. end
->out();

