<?php
if ( php_sapi_name() != 'cli' )
	die( 'only for CLI' );
require( __DIR__. '/common-web.php' );

/*
@ data更新
{http://marem/emnavi/docdb.php
*/
define( 'TAG_OK', array_fill_keys([
	'title' ,
	'abst' ,
	'main' ,
	'main_e' ,
	'main_j' ,
	'id' ,
	'tag' ,
	'rel' ,
	'link' ,
	'title' ,
	'img' ,
	'wikipe' ,
	'url' ,
], true ));

//. doc読み込み
$_count_id = 0;
$_doc = [];
$_done = [];
$_related = [];
$_rep = $_rep_global = [];
require( __DIR__. '/_doc_data.php' );
if ( count( $_doc ) == 0 ) die();

//. リンクチェック
$_rel_error = [];
foreach ( $_doc as $id => $d ) {
	foreach ( (array)$d[ 'rel' ] as $rid ) {
		if ( $_doc[ $rid ] ) continue;
		$_rel_error[ $id ][] = $rid;
	}
}
if ( count( $_rel_error ) > 0 )
	echo( _imp( $_rel_error ). ': リンクの相手がいない'. "\n" );

//. json書き込み
$changed = false;
$fn = $argv[1];
if ( $_doc != _json_load( $fn ) ) {
	_json_save( $fn, $_doc );
	$changed = true;
}
echo( 'データ読み込み: '. ( $changed ? '変更あり' : '変更なし' ) );

//. function
//.. _d:  登録 new ver
function _d( $in ) {
	global $_doc, $_count_id, $_type, $_done, $_related, $_rep;

	$key = 'dummy';
	$data = [];
	$lev2 = [];
	$link_id = 1;
	foreach ( _doc_prep( $in ) as $line ) {
		$t_line = trim( $line );
		if ( !$t_line || substr( $t_line, 0, 2 ) == '//' ) continue;
		if ( substr( $line, 0, 1 ) != "\t" ) {
			$key = $t_line;
			if ( ! TAG_OK[ $t_line ] )
				echo( "$t_line: 不明なキー !!!!!!!!!!!!!!!!!!\n" );
			continue;
		}
		if ( $key == 'link' ) {
			//- リンク
			if ( $t_line == '-' )  {
				++ $link_id;
				continue;
			}
			list( $sub_key, $sub_line ) = explode( "\t", $t_line, 2 );
			$data['link'][ trim( $sub_key ). "-$link_id" ] = trim( $sub_line );
		} else if ( in_array( $key, [ 'id', 'url', 'img' ] ) ) {
			//- 単独の要素
			$data[ $key ] = $t_line;
		} else if ( in_array( $key, [ 'main_e', 'main_j' ] ) ) {
			//- main
			$t_line = _pre( $t_line, true );
			if ( substr( $line, 0, 2 ) == "\t\t" ) {		//- 箇条書き、レベル2
				$lev2[ $key ][] = $t_line;
			} else {
				if ( $lev2[ $key ] ) { //- レベル2、終了
					$data[ $key ][] = $lev2[ $key ];
					$lev2[ $key ] = [];
				}
				$data[ $key ][] = $t_line;
			}
		} else {
			//- その他
			$data[ $key ][] = $t_line;
		}
	}
	//-  箇条書き、レベル2の残り
	foreach ( [ 'main_e', 'main_j' ] as $key ) {
		if ( ! $lev2[ $key ] ) continue;
		$data[ $key ][] = $lev2[ $key ];
	}

	$id = $url = $img = $link = $rel = $tag = $wikipe = null;
	$title = $abst = $main_e = $main_j = $main = null;
	extract( $data );

	//- 整理
	$title_e = $title[0];
	$title_j = $title[1] ?: $title[0];
	$abst_e = $abst[0];
	$abst_j = $abst[1] ?: $abst[0];

	//- 空白区切りでも配列にする
	$tag = array_filter( explode( ' ', implode( ' ', (array)$tag ) ) );
	$rel = array_filter( explode( ' ', implode( ' ', (array)$rel ) ) );

	//... ドキュメントID、無ければ適当に割り振る
	if ( ! $id ) {
		$id = $_count_id;
		++ $_count_id;
	}

	//- IDが重複
	if ( $_done[ $id ] )
		die( "$id: IDが重複" );
	$_done[ $id ] = true;

	echo( "----- $id: $title_e\n" );

	//... link
	$link_e = $link_j = [];
	foreach ( range( 1, 20 ) as $num ) {
		if ( $link[ "u-$num" ] ) { //- 英語 - 日本語のURLしかない場合はなし
			$link_e[] = _ab(
				$link["u-$num"],
				IC_L. ( $link["t-$num"] ?: $link["u-$num"] )
			);
		}
		if ( $link[ "uj-$num" ] || $link[ "u-$num" ] ) { //- 日本語
			$link_j[] = _ab(
				$link["uj-$num"] ?: $link["u-$num"] ,
				IC_L
				. ( $link["tj-$num"] ?: $link["t-$num"] ?: $link["uj-$num"] ?: $link["u-$num"] )
			);
		}
	}

	//... 画像
	$fn_img = '';
	$dn = '../emnavi/img';
	foreach ( [ "$dn/$img", "$dn/$id.png", "$dn/$id.jpg" ] as $fn ) {
		if ( is_file( $fn ) && file_exists( $fn ) )
			$fn_img = $fn;
	}

	//... FAQ
	$q = $a = '';
	if ( $_type == 'faq' ) {
		$q = 'Q: ';
		$a = 'A: ';
	}

	//... news
	if ( $_type == 'news' ) {
		$date = substr( $title_e, 0, 10 );
		$title_e = _datestr( $date, 'e' ). '. '. strip_tags( $abst_e );
		$title_j = _datestr( $date, 'j' ). ': '. strip_tags( $abst_j );
	}

	//... rel 相互に関連付ける
	foreach ( (array)$rel as $i ) {
		if ( ! $i ) continue;
		if ( is_array( $_doc[ $i ] ) ) //- すでにあるデータなら追記
			$_doc[ $i ][ 'rel' ] =
				array_unique( (array)array_merge( $_doc[$i]['rel'] ?: [] , [$id] ) );
		else
			$_related[ $i ] = $id;
	}

	//- 既に別のドキュメントからのリンクがある？
//	$rel = $_doc[ $id ][ 'rel' ] + $opt[ 'rel' ];
//	if ( is_array( $_doc[ $id ][ 'rel' ] )
//		$rel += $_doc[ $id ][ 'rel' ]

	//... output
	$_doc[ $id ] = array_filter([
		'e' => [
			't' => $q. $title_e ,
			's' => $a. $abst_e ,
			'c' => _prep_ul( $main_e ?: $main ) ,
			'l' => _imp2( $link_e ) ,
		] ,
		'j' => [
			't' => $q. $title_j ,
			's' => $a. $abst_j ,
			'c' => _prep_ul( $main_j ?: $main ) ,
			'l' => _imp2( $link_j ) ,
		] ,
		'tag'	=> $tag ,
		'type'	=> $_type ,
		'rel'	=> array_merge( (array)$rel, (array)$_related[ $id ] ),
		'url'	=> $url ,
		'img'	=> $fn_img ,
		'wikipe' => $wikipe ,
	]);
	$_rep = []; //- rep リセット
}

//.. _table_prep
function _table_prep( $in ) {
	global $_rep;
	$id = 0;
	foreach ( explode( '-----', $in ) as $block ) {
		++ $id;
		$data = [];
		$flg_1st = true;
		$key = 'dummy';
		foreach ( _doc_prep( $block ) as $line ) {
			$t_line = trim( $line );
			if ( !$t_line || substr( $t_line, 0, 2 ) == '//' ) continue;
			if ( substr( $line, 0, 1 ) != "\t" ) {
				$key = $t_line;
			} else {
				$data[ $key ][] = $t_line;
			}
		}
		$table = null;
		$flg_1st = true;
		foreach ( $data as $th => $td ) {
			if ( [ $th, $td ] == [ '_', ['_'] ] ) {
				$flg_1st = false;
				continue;
			}
			if ( $th == '_' )
				$th = '';
			$table .= $flg_1st
				? TR_TOP.TH. $th. TH. implode( TH, $td )
				: TR.    TH. $th. TD. implode( TD, $td )
			;
			$flg_1st = false;
		}
		$_rep[ "%table-$id%" ] = _t( 'table', $table );
	}
}

//.. _rep
function _rep( $in, $flg_global = false ) {
	global $_rep, $_rep_global;
	$rep = [];
	$rep_multi = [];
	$key = 'dummy';
	foreach ( explode( "\n", $in ) as $line ) {
		$t_line = trim( $line );
		if ( substr( $line, 0, 1 ) != "\t" )
			$key = $t_line;
		else {
			$rep[ "%$key%" ] = $t_line;
			$rep_multi[ "%$key%" ][] = $line;
		}
	}
	//- 複数行データ対応
	foreach ( $rep_multi as $key => $val ) {
		if ( 1 < count( $val ) )
			$rep[ $key ] = implode( "\n", $val );
	}
	if ( $flg_global )
		$_rep_global = $rep;
	else
		$_rep = $rep;
}

//.. _prep_ul: 配列を箇条書きに
function _prep_ul( $in, $child = false ) {
	if ( ! $in ) return;
	if ( is_string( $in ) )
		return _t( 'li', $in );
	if ( ! $child && count( (array)$in ) == 1 )
		return _t( 'p', $in[0] );

	$ret = '';
	foreach ( $in as $c ) {
		$ret .= _prep_ul( $c, true );
	}
	return _t( 'ul | .doc_ul', $ret );
}

//.. _doc_prep: 文書 前処理
function _doc_prep( $in ) {
	global $_rep, $_rep_global;
//	print_r( $_rep_global );
	return explode( "\n", _reg_rep(
		strtr( $in, array_merge( $_rep, $_rep_global ) ) //- repによる変換
		,
		[
			'/[\n\r]+[\t ]+NR\t/'		=> '' , //- 改行消し
			'/[\n\r]+[\t ]+BR\t/'		=> '<br>' , //- 改行タグ入れ
			'/< *(.+?) *\|>/'			=> _ab( '$1', '$1' ) , //- link url表示
			'/< *(.+?) *\|[\n\r\t ]*(.+?) *>/' => _ab( '$1', '$2' ) , //- link
		]
	));
}

//.. _q: クオート処理
function _q( $s ) {
	return "'" . strtr( $s, [ "'" => "''" ] ) . "'";
}

//.. _pre
function _pre( $in, $rev = false ) {
	return strtr( $in, $rev ? [ '_n_' => "\n" ] : [ "\n" => '_n_' ] );
}

//.. _lnk
function _lnk( $url, $title ) {
	return "u\t$url\n\tt\t$title";
}

