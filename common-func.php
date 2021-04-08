<?php

//. 条件定義の関数

//.. _l: 翻訳
if ( L_EN ) {
	function _l( $s ) { return $s; }
	function _ej( $s1, $s2 ) { return $s1; }
} else {
	function _l( $s ) {
		global $_e2j_dic;
		return $_e2j_dic[ strtolower( $s ) ] ?: $s;
	}
	function _ej( $s1, $s2 ) { return $s2; }	
}

//.. _test: テストモードなら文字列を返す
if ( TEST ) {
	function _test( $s ) { return $s; }
} else {
	function _test( $s ) {}
}

//. DB関係ない系
//.. _obj 一個のインスタンスを使い回すクラスオブジェクト用
$_obj_cache = [];
function _obj( $name ) {
	global $_obj_cache;
	$name = "cls_$name";
	if ( ! $_obj_cache[ $name ] )
		$_obj_cache[ $name ] = new $name;
	return $_obj_cache[ $name ];
}

//.. _getpost:
function _getpost( $s = '' ) {
	return trim( strip_tags( mb_convert_kana( $_GET[ $s ] ?: $_POST[ $s ], 'a' ) ) );
}

//.. _getpost_safe:
function _getpost_safe( $s = '' ) {
	return preg_replace( '/[^a-zA-Z0-9_\.\-,;:]/', '', _getpost( $s ) );
}

//.. _instr: 文字列が含まれるか？
//- mng版よりも低機能
function _instr( $needle, $heystack ) {
	return stripos( $heystack, $needle ) !== false;
}

//.. _headmatch: 文字列の先頭が一致するか？
function _headmatch( $needle, $heystack ) {
	return stripos( $heystack, $needle ) === 0;
}

//.. _short: ユニコード文字列を短く
//- ページタイトル用
function _short( $s, $l = 70 ) {
	return ( mb_strlen( $s ) > $l )
		? mb_substr( $s, 0, $l - 5 ) . "..."
		: $s
	;
}

//.. _ic: アイコン画像
function _ic( $s = 'link' ) {
	$s = strtolower( $s ) ?: 'link';
	return "<img src=\"img/lk-$s.gif\" class=\"lkicon\">";
}

//.. _x: xmlから取り出した文字列の修正、改行を消去とか
function _x( $s ) {
	if ( is_object( $s ) || is_array( $s ) )
		return _test( _t( 'pre | .red bld', print_r( $s, true ) ) );
	if ( strlen( $s ) > 5000 )
		$s = substr( $s, 0, 5000 ) . ' ...';
	return _reg_rep( trim( $s ), [
		'/[\n\r\t ]+/' => ' ',
		'/^(na|n\/a|null|none)$/i' => '' ,
	]);
}

//.. _imp2: implodeのラッパー
function _imp2( $ar ) {
	//- デフォルトのセパレータ区切る
	//- 配列で受け取っても、引数の羅列で受け取ってもOK
	return implode( SEP, _armix( func_get_args() ) );
}

//.. _format_bytes: KBとかにする
function _format_bytes( $b ) {
	$r = [ 1099511627776, ' TB' ];
	if ( $b < 1099511627776 ) $r = [ 1073741824, ' GB' ];
	if ( $b <    1073741824 ) $r = [    1048576, ' MB' ];
	if ( $b <       1048576 ) $r = [       1024, ' KB' ];
	if ( $b <          1024 ) $r = [          1, ' B'  ];
	if ( $b == 0 ) return;
	return round( $b / $r[0], 1 ) . $r[1];
}

//.. _kv: key : value
function _kv( $a, $del = SEP ) {
	//- $del: デリミタ
	$ret = [];
	foreach ( $a as $k => $v ) {
		if ( $k == '_d' ) {
			$del = $v;
			continue;
		}
		if ( $v == '' ) continue;
		$k = function_exists( '_trep' ) ? _trep( $k ) : _l( $k );
//		$k = _l( $k );
		$ret[] = "<b>$k</b>: $v";
	}
	return implode( $del, $ret ) ;//. print_r( _t( 'pre', $a ), 1 );
}


//.. _csv
//- ダブルクオートは予め処理しておく
function _csv( $fn, $data, $sep = ',' ) {
	$out = [];
	foreach ( $data as $line )
		$out[] = implode( $sep, $line );
	$out = implode( "\n", $out );

	header( "Content-Type: application/octet-stream" );
	header( "Content-Disposition: attachment; filename=$fn" );
	die( L_JA ? mb_convert_encoding( $out, "SJIS", "UTF-8" ) : $out );
}

//.. _download_text
//- tsv/csvでデータをダウンロード
function _download_text( $type, $basename, $data ) {
	if ( $type == 'tsv' ) {
		foreach ( $data as &$line )
			$line = implode( "\t", $line );
		$data = implode( "\n", $data ). "\n";
	} else if ( $type == 'csv' ) {
		foreach ( $data as &$line ) {
			foreach ( $line as &$v ) {
				if ( ! _instr( ',', $v ) && ! _instr( '"', $v ) ) continue;
				$v = '"'. strtr( $v, [ '"' => '""' ] ). '"';
			}
			$line = implode( ',', $line );
		}
		$data = implode( "\n", $data ). "\n";
	} else if ( $type == 'json' ) {
		$data = _to_json( $data );
	}
	$fn = _instr( '.', $basename ) ? $basename : "$basename.$type";
	$type_str = [
		'tsv'	=> 'text/tab-separated-values' ,
		'csv'	=> 'text/csv' ,
		'json'	=> 'application/json' ,
	][ $type ] ?: $type;

	header( "Content-Type: $type_str" );
	header( "Content-Disposition: attachment; filename=$fn" );
	die( L_JA && $type != 'json'
		? mb_convert_encoding( $data, "SJIS", "UTF-8" )
		: $data
	);
}

//.. _group_name
//- 名前文字列を先頭文字列でグループ化
//- 先頭文字列を返す
function _group_name( $names ) {
	$ret = [];
	foreach ( array_reverse( range( 13, 100 ) ) as $num ) {
		$sum = [];
		foreach ( $names as $idx => $name ) {
			$n = substr( $name, 0, $num );
			if ( ! in_array( substr( $n, -1 ), [ '-', ':', ';', ' ', '.' ] ) )
				continue;
			$sum[ $n ][] = $idx;
		}
		foreach ( $sum as $name => $idxs ) {
			if ( count( $idxs ) < 2 ) continue;
			$ret[] = $name;
			foreach ( $idxs as $i ) {
				unset( $names[ $i ] );
			}
		}
		$names = array_filter( $names );
		if ( count( $names ) == 0 ) break;
	}
	sort( $ret );
	return $ret;
//	_die( 'hoge' );
}


//. html文字列を生成する系
//.. _t: タグ
define( 'TAG_REP', [
	'/^#/'					=> 'id:' ,
	'/^\./'					=> 'class:',
	'/^\?/'					=> 'title:',
	'/^st:/'				=> 'style:',
	'/^!/'					=> 'onclick:',
	'/^([a-z\-]+): ?(.*)/'	=> '$1="$2"' ,
]);

function _t( $tag, $str = '' ) {
	$split = preg_split( '/ *\| */', trim( $tag ), 0, PREG_SPLIT_NO_EMPTY );
	return '<'
		. implode( ' ', _reg_rep( $split, TAG_REP ) )
		. ">$str</{$split[0]}>"
	;
}

//.. _e: 空タグ
function _e( $tag ) {
	return '<'
		. implode( ' ',	_reg_rep(
			preg_split( '/ *\| */', trim( $tag ), 0, PREG_SPLIT_NO_EMPTY ) ,
			TAG_REP
		)) . '>'
	;
}

//.. _meta:
function _meta( $name, $cont = false ) {
	$cont = is_array( $cont ) ? implode( ',', $cont ) : $cont;
	return _e( "meta| name:$name| content:$cont" );
}

//.. _a: リンクタグ
function _a( $url, $str, $opt = '' ) {
	// 別窓で開く指定は、$optに入れる '_b'
	if ( is_array( $url ) )
		$url = _local_link( $url );
	return _t( "a| href:$url |$opt", $str );
}

//- 別窓
function _ab( $url, $str = '', $opt = '' ) {
//	if ( $str == '' ) return;
	$url = _local_link( $url );
	return _t( "a| href:$url | target:_blank |$opt", $str );
}

//- アイコン付き
function _ai( $url, $icon, $str = '', $opt = '' ) {
	$url = _local_link( $url );
	return _t( "a| href:$url | $opt", _ic( $icon ) . $str );
}

//- 今のページなら太字、違えばリンク
function _a_flg( $flg, $url, $str, $opt = '' ) {
	$url = _local_link( $url );
	return $flg ? "<b>$str</b>" : _a( $url, $str, $opt );
}

//.. _local_link サイト内リンク
function _local_link( $in ) {
	global $urls;
	if ( $in == '' )
		return '========'; //- 問題対策 _mng-docs.phpでの問題
	if ( is_string( $in ) )
		return $urls[ $in ] ?: $in;
	if ( array_keys( $in ) == [0, 1] ) //- _url関数の短縮
		return _url( $in[0], $in[1] );
	$php = $in[0] ? $in[0] . '.php' : '';
	$sharp = $in['#'] ? '#' . $in['#']: '';
	unset( $in[0], $in['#'] );
	return ( $_SERVER['PHP_SELF'] == $php ? '' : $php )
		.'?'. http_build_query( array_filter( $in ) ) . $sharp;
}

//.. _get_query GET文字列
function _get_query( $array = [] ) {
	return '?'. http_build_query( 
		array_filter( array_merge( $_GET, [ 'lang' => '' ], $array ) )
	);
}

//.. _img
function _img( $s1, $s2 = '' ) {
	$fn = $s2 ?: $s1;
	$fn = is_array( $fn )
		? _local_link( $fn )
		: ( substr( $fn, -2 ) == '.g' ? "img/{$s1}if" : $fn )
	;
//	$d = TEST ? ' decoding="async"' : '';
	$d = ' decoding="async"'; //- decoding属性
	return $s2 ? _e( "img|src:$fn|$s1|$d" ) : "<img src=\"$fn\" $d>" ;
}


//.. _btn
function _btn( $opt, $str ) {
	return _t( "button|$opt", $str );
}

//.. _div
function _div( $opt, $str = '' ) {
	return _t( "div|$opt", $str );
}

//.. _span
function _span( $opt, $str = '' ) {
	return _t( "span|$opt", $str );
}

//.. _p
function _p( $s1, $s2 = '' ) {
	return $s2 ? _t( "p|$s1", $s2 ) : "<p>$s1</p>";
}

//.. _radiobtns: ラジオボタンのセット
//- $opt => [ $name => get用の name, on => 初期選択アイテム ]
//- $btns => [ 'name1' => 'text1', .... ]
function _radiobtns( $opt, $btns ) {
	extract( $opt ); //- $name, $on, $otheropt
	$ret = [];
	foreach ( $btns as $val => $txt ) {
		$i = "rb_{$name}_{$val}";
		$ret[] = _span( '.nw', ''
			. _e( "input |#$i |type:radio |name:$name |value:$val |$otheropt"
				. ( $on == $val ? '|checked' : '' )
			)
			. _t( "label | for:$i", $txt ? _icon_title( $txt ) : $val )
		);
	}
	return implode( ' ', $ret );
}

//.. _input 
function _input( $type, $opt = '', $val = '' ) {
	if ( $type == 'submit' || $type == 'reset' )
		$opt .= '|.submitbtn';
	return _e( "input| type:$type| $opt"
		. ( $val == '' ? '' : '| value:'. strip_tags( $val ) )
	);
}

//.. _chkbox チェックボックス
function _chkbox( $label, $opt, $flg = false ) {
	$id = preg_match( '/#([a-zA-Z0-9_]+)/', $opt, $m );
	$id = $m[1];
	if ( $id == '' ) {
		$id = _cssid();
		$opt = "#$id|$opt";
	}
	return _span( '.nw', 
		_e( "input| type:checkbox| $opt" . ( $flg ? '|checked:checked' : '' ) )
		. _t( "label| for:$id", _l( $label ) )
	);
}

//.. _selopt: ドロップダウンメニュー
//- $opt 
function _selopt( $opt, $ar, $sel = '' ) {
	$ret = '';
	foreach ( $ar as $k => $v ) {
		$s = $k == $sel ? 'selected:selected' : '';
		$ret .= _t( "option | value:$k | $s", $v );
	}
	return _t( 'select |' . $opt, $ret );
}

//.. _ul 箇条書きリスト
//- $num = 0  => いくつあっても全部表示
function _ul( $items, $num = 4 ) {
	$items = array_values( array_filter( (array)$items ) );
	if ( !$items ) return;
	$cnt = count( $items );
	if ( $cnt < $num + 2 || $num === 0 )
		return _t( 'ul', LI. implode( LI, $items ) );
	//- main
	$id = _cssid();
	$n = $cnt - $num;
	$li = _e( 'li| .more hide' );
	return _t( "ul| #ulm_$id", ''
		.LI . implode( LI , array_slice( $items, 0, $num ) )
		.$li. implode( $li, array_slice( $items, $num ) )
	)
	. _btn( "!_limany('$id',1)| #more_$id",
		_fa( 'angle-double-down' ). _term_rep( TERM_MORE_LI, $n )
	)
	. _btn( "!_limany('$id')| #less_$id| .hide" ,
		_fa( 'angle-double-up' ). TERM_LESS_LI
	);
}

//.. _table_2col: 左ヘッダのテーブル
function _table_2col( $cont, $o = [] ) {
	$topth = $opt = $multi_col = '';
	if ( is_array( $o ) )
		extract( $o );
	else
		$opt = $o;
	$ret = '';
	foreach ( (array)$cont as $th => $td ) {
		if ( $td == '' ) continue;
		$th = function_exists( '_trep' ) ? _trep( $th ) : _l( $th );
		if ( $topth || _instr( '[th]', $th ) ) {
			$th = strtr( $th, [ '[th]' => '' ] );
			//- ヘッダ列作成
			if ( is_array( $td ) ) {
				$multi_col = true;
				foreach ( $td as &$d ) {
					$d = function_exists( '_trep' ) ? _trep( $d ) : _l( $d );
				}
				$td = implode( TH, $td );
			}
			$ret .=  TR_TOP.TH. $th. TH. $td;
			$topth = false;
		} else {
			//- データ列
			$ret .= TR.TH. $th. TD. ( is_array( $td )
				? ( $multi_col ? implode( TD, $td ) : _imp2( $td ) )
				: $td
			);
		}
	}
	return _t( "table|$opt", $ret ) ;
}

//.. _table_toph 上ヘッダテーブル
function _table_toph( $toph, $data, $o = [] ) {
	if ( ! $data ) return;
	$opt = '';
	extract( $o );
	$ret = TR_TOP;
	//- top th
	foreach ( $toph as $th )
		$ret .= TH. ( function_exists( '_trep' ) ? _trep( $th ) : _l( $th ) );

	//- data
	foreach ( $data as $row ) {
		$ret .= is_array( $row )
			? TR.TD. implode( TD, $row )
			: TR. _e( 'td|colspan=' . count( $toph ), $row )
		;
	}
	return _t( "table|$opt", $ret );
}

/*
//.. _table_3col: 3カラム以上のテーブル
function _table_3col( $cont, $o = [] ) {
	$topth = $opt = '';
	extract( $o );
	$ret = '';
	foreach ( $cont as $th => $ar ) {
		$td = [];
		foreach ( $ar as $a )
			$td[] = is_array( $a ) ? _imp2( $a ) : $a;
		$th = _l( $th );
		$ret .=  $topth
			 ? TR_TOP . TH . $th . TH . implode( TH, $td )
			 : TR	  . TH . $th . TD . implode( TD, $td )
		;
		$topth = false;
	}
	return _t( "table|$opt", $ret ) ;
}
*/

//.. _xbtn
function _xbtn( $js = '', $o = '' ) {
	return _t( "button| !$js| .xbtn| $o", 'X' );
}


//. sqlite系
//.. _kwprep: 検索キーワードの変換
//- ダメ文字消して、小文字にして、分割して配列にして返す
function _kwprep( $kw ) {
	if ( $kw == '' ) return;
	$kw = strtolower( preg_replace( "/[' ]+/", ' ', $kw ) );

	$f = true;
	$ret  = [];
	foreach( explode( '"', $kw ) as $w ) {
		$w = trim( $w );
		if ( $f ) { //- ""の外
			if ( $w != '' )
				$ret = array_merge( $ret, explode( ' ', $w ) );
		} else { //- ""の中
			$ret[] = $w;
		}
		$f = ! $f;
	}
	foreach ( $ret as $i => $s )
		$ret[$i] = trim( $s );
	return array_filter( $ret );
}

//.. _kw2sql
function _kw2sql( $kw, $db ) {
	$col_kw = _search_cols( $db );
	$q = [];
	foreach ( (array)_kwprep( $kw ) as $w )
		$q[] = _like( $col_kw, $w );
	return $q;
}

//.. _search_cols:
function _search_cols( $db ) {
	$cols = is_array( $db ) ? $db : [
		'pdb'	=> [ 'search_kw', 'title', 'search_auth' ] ,
		'emdb'	=> [ 'search_words' ]  ,
		'sas'	=> [ 'kw' ] ,
		'chem'	=> [ 'kw' ] ,
		'dbid'  => [ 'title', 'db_id' ] ,
		'met'   => [ 'key', 'name', 'for' ] ,
		'taxo'	=> [ 'name', 'kw' ],
		'pap'	=> [ 'search_kw' ] ,
		'doc'	=> [ 'kw' ] ,
	][ $db ];
	if ( count( $cols ) == 1 )
		return $cols[0];
	foreach ( $cols as &$c )
		$c = "ifnull($c, '')";
	return implode( " || '|' || ", $cols );
}

//.. _like like検索
function _like( $col, $val, $match = false) {
//- [ ] は、の前後は空白に一致するように（完全一致風の検索)
//- match = trueでも同様
	return "$col LIKE ". _quote( '%'
		. _reg_rep( $match ? " $val " : $val , [
			'/^\[/' => ' ' ,
			'/\]$/' => ' ' ,
		]). '%'
	);
}


//. サブデータ
define( 'SUBDATA_TYPE', [
	'trep' => _ej( 'trep|en', 'trep|ja' )
]);

//.. _subdata
function _subdata( $type, $name ) {
	$t = $type == 'trep' ? 'trep|'. _ej( 'en', 'ja' ): $type;
	if ( !TEST ) return json_decode( _ezsqlite([
		'dbname' => 'subdata' ,
		'select' => 'data' ,
		'where'  => [ 'key', ( SUBDATA_TYPE[ $type ] ?: $type ). "|$name" ]
	]), true );
	return SUBDATA_TSV[ $type ][ $name ];
}

//.. _add_lang 辞書に追加
function _add_lang( $in ) {
	global $_e2j_dic;
	if ( L_EN ) return;
	if ( is_string( $in ) ) {
		if ( $_e2j_dic[ 'loaded' ][ $in ] ) return;
		$_e2j_dic[ 'loaded' ][ $in ] = true;
	}
	$_e2j_dic = array_merge(
		(array)$_e2j_dic,
		array_change_key_case( is_array( $in )
			? $in
			: _subdata( 'e2j', $in )
		)
	);
}

//.. _url: 各種URLを返す
function _url( $type, $i = '', $i2 = '' ) {
	global $urls, $id;
	return strtr( $urls[ $type ], [
		'[id]'		=> $i ?: $id ,
		'[id2]'		=> $i2 
	]);
}

//.. _add_url
function _add_url( $in ) {
	global $urls;
	if ( $urls['loaded'][ $in ] ) return;
	$urls['loaded'][ $in ] = true;
	$urls = array_merge(
		$urls ,
		is_array( $in ) ? $in : _subdata( 'url', $in )
	);
}

//.. _fn: 各種ファイル名を返す
function _fn( $type, $i = '', $s1 = '', $s2 = '' ) {
	global $id, $_filenames;
	return strtr( $_filenames[ $type ], [
		'<id>'		=> $i ?: $id ,
		'<s1>'		=> $s1 ,
		'<s2>'		=> $s2 ,
		'<data>'	=> DN_DATA ,
		'<fdata>'	=> DN_FDATA ,
		'<prep>'	=> DN_PREP ,
		'<omokage>'	=> defined( 'DN_OMO' ) ? DN_OMO : '../omokage'
	]);
}

//.. _add_fn
function _add_fn( $in ) {
	global $_filenames;
	$_filenames = array_merge(
		$_filenames ,
		is_array( $in ) ? $in :_subdata( 'fn', $in )
	);
}

//..  _url_file_ex: _url() に相当するローカルファイルが有るか
function _url_file_ex( $type, $a = '', $b = '' ) {
	$fn	= _url( $type, $a, $b );
	return file_exists( $fn ) ? $fn : '' ;
}

//.. _chemform2html: 化学式をかっこ良くする
function _chemform2html( $in ) {
	//- SO4、PO4、NH2など対策 （現在、中途半端）
	$in = preg_replace( 
		[ '/^(O[34]) ([P-Z]+) (-[0-9]+)$/', '/^(H[234]) (N)(| \+1)$/' ],
		'$2 $1 $3',
		$in 
	);

	$ret = '';
	foreach ( (array)explode( ' ', $in ) as $l ) {
		$t = substr( $l, -1 );
		if ( $t == '-' || $t == '+' ) {
			//- 電荷？（上付きに）
			$num = abs( $l );
			$ret .= '<sup>' . $t . ( $num > 1 ? $num : '' ) . '</sup>' ;
		} else {
			//- 元素名+数（下付に）
			preg_match( '/([A-Za-z]+)([0-9]*)/', $l, $a );
			$ret .= ''
				. ucfirst( strtolower( $a[1] ) )			//- 元素名
				. ( $a[2] > 1 ? "<sub>{$a[2]}</sub>" : '' )	//- 個数
			;
		}
	}
	return $ret;
}

//.. _keywords: キーワードリスト
//- キーワードリストを受け取り(配列、文字列)、検索リンクとして返す
function _keywords( $kw ) {
	if ( is_string( $kw ) )
		$kw = explode( ',', strtr( $kw, [ ';' => ',' ] ) );
	$ret = [];
	$done = [];
	foreach( array_filter( (array)$kw ) as $term ) {
		$term = trim( $term, ", \n\r\t" );

		$lower = strtolower( $term );
		if ( in_array( $lower, $done ) ) continue; //- 重複を避ける
		$done[] = $lower;

		$ret[] = _pop_ajax(
			IC_KEY. ( _obj('wikipe')->term($term)->icon() )
			. $term. _ifnn( _obj('wikipe')->e2j(), ' (\1)' ) ,
			[ 'mode' => 'kw', 'kw' => $term ]
		);
	}
	return _imp2( $ret );
}

//.. _pubmed_abst
function _pubmed_abst( $j ) {
	$o = [];
	foreach ( $j as $k => $v ) {
		if ( $k == 'Copyright' ) continue;
		$o[ is_numeric( $k ) ? "#notag$k2" : $k ] = $v;
	}
	return _long( _kv( $o ), 200 );
}

//.. _term_rep
function _term_rep( $term, $r1, $r2 = '', $r3 = '', $r4 = '' ) {
	return strtr( $term, [
		'_1_' => $r1 ,
		'_2_' => $r2 ,
		'_3_' => $r3 ,
		'_4_' => $r4 ,
		'_'   => ' ' ,
	]);
}

//.. _define_term
function _define_term( $in ) {
	$key = $en = '';
//	foreach ( explode( "\n", strtr( $in, [ '"' => '\"' ] ) ) as $line ) {
	foreach ( explode( "\n", $in ) as $line ) {
		$trim = trim( $line );
		if ( ! $trim || substr( $trim, 0, 2 ) == '//' ) continue;
		//- キー 
		if ( substr( $line, 0, 1 ) != "\t" ) {
			$key = $trim;
			continue;
		}
		if ( ! $key ) continue;
		if ( ! $en ) {
			$en = $trim;
			continue;
		}
		if ( defined( $key ) )
			_testinfo( $key, '_define_term, 2nd def.' );
		else
			define( $key, L_EN ? $en : $trim );

		$key = $en = '';
	}
}

//.. _ym_annot_chem
function _ym_annot_chem( $chem_id ) {
	$ret = (array)json_decode( _ezsqlite([
		'dbname' => 'chem' ,
		'where'  => [ 'id', $chem_id ] ,
		'select' => 'json'
	]))->comment;
	if ( ! $ret ) return;
	_add_lang( 'chem_info' );
	if ( L_JA ) foreach ( $ret as $k => $v ) {
		$ret[ $k ] = _l( $v );
	}
	return _imp( $ret ). LABEL_YM_ANNOT;
}
//.. _nikkaji_name
function _nikkaji_name( $chem_id ){
	if ( L_EN ) return;
	_add_url( 'quick-chem' );
	list( $id, $name ) = (array)json_decode( _ezsqlite([
		'dbname' => 'chem' ,
		'where'  => [ 'id', $chem_id ] ,
		'select' => 'json'
	]))->nikkaji;
	return $name ? _ab( [ 'Nikkaji-j', $id ], $name ) : '';
}


//. ページ構成要素

//.. _die: 文字列を吐いて死ぬ
function _die( $a ) {
	if ( is_array( $a ) || is_object( $a ) )
		$a = print_r( $a, 1 );
	die( _p( '強制終了' ) . _t( 'pre', $a ) );
}

//.. _redirect: リダイレクト
function _redirect( $u ) {
	header( "HTTP/1.1 303 See Other" ); 
	header( "Location: $u" );
	die();
}

//.. _robokill: ロボットからだったら503を吐いて死ぬ
function _robokill( $num = 10 ) {
	if ( $num < 10 )
		if ( $num < substr( microtime(), 4, 1 ) ) return;

	$u = $_SERVER[ 'HTTP_USER_AGENT' ];
	$r = false;
	if ( _instr( 'spider', $u ) || _instr( 'robot', $u ) || _instr( '.htm', $u ) ) {
//		if ( _instr( 'baidu', $u )
//			or _instr( 'soso', $u )
//			or _instr( 'sogou', $u )
//			or _instr( 'naver', $u )
//		) $r = 1;
		$r = true;
	}

	if ( $r and substr( microtime(), 4, 1 ) < $num ) {
		header("HTTP/1.1 503 Service Temporarily Unavailable");
		header("Status: 503 Service Temporarily Unavailable");
		header("Retry-After: 86400");
		header("Connection: Close");
		die( "503 Service Temporarily Unavailable" );
	}

}

//. function - 文字列

//.. _sharp 数値なら # を添える
function _sharp( $s ) {
	return ctype_digit( $s ) ? "#$s" : $s;
}

//.. _kakko カッコをつける
function _kakko( $s ) {
	return $s == null ? null : ' ('. _l( $s ). ')';
}

//.. _datestr
function _datestr( $in, $lang = '') {
	if ( $in == '' ) return;
	$s = strtotime( $in ) ;
	if ( $s == 0 )
		return $in; //. (TEST ? _span( '.red',' #non-date str#') : '');
	$lang = $lang ?: _ej( 'e', 'j' );
	return date( $lang == 'e' ? 'M j, Y' : 'Y年n月j日', $s );
}


//.. _eqstr:
//- 同じ文字列 case鈍感、trimあり
function _eqstr( $s1, $s2 ) {
	return (
		strtolower( trim( $s1 ) )
		==
		strtolower( trim( $s2 ) )
	);
}

//.. _breakable: word breakをさしこむ
function _breakable( $str ) {
//	if ( !is_str( $str ) ) return $str;
//	return preg_replace( '/([}\.,\)])/', '$1<wbr>', $str );
	if ( strip_tags( $str ) != $str ) return $str; //- もうタグが付いていたらやらない
	return preg_replace( ['/\b/', '/_/'], ['<wbr>', '_<wbr>'], $str );
}

//. ファイル操作系
//.. _tsv_load:
function _tsv_load( $fn ) {
	foreach ( _file( $fn ) as $l ) {
		$a = explode( "\t", $l );
		if ( $a[ 1 ] != '' )
			$ret[ $a[0] ] = $a[1];
	}
	return $ret;
}

//.. _mkdir
function _mkdir( $d ) {
	if ( is_dir( $d ) ) return;
	mkdir( $d );
}

//. simpleFW
//.. _cssid
//- cssに使うユニークな数字を返す
function _cssid() {
	global $_cssid;
	++ $_cssid;
	return ( AJAX ? 'a' : 'i' )
		. ( defined( 'CSSID_PREFIX' ) ? CSSID_PREFIX : '' ) 
		. $_cssid
	;
}

//.. _icon_title: タイトルをアイコン付きにする
define( 'ICONS_FOR_H1', [
	'Open data'					=> 'open' ,
	'Basic information'			=> 'entry' ,
	'strvis'					=> 'view' ,
	'Sample'					=> 'sample' ,
	'Sample preparation' 		=> 'sample' ,
	'Assembly'					=> 'asb' ,
	'Map'						=> 'map' ,
	'Components'				=> 'components' ,
	'Sample components'			=> 'components' ,
	'downlink'					=> 'download' ,
	'Downloads'					=> 'download' ,
	'External links'			=> 'link' ,

	'About this page'			=> 'help' ,
	'Authors'					=> 'auth' ,
	'Contact author'			=> 'auth' ,
	'Citation'					=> 'article' , 
	'Keywords'					=> 'lens' ,
	'Map data'					=> 'map' ,
	'Imaging'					=> 'em' ,
	'Electron microscopy imaging' => 'em' ,
	'Electron microscopy' 		=> 'em' ,
	'Processing'				=> 'processing' ,
	'Computation'				=> 'processing' ,
	'Image processing'			=> 'processing' ,
	'Download'					=> 'download' ,
	'Links'						=> 'link' ,
]);

function _icon_title( $in, $icon = '' ) {
//- 	$in: "タイトル|サブタイトル|タグ名"
//- 	タグ名は_trepに渡す

	//- new type, font-awesome利用
	if ( is_array( $in ) )
		return _fa( $in[0] ). _l( $in[1] );

	list( $title, $sub, $tag ) = explode( '|', $in, 3 );
	$title = trim( $title, ' ~' );
	return ( $icon != ''
			? _ic( $icon )
			: ( ICONS_FOR_H1[ $title ] ? _ic( ICONS_FOR_H1[ $title ] ) : '' )
		)
		. (	function_exists( '_trep' ) ? _trep( $title, [ 'tag' => $tag ]) : _l( $title ) )
		. ( $sub ? ' '. trim( $sub ) : '' )
	;
}

//.. _simple_tabs
/*
引数は任意個、あるいは一つの配列
	(文字列)'説明' , タブの一番左に書く説明
	(#で始まる文字列)'#hoge' , 独自ID
	[
		'id' => 'id', なくてもいい
		'active' => true
		'tab' => tab
		'div' => contents
		'js' => スクリプト
	]
*/
function _simple_tabs() {
	$ar = func_get_args();
	if ( count( $ar ) == 1 ) //- 一つの配列で受け取る場合
		$ar = $ar[0];

	$gid = ''; //- 全体のID
	$tabstr = ''; //- タブの前に書く文字

	//- まずIDとかを拾っておく
	$flg = false;
	$first = -1;
	foreach ( $ar as $num => $a ) {
		if ( is_string( $a ) ) {
			if ( substr( $a, 0, 1 ) == '#' ) {
				//- ID取得
				$gid = substr( $a, 1 );
			} else {
				//- タブの前に書くこと
				$tabstr .= _l( $a );
			}
		} else {
			if ( $a[ 'active' ] ) $flg = true;
			if ( $first == -1 ) $first = $num; //- 最初のタブ
		}
	}
	//- どのタブもアクティブラグがなければ、最初のタブをアクティブに
	if ( ! $flg )
		$ar[ $first ][ 'active' ] = true;

	//- タブ前文字
	if ( $tabstr != '' )
		$tabstr = _span( '.tabstr', "$tabstr: " );

	//- ID決定
	if ( $gid == '' )
		$gid = _cssid();

	$tabs = '';
	$divs = '';
	foreach ( $ar as $a ) {
		if ( is_string( $a ) ) continue;
		$id = $active = $tab = $div = $js = '';
		extract( $a ); //- $id, $active, $tab, $div, $js
		if ( $id == '' )
			$id = _cssid();
		$d = $active ? '| disabled' : '';

		//- js: 開いた時に実行するスクリプト
		if ( $js != '' )
			$js = ",'" . strtr( htmlspecialchars( $js ), [ "'" => "\\'" ] ) . "'";

		$tabs .= _btn(
			"#tabbtn_{$gid}_{$id} |type:button | .tabbtn tabbtn_$gid $d| !_tab.s('$gid','$id'$js)"
			. '| autocomplete="off"'
			,
			_icon_title( $tab )
		);

		$cls = $active ? '' : 'hide';
		$divs .= _div( "#tabdiv_{$gid}_{$id} | .tabdiv tabdiv_{$gid} $cls", $div );
	}
	return _div( '', ''
//		. _t( 'p|.tabp_pre hide', $tabstr )
		. _p( '.tabp', $tabstr . _span('.wrap hide', BR) . $tabs )
		. $divs
	);
}

//.. _simple_table
function _simple_table( $ar ) {
	if ( ! is_array( $ar ) ) return $ar;
	$s = '';
	foreach ( $ar as $key => $val ) {
		if ( $key == '' || $val == '' ) continue;
		$s .= TR.TH. _icon_title( $key ) .TD. $val;
	}
	return $s == '' ? '' : _t( 'table | .maintable', $s );
}

//.. _more
function _more( $cont, $opt = [] ) {
	if ( !$cont ) return;
	//- defo
	$btn  = _fa( 'angle-double-down' ). _l( 'Details' );
	$btn2 = _fa( 'angle-double-up' ). _l( 'Hide details' );
	$type = 'div';

	extract( $opt ); //- $id $btn $btn2 $type
	$id ?: $id = _cssid();
	return ''
		. _btn( "!_more('$id') | #moreb_$id", $btn )
		. _btn( "!_more('$id',1) | #lessb_$id | .hide", $btn2 )
		. _t( "$type | #more_$id | .hide", $cont );
	;
}

//.. _long
// 'abcd', 'efgh'
// 'abcd', 4
function _long( $val, $len = '' ) {
	if ( is_array( $val ) ) {
		$len = $len ?: 3;
		//- 配列で受け取る
		$long = _imp2( $val );
		if ( count( $val ) < $len + 2 )
			return  $long;
		$short = _imp2( array_slice( $val, 0, $len ) );
	} else {
		if ( _instr( 'id="short_', $val ) ) return $val;
		//- 何番目の文字以降を隠すか指定
		$len = $len ?: 100;
		$val_notag = strip_tags( $val );
		if ( strlen( $val_notag ) < $len * 1.1 )
			return $val;
		$long = $val;
		$short = preg_replace( '/[^\. ;:_\]\)\-]+$/', '', substr( $val_notag, 0, $len ) );
	}
	$id = _cssid();
	return ''
		. _span( "#short_$id" ,
			$short. ' ...'. _btn( "!_long('$id')", _fa('angle-double-right') )
		)
		. _span( "#long_$id| .hide" ,
			$long. _btn( "!_long('$id',1)", _fa('angle-double-left') )
		)
	;
}

//.. _pop
//- 内容は data-pop内に入る
function _pop( $btn_str, $div_cont, $opt = [] ) {
	//- optで受け取る値のデフォルト
	$type = 'span' ;
	$trgopt = '.poptrg' ;
	$url = $js = $pre = '';
	extract( $opt );

	$o = '!_pop.up(this)'
		. _atr_data( 'pop', $div_cont ?: LOADING )
		. _atr_data( 'pre', $pre )
		. _atr_data( 'url', $url )
		. _atr_data( 'js' , $js )
		. " | $trgopt"
	;
	return $type == 'img'
		? _img( $o, $btn_str )
		: _t( "$type | $o", $btn_str )
	;
}

//.. _pop_ajax
function _pop_ajax( $str, $ar ) {
	if ( !$ar[0] )
		$ar['0'] = 'ajax';
	if ( $ar[0] == '?' )
		$ar['0'] = '';
	if ( !$ar['ajax'] )
		$ar['ajax'] = 1;
	$pre = $ar['pre'];
	unset( $ar['pre'] );
	return _pop( $str, '', [ 'url' => _local_link( $ar ), 'pre' => $pre ] );
}

//.. _idinput
function _idinput( $id, $opt = [] ) {
	$btnlabel = _l( 'Submit' );
	$size = 40;
	$name = 'id';
	extract( $opt ); //- $btnlabel, $size, $action, $posttext, $acomp

	return 	_t(
		"form | #id_form | method:get" . _attr( 'action', $action )
		, ''
		. _input( 'search', "name:$name | #idbox"
			. _attr( 'size', $size )

			//- ID初期値
			. _attr( 'value', strip_tags( $id ) )

			//- 自動補完
			. ( $acomp ? '|.acomp|list:acomp_'. $acomp : ''  )
		)
		. ( ! defined( 'IMG_MODE' ) ? '' :
			_input( 'hidden', 'name:img_mode', IMG_MODE )
		)
		. _input( 'submit', '',  $btnlabel )
	)
	. $posttext
	. _div( '#ent_info', '' )
	;
}

//.. _inputbox
function _inpbox( $name, $val, $o = [] ) {
	$cls = 'inpbox';
	extract( $o ); //- $acomp, $cls, $idbox;
	$acomp = $acomp ? "acomp | list:acomp_$acomp": '';
	return _input(
		'search'
		,
		"name:$name| .$cls $acomp|"
		. ( $idbox ? '|#idbox' : '' )
		. _attr( 'value', $val ) 
	);
}

//.. _print_r
function _print_r( $data ) {
	return _t( 'pre | .simple_border', print_r( $data, true ) );
}

//.. _atr_data
//- オブジェクト、配列はここでJSONにする
function _atr_data( $k, $v ) {
	return $v == ''
		? ''
		: "|data-$k=\"" . htmlspecialchars(
			is_string( $v ) ? $v : json_encode( $v )
	) . '"';
}

//.. _atr_js
function _atr_js( $v ) {
	return $v == ''
		? ''
		: "|!" . htmlspecialchars( $v ) . '"'
	;
}
//.. _attr
function _attr( $k, $v ) {
	return $v == '' ? '' : "|$k=\"" . htmlspecialchars( $v ) . '"';
}

//. viewer系
//.. _mov_remocon: ムービーリモコン
function _mov_remocon( $child = false ) {
	$js = $child
		? '!window.opener._pmov'
		: '!_pmov'
	;
//	die( $js );
	return [
		_l( 'Size' ). ': '
		. _btn( "$js.size(-50)", '<' )
		. _sizebtn( 'ss', "| $js.size(0)" )
		. _sizebtn( 's' , "| $js.size(1)" )
		. _sizebtn( 'm' , "| $js.size(2)" )
		. _sizebtn( 'l' , "| $js.size(3)" )
		. _sizebtn( 'll', "| $js.size(4)" )
		. _btn( "$js.size(50)", '>' )
	,
		_btn( "$js.tile()" , _l( 'tile' ) )
	,
		_btn( "$js.play(1)", _ic( 'play' ) . _l( 'Play' ) ) .
		_btn( "$js.play()" , _ic( 'pause' ) . _l( 'Pause' ) )
	,
		_l( 'Orientation' ) . ':'
		. _t( 'table | .noborder', TR
			.TD
			.TD. _btn( "$js.ori2('top')"    , _img( 'top.g' )     )
			.TD
		.TR
			.TD. _btn( "$js.ori2('left')"   , _img( 'left.g' )    )
			.TD. _btn( "$js.ori2('front')"  , _img( 'front.g' )   )
			.TD. _btn( "$js.ori2('right')"  , _img( 'right.g' )   )
		.TR
			.TD. _btn( "$js.ori2('back')"   , _img( 'back.g' )    )
			.TD. _btn( "$js.ori2('bottom')" , _img( 'bottom.g' )  )
			.TD. _btn( "$js.ori2('cut')"    , _img( 'cut.g' )     )
		)
	];
}

//.. _viewer_selector
function _viewer_selector( $type = 'mol' ) {
	return ''
		. ( $type == 'mol' ? _l( 'Molecule' ) : _l( 'EM map' ) )
		. ': '
		. _selopt( ".menu_viewer_$type| onchange:_vw.select_defvw('$type',this)",
			$type == 'mol' ? [
				'molmil' => 'Molmil' ,
				'jmol'   => 'Jmol/JSmol'
			]: [
				'sview'  => 'SurfView' ,
				'molmil' => 'Molmil' ,
				'jmol'   => 'Jmol/JSmol' ,
			]
		)
	;
}

//.. _btn_popviewer: ビューアポップアップボタン
function _btn_popviewer( $did, $param = [] ) {
	//- param: jmol/molmil [ cmd, param ], btn_label, ...
	$jmol = $molmil = [];
	$btn_type = $btn_cls = $btn_pop = '';
	$btn_label = VW_BTN_LABEL;
	extract( $param );
	$mapflg = substr( $did, 0, 1 ) == 'e' ? ',map:1' : '';
	return _t(
		( $btn_type ?: 'button' )
		. "|!_vw.open('$did',{obj:this$mapflg})" 
		. ( $btn_cls ? "|.$btn_cls" : '' )
		. "|title:". ( $btn_pop ?: TERM_POP_VIEWER )
		. ( $jmol
			? _atr_data( 'jmol'   , [ 'cmd' => $jmol[0]   , 'param' => $jmol[1]   ] )
			: ''
		)
		. ( $molmil
			? _atr_data( 'molmil' , [ 'cmd' => $molmil[0] , 'param' => $molmil[1] ] )
			: ''
		) ,
		$btn_label
	);
}

//.. _btn_popmov: ムービーポップアップボタン
function _btn_popmov( $id ) {
	return _btn( "!_pmov.open('$id')", _ic( 'play' ). _l( 'Movie' ) );
}

//.. _jmolobj

//- jsオブジェクト文字列を作って返す
function _jmolobj( $a ) {
	extract( $a ); //- $hq, $size, $use, $init, $jmolid, $autostart, $db, $id
	$q = $hq
		? 'set antialiasDisplay ON; set antialiasTranslucent ON;set ribbonBorder ON;'
		: 'set antialiasDisplay OFF; set antialiasTranslucent OFF;set ribbonBorder OFF;'
	;
	$size = $size ?: 250;
	$jmolid = $jmolid ?: '0';

	$obj = json_encode([
		'width'		=> $size ,
		'height'	=> $size ,
		'color'		=> 'white' ,
		'use'		=> $use ?: 'JAVA HTML5' ,
		'isSigned'	=> true ,
		'jarFile'	=> 'JmolAppletSigned.jar' ,
		'j2sPath'	=> JMOLPATH . '/j2s' ,
		'jarPath'	=> JMOLPATH . '/java' ,
		'serverURL' => JMOLPATH . '/php/jsmol.php' ,


		'script'	=> ''
			. 'set ambientPercent 20; set diffusePercent 70;'
			. 'set specular ON; set specularPower 80; set specularExponent 5;'
			. 'set specularPercent 70;'
			. $q
			. 'set MessageCallback "_jmolmsg";'
			. 'set languageTranslation OFF;'
			. ( $id != ''
				? _jmolloadcmd( $db, $id )
				: ''
			)
			. $init
	]);

	if ( $autostart )
		return _t( 'script',
			'Jmol._alertNoBinary = false;' .
			"Jmol.getApplet('jmol$jmolid', $obj);" 
		);
	else
		return 
			'Jmol.setDocument(0);' .
			'Jmol._alertNoBinary = false;' .
			"Jmol.getApplet('jmol$jmolid', $obj);"
		;
}

//.. _jmol_loadcmd
define( 'INIT_STYLE_CHAIN',''
	. 'define _nonpoly ligand or solvent or ((dna or rna) and hetero);'
	. 'define _carbon_etc (carbon | (*.P & ! ligand) | (backbone & (dna|rna)) );'
	. "select !unk; cartoon ONLY; "
	. "select connected(0,0) and (!hetero);cpk 70%; backbone 200;"
	. 'select (unk and !sidechain); wireframe 0.3;cpk 50%; backbone 200; color chain;'
	. "select !unk; color chain; color (! _carbon_etc) CPK; "
	. 'select _nonpoly; wireframe 0.25; spacefill 33%; color CPK; '
	. 'hide water;'
	. 'select all;'
);

define( 'INIT_STYLE_MONO',
	strtr( INIT_STYLE_CHAIN, [ 'color chain;' => 'color monomer;' ] )
);

function _jmolloadcmd( $db, $id, $opt = [] )  {
	return implode( ';', _jmol_params( $db, $id, $opt ) );
}

//.. _jmol_params
function _jmol_params( $db, $id, $opt = [] ) {
	if ( $db == '' ) return;
	$d = URL_DATA;
	$zs = 'set zshade on; set zshadepower 1;';

	//... chem
	if ( $db == 'chem' ) {
		return [
			'load'	=> "load \"$d/chem/cif/$id.cif.gz\"" ,
			'init'	=> 'select all; wireframe 0.25; spacefill 33%; color CPK; rotate best;$zs'
		];
	}

	//... EMDB
	if ( $db == 'emdb' ) {
		$dn = "emdb/media/$id/ym";
		$insideout = file_exists( DN_DATA . "/$dn/insideout1" ) ? 'insideout' : '';
		return [
			'load' 	=> "load \"$d/$dn/pg1.pdb\"" ,
			'init'	=> "isosurface s1 $insideout file \"$d/$dn/o1.zip|o1.jvxl\";"
				. " isosurface s1 OPAQUE [x77ee77];$zs"
		];
	}

	//... VQ
	if ( $db == 'vq' ) {
		//- vq idは ida形式 1oel-1, e1003, s100
		$f = substr( $id, 0, 1 );
		if ( $f == 'e' ) {
			$id = _numonly( $id );
			$u1 = URL_DATA. "/emdb/vq/$id-30.pdb";
			$u2 = URL_DATA. "/emdb/vq/$id-50.pdb";
		} else if ( $f == 's' ) {
			$id = _numonly( $id );
			$u1 = URL_DATA. "/sas/vq/$id-vq30.pdb";
			$u2 = URL_DATA. "/sas/vq/$id-vq50.pdb";
		} else {
			$u1 = URL_DATA. "/pdb/vq/$id-30.pdb";
			$u2 = URL_DATA. "/pdb/vq/$id-50.pdb";
		}
		return [
			'load' => "load append \"$u1\";load append \"$u2\";" ,
			'init' => $zs
			
		];
	}


	//... SASBDB
	if ( $db == 'sasbdb-model' ) {
		$j = _json_load2( _fn( 'sas_json', _sas_info( 'mid2id', $id ) ) )->sas_model;

		//- ダミー原子なら CPK
		$init = 'spacefill only; color CPK;';;
		foreach ( $j as $c ) {
			if ( $c->id != $id ) continue;
			if ( $c->type_of_model == 'atomic' )
				$init = INIT_STYLE_CHAIN;
			break;
		}
		$u = URL_DATA . "/sas/splitcif/$id.cif";
		return [
			'load' 	=> "load \"$u\"" ,
			'init'	=> $init . $zs
		];
	}

	if ( $db == 'sasbdb' ) {
		$j = _json_load2( _fn( 'sas_json', $id ) )->sas_model[0];
		$mid = $j->id;
		$init = ( $j->type_of_model == 'atomic' )
			? INIT_STYLE_CHAIN
			: 'spacefill only; color CPK;'
		;
		$u = URL_DATA . "/sas/splitcif/$mid.cif";
		return [
			'load' 	=> "load \"$u\"" ,
			'init'	=> $init . $zs
		];
	}

	//... PDB

	if ( $opt[ 'csmodel' ] ) {
		return [
			'load' => "load \"csmodel.php?id=$id" . _ifnn( $opt[ 'asb' ], '-\1' ) . '"',
			'init' => $init . '; trace 1000 only;  color chain; model all;' .  $zs
		];
	}


	$filt = [];
	if ( $opt[ 'asb' ] != '' )
		$filt[] = 'biomolecule ' . $opt[ 'asb' ];
	if ( $opt[ 'bb' ] )
		$filt[] = '*.CA,*.P';
	$filt = count( $filt ) == 0
		? ''
		: ' filter "' . implode( ',', $filt ) . '"'
	;

	if ( $db == 'pdb-mono' )
		$init = INIT_STYLE_MONO;
	else if ( $db == 'pdb-chain' )
		$init = INIT_STYLE_CHAIN;
	else
		$init = _inlist( $id, 'large' ) || _inlist( $id, 'multic' )
			? INIT_STYLE_CHAIN
			: INIT_STYLE_MONO
		;

	return [
		'load' 	=> "load \"" . _url( 'mmcif', $id ) . "\"" ,
		'init'	=> $init . $zs
	];

}

//. リンク系

//.. _dblink
function _dblink( $db, $id, $opt = [] ) {
	$icon = IC_L;
	$url = '';
	extract( $opt ); //- $icon, $url
	$id = trim( $id );
	$text = "$icon$db: $id";
	$url = $url ?: _url( strtolower( $db ), $id );
	return _span( '.nw', $url ? _ab( $url, $text ) : $text );
}

//.. _gmfit
function _gmfit( $ida1, $ida2, $title = '', $opt=[] ) {
	$a = [];
	foreach ( [ $ida1, $ida2 ] as $n => $i ) {
		$f = substr( $i, 0, 1 );
		if ( $f == '_' ) {
			//- ユーザーデータ
			$a[ $n ] = $i;
		} else if ( $f == 'e' ) {
			//- EMDBデータ
			$a[ $n ] = 'emdb_' . _numonly( $i );
		} else if ( $f == 's' ) {
			//- SASBDBデータ
			$a[ $n ] = $i;
		} else {
			//- PDBデータ
			$a[ $n ] = 'pdb_'
				. ( substr( $i, -1 ) == 'd' 
					? substr( $i, 0, 4 )  	//- 登録モデル
					: $i					//- assembly
				)
			;
		}
	}
	$u = ( TESTSV
		? 'http://pdbj.org'
		: 'http://' . $_SERVER[ 'SERVER_NAME' ]
	) . "/gmfit/cgi-bin/pairgmfit.cgi?idref={$a[0]}&idtar={$a[1]}";
	$ic = $opt[ 'noicon' ] ? '': _ic( 'gmfit' );
	return _ab( $u, $ic . ( $title ?: TERM_GMFIT_LINK ?: 'gmfit' ), '.nw' );
}

//.. _fullurl
//- emnnaviの相対 urlをフルのURLにする
function _fullurl( $u ) {
	if ( $u == 'omo-search.php' )
		return 'https://pdbj.org/omokage/' ;
	return _instr( 'http', $u ) || substr( $u, 0, 2 ) == '//'
		? $u
		: 'https://' . $_SERVER['HTTP_HOST'] . "/emnavi/$u"
	;
}

//.. _links
function _links( $a ) {
	extract( $a ); //- $db, $id, $pages, $ida
	$pages = $pages ?: 'ym omos emn json mine' ;
	$ret = [];

	//- SASBDBモデルの場合は、sas-が必要
	if ( ( $db == 'sasbdb' || $db == 'sasbdb-model' ) && ctype_digit( $id ) ) {
		$id = "sas-$id";
//		$ret[] = json_encode( $a );
	}

	foreach ([
		//- urlコード, アイコン名、タイトル
		'quick'	=> [ 'quick',	'q', 		'Quick' ] ,
		'ym'	=> [ 'ym',		'miru',		_l( 'Yorodumi' ) ] ,
		'emn'	=> [ 'det',		'emn',		'EM Navigator' ] ,
		'json'	=> [ 'json',	'json', 	'JSON view' ] ,
		'omos'	=> [ 'omos',	'omokage',	_l( 'Omokage search' ) ] ,
		'omov'	=> [ 'omov',	'omokage',	_l( 'Omokage comparison' ) ] ,
		'mine'	=> [ 'mine',	'pdbj',   	'PDBj mine' ],
		'sas'	=> [ 'sasbdb',	'',   		_ej( 'SASBDB detail page', 'SASBDBの詳細ページ' ) ]
	] as $name => $a ) {
		if ( ! _instr( $name, $pages ) ) continue;

		//- json はもうちょっと公開待ち
		if ( ! TEST && $name == 'json' ) continue;

		//- 
		$i = $id;
		if ( $name == 'omos' )
			$i = $ida ?: $id;

		//- EMN
		if ( $name == 'emn' ) {
			if ( $db != 'emdb' && ! _inlist( $id, 'epdb' ) )
				continue;
		}

		//- SASBDB
		if ( $name == 'sas' ) {
			if ( $db != 'sasbdb-model' && $db != 'sasbdb' )
				continue;
			$i = _sas_info( 'mid2id', $id ) ?: $id;
		}

		//- mine
		if ( $name == 'mine' && $db != 'pdb' ) continue;

		$ret[] = _ab( _url( $a[0], $i ), _ic( $a[1] ) . $a[2], '.nw' );
	}
	return _imp( $ret );
}

//.. _authlist 登録者の一覧を検索へのリンクへ
function _authlist( $auth, $em_mode = false ) {
//	$u = IMG_MODE == 'em' ? 'esearch' : 'ysearch';
	$y = 'ysearch.php';
	$e = 'esearch.php';

	//- 検索ページ由来なら、そのページで探す
	if ( in_array( $_SERVER[ "SCRIPT_NAME" ], [ $y, $e ] ) ) {
		$u = '';
	} else {
		$u = $em_mode ? $e : $y;
	}
	$ret = [];
	foreach ( (array)$auth as $n ) {
		$n = trim( $n );
		if ( $n == '' ) continue;
		$ret[] = _ab( "$u?auth=" . urlencode( '"' . $n . '"' ), $n );
	}
	return _long( $ret, 10 );
}

//. misc
//.. _fa: font awesome
function _fa( $name, $cls = 'dark large' ) {
	return "<i class=\"fa fa-$name $cls\"></i>";
}

//.. _pap_item 論文リストのアイテム
function _pap_item( $a, $opt = [] ) {
	global $_simple;
	extract( $a ); //-  $pmid $journal, $date, $data, $if
	$u = ( _instr( 'pap.php', $_SERVER[ 'PHP_SELF' ] ) ? '' : 'pap.php')
		. ( COLOR_MODE == 'emn' ? '?em=1&' : '?em=0&' )
	;
	$if = $if && TEST ? _span( '.red', " ($if)" ) : '';
	if ( is_string( $data ) )
		$data = json_decode( $data, true );
	$is = "<i>$journal</i>$if, {$data[ 'issue' ]}";
	$ts = '<b>' . $data[ 'title' ] . '</b>';
	
	return $_simple->hdiv( $ts, ''
		. _p( '.pp_sub', ''
			. _ab( "$u&id=$pmid", _ic( 'article' ). $is )
			. BR
			. _ic( 'auth' ). _authlist( $data[ 'author' ] )
			. BR
			. _l( 'Methods' ). ': '. _methodlist( $data[ 'method2' ] )
		)
		. _ent_catalog( $data[ 'ids' ], [ 'mode' => 'icon' ] )
		. $opt[ 'add' ]
		,
		[ 'type' => 'h2', 'hide' => $opt[ 'hide' ] ]
	);
}

//.. _methodlist: メソッドの列挙
//- 事実上pap専用だけど、他でも使えるか？
function _methodlist( $in ) {
	$names = _subdata( 'trep', 'met2name' );
	$ret = [];
	foreach ( (array)$in as $m )
		$ret[] = $names[ $m ] ?: $m;
	return _imp2( $ret );
}

//.. _sizebtn サイズ変更ボタン
//- $optの最初はクラス
//- $size: 'ss', 's', 'm', 'l', 'll'
function _sizebtn( $size, $opt ) {
	return _btn( ".sizebtn $opt", _div( ".sizebox_$size", '' ) );
}

//.. _sas_info
function _sas_info( $mode, $v ) {
	$j = _json_cache( DN_DATA. '/sas/subdata.json.gz' );

	if ( $mode == 'title' ) {
		return ctype_digit( $v )
			? $j->{ $j->mid->$v }->title
			: $j->$v->title
		;
	}

	if ( $mode == 'mid2id' ) {
		return isset( $j->mid->{ _numonly( $v ) } )
			?  $j->mid->{ _numonly( $v ) } : '';
	}

	if ( $mode == 'id2mid' ) {
		return $j->$v->mid;
	}
}

//.. _pubmed_auth
function _pubmed_auth( $pubmed_json ) {
	$ret = [];
	$flgs = [];
		
	//- author
	foreach ( $pubmed_json->auth as $name ) {
		//- 名前
		$items = [_ab([
			_getpost( 'img_mode' ) == 'em' ? 'esearch' : 'ysearch' ,
			'auth' => $name
		], _fa( 'user' ) . $name ) ];
		if ( $oid = (string)$pubmed_json->orcid->$name )
			$items[] = _ab([ 'orcid', $oid ], IC_L. "ORCiD: $oid" );

		foreach ( (array)$pubmed_json->affi->$name as $s ) {
			if ( ! $s ) continue;
			$flg = _country_flag( trim( $s, '.' ) );
			$items[] = $s . $flg;
			$flgs[] = trim( $flg );
		}
		$ret[] = $pubmed_json->affi || $pubmed_json->orcid
			? _pop(
				$name,
				_ul( $items )
			)
			: $name_link
		;
	}
	$ret[] = implode( ' ', _uniqfilt( $flgs ) );
	return $ret;
}

//.. _set_categ
function _set_categ( $id ) {
	if ( ! TEST ) return;
	$c = [];
	if ( ! defined( 'CATEG_LIST' ) ) {
		foreach ( _json_load( DN_DATA. '/emn/categ.json' ) as $k => $v ) {
			$c[ "$k" ] = $v['j'];
		}
		$c[ '_' ] = 'リセット';
		define( 'CATEG_LIST', $c );
	}
	if ( ! defined( 'ID2CATEG' ) )
		define( 'ID2CATEG', _json_load( DN_DATA. '/emn/id2categ.json' ) );
	$cat = ID2CATEG[ $id ] ?: '???';
	$pop = [];
	foreach ( CATEG_LIST as $k => $v ) {
		$pop[] = $k == $cat
			? "[$v]"
			: _ab( "_mng.php?mode=categ&categ-$id=$k", $v )
		;
	}
	return _pop( "Categ", _ul( $pop, 0 ) );
}

//.. _get_chemlinks
function _get_chemlinks( $id, $inchikey = '' ) {
	global $urls;
	_add_url( 'quick-chem' );

	$idmap = json_decode( _ezsqlite([
		'dbname' => 'chem' ,
		'where'	 => [ 'id', $id ] ,
		'select' => 'idmap' ,
	]), true );
	$inchikey = $inchikey ?: _ezsqlite([
		'dbname' => 'chem' ,
		'where'	 => [ 'id', $id ] ,
		'select' => 'inchikey' ,
	]);

	$out = [
		_ab([ 'unichem'		, $inchikey ], IC_L. 'UniChem' ) ,
		_ab([ 'chemspider'  , $inchikey ], IC_L. "ChemSpider" ) ,
	];
	$no_url = [];
	foreach ( (array)$idmap as $exdb => $exid_set ) {
		$o = [];
		$flg_first = true;
		foreach ( $exid_set as $exid ) {
			$d = L_JA && $urls[ "$exdb-j" ] ? "$exdb-j" : $exdb;
			if ( ! $urls[ $d ] ) {
				if ( TEST ) $no_url[] = "[$exdb]";
				continue;
			}
			$o[] = _ab([ $d, $exid ], IC_L. ( $flg_first ? _l( $exdb ) : '' ) );
			$flg_first = false;
		}
		$out[ $exdb ] = implode( ' ', $o );
	}
	ksort( $out );
	return array_merge( 
		array_values( $out ),
		TEST ? $no_url : [] ,
		[
			_ab(
			'https://ja.wikipedia.org/wiki/Special:Search?go=Go&ns0=1&search='
			. $inchikey ,
			IC_L. 'Wikipedia search'
			) ,
			_ab(
				'https://www.google.com/search?q='. $inchikey ,
				IC_L. 'Google search'
			)
		]
	);
}

//.. _release_date
//- リリース日（直近の水曜）を返す
function _release_date( $weeks = 0 ) {
	if ( ! defined( 'REL_DATE' ) )
		define( 'REL_DATE', _json_load2( _fn( 'binfo' ) )->rel_date );
	$date = REL_DATE;
	if ( $weeks ) {
		$date = date( 'Y-m-d', strtotime( REL_DATE ) - $weeks * 7 * 3600 * 24 );
	}
	return $date;
}

//.. _mom_link
function _mom_link( $mom_id, $add = '' ) {
	extract( _ezsqlite([
		'dbname' => 'mominfo' ,
		'select' => [ 'en', 'ja', 'month' ] ,
		'where'  => [ 'id', $mom_id ]
	]) ); //- $en, $ja, $month
	if ( ! $en ) return;

	//- url
	$url = TEST
		? '_mng-mom.php?id='. $mom_id
		: _ej(
			"http://pdb101.rcsb.org/motm/$mom_id",
			"https://numon.pdbj.org/mom/$mom_id" 
		)
	;
	$i3 = substr( '00'. $mom_id, -3 );

	//- return
	return _div( '.clearfix', ''
		. _div( '.momimg_outer', _ab( $url,
			_img( "https://numon.pdbj.org/momimages/mom{$i3}_01.png" )
		))
		. implode( BR, array_filter([
			_ab( $url, ''
				. "<b>#$mom_id</b> - "
				. date( _ej( 'M Y', 'Y年n月' ), strtotime( $month ) )
			) ,
			_ej( $en, $ja. _kakko( $en ) ) ,
			$add
		]) )
	);
}

//.. _input_emdb_unp
function _input_emdb_unp( $id, $pmid = '' ) {
	if ( ! defined( 'EMDB_UNP_TSV' ) )
		define( 'EMDB_UNP_TSV', _tsv_load( DN_EDIT. '/unpid_emdb_annot.tsv' ) );

	$menu = [ '-' => '-', 'x' => 'x' ];
	foreach ( (array)explode( ',', _ezsqlite([
		'dbname' => 'pmid2did' ,
		'select' => 'ids' ,
		'where'  => [ 'pmid', $pmid ?: $id ]
	])) as $i ) {
		if ( in_array( substr( $i, 0, 1 ), [ 'e', 'S' ] ) ) continue;
		$menu[ $i ] = $i;
	}

	$form = 'form| target:_blank| method:get| action:_mng-emdbunp.php'
		. '|st: display:inline-block'
	;
	$hidden = _input( 'hidden', 'name:id', $id );
	$val = EMDB_UNP_TSV[ $id ] ?: '-';
	return _t( $form, ''
		. _selopt( 'name: val| st: font-size: large', $menu, $val )
		. $hidden
		. _input( 'submit', '.minibtn| st: font-size: small' )
	)
	. SEP
	._t( $form, ''
		. _input( 'search', 'name: val| size:10', $val )
		. $hidden
	);
}

function _flg_emdb_unp( $id ) {
	if ( ! defined( 'EMDB_UNP_TSV' ) )
		define( 'EMDB_UNP_TSV', _tsv_load( DN_EDIT. '/unpid_emdb_annot.tsv' ) );

	//- tsvに書いてあったら、true
	if ( EMDB_UNP_TSV[ $id ] ) return true;

	//- トモグラフィーなら false
	if ( _ezsqlite([
		'dbname' => 'main' ,
		'select' => 'method' ,
		'where'  => [ 'db_id', "emdb-$id" ]
	]) == 't' ) return false;

	//- fit がある・マップ未公開なら false
	extract( _ezsqlite([
		'dbname' => 'emn' ,
		'select' => [ 'status', 'fit' ] ,
		'where'  => [ 'did', "emdb-$id" ]
	]) );//- $status, $fit
	if ( ! $status || $fit ) return false;

	//- f&h infoが空白
	return _ezsqlite([
		'dbname' => 'strid2dbids' ,
		'select' => 'dbids' ,
		'where'  => [ 'strid', $id ]
	]) == '' ;
}

//.. _before_release
function _before_release_time() {
	if ( ! TESTSV ) return false;
	$w = date( 'w', time() );
	$h = date( 'H', time() );
	return $w < 3 || ( $w == 3 && $h < 9 ) || $w == 6; //- 日月火・土
}

//. doc
//.. _doc_hdiv
//- hdivで返す
function _doc_hdiv( $docid, $opt = [] ) {
	global $_simple;
	$lang = $nourl = $type = $hide = null;
	extract( $opt );
	$lang = $lang ?: _ej( 'e', 'j' );
	$doc = json_decode( _ezsqlite([
		'dbname' => 'doc' ,
		'select' => 'json' ,
		'where'  => [ 'id', $docid ]
	]), true );

	//- 関連情報
	$related = '';
	if ( $doc[ 'rel' ] ) {
		$a = [];
		foreach ( $doc[ 'rel' ] as $d )
			$a[] = _doc_pop( $d );
		$related = _p( '.small', DOC_RELATED. _imp2( $a ) );
	}

	//- 画像
	$img = ( ! $doc[ 'img' ] || $nourl )
		? '' 
		: _img( '.docimg', $doc[ 'img' ] )
	;
	if ( $img && $doc[ 'url' ] )
		$img = _ab( $doc[ 'url' ], $img );

	$o = [
		'retrun'	=> true,
		'type'		=> $type ?: 'h2' ,
		'hide'		=> $hide
	];
	
	//- url: そのページのAboutの場合は、URLは書かない
	$url = $nourl ? '' : $doc[ 'url' ];
	if ( $url != '' ) {
		$url = _p( 'URL: ' . _ab( $url, _fullurl( $url ) ) );
	}

	//- wikipe
	$wikipe = [];
	foreach ( is_array( $doc[ 'wikipe' ] ) ? $doc[ 'wikipe' ] : [ $doc[ 'wikipe' ] ] as $w ) {
		$s = _obj('wikipe')->get( $w )->show()
			?: _test( _span( '.red', "no wikipe data: $w" ) )
		;
		if ( $s )
			$wikipe[] = $s;
	}

	//- 文章
	if ( $doc[ $lang ] == '' ) {
		return $_simple->hdiv(
			TERM_DOC_NOT_FOUND ,
			"ID: $docid - $lang" ,
			$o
		);
	} else {
		$t = $s = $c = $l = '';
		extract( $doc[ $lang ] );
		return $_simple->hdiv(
			//- タイトル
			$t
			//- 中身
			, ''
			//- 概要
			. ( $s == '' ? '' : _t( 'p|.bld', $s ) )
			//- 画像
			. $img
			//- URL
			. $url
			//- コンテンツ
			. $c
			. _test( _ul( $wikipe ) )
			//- 関連情報
			. $related
			//- 外部リンク
			. ( $l == '' ? '' : _p( '.small', DOC_LINK . $l ) )
			,
			$o
		);
	}
}

//.. _doc_pop
//- ポップアップで返す
//- $opt[ btnstr: ボタンに書く名称、noiconアイコン無し 
function _doc_pop( $docid, $opt = [] ) {
	extract( $opt ) ; //- $label, $noicon
	return _pop_ajax(
		$label != ''
			? $label
			: ( $noicon ? '' : IC_HELP ). json_decode( _ezsqlite([
				'dbname' => 'doc' ,
				'select' => 'json' ,
				'where'  => [ 'id', $docid ]
			]) )->{ L_EN ? 'e' : 'j' }->t
		,
		[ 'mode' => 'doc', 'id' => $docid ]
	);
}

//.. _doc_div ポップアップ用のコンテンツ
function _doc_div( $docid ) {
	$doc = json_decode( _ezsqlite([
		'dbname' => 'doc' ,
		'select' => 'json' ,
		'where'  => [ 'id', $docid ]
	]), true );
	$url = $doc[ 'url' ];
	//- 文章
	if ( ! $doc[ 'e' ] ) {
		return TEST ? "Doc '$docid' not found" : '' ;
	} else {
		$t = $s = $c = $l = ''; //- title, subtitle(?), $contents, $l?
		extract( $doc[ L_EN ? 'e' : 'j' ] );
		return implode( array_filter([
			//- タイトル
			'<b>'. ( $url ? _ab( $url, $t ) : $t ). '</b>' ,

			//- 概要かコンテンツ
			$s ?: $c ?: 'no document' , 

			//- 画像
			$doc[ 'img' ]
				? ( $url
					? _ab( $url, _img( '.docimg', $doc[ 'img' ] ) )
					: _img( '.docimg', $doc[ 'img' ] )
				)
				: ''
			,
			//- 詳細へのリンク
			_ab([ 'doc', 'id' => $docid ],
				IC_HELP . _ej( 'Read more', '詳細を読む' )
			)
		]), BR );
	}
}

//. met系
//.. _met_pop
function _met_pop( $name, $type = 'm', $label = '' ) {
	if ( $name == '' || $name == 'none' || $name == 'NONE' ) return;
	_add_lang( 'met' );
	if ( ! defined( 'MET_SYN' ) ) {
		define( 'MET_SYN', _json_load( DN_DATA. '/met_syn.json.gz' ) );
	}
	$ret = [];
	foreach ( (array)explode( '@|@', $name ) as $n ) {
		$n = trim( $n );
		$k = strtolower( "$type:". _reg_rep( $n, MET_SYN[ $type ] ) );

		extract( _ezsqlite([
			'dbname' => 'met' ,
			'where'  => [ 'key', $k ] ,
			'select' => [ 'name', 'data' ], //- $name, $data
		]) );
		$data = json_decode( $data );

		//- wikipe情報あるか
		$w = $data->wikipe;
		$flg_wikipe = false;
		if ( $w != 'x' ) {
			$w2 = $type == 'm' && !$w //- 「手法」なら検索
				? _obj('wikipe')->get( explode( '|', $name )[0] )->flg()
				: false
			;
			$flg_wikipe = $w || $w2;
		}
		
		$ret[] = $name == '' 
			? ( $label ?: $n ) . _test( _span( '.red', "[x]($k) " ) )
			: _pop_ajax(
				_fa([
					'm' => 'hand-paper-o' ,
					's' => 'desktop' ,
					'e' => 'thermometer' ,
				][ $type ])
				.( $flg_wikipe ? IC_WIKIPE : '' ) //- wikipe icon
				. _l( $label ?: $n )
				. _country_flag( $data->place ) //- 国旗
				,
				[ 'mode' => 'met', 'key' => $k ]
			)
		;
	}
	return _imp( $ret );
}

//.. _met_data
function _met_data( $key, $flg_less = false ) {

	//... 準備
	_add_lang( 'met' );

	$ans = ( new cls_sqlite( 'met' ) )
		->where( 'key='. _quote( $key ) )
		->qobj(['select' => '*' ])[0]
	;
	if ( !$ans )
		die( _ej( 'No data', 'データがありません' ) .': '. _test( $key ) );

	$data = json_decode( $ans->data );
	$name = explode( '|', $ans->name )[0]; 
	$cat =  explode( ':', $ans->key )[0];

	//... for
	$for = [];
	$num = '';
	if ( ! $flg_less ) {
		foreach ( explode( '|', $ans->for ) as $s ) {
			$for[] = _ab(
				[ 'ysearch', 'act_tab' => 'met', 'kw' => _quote( $s, 2 ) ] ,
				_l( $s ) 
			);
		}
		
		$num = $ans->num ? _ab(
			[ 'ysearch', 'kw' => _quote( 'm:'. $key, 2 ) ] ,
			number_format( $ans->num ) . _ej( ' structure data', '個の構造データ' )
		) : '';
	}

	//... wikipe
	$wikipe = '';
	if ( $data->wikipe != 'x' ) {
		$o = [];
		foreach ( array_filter( explode( '|', $data->wikipe ) ) as $w ) {
			 $o[] = _obj('wikipe')->get( $w )->show();
		}
		$wikipe = $o ? implode( BR, $o )
			: ( $cat == 'm' //- 装置名、ソフト名は変なものと被るので
				? _obj('wikipe')->get( $name )->show()
				: ''
			)
		;
	}

	//... yearly stat
	$yearly_box = TR;
	$yearly = explode( '|', $ans->yearly );
	$max = max( $yearly ) / 50;
	$this_year = date( 'Y' );
	foreach ( $yearly as $y => $n ) {
		if ( $y % 10 == 0 )
			$yearly_box .= TD;
		$year = $this_year - 29 + $y;
		$yearly_box .= _pop( '' ,
			_ej( "Year $year".BR."$n entries", $year. '年'.BR.$n.'件' ) ,
			['type' => 'div',
				'trgopt' => '.poptrg '
					. ( $year == $this_year ? 'met_ybar_last' : '' )
					. ' met_ybar ' 
				. '| ?year: ' . $year . ', count: ' . $n 
				. '| st:border-bottom-width: '. round( $n/$max + 1) .'px', '' 
			]
		);
	}
	$yearly_box .= TR
		.TD.($this_year - 29). ' -'
		.TD.($this_year - 19). ' -'
		.TD.($this_year -  9). ' -'
	;

	//... output
	$jname = _l( $name );
	return _table_2col( array_filter([
		_ej( 'Category - name', _span( '.nw', 'カテゴリ ' ). '- 名称' ) =>
			_ab([ 'ysearch', 'kw' => _quote( 'm:'. $key, 2 ) ] 
				,
				_l( [
					'm' => _fa('hand-paper-o') . _l( 'Method' ) ,
					'e' => _fa('thermometer')  . _l( 'Equipment' ) ,
					's' => _fa('desktop')      . _l( 'Software' ) ,
				][ $cat ] )
				.' - '. ( $name == $jname ? $name: "$name ($jname)"  )
			)
		,
//		'other names'	=> _imp2( array_slice( explode( '|', $ans->name ), 1 ) ) ,
		'other names'	=> _imp2( explode( '|', $ans->name ) ) ,
		'as/for'		=> _imp2( $for ) ,
		'Yearly stat'	=> _t( 'table| .met_yearly', $yearly_box ) ,
		'Total'			=> $num ,
		'Place'			=> $data->place . _country_flag( $data->place ) ,
		'Website'		=> $data->url ?  _ab( $data->url, $data->url )
			: _test( _ab( 'https://www.google.co.jp/search?q=' . $name,
				_span( '.red', 'Google検索' ) ) .' ' . $key 
			)
		,
		'Wikipedia'		=> $wikipe ,
		'Comment'		=> $data->comment 
	]));
}

//.. _country_flag
function _country_flag( $str ) {
	if ( ! $str ) return;
	$str = trim( strtolower( $str ), ';. ' );
	$str = strtr( $str, [
		'the '						=> '' ,
		' and '						=> '' ,
		'united states' 			=> 'usa' ,
		'united kingdom'			=> 'uk' ,
		'czech republic'			=> 'czech' ,
		'korea, republic of'		=> 'korea' ,
		'hong kong'					=> 'china' ,
		'new zealand'				=> 'newzealand' ,
		'south africa'				=> 'southafrica' ,
		'saudi arabia'				=> 'saudiarabia' ,
		'democratic republic of '	=> '' ,
		'russian federation'		=> 'russia' ,
	]);
	$c = trim( _reg_rep( $str, [ '/^.+,\s*/' => '' ] ) );
	return in_array( $c, [
		'japan', 'usa', 'uk', 'china', 'germany', 'france', 'switzerland', 'spain', 'australia',
		'canada', 'korea', 'italy', 'taiwan', 'brazil', 'sweden', 'india', 'russia', 'thailand',
		'netherlands', 'austria', 'singapore', 'portugal', 'denmark', 'poland', 'finland',
		'newzealand', 'norway', 'croatia', 'czech', 'israel', 'belgium','southafrica',
		'slovenia', 'slovakia', 'malaysia', 'venezuela', 'mexico', 'hungary', 'saudiarabia',
		'egypt', 'ireland', 'greece', 'latvia', 'estonia', 'kenya', 'hungary', 'argentina',
		'ukraine', 'cameroon', 'trinidadtobago', 'centralafricanrepublic', 'vietnam', 'congo',
		'iceland', 'malawi', 'peru', 'iran', 'serbia', 'rwanda', 'turkey', 'chile', 'romania' ,
		'pakistan',
	] )
		? ' ' . _img( 'img/flg_'. $c. '.gif' )
		: _test( _span( '.red', "[$c?]" ))
	;
}

//. ent カタログ
//.. _ent_catalog
//- 複数エントリのカタログ
function _ent_catalog( $ids, $opt = [] ) {
	$max = 200;
	extract( (array)$opt ); //- $mode, の他？
	//- auto mode ? デフォルト閾値7
	if ( $mode != 'list' && $mode != 'icon' ) {
		$mode = count( $ids ) < ( ctype_digit( $mode ) ? $mode : 7 )
			? 'list' : 'icon';
	}
	unset( $opt[ 'mode' ] );
	$less = $more = '';
	$cnt = 0;
	foreach ( (array)$ids as $i ) {
		$a = ( new cls_entid() )->set( $i )->ent_item( $mode, $opt );
		if ( $max < $cnt ) {
			$more .= $a;
		} else {
			$less .= $a;
			++ $cnt;
		}
	}
	return $less. _more( $more );
}

//. class cls_entid 
class cls_entid extends abs_entid {
	public $status_text;

	//.. ent_item
	function ent_item( $type, $opt = [] ) {
		 return $type == 'list'
		 	? $this->ent_item_list( $opt )
		 	: $this->ent_item_img( $opt )
		 ;
	}

	//.. ent_item_img
	function ent_item_img( $opt = [] ) { 
		$txt = $add = '';
		extract( (array)$opt );

		if ( TEST ) {
			//- マップコントアがauthorじゃないやつに印
			if ( $this->db == 'emdb' && $this->add()->non_auth_clev )
				$add .= _span( '.red', '*' );

			//- ムービーはないが、セッションはもうできているやつに印
			$s = '';
			if ( TEST && MOV_TASK_INFO[ $this->id. '-session' ] )
				$s .= 'S';
			if ( TEST && MOV_TASK_INFO[ $this->id. '-polygon' ] )
				$s .= 'P';
			if ( $s == 'SP' )
				$s = '@';
			if ( $s )
				$add .= _span( '.blue', "[$s]" );
		}

		return _pop(
			_img( $this->imgfile() )
			. _p( $this->link_ym( $this->id ). $this->status_str(). $add ),
			$this->ent_itemstr( $opt ) ,
			[
			 	'type' => 'div' , 
			 	'trgopt' => ".enticon enticon_cap | ?$txt"
			]
		);
	}

	//.. ent_item_list
	function ent_item_list( $opt = [] ) {
		extract( (array)$opt ); //- $txt, $add
		return _div( '.clearfix topline' ,
//			$this->link_ym( _img( '.left', $this->imgfile() ) )
			$this->link_ym( _div( '.enticon_cap left',
				_img( $this->imgfile() ). _p( $this->status_str() )
			))
			. $add
			. _p( $this->ent_itemstr( $opt ) )
		);
	}

	//.. status_str 未公開など
	function status_str() {
		//- 内容はimgfileメソッド内で定義
		return $this->status_text
			? BR. _span( '.smaller', $this->status_text )
			: ''
		;
	}

	//.. ent_itemstr
	private function ent_itemstr( $opt = [] ) {
		extract( (array)$opt ); //- $txt, $add, $data, $title?

		//- 規定タイトルがある？
		$et = $title ?: $data[ 'title' ] ?: $this->title();
		if ( $this->db == 'chem' ) {
			$et = _imp2([
				$et ,
				_nikkaji_name( $this->id ) ,
				_ym_annot_chem( $this->id ) ,
				_obj('wikipe')->chem( $this->id )->pop() ,
				TEST ? _pop_ajax(
					IC_L. _l( 'External DB' ),
					[ 'mode' => 'chemlinks', 'id' => $this->id ]
				): '' ,
			]); 
		}
		//- 不明データ？
		if ( $et == '' ) {
			if ( $this->is_prerel() )
				$et = _ej( 'PDB unreleased entry', 'PDB未公開エントリ' );
			else {
				$et = _ej( 'Unknown data', '不明なデータ' ) . $txt;
				$this->db = 'unknown';
			}
		}

		//- ビューアボタン
		$btns = '';
		//- pap link
		if (
			TEST && 
			! _instr( 'pap.php', $_SERVER[ 'PHP_SELF' ] ) &&
			in_array( $this->db, [ 'emdb', 'pdb', 'sasbdb' ] )	
		) {
			$btns .= _ab( 'pap.php?str='. $this->did, _ic('article') ); //. _l('article') );
		}

		//- ムービー
		if ( $this->ex_mov() )
			$btns .= _btn_popmov( $this->did );

		//- ビューア
		if ( 
			( $this->db == 'chem' ) ||
			( $this->db == 'bird' ) ||
			( $this->db == 'pdb'   	&& !$this->is_prerel() ) ||
			( $this->db == 'emdb'   && $this->ex_polygon() ) ||
			( $this->db == 'sasbdb' && _sas_info( 'id2mid', $this->id ) != '' )
		)
			$btns .= _btn_popviewer( $this->did );

		//- まとめ
		return '<b>'. $this->link_ym(). "</b>: "
			. ( $btns == '' ? '' : $btns . BR )
			. $et
			//- 追加文字列
			. ( $txt == '' ? '' : " ($txt)" )
			//- 追加情報
			. ( $data == '' ? '' : BR . _kv( $data ) )
		;
	}

	//.. imgfile: 画像ファイル
	function imgfile( $size = 's', $type = '' ) {
		$id = $this->id;

		//... EMDB
		if ( $this->db == 'emdb' ) {
			if ( ! $this->status()->map ) {
				$this->status_text = TERM_ICONCAP_NOMAP;
				return 'img/gray.png';
			}

			$m = _fn( 'emdb_med', $id ). '/mapi';
			//- タイプ指定なし
			$auto = [
				'0' 		=> 'img/gray.png' ,
				'p' 		=> DN_EMDB_MED. "/$id/mapi/proj0.jpg" ,
				'1' 		=> _fn( 'emdb_snap', $id, $size . '1' ) ,
				'2' 		=> _fn( 'emdb_snap', $id, $size . '2' ) ,
				'3' 		=> _fn( 'emdb_snap', $id, $size . '3' ) ,
				'_' 		=> 'img/gray.gif' ,
				'surf_x'	=> "$m/surf_x.jpg" ,
				'surf_y'	=> "$m/surf_y.jpg" ,
				'surf_z'	=> "$m/surf_z.jpg" ,
				'proj0'		=> "$m/proj0.jpg" ,
				'proj2'		=> "$m/proj2.jpg" ,
				'proj3'		=> "$m/proj3.jpg" ,
			][ $this->status()->img ?: '0' ];
			if ( ! $this->status()->img )
				$this->status_text = TERM_ICONCAP_EMDB_NOIMG;

			return $type == ''
				? $auto
				: ( file_exists( $f = $a[ $type ] ) ? $f : $auto )
			;

		//... PDB
		} else if ( $this->db == 'pdb' ) {
			//- 未公開データ？
			if ( $this->is_prerel() ) {
				$this->status_text = TERM_ICONCAP_PDB_UNREL;
				return 'img/gray.png';
			//- EM-PDB
			} else if ( defined( 'IMG_MODE' ) && IMG_MODE == 'em' && _inlist( $id, 'epdb' ) ) {
				//- PDB-EM
				$s = $this->status()->snap;
				$auto = $s == ''
					? _fn( 'pdb_img', $id )
					: _fn( 'pdb_snap', $id, $size . $s )
				;
				return $type == ''
					? $auto
					: ( file_exists( $f = _fn( 'pdb_snap', $id, "$size$type" ) )
						? $f 
						: $auto
					)
				;
			} else {
				return  file_exists( $f = _fn( 'pdb_img', $id ) )
					? $f
					: _url( 'pdbjimg', $id )
				;
			}

		//... SASBDB
		} else if ( $this->db == 'sasbdb' || $this->db == 'sasbdb-model' ) {
			$fn = _fn( 'sas_img', $id );
			if ( file_exists( $fn ) ) return $fn;
			$this->status_text = TERM_ICONCAP_SASBDB_NOIMG;
			return 'img/gray.png';

		//... bird
		} else if ( $this->db == 'bird' ){
			$fn = _fn( 'bird_img', _numonly( $id ) );
			if ( file_exists( $fn ) ) return $fn;

			$fn = _fn( 'bird_img2', _numonly( $id ) );
			if ( file_exists( $fn ) ) return $fn;

			$this->status_text = TERM_ICONCAP_NOIMG;
			return 'img/gray.png';

		//... chem
		} else {
			$fn = _fn( 'chem_img', strtoupper( $id ) );
			if ( file_exists( $fn ) ) return $fn;
			$this->status_text = TERM_ICONCAP_CHEM_NOIMG;
			return 'img/gray.png';
		}
	}

	//.. link_ym リンク文字列生成系
	function link_ym( $str = '' ) {
		return _ab( _url( 'ym', $this->did ), $str ?: $this->DID );
	}
	//.. movinfo
	function movinfo() {
		return $this->db == 'emdb'
			? $this->movinfo_emdb()
			: $this->movinfo_pdb()
		;
	}

	//... EMDBデータ
	private function movinfo_emdb() {
		if ( ! $this->ex_mov() ) return;
		$id = $this->id;
		$movcap_ini = parse_ini_file( DN_DATA. "/movie_caption.ini" );
//		$ini = $ini[ $id . '_' . $i ];
		$ret = [];
		foreach ( (array)$this->movjson() as $mov_num => $js ) {
			if ( $mov_num == 'jmfit' ) continue;
			$caps = [];

			//- キャプション ini
			$c = $movcap_ini[ "{$id}_$mov_num"];
			$caps[] = $this->movcap_str( $c ) ?: $c ;

			//- キャプションその他
			$caps[] = $this->movcap_str( $js->mode, $mov_num );

			//- キャプション pdbあてはめ
			$a = [];
			foreach ( (array)$js->fittedpdb as $p )
				$a[] = _ab([ 'ym', $p ],  "PDB-$p" );
			if ( $a )
				$caps[] = $this->movcap_str( 'atomic' ) .': '. _imp( $a );


			//- キャプション表面レベル
			if ( $js->threshold != '' )
				$caps[] = $this->movcap_str( 'surflev' ) . $js->threshold;

			$caps[] = $this->movcap_str( 'chimera' );

			//- 返り値
			$d = DN_EMDB_MED . "/$id";
			$ret[ $mov_num ] = [
				'cap'	=> array_filter( $caps ) ,
				'file'	=> "$d/movie$mov_num" ,
				'img'	=> "$d/snapl$mov_num.jpg" ,
				'imgs'	=> "$d/snaps$mov_num.jpg" ,
				'imgss'	=> "$d/snapss$mov_num.jpg" ,
				'dl'	=> "$d/movie$mov_num" ,
				'files' => $this->movurl( $mov_num ) ,
				'type'	=> $mov_num
			];
			$mov_num_last = $mov_num;
		}
		$mov_num = $mov_num_last;

		//- jmfit Jmolのムービー
		foreach ( (array)$this->movjson()->jmfit as $pid ) {
			++ $mov_num;
			$d = DN_PDB_MED . "/$pid";
			$ret[ $mov_num ] =  [
			 	'cap'	=> [ 
			 		$this->movcap_str( 'simpfit' ) ,
			 		$this->movcap_str( 'atomic' ) . _ab([ 'ym', $pid ], "PDB-$pid" ) ,
			 		$this->movcap_str( 'jmol' )
				] ,
				'file'	=> "$d/moviejm$id" ,
			 	'img'	=> "$d/snapljm$id.jpg" ,
			 	'imgs'	=> "$d/snapsjm$id.jpg" ,
			 	'imgss'	=> "$d/snapssjm$id.jpg" ,
				'dl'	=> "$d/moviejm$id" ,
				'files' => $this->movurl( "jm$id", $d ) ,
				'type'	=> "jm$id"
			];
		}
		
		//- トモグラムのポリゴンムービー
		$d = DN_EMDB_MED . "/$id";
		if ( file_exists( "$d/moviejm.webm" ) ) {
			++ $mov_num;
			$ret[ $mov_num ] = [
			 	'cap'	=> [
			 		$this->movcap_str( 'simpsurf' ) ,
			 		$this->movcap_str( 'jmol' )
				] ,
				'file'	=> "$d/moviejm" ,
			 	'img'	=> "$d/snapljm.jpg" ,
			 	'imgs'	=> "$d/snapsjm.jpg" ,
			 	'imgss'	=> "$d/snapssjm.jpg" ,
				'dl'	=> "$d/moviejm" ,
				'files' => $this->movurl( 'jm', $d ) ,
				'type'	=> 'jm'
			];
		} 
		return $ret;
	}

	//... PDBデータ
	private function movinfo_pdb() {
		//- 集合体情報
		$ret = [];
		foreach ( (array)$this->movjson() as $cnt => $js ) {
			$caps = [];
			if ( $js->name == 'emdb' ) {
				//- EMDB movie
				$caps = [
					$this->movcap_str( 'on_emdb' ) ,
					$this->movcap_str( 'emdbid' )
						. _ab([ 'ym', $js->id ], "EMDB-{$js->id}" ) 
				];
				//- 一緒にあてはめたPDB
				if ( $js->cofit != '' ) {
					$a = [];
					foreach ( explode( ',', $js->cofit ) as $i2 )
						$a[] = _ab([ 'ym', $i2 ], "PDB-$i2" );
					$caps[] = '+ ' . _imp( $a );
				}

				//- 返り値
				$d = DN_EMDB_MED . "/{$js->id}";
				$caps[] = $this->movcap_str( 'chimera' );
				$ret[ $cnt ] =  [
					'cap'	=> array_filter( $caps ) ,
					'img'	=> "$d/snapl{$js->num}.jpg" ,
					'imgs'	=> "$d/snaps{$js->num}.jpg" ,
					'imgss'	=> "$d/snapss{$js->num}.jpg" ,
					'file'	=> "$d/movie{$js->num}" ,
					'dl'	=> "$d/movie{$js->num}" ,
					'files' => $this->movurl( $js->num, $d ) ,
					'type'	=> $js->num
				];
			} else {
				//- Jmol movie
				if ( $js->name == 'dep' ) {
					//- 登録構造
					$caps[] = $this->movcap_str( 'depo' );

				} else if ( $js->name == 'sp' || $js->name == 'sp2' ) {
					//- 分割エントリ
					$a = [];
					foreach ( (array)$js->ids as $i )
						$a[] = _ab([ 'ym', $i ], "PDB-$i" );
					$i = _imp( $a );
					$caps[] = _ej( "With $i", "$i との合成表示" );

				} else if ( substr( $js->name, 0, 2 ) == 'jm' ) {
					//- Jmol-fit
					$caps = [
						$this->movcap_str( 'simpfit' ) ,
						$this->movcap_str( 'emdbid' )
							. _ab([ 'ym', $js->id ], 'EMDB-' . $js->id )
					];
				} else {
					//- その他 BM
					$caps[] = $this->movcap_str( 'bu' )
						. (
							$this->movcap_str( $js->type ) ?:
							$js->type ?:
							_ej( 'assembly', '集合体' ) 
						);
				}
				$caps[] = $this->movcap_str( 'jmol' );

				//- 返り値
				$d = DN_PDB_MED. "/{$this->id}";
				$ret[ $cnt ] = [
					'cap'	=> array_filter( $caps ) ,
					'img'	=> "$d/snapl{$js->name}.jpg" ,
					'imgs'	=> "$d/snaps{$js->name}.jpg" ,
					'imgss'	=> "$d/snapss{$js->name}.jpg" ,
					'file'	=> "$d/movie{$js->name}" ,
					'dl'	=> "$d/movie{$js->name}" ,
					'files' => $this->movurl( $js->name ) ,
					'type'	=> $js->name
				];
			}
		}
		return $ret;
	}
	//.. movcap_str
	function movcap_str( $s, $s2 = 'dummy' ) {
		if ( ! defined( 'MOVCAP_DIC' ) ) {
			define( 'MOVCAP_DIC', _reg_rep(
				_subdata( 'mov_caption', _ej( 'en', 'ja' ) ), [
					'/_chimera_/'	=> _met_pop( 'UCSF Chimera', 's' ) ,
					'/_jmol_/'		=> _ab( _url( 'jmol' ), 'Jmol' )
				]
			));
		}
		return MOVCAP_DIC[ $s ] ?: MOVCAP_DIC[ $s2 ];
	}

	//.. movurl: ムービーファイル名を返す
	function movurl( $num, $s = '' ) {
		if ( $s == '' )
			$s = ( $this->db == 'emdb' ? DN_EMDB_MED : DN_PDB_MED ). '/' . $this->id;
		return [
			'l' => [
				'webmv'  => "$s/movie$num.webm" ,
				'm4v'    => "$s/movie$num.mp4" ,
				'poster' => "$s/snapl$num.jpg"
			] ,
			's' => [
				'webmv'  => "$s/movies$num.webm" ,
				'm4v'    => "$s/movies$num.mp4" ,
				'poster' => "$s/snapl$num.jpg"
			]
		];
	}


}

