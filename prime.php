<?php
/*
通常はincludeで読み込まれる
直接呼ばれるのは、テストクッキーの操作
*/

//. 直接呼ばれたら、リダイレクト
if ( ! defined( 'PRIME' ) && $_GET[ 'kw' ] == '' ) {
	$id = $_GET[ 'id' ];
	if ( $id != '' ) $id = "&id=$id";
	header( "HTTP/1.1 303 See Other" ); 
	header( "Location: pop_molmil.php?prime=1$id" );
}

//. キーワード
if ( $_GET[ 'kw' ] != '' ) {
	require( __DIR__. '/common-web.php' );
	$o_id = new cls_entid( $_GET[ 'kw' ]);
	_redirect( $o_id->ex()
		? 'pop_molmil.php?prime=1&id=' . $o_id->id 
		: _local_link([ 'ysearch', 'kw' => $_GET[ 'kw' ] ])
	);
}

//. function _prime_init
//- ajaxのときは呼ばれない
function _prime_init() {
	global $_simple, $title, $o_id;
	$title = _ej( 'YorodumiPrime', '万見プライム' )
		. _span( '.wide_only', " - " . ( $a[ 'title' ] ?: $o_id->DID ) )
	;

	$_simple->jsvar([
		'postv' => [ 'prime' => 1, 'top' => _getpost( 'id' ) == '' ? 1 : 0 ] ,
		'prime' => 1, 
		'shomenu' => 1 ,
		'init_cmd' => _prime_data( $o_id->id )[ 'init']
	]);
}

//. function: _gmenu_items_prime メニューを返す関数
function _gmenu_items_prime() {
	global $o_id, $tabs;
	_add_lang([
		'prime' 		=> '万見プライム' ,
		'open' 			=> '開く' ,
		'about' 		=> 'ヘルプ' ,
		'explanation'	=> '解説' ,
		'Hide'			=> '隠す' ,
		'Black BG'		=> '黒背景' ,
		'Stereo*'		=> '立体視*', 
		'Fog effect' 	=> 'かすみ効果', 
	]);
//	$tabs = _getpost( 'top' ) ? [

	$tabs = [
		'data' 	=> [] ,
		'explanation' => [], 
		'open' => [] ,
		'help'	=> [] ,
	];

	$l_id = strtolower( $o_id->id );

	$data_title =  
		/* is_dir( 'portable_data' )
		? $o_id->DID
		:*/ _ab(
			strtr( _url( 'ym', $o_id->did ), [ 'numon.pdbj' => 'pdbj' ] ) ,
			$o_id->DID .' - '. $o_id->title() 
		)
	;

	//.. このデータ
	_tab_item( 'data',
		'タイトル: ' . ( _prime_data( $l_id,  'title' ) ?: $o_id->DID )
	);
	_tab_item( 'data', '表示: '
		. _vw_chkbox( 'Black BG'	, 'blackbg' , '#chkbox_blackbg' )
		. _vw_chkbox( 'Fog effect'	, 'fog'		, '#chkbox_fog' )
		. _vw_chkbox( 'Stereo*'		, 'stereo'	, '#chkbox_stereo_br' )
		
		. _btn( '!_mm.zoom(1.5)', '近くへ' )
		. _btn( '!_mm.zoom(0.6667)', '遠くへ' )
		. BR . '*' . _ic( 'stereo' ) . '右目が青、左目が赤のメガネを利用します'
	);
	_tab_item( 'data', 'スタイル: '
		. ( $o_id->db == 'emdb' ? '' : ''
			. _btn( "!_mm.style('cpk').rebuild()", '原子をボールで表示' )
			. _btn( "!_mm.style('bs').rebuild()", '原子のつながりを棒で表示' )
			. _btn( "!_mm.style_init().rebuild()", 'もとにもどす' )
		)
	);

	_tab_item( 'data', '構造データ: ' . $data_title );
	_tab_item( 'data', _a( 'https://numon.pdbj.org', 'PDBj入門ページへ' ) );

//	_tab_item( 'data', 'sqlite-log: '. json_encode( $_sqlite_log, JSON_PRETTY_PRINT ) ); 

//	_tab_item( 'data', _more( _prime_doc( $l_id ), [
//		'btn'  => _l( 'Show explanation' ), 
//		'btn2' => _l( 'Hide explanation' ) 
//	]));

	//.. 解説
	_tab_item( 'explanation',
		( _prime_doc( $l_id ) ?: 'このデータ: '. $data_title ). _auto_exp() 
	);
	
	//.. ヘルプ
	$lk_molmil = _ab( 'doc.php?id=molmil', 'Molmil' );
	_tab_item( 'help',
		'万見（よろづみ）プライム' . _ul([
		'万見プライムは、生き物の分子の立体構造を見ながら、生物学を楽しみ学ぶためのサービスです。',
		'小中学生の学習にも使えるページを目指しています。' ,
		'立体視用の赤青メガネを利用すると、簡単に立体的に見ることができます。' ,
		'スマートフォンから大画面のPCまで、幅広い環境で使えるようにしています。' ,
		"分子構造ビューアとして、{$lk_molmil}を利用しています。",
	
		_ab( URL_PDBJ, 'PDBj' ) . 'が運営しています。' 
	], 0 ));

	_tab_item( 'help', '立体構造からなにがわかるの？'
		. _p( '時計がどいう仕組みで動くのか、車がどういう仕組みで走るのか、そういった機械の仕組みを知るには、歯車やエンジンなど部品の構造を知る必要があります。生き物の仕組みを理解するには、生き物の部品である「生体分子」の構造を理解する必要があります。世界中の研究者がさまざまな生命現象の仕組みを解明するために、さまざまな生体分子の構造を研究しています。万見プライムで立体構造を見て、生き物の仕組みにふれてみてください。' )
	);


	//.. open
	$links = '';
	foreach ( _prime_data() as $i => $files ) {
		//- 今見ているデータ
		$links .= ( $i == $l_id )
			? _img( ".prime_img_cur sd2 br4 | ?このデータ", $files[ 'img' ] )
			: _a( "?prime=1&id=$i", 
				_img( '.prime_img sd2 br4 | ?' . $files[ 'title' ], $files[ 'img' ] ) 
			)
		;
	}
	_tab_item( 'open',
		'どの構造データを見ますか？'
		. _p( $links ) 
		. 'IDかキーワード'
		. _idinput( _getpost( 'id' ),
			[ 'name' => 'kw', 'action' => 'prime.php']
		)
	);
}

//. function: _prime_doc: ドキュメント解析
/*
変換
[捕鯨 ほげい] => ルビ
{http?? hoge} => リンク
{wikipe hoge} 
{mom hoge}
{eprots id text}
{} 太字
*/

function _prime_doc( $id ) {
	//.. 準備
	$rep_in = [
		'/\[(.+?) (.+?)\]/',
		'/{http(.+?) (.+?)}/',
		'/{wikipe (.+?)}/',
		'/{mom (.+?) (.+?)}/',
		'/{eprots (.+?) (.+?)}/',
		'/{(.+?)}/'
	];

	$rep_out = [
		'<ruby>$1<rp>(</rp><rt>$2</rt><rp>)</rp></ruby>' ,
		_ab( 'http$1', '$2' ) ,
		_ab( '//jp.wikipedia.org/wiki/$1', '$1 - ウィキペディア' ) ,
		_ab( '//pdbj.org/mom/$1',
			_img( '.mom_img', "//pdbj.org/mom_data_files/images/mom$1_01.png")
			.BR. '$2 - 今月の分子 PDBj' 
		) ,
		_ab( 'https://pdbj.org/eprots/index_ja.cgi?PDB%3a$1', '$1 - eProts PDBj' ) ,
		'<b>$1</b>'
	];

	$fn = _prime_data( $id, 'txt' );
	if ( ! file_exists( $fn ) ) return '';

	//.. 行ごとのループ
	$out = '';
	foreach ( _file( $fn ) as $line ) {
		$line = trim( preg_replace( '/\/\*.*?\*\//', '', $line ) );
		if ( ! $line ) continue;

		//- データのタイトル（最初の行）
		if ( $title == '' ) {
			$title = $line;
//			//- 未公開データ印
//			if ( _instr( '/pre/', $datafile ) )
//				$title .= _span( '.red', ' [未公開]' );
//			continue;
		}
		//- 初期Jmolコマンド (cmd \t hogehoge) 不使用
		if ( substr( $line, 0, 3 ) == 'cmd' ) {
			continue;
		}
		if ( substr( $line, 0, 4 ) == 'init' ) {
			continue;
		}
		if ( substr( $line, 0, 6 ) == 'wikipe' ) {
			continue;
		}

		//- ルビなど
		$line_rep = preg_replace( $rep_in, $rep_out, $line );
		list( $type, $opt, $opt2 ) = explode( "\t", $line_rep, 3 ); //- 変換後
		$ao = explode( "\t", $line, 3 ); //- 変換前

		//- コメントアウト
		if ( $type == '//' || $type == ';' ) continue;

		//- ボタン - Jmol用だったもの不使用
		if ( $type == 'btn' ) {
			continue;
		}

		//- ボタン2 molmil用
		if ( $type == 'btn2' ) {
			$out .= _btn( _atr_js( $ao[2] ), $opt );
			continue;
		}

		if ( $type == 'img' ) {
			
		}

		//- 普通のタグ
		if ( in_array( $type, [ 'h1', 'h2', 'h3', 'li' ] ) ) {
			$out .= _t( $type, $opt . $a[2] );
			continue;
		}
		//- その他
		$out .= _p( $line_rep );
	}
	return $out;
}

//. function: _prime_data: プライムのデータファイルのリストを返す
$_prime_data = [];
function _prime_data( $id = '', $cont = '' ) {
	global $_prime_data;
	if ( $_prime_data == [] )
		_get_prime_data();
	return $id.$cont == '' ? $_prime_data 
		: ( $cont == ''
			? $_prime_data[ strtolower( $id ) ]
			: $_prime_data[ strtolower( $id ) ][ $cont ]
		)
	;
}

function _get_prime_data() {
	global $_prime_data;
	foreach ( glob( 'prime2data/*.txt' ) as $fn ) {
		$id = strtolower( basename( $fn, '.txt' ) );
		if ( $_prime_data[ $id ]  ) continue;

		//- 画像
		$imgfn = strtr( $fn, [ '.txt'  => '_m.jpg' ] );
		if ( ! file_exists( $imgfn ) )
			$imgfn = strtr( $fn, [ '.txt'  => '.jpg' ] );
		if ( ! file_exists( $imgfn ) )
			$imgfn = _fn( 'pdb_img', $id );

		//- タイトル、初期コマンド
		$title = '';
		$init = '';
		$wikipe = [];
		foreach ( _file( $fn ) as $line ) {
			if ( ! $line ) continue;
			list( $left, $right ) = explode( "\t", $line, 2 ); 
			if ( $left == 'init' ) 
				$init = $right;
			else if ( $left == 'wikipe' ) 
				$wikipe[] = $right;
			else if ( $title == '' )
				$title = $line;
//			if ( $title != '' && $init != '' ) break;
		}

		$_prime_data[ $id ] = [
			'txt' => $fn ,
			'img' => $imgfn ,
			'title' => $title ,
			'init' => $init,
			'wikipe' => $wikipe
			
		];
	}
}


//. function: _auto_exp: 自動解説
function _auto_exp() {
	global $o_id;
	$already = [];
	$ret = '';

	//.. その他の解説
	$i = [];
	$kw = array_merge(
		(array)_prime_data( $o_id->id, 'wikipe' )
		,
		explode( ', ', implode( ', ', (array)$o_id->mainjson()->struct_keywords[0] ) )
	);

	foreach ( $kw as $k ) {
		if ( _already( $k ) ) continue;
		$i[] = _obj('wikipe')->show( $k );
	}
	if ( array_filter( $i ) )
		$ret .= _t( 'h1', 'このデータのキーワード' ) . _ul( $i );

	//.. entity

	$i = [];
	foreach ( (array)$o_id->mainjson()->entity as $j ) {
		if ( $j->type == 'non-polymer' || $j->type == 'water' ) continue;
		$n = $j->pdbx_description;
		$i[] = $n . ( _already( $n ) ? '' : _obj('wikipe')->term( $n )->show() );
	}
	foreach ( (array)$o_id->mainjson()->pdbx_entity_nonpoly as $j ) {
		if ( $j->comp_id == 'HOH' ) continue;
		$i[] = $j->name . _obj('wikipe')->chem( $j->comp_id )->show();
	}
	if ( $i ) {
		$ret .= _t( 'h1', 'この分子にふくまれるもの' ) . _ul( $i );
	}

	//.. source
	$i = [];
	foreach ( (array)json_decode( _ezsqlite([
		'dbname' => 'pdb' ,
		'where'  => [ 'id', $o_id->id ] ,
		'select' => 'json' ,
	]))->src as $s ) {
		$i[] = _div( '', _obj('taxo')->from_db( $s )->pop_cont() );
	}
	if ( $i ) 
		$ret .= _t( 'h1', '由来生物' ) . implode( '<hr>', $i );

	//.. end
	return $ret;
}
function _already( $s ) {
	global $already;
	if ( ! $s ) return true;
	$s = strtolower( $s );
	$ret = $already[ $s ];
	$already[ $s ] = true;
	return $ret;
}