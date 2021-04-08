<?php
if ( php_sapi_name() != 'cli' )
	die( 'only for CLI' );
require( __DIR__. '/common-web.php' );

/*
@ data更新
{http://marem/emnavi/docdb.php
*/

//. doc読み込み
$_count_id = 0;
$_doc = [];
$_done = [];
$_related = [];

require( __DIR__. '/_doc_data.php' );
if ( count( $_doc ) == 0 ) die();

//. リンクチェック
$_rel_error = [];
foreach ( $_doc as $id => $d ) {
	foreach ( $d[ 'rel' ] as $rid ) {
		if ( $_doc[ $rid ] != '' ) continue;
		$_rel_error[ $id ][] = $rid;
	}
}
if ( count( $_rel_error ) > 0 )
	echo( $_rel_error. 'Error in connection of relation' );

//. json書き込み
$changed = false;
$fn = $argv[1];
if ( $_doc != _json_load( $fn ) ) {
	_json_save( $fn, $_doc );
	$changed = true;
}
echo( 'データ読み込み: '. ( $changed ? '変更あり' : '変更なし' ) );

/*
//. sqlite DB
if ( $changed ) {

	//.. perp
	$dbfn  = realpath( 'doc/doc.sqlite' );
	$columns = implode( ',', [
		'id UNIQUE COLLATE NOCASE' ,
		'kw' ,
		'type' ,
		'tag' 
	]);

	_del( $dbfn );
	$pdo = new PDO( "sqlite:$dbfn", '', '' );
	$pdo->beginTransaction();
	$res = $pdo->query( "CREATE TABLE main( $columns )" );

	//.. main
	$res_out = [];
	foreach ( $_doc as $id => $c ) {

		//- キーワード
		$kw = [];
		foreach ( [ 'e', 'j' ] as $l ) {
			foreach ( (array)$c[ $l ] as $s ) {
				foreach ( preg_split( '/(<br>|<p>|<li>|<td>|<tr>)/', $s ) as $s2 ) {
					$kw[] = strip_tags( $s2 );
				}
			}
		}

		//- クエリ文字列作成
		$vals = implode( ', ', [
			//- ID
			_q( $id ) ,

			//- キーワード
			_q( implode( '|', array_unique( array_filter( $kw ) ) ) ) ,

			//- タイプ
			_q( $c[ 'type' ] ) ,

			//- タグ
			_q( '|' . implode( '|', (array)$c[ 'tag' ] ) . '|' ) ,
		
		]);
		if ( $pdo->query( "REPLACE INTO main VALUES ( $vals )" ) === false ) {
			$res_out[] = "$id: 失敗 <pre>" . print_r( $er = $pdo->errorInfo(), 1 ) . '</pre>';
		}
	}
	//- DB終了
	$pdo->commit();
}

//. テスト出力

//.. 指定doc
$id = _getpost( 'show' );
if ( $id != '' ) {
	$_simple->hdiv( "doc-ID: $id",
		_doc_hdiv( $id, [ 'lang' => 'e' ] ) .
		_doc_hdiv( $id, [ 'lang' => 'j' ] )
	);
}

//.. その他のdoc
$data = [];
foreach ( $_doc as $id => $doc ) {
	$data[ $doc[ 'type' ] ][] = _a(
		"?show=$id",
		$doc[ 'e' ][ 't' ] ?: $doc[ 'j' ][ 't' ]
	);
}
$out = '';
foreach ( $data as $type => $items ) {
	$out .= $_simple->hdiv( $type, _imp2( $items ), [ 'type' => 'h2' ] );
}
$_simple->hdiv( 'All docs', $out );
*/

//. function
//.. _d:  登録
function _d( $d, $opt ) {
	global $_doc, $_count_id, $_type, $_done, $_related;

	$id = $url = $img = '';
	$link = $rel = $tag = $wikipe = [];
	extract( $opt );

	//... ドキュメントID、無ければ適当に割り振る
	if ( $id == '' ) {
		$id = $_count_id;
		++ $_count_id;
	}

	//- IDが重複
	if ( $_done[ $id ] )
		die( "$id: IDが重複" );
	$_done[ $id ] = true;

	//... link
	$le = $lj = [];
	foreach ( (array)$link as $a ) {
		if ( $a[0] == '' && $a[1] != '' ) {
			//- 最初がヌル、日本語だけ
			$lj[] = _ab( $a[1], IC_L . $a[2] );			
		} else if ( count( $a ) == 4 ) {
			//- 値が4つ、URLも別々
			$le[] = _ab( $a[0], IC_L . $a[1] );
			$lj[] = _ab( $a[2], IC_L . $a[3] );
		} else {
			//- 値が2つ、両言語で同じ
			//- 値が3つ、URLは両言語で共通
			$le[] = _ab( $a[0], IC_L . $a[1] );
			$lj[] = _ab( $a[0], IC_L . ( $a[2] ?: $a[1] ) );
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
		$s1 = substr( $d[0], 0, 10 );
		$s2 = substr( $d[0], 10 );
		$d[0] = _datestr( $s1, 'e' ) . '. '
			. ( $s2 ?: strip_tags( $d[2] ) );
		$d[1] = _datestr( $s1, 'j' ) . ': '
			. ( substr( $d[1], 10 ) ?: $s2 ?: strip_tags( $d[3] ) ?: strip_tags( $d[2] ) );
	}

	//... rel 相互に関連付ける
	foreach ( (array)$rel as $i ) {
		if ( $i == '' ) continue;
		if ( is_array( $_doc[ $i ] ) ) //- すでにあるデータなら追記
			$_doc[ $i ][ 'rel' ] =
				array_unique( (array)array_merge( $_doc[$i]['rel'] , [$id] ) );
		else
			$_related[ $i ] = $id;
	}

	//- 既に別のドキュメントからのリンクがある？
//	$rel = $_doc[ $id ][ 'rel' ] + $opt[ 'rel' ];
//	if ( is_array( $_doc[ $id ][ 'rel' ] )
//		$rel += $_doc[ $id ][ 'rel' ]

	//... output
	$_doc[ $id ] = [
		'e' => [
			't' => $q . $d[0] ,
			's' => $a . $d[2] ,
			'c' => _prep( $d[4] ) ,
			'l' => _imp2( $le ) ,
		] ,
		'j' => [
			't' => $q . ( $d[1] ?: $d[0] ) ,
			's' => $a . ( $d[3] ?: $d[2] ) ,
			'c' => _prep( $d[5] ) ,
			'l' => _imp2( $lj ) ,
		] ,
		'tag'	=> is_array( $tag ) ? $tag : explode( ' ', $tag ) ,
		'type'	=> $_type ,
		'rel'	=> array_merge( (array)$rel, (array)$_related[ $id ] ),
		'url'	=> $url ,
		'img'	=> $fn_img ,
		'wikipe' => $wikipe ,
	];

}

//.. _prep: 配列を箇条書きに
function _prep( $in ) {
	global $out;
	if ( $in == '' ) return;
	if ( is_string( $in ) )
		return _t( 'li', $in );

	$ret = '';
	foreach ( $in as $c ) {
		$ret .= _prep( $c );
	}
	return _t( 'ul | .doc_ul', $ret );
}

//.. _q: クオート処理
function _q( $s ) {
	return "'" . strtr( $s, [ "'" => "''" ] ) . "'";
}

