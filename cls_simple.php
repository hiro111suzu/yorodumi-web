<?php

class cls_simple {

//. 変数
public
	$contents = '' ,
	$hidden = '' ,
	$meta = '' ,
	$about = []  ,
	$sqlite_log = []
;
protected
	$testinfo_data = '' ,
	$h1_list = [] ,
	$time_log = [] ,
	$css_store = [],
	$js_store = [] ,
	$jsvar = [] ,
	$conf = [] ,
	$doctype = '<!DOCTYPE HTML>'
	. '<meta http-equiv="content-type" content="text/html; charset=UTF-8">'
	. '<meta http-equiv="X-UA-Compatible" content="IE=edge">'
;

//. page_conf
function page_conf( $in ) {
	$this->conf = array_merge( $this->conf, $in );
	return $this;
}

//. out
//- 全要素出力
//- エラーとかajax以外は、これが唯一のecho

function out( $a = [] ) {
	extract( array_merge( $this->conf, $a ) );
		//- $title, $sub, $icon ,$js, $openabout, $newstag, $docid, $title_pk,
		//- $auth_autocomp,
	unset( $a );
	
	$this->jsvar([
		'idservadr' => defined( 'IMG_MODE' ) && IMG_MODE == 'em'
			? 'ajax.php?mode=id2img&img_mode=em&id='
			: 'ajax.php?mode=id2img&id='
		,
		'popxbtn' => [
			1 => _btn( '!_pop.hide()  | .pophide', 'X' ) ,
			2 => _btn( '!_pop.hide(2) | .pophide', 'X' ) ,
			3 => _btn( '!_pop.hide(3) | .pophide', 'X' ) 
		],
	]);

	//.. autocomp
	$j = _json_load2( DN_DATA. '/autocomp.json.gz' );
	$e = COLOR_MODE == 'emn';
	$autocomp = _t( 'datalist | #acomp_kw', $j->kw );
	if ( $e )
		$autocomp .= _t( 'datalist | #acomp_em', $j->kw_em );
	if ( $auth_autocomp ) {
		$autocomp .= _t( 'datalist | #acomp_an' . ( $e ? '_em' : '' ) ,
			$e ? $j->an_em : $j->an 
		);
	}

	//.. タイトル
	$title_color = $title;
	$title = strtr( $title, [
		'EM Navigator'	=> 'EM| Navigator|' ,
		'EMN'			=> 'EM|N|' ,
		'Yorodumi'		=> 'Yorodu|mi|' ,
		'Omokage'		=> 'Omo|kage|',
		'万見'			=> '万|見|',
	]);
	if ( _instr( '|', $title ) ) {
		$a = explode( '|', (string)$title );
		$title_color = _span( '.ttcol1', $a[0] ) . _span( '.ttcol2', $a[1] ) . $a[2];
		$title = implode( '', $a );
	}

	//.. about
	if ( $this->about ) {
		 $this->hdiv(
		 	IC_HELP. _ej( "About $title", $title. 'について' ) ,
		 	_ul( $this->about ) ,
			[ 'hide' => ! $openabout, 'id' => 'about' ]
		);
	}

	//- docから（こっちをメインに切替よう）
	$about = '';
	if ( $newstag ) {
		$news = '';
		$cnt = 0;
		foreach ( ( new cls_sqlite( 'doc' ) )->where([
			'type is "news"' ,
			'tag NOT LIKE "%|old|%"' ,
			"tag LIKE \"%|$newstag|%\""
		])->qcol([
			'select' => 'id' ,
			'order by' => 'num' ,
			'limit' => 5 ,
		]) as $i ) {
			$news .= _doc_hdiv( $i, [ 'type' => 'h3', 'hide' => $cnt > 1 ] );
			++ $cnt;
		}
		if ( $news ) {
			$about .= $this->hdiv(
				'News',
				$news . _p( _page_link( 'news', _ej( 'Read more', 'すべてのお知らせ' ) ) ) ,
				[ 'type' => 'h2' ] 
			);
		}
	}

	//- 解説
	if ( $docid ) {
		$about .= _doc_hdiv( $docid, [ 'nourl' => true ] );
		//- サブタイトルもdocから
		$sub = $sub ?: json_decode( _ezsqlite([
			'dbname' => 'doc' ,
			'select' => 'json' ,
			'where'  => [ 'id', $docid ]
		]))->{ _ej( 'e', 'j' ) }->s;
	}

	if ( $about != '' ) {
		 $this->hdiv(
		 	IC_HELP. _ej( "About $title", $title . 'について' ) ,
		 	$about
		 	. _p( _ab([ 'doc', 'tag' => $newstag ], _ej( 'Read more', '他の情報も見る' ) ) )
		 	,
			[ 'hide' => ! $openabout, 'id' => 'about' ]
		);
	}
	
	//.. icon
	$favicon = '';	//- favicon
	$icon_s = '';
	$icon32 = '';	//- タイトル用アイコン
	if ( $icon != '' ) {
		foreach ([ "img/lk-$icon.gif", "img/$icon.gif", "img/$icon" ] as $icon_s ) {
			if ( ! file_exists( $icon_s ) ) continue;
			$icon32  = file_exists( $l = strtr( $icon_s, [ '.' => '32.' ] ) )
				? $l : $icon_s ;
			if ( $icon == 'emn' ) //- そのうちちゃんとする
				$icon32 = "img/emn32.png";
			$favicon = _e( "link | rel:icon | href:$icon_s" );
			$icon_s = _img( $icon_s );
			$icon32 = _img( $icon32 );
			break;
		}
	}


	//.. ページ一覧
	//... パンくず
	$pk = 'li| itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"';
	$s = _t( 'li', $icon_s
		. _span( 'itemprop="title"', $title_pk ?: $title )
	);

	//- トップページでない (EMN /YMでない)
	if ( ! defined( 'TOP_PAGE' ) ) {
		$s = _t( 'ul', _t( $pk, _page_link( COLOR_MODE == 'emn' ? 'emn' : 'ym' ) )
			. _t( 'ul', $s )
		);
	}

	$item_pankuzu = _t( 'nav' , 
		_t( $pk, _page_link( 'pdbj' ) )
		. _t( 'ul| .pankuzu', $s ) //- 以下
	);

	//... サーチ
	$item_search = _t( 'form |.topline | method:get | action:ysearch.php', ''
		. _page_link( 'y_search', _l( 'Cross-search' ) )
		. ': '
		. _input( 'search', '.acomp| name:kw| list:acomp_kw| size:15' )
	);

	//... その他のページ
	$link_items = [
		$this->link_tree( 'emn' ,
			[ 'e_search', 'e_stat', 'gallery', 'e_pap', 'covid19', 'doc_emn' ]
		), 
		$this->link_tree( 'ym', [ 'y_search', 'pap', 'taxo', 'doc_ym', ] ) ,
		$this->link_tree( 'omos', [ 'doc_omos' ] ) ,
		$this->link_tree( 'doc', [ 'help', 'faq', 'news', 'pages' ] ) ,
	];

	//.. toplinks
	$toplinks = _t( 'ul', ''
		. $item_pankuzu
		. LI
		. $item_search
		. LI. implode( LI, $link_items )
		. ( TEST ? LI. _mng_input() : null )
	);


	//.. 言語切替ボタン
	$top = $_SERVER[ 'PHP_SELF' ];
	$langlink = ''
		. '<b>['. _l( 'English' ). ']</b> '
		. _a( "$top?"
			. http_build_query( [ 'lang' => _l( 'ja' ) ] + $_GET ) ,
			_l( '日本語' )
		)
	;

	//.. ボトム
	$footer = implode( BR, [
		implode( ' ', $link_items ) ,
		implode( ' ', [
			_page_link( 'pdbj', 'Protein Databank Japan (PDBj)' ) ,
			_page_link( 'pdbj_help' ) ,
			_page_link( 'pdbj_contact' ) ,
		]) ,
		_a([ 'doc', 'id' => 'developer' ],
			_l( 'Developed by' ). ' '. _img( 'img/face.jpg' ). ' H. Suzuki@PDBj' 
		)
	]);
	//- PDBj / Quick/  EMN / YM / Developper: H. Suzuki @PDBj

	//.. ext_column
	$m = '';
	foreach ( $this->h1_list as $i => $str )
		$m .= _t( "li | .clickable  | !_hdiv.focus('$i')", $str );

	$pg_menu = ''
		. _btn( '!_hdiv.all()', _l( 'Show/hide all' ) )
		. _chkbox( _l( 'Exclusive' ), '#hdiv_exc', $_COOKIE[ 'hdiv_exc' ] )
		. _t( 'ul', $m )
	;

	//- fontsize
	$fs = $_COOKIE[ 'vfsize' ] ?: 2 ;
	foreach ( [0,1,2,3,4] as  $i )
		$da[ $i ] = $i == $fs ? 'disabled' : '';

	$ext_column =  _div( '#menubox | .extcol_item_outer hide', ''
		. _btn( '.closebtn | !_extcol.menu(0)', 'X' )
		. _p( _span( '!_extcol.menu(0)', _fa( 'bars', 'white' ). _l( 'Menu' ) ) )
		. _div( '.extcol_item_inner', ''

			//- ムービー
			. _div( '#movctrl | .hide', ''
				. $this->hdiv(
					_ic( 'play' ). _l( 'Movie' ) ,
					_t( 'ul | #movlist', '' )
					. _pop(
						_ic( 'opt' ). _l( 'Controller' ),
						_ul( _mov_remocon() )
					) ,
					[ 'id' => 'page_menu', 'type' => 'h2' ]
				)
			)

			//- 構造ビューア
			. _div( '#vwctrl | .hide', ''
				. $this->hdiv(
					IC_VIEW. _l( 'Structure viewers' ) ,
					_t( 'ul | #vwlist', '' ) ,
					[ 'id' => 'page_menu', 'type' => 'h2' ]
				)
				. $this->hdiv(
					_l( 'Viewer log' ) ,
					_div( '#cmdhist', '' ),
					[ 'type' => 'h2', 'hide' => true ]
				)
			)

			//- このページ
			. $this->hdiv(
				_l( 'This page' ) ,
				$pg_menu ,
				[ 'id' => 'page_menu', 'type' => 'h2' ]
			)

			//- このサイト
			. $this->hdiv(
				_l( 'This web site' ) ,
				$toplinks ,
				[ 'type' => 'h2' ]
			)

			//- オプション
			. $this->hdiv(
				_ic('opt') . _l( 'Options' ), ''
				. _ul([
					$langlink ,
					_l( 'Structure viewers' ). _ul([
						_viewer_selector( 'mol' ) ,
						_viewer_selector( 'map' ) ,
					]) ,
					_l( 'Font size' )
					. ': '
					. _sizebtn( 'ss', ' fsizebtn| #fsize0 | !_fsize(0)|' . $da[0] )
					. _sizebtn( 's' , ' fsizebtn| #fsize1 | !_fsize(1)|' . $da[1] )
					. _sizebtn( 'm' , ' fsizebtn| #fsize2 | !_fsize(2)|' . $da[2] )
					. _sizebtn( 'l' , ' fsizebtn| #fsize3 | !_fsize(3)|' . $da[3] )
					. _sizebtn( 'll', ' fsizebtn| #fsize4 | !_fsize(4)|' . $da[4] )
				]) ,
				[ 'type' => 'h2', 'hide' => false ]
			)
		)
	);

	//.. testinfo
	$this->org_testinfo();

	//.. 出力
	echo $this->doctype
		. $this->meta_store
		. _t( 'title', _ifnn( $sub, "$sub - " ) . $title )
		. $favicon
		. '<link href="//netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">'
		. $this->jslib( $jslib, 'ui' )
		. $this->css_render( $this->css_store ) 
		. _div( '#ext_column', $ext_column )
		. _div( '#mainbox', ''

			//- topbar
			. _t( 'header', _div( '#simple_top | .clearfix', ''
				. _div( '#top_opt', $langlink . BR
					. _btn( '#btn_menu_pop |!_extcol.menu(1)',
						_ic( 'menu' ). _l( 'menu' )
					)
				)
				. _pop(
					$icon32 . $title_color ,
					$toplinks , 
					[ 'trgopt' => '#simple_top_title', 'js' => '_acomp.init()' ]
				)
				. ( $sub ? _div( '#simple_top_sub', "- $sub -" ) : '' )
			))
			. _div( '#maininner', $this->contents )
			. _t( 'footer', _div( '#simple_bottom', $footer ) )
		)
		. _div( '#popbox|.popbox', '' )
		. _div( '#popbox2|.popbox', '' )
		. _div( '#popbox3|.popbox', '' )
		. $this->hidden //- jsで使う隠し要素など
		. $autocomp
		. $this->js_render( 'simple_common', 'simple', $js, $this->js_store ) // 'simple_mov',
	;
}
//. link_tree
function link_tree( $link1, $link2 ) {
	return _page_link( $link1 ). ' '. _pop(
		IC_PLUS ,
		_ul( array_map( '_page_link', $link2 ), 0 ) //- _page_link( ) 関数利用
	);
}

//. popvw_output
function popvw_output( $a = [] ) {
	global $o_id;
	extract( array_merge( $this->conf, $a ) ); //- $icon, $js, $jslib, $loading
	unset( $a );

	$this->jsvar([
		'popxbtn' => [
			1 => _btn( '!_pop.hide() | .pophide', 'X' ) ,
			2 => _btn( '!_pop.hide(2) | .pophide', 'X' ) ,
			3 => _btn( '!_pop.hide(3) | .pophide', 'X' ) 
		],
	]);

	$title = $title ?: $o_id->DID. ' - '. VIEWER_NAME. ' - '. _l( 'Yorodumi' );

	die( $this->doctype
		. $this->meta_store
		. _t( 'title', strip_tags( $title ) )
		. ( $icon != '' ? _e( "link | rel:icon | href:$icon" ) : '' ) //- favicon 
		. $this->jslib( $jslib )
		. $this->css_render( $this->css_store )
		. _p( '#ttbar | !_gmenu.show()',
			$title . _span( '.right', _ic( 'menuw' ). _l( 'Menu' ) )
		)
		. _div( '#gmenu | .hide', LOADING )
		. _span( '#uibox', '' )
		. _div( '#pmsgbox', ''//. ( $loading ? '' : '|.hide' ), 
			. ( $loading ? _span( '#loadingbar', LOADING . 'Loading' ) : '' )
			. _div( '#pmsg',  '' )
		)
		. $this->contents
		. _div( '#popbox|.popbox', '' )
		. _div( '#popbox2|.popbox', '' )
		. _div( '#popbox3|.popbox', '' )
		. $this->js_render( 'pop_common', 'simple_common', $js, $this->js_store )
	);
}

//. meta
function meta( $in, $in2 = '' ) {
	$in = $in2 ? [
		'name' => $in ,
		'content' => is_array( $in2 ) ? implode( ',', $in2 ) : $in2
	] : $in;
	$out = '';
	foreach ( $in as $k => $v )
		$out .= " $k=\"$v\"";
	$this->meta_store .= _e( "meta$out" );
	return $this; 
}

//. hdiv
/*
$id 特別なIDをふるか css-id
$hide デフォルトで隠しておく 
$return: $this->contentsに追加せずにreturnするフラグ
$only: open時、他をcloseする要素
$js 開いたとき実行するスクリプト

アイコンはタイトルから自動付加されるが、$iconで指定も可能

h1以外の時はオプション無しでも自動出力しないモード
*/

function hdiv( $h1cont, $div_cont, $opt = [] ) {
	$type = 'h1';
	$h1add = $icon = $hide = $only = $return = false;
	extract( $opt ); //- $id, $hide, $only, $return, $js, $type
	if ( $type != 'h1' )
		$return = true;

	//- ID
	if ( $id == '' )
		$id = _cssid();
	
	//- 排他モード
	$o = $only ? ',2' : '';

	//- h1 str
	$h1str = _icon_title( $h1cont, $icon );

	//- メニュー用
	if ( $type == 'h1' ) {
		$this->h1_list[ $id ] = $h1str;
	}

	$cls = ( $type == 'h1' ? ' lev1' : ( $type == 'h2' ? ' lev2' : '' ) )
		. ( $hide ? ' hide' : '' )
	;

	$ret .= _t( "$type | #h_$id | !_hdiv.oc('$id'$o)" , ''
		. _div( "#oc_btn_$id | .oc_btn ", $hide ? '+' : '-' )
		. $h1str . $h1add
	)
	. _div( "#oc_div_$id | .oc_div$cls" . _atr_data( 'js', $js ), $div_cont )
	;

	//- 自動出力/return
	if ( ! $return ) {
		$this->contents .= $ret;
		return $this;
	}
	return $ret;
}

//. add_contents
function add_contents( $str ) {
	$this->contents .= $str;
	return $this;
}

//. time
function time( $name = 'time' ) {
	if ( $_COOKIE['time_log'] )
		$this->time_log[ $name ] = microtime( TRUE );
	return $this;
}

//. testinfo_add
function testinfo_add( $info, $info2 ) {
	if ( ! TEST ) return;
	$this->testinfo_data .= $this->hdiv(
		$info2 ?: 'testinfo' ,
		_print_r( $info ) ,
		[ 'type' => 'h2' ]
	);
}

//. js
function js( $str ) {
	$this->js_store[] = $str;
	return $this;
}

//. css
function css( $str ) {
	$this->css_store[] = $str;
	return $this;
}

//. css_render
function css_render() {
	$code = '';
	$link = '';
	foreach ( _armix( func_get_args() ) as $n ) {
		if ( _instr( '<link', $_css[ $n ] ) ) {
			$link .= $_css[ $n ];
		} else {
			$code .= _reg_rep( $n, TEST ? [
				'/\/\/.*([\n\r]+|$)/' => '' //- コメント消し
			] : [
				'/\/\/.*([\n\r]+|$)/' => '' ,
				'/ *([,:;{}]) */'	=> '$1' , //- 無駄な空白消し
				'/[\t\n\r]+/'		=> '' 
			]);
		}
	}
	return "<style>\n$code\n</style>$link\n";
}

//. jsvar
function jsvar( $a ) {
	$this->jsvar = array_replace_recursive( $this->jsvar, $a );
	return $this;
}

//. js_render: jsのコードを返す
//- 配列でも、複数の要素でもOK
function js_render() {
	function _js_comment( $s ) {
		return TEST ? "\n// ---------- $s ----------\n\n" : null;
	}

	$ret = '';
	//- jsvar
	if ( $this->jsvar ) $ret .= ''
		. _js_comment('jsvar')
		. 'var phpvar='. json_encode( $this->jsvar, TEST ? JSON_PRETTY_PRINT : null ). ';'
	;

	//- スクリプト
	foreach ( _armix( func_get_args() ) as $name ) {
		$ret .= _instr( ';', $name ) //- セミコロンがあったらjsコード、なければ名称
			? _js_comment('direct'). $name
			: _js_comment( $name )
			 . ( TEST
			 	? file_get_contents( "jsprec/$name.js" )
			 	: _ezsqlite([
		 			'dbname' => 'subdata' ,
					'select' => 'data' ,
					'where'  => [ 'key', "js|$name" ]
			 	])
			 )
		;
	}
	return "<script>\n$ret\n</script>\n";
}


//. jslib: javascript のライブラリ読み込みのタグを返す
//- 配列でも、複数の要素でもOK
function jslib() {
	$in = _armix( func_get_args() );

	//- jquery はローカル指定があればローカル、
	if ( ! in_array( 'jq_local', $in ) )
		$in = array_merge( [ 'jq' ], $in );

	//- その他
	$ret = [];
	foreach ( $in as $s ) {
		$ret[] = _instr( '<', $s ) //- スクリプト直書き
			? $s
			: '<script src="'. ([
				'jq'			=> '//code.jquery.com/jquery-3.4.1.min.js' ,
				'jq_local'		=> 'js/jquery-3.4.1.min.js' ,
				'ui'			=> 'js/jquery-ui.min.1.12.1.js' ,
				'jplayer'		=> 'js/jquery.jplayer.min.js' ,
				'tablesorter'	=> 'js/jquery.tablesorter.min.js' ,

				'jmol'			=> JMOLPATH. '/JSmol.min.js' ,
				'jmol3'			=> JMOLPATH. '/js/JSmolThree.js' ,
				'jmolgl'		=> JMOLPATH. '/js/JSmolGLmol.js' 
			][ $s ] ?: $s ). '"></script>'
		;
	}
	$n = TEST ? "\n\n" : '';
	return $n. implode( $n, $ret ). $n;
}
/*
jquery-uiダウンロード、positionのみを選択ダウンロード

以下は以前の情報
draggable, resizable, slider, Tabs, autocomplete, position
cssは、smoothness
*/

//. org_testinfo
function org_testinfo() {
	if ( ! TEST ) return;

	//.. sqlite log
	if ( $this->sqlite_log ) {
		$table = '';
		foreach ( $this->sqlite_log as $a )
			$table .= TR.TH. implode( TD, $a );
		$this->testinfo_data .= $this->hdiv(
			'SQlite log' ,
			_t( 'table| .small', $table ),
			[ 'type' => 'h2' ]
		);
	}

	//.. 時間計測
	if ( $this->time_log ) {
		$this->time( 'output' );
		$prev = TIME_START;
		$table = [];
		foreach ( $this->time_log as $name => $val ) {
			$t = $val - $prev;
			$prev = $val;
			$text = number_format( $t * 1000 );
			$table[ $name ] = $t < 0.05 ? $text : _span( '.red', $text );

		}
		$table[ 'total' ] = number_format( ( microtime( TRUE ) - TIME_START ) * 1000 );
		$this->testinfo_data .= $this->hdiv(
			'comp time' ,
			_table_2col( $table, '.small' ) ,
			[ 'type' => 'h2' ]
		);
	}

	//.. testinfoまとめ
	if ( $this->testinfo_data )
		$this->hdiv( 'Test info', $this->testinfo_data );
}

//. end of class
}

//. ラッパー関数
//.. _testinfo
function _testinfo( $info, $info2 = '' ) {
	global $_simple;
	$_simple->testinfo_add( $info, $info2 );
}
