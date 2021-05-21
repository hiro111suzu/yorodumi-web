<?php
//. init misc
define( 'COLOR_MODE', 'emn' );
define( 'IMG_MODE', 'em' );
require( __DIR__. '/common-web.php' );

_define_term( <<<EOD
TERM_NUM_ENT
	Number of data entries
	データエントリ数
TERM_TOTAL
	Total
	合計
TERM_DISP_OPTION
	Display options
	表示オプション
TERM_NO_2NDKEY
	None (bar graph)
	なし (棒グラフ)
TERM_BOTH_SUM
	Sum of both
	両方の合計
TERM_ANNOOT_TITLE
	Annotation about this statistics
	この統計に関する注釈
TERM_ANNOOT_EDIT
	Some data are modified
	内容を一部編集しています
TERM_ANNOOT_COUNT
	Counted not per "structre" but per data entry
	構造の数ではなく、データエントリーの数です
TERM_ANNOOT_SORT
	Click column head to sort ([Shift] + click for multi-column sort)
	列の先頭をクリックすると、その列基準のソートになります（[Shift]+クリックで複数列基準）
TERM_ANNOOT_ONLY_REP
	Only representative items are shown
	代表的な項目のみ表示しています
TERM_DL_TSV
	TSV format (Tab Separated Values)
	TSV形式 (タブ区切り)
TERM_DL_CSV
	CSV format (Comma-Separated Values, for Excel, etc.)
	CSV形式 (コンマ区切り, Excelなどに)

EOD
);
_add_lang( 'stat' );

//.. getpost etc.
define( 'GET_KEY1'		, _getpost( 'key' ) ?: 'submit_year' );
define( 'GET_KEY2'		, _getpost( 'k2' ) );
define( 'GET_DB'		, _getpost( 'db' ) ); 
define( 'GET_DOWNLOAD'	, _getpost( 'download' ) );

//- データ読み込み
define( 'TABLE_DATA', _json_load( DN_DATA. '/emn/tabledata.json' ) );
define( 'DATA_COUNT', _json_load( DN_DATA. '/emn/datacount.json.gz' ) );

//- キー名
define( 'LANG_NAME', _ej( 'ename', 'jname' ) );
define( 'KEY1_NAME', TABLE_DATA[ GET_KEY1 ][ LANG_NAME ] );
define( 'KEY2_NAME', TABLE_DATA[ GET_KEY2 ][ LANG_NAME ] );

//- 種類多すぎデータ対応
define( 'NUM_LIMIT', [ 'authors' => 10, 'kw' => 5 ] );

//- 主キー名の長すぎレベル
define( 'LIM_NAME_LEN', GET_KEY2 ? 25 : 35 );

if ( ! DATA_COUNT[ GET_KEY1 ] )
	die( 'Database error' ); //- ?

//- total (datacount.jsonから)
$sum_both = [];
foreach ( DATA_COUNT[ GET_KEY1 ] as $n => $v ) {
	//- 多すぎデータ
	if ( NUM_LIMIT[ GET_KEY1 ] > $v[ 'b' ] ) continue;
	$sum_both[ $n ] = $v[ GET_DB ?: 'b' ];
	$sum_emdb[ $n ] = $v[ 'e' ];
	$sum_pdb[ $n ]  = $v[ 'p' ];
}

//- エラー？
if ( ! $sum_both )
	die( 'error nodata' );

ksort( $sum_both );
define( 'MAX_TOTAL'	, max( array_values( $sum_both ) ) );
define( 'MANY'		, count( $sum_both ) > 100 );
define( 'MET_NAME_SHORT', _subdata( 'trep', 'met_name_short' ) );

//. データベース処理
if ( GET_KEY2 ) {
	//- key2の値リスト取得
	$key2list = array_keys( DATA_COUNT[ GET_KEY2 ] );
	sort( $key2list );
	
	if ( GET_KEY2 == 'method' )
		$key2list = [ '2', 'H', 'S', 'A', 'T' ];

	//.. クエリ作成
	$where_db = '';
	if ( GET_DB ) 
		$where_db = 'AND database="' . ( GET_DB == 'e' ? 'EMDB' : 'PDB' ) . '"';

	$query = [];
	$num = 0;
	$num2k = [];
	foreach ( array_keys( $sum_both ) as $k1 ) {
		$where_eq = $k1 == 'N/A' 
			? 'is NULL'
			: ( TABLE_DATA[ GET_KEY1 ][ 'multi' ]
				? "LIKE \"%|$k1|%\""
				: "= \"$k1\""
			)
		;
		$query[] = ''
			. "SELECT '$num' as n, ". GET_KEY2. " as k, count(*) as c "
			. 'FROM main '
			. 'WHERE '. GET_KEY1. " $where_eq  $where_db "
			. 'GROUP BY '. GET_KEY2. ' COLLATE NOCASE'
		;
		$num2k[ $num ] = $k1;
		++ $num;
	}

	//.. SQLite処理
	$sq = new cls_sqlite();
	$ans = $sq->qar( implode( ' UNION ALL ', $query ) );
	unset( $sq );

	//.. データ整頓
	$m = [];
	$data2d = [];
	if ( count( $ans ) > 0 ) {
		foreach ( $ans as $a )
			$m[] = $data2d[ $num2k[ $a[ 'n' ] ] ][ $a[ 'k' ] ] = $a[ 'c' ];
		$max2 = max( $m );
	}
}

//. ダウンロード
if ( GET_DOWNLOAD ) {

	//.. key1 vs key2
	if ( GET_KEY2 ) {
		$data = [ [ KEY1_NAME ] ];
		//- header
		foreach ( $key2list as $n )
			$data[0][] = _valrep( $n, 'csv' );
		$data[0][] = _l( 'sum' );

		//- main
		foreach ( $sum_both as $name => $tcount ) {
			if ( $tcount == '' ) continue; 
			$row = [ _valrep( $name ) ];
			foreach ( $key2list as $name2 )
				$row[] = $data2d[ $name ][ strtolower( $name2 ) ] ?: '0';
			$row[] = $tcount;
			$data[] = $row;
		}

	//.. key1 only
	} else {
		$data = [[ KEY1_NAME, 'EMDB', 'PDB', _l( 'sum' ) ]];
		foreach ( $sum_both as $name => $tcount ) {
			$data[] = [
				_valrep( $name ) ,
				$sum_emdb[ $name ] ?: '0',
				$sum_pdb[ $name ] ?: '0',
				$tcount ?: '0'
			];
		}
	}
	_download_text( GET_DOWNLOAD, 'emn_stats', $data );
}

//. コンテンツ出力
//.. オプションテーブル
//... key1
$a = [];
foreach ( TABLE_DATA as $k => $v ) {
	if ( ! $v[ 'count' ] ) continue;
	$a[ $v[ 'categ' ] ] .= _opt_item( $v[ LANG_NAME ], GET_KEY1, 'key', $k );
}

$k1 = [];
foreach ( $a as $k => $v ) {
	$k1[] = _kv([ _ic( $k ). _l( $k ) => $v ]);
}

//... key2
$k2 = '';
foreach ( [ 'submit_year', 'method', 'reso_seg' ] as $k )
	$k2 .= _opt_item( TABLE_DATA[ $k ][ LANG_NAME ], GET_KEY2, 'k2', $k ) ;

//... db
//- 棒グラフのサンプル
$e = $p = '';
if ( ! GET_KEY2 ) {
	$e = _bar( 'emdb', 25 );
	$p = _bar( 'pdb' , 25 );
}

$ce = count( _idlist( 'emdb' ) );
$cp = count( _idlist( 'epdb' ) );
$cb = $ce + $cp;

//... output
$_simple->hdiv(
	_ic( 'opt' ). TERM_DISP_OPTION
	,
	_table_2col([
		'Row key' => implode( BR, $k1 ) ,
		'Column key' => ''
			. _opt_item( TERM_NO_2NDKEY, GET_KEY2, 'k2', '' )
			. $k2
		,
		'Database' => ''
			. _opt_item( "$e$p ". TERM_BOTH_SUM. _kakko( $cb ), GET_DB,  'db', '' )
			. _opt_item( "$e EMDB ($ce)", GET_DB, 'db', 'e' )
			. _opt_item( "$p PDB ($cp)" , GET_DB, 'db', 'p' )
		,
	])
);

function _opt_item( $str, $cval, $key, $val ) {
	//- 今の値を示すアイテム
	return  $cval == $val || ( ! $cval && ! $val )
		? _span( '.optact br4 sd', $str ) . ' '
		: _a( _get_query([ $key => $val ]), $str, '.optitem br4' ) . ' '
	;
}

//.. ことわり書き
$d = preg_replace(
	[ '/_same/', '/_mod/' ] ,
	[ 
		'<b>[' . _ej( 'synonym?', '同じ？' ) . ']</b>' ,  
		'<b>[' . _ej( 'modified', '編集'   ) . ']</b>' ,  
	], [
		'microscope' => '_mod FEI/PHILIPS => FEI, JEOL JEM-xxx => JEOL xxx ' ,
		'detector'   => '_mod Tietz/TVIPS => TIETZ, _mod '
			. _ej( 'Pixel number (e.g. 4K x 4K) => removed',
				'カメラのピクセル数(例: 4K x 4K) => 削除' ) ,
		'inst_vitr'  => '_mod REICHERT-JUNG/Leica => LEICA, etc'  ,
		'rec_soft'   => '_mod EMAN2 => EMAN, Web => SPIDER' ,
		'fit_soft'   => '_mod AMIRAMOL => AMIRA, NORMAL MODE-BASED FLEXIBLE FITTING => NMFF, MOLECULAR DYNAMICS BASED FLEXIBLE FITTING => MDFF, _same NAMD & MDFF, FOLDHUNTER & EMAN' ,
		'reso_method' => '_mod Fourier shell correlation at XX => FSC XX' ,
		'nation'	=> _ej( 'from PubMed data of primary citation', '文献のPubMedデータより' ) ,
		'authors' 	=> _ej( 'Counted only frequenters', '登録数の多い著者のみカウントしています' )  ,
		'kw'		=> _ej( 'Counted only frequent terms', '頻出語のみカウントしています' ) 
	]
);

$_simple->hdiv( TERM_ANNOOT_TITLE, _ul([
	TERM_ANNOOT_EDIT. _ul([
		_kv([ KEY1_NAME => $d[ GET_KEY1 ] ]) ,
		_kv([ KEY2_NAME => $d[ GET_KEY2 ] ]) ,
	]) ,
	TERM_ANNOOT_COUNT ,
	TERM_ANNOOT_SORT ,

	//- 省略データ表示用のボタン
	( MANY 
		? _span( '#showall' ,
			TERM_ANNOOT_ONLY_REP
			. _btn( "! _showall(1)", _ej( 'Show all', 'すべて表示' ) )
			. _btn( "! _showall() | .hrow" ,
				_ej( 'Show only representative items', '代表的な項目のみ表示' ) )
		)
		: '' 
	)
]));

//.. main table 中身作成
$main_table = '';
$cnt1 = 1;
$js_k1vals = [];
$js_k2vals = [];

foreach ( $sum_both as $name => $tcount ) {
	if ( $tcount == '' )
		continue; 

	$k2vals = '';
	$bars = '';
	$cnt2 = 1;

	//... 2D
	if ( GET_KEY2  ) {
		foreach ( (array)$key2list as $name2 ) {
			$num = $data2d[ $name ][ strtolower( $name2 ) ];
			$id = "#r{$cnt1}c{$cnt2}";
			if ( $num == 0  ) {
				//- 空のセル
				$k2vals .= _e( "td | $id | .dcnt | k:$cnt1 | sk:$cnt2" );
			} else {
				//- 色つけしたりする
				$bld = ( $num > $max2 * 0.75 ) ? 'bld' : '';
				$b =  substr( '00' . dechex(
					round( ( 1 - ( $num / $max2 ) ) * 250 ) 
				), -2 );

				$k2vals .= _e( 
					"td |.dcnt dlnk $bld | $id |"
					. " style:background:#ff$b$b | k:$cnt1 | sk:$cnt2"
				) . $num;
			}
			$js_k2vals[ $cnt2 ] = $name2;
			++ $cnt2;
		}

	//... 1D 棒グラフ
	} else {
		$e = $sum_emdb[ $name ];
		$p = $sum_pdb[ $name ];
		$bars =  _t( 'td | .dplot nw', ''
			. _bar( 'emdb',
				GET_DB == 'p' ? 0 : round( $e / MAX_TOTAL * 500 ),
				"k:$cnt1| d:e| ?EMDB: $e"
			)
			. _bar( 'pdb',
				GET_DB == 'e' ? 0 : round( $p / MAX_TOTAL * 500 ) ,
				"k:$cnt1| d:p| ?PDB: $p"
			)
		);
	}

	//... まとめ
	$main_table .= _e( 'tr'. ( MANY && $tcount == 1 ? '|.hrow' : '' ) )
		//- 主キー
		. ( strlen( $name ) > LIM_NAME_LEN
			//- 長い名前を省略表示
			? _e( "td|.dkey dlnk | k:$cnt1 | ? : $name" )
				. substr( $name, 0, LIM_NAME_LEN - 4 ) . _span( '.red', '...' )
			//- そのまま表示
			: _e( "td|.dkey dlnk | k:$cnt1" ). _valrep( $name )
		)
		//- 個々の数値
		. $k2vals

		//- 数・合計
		. _e( "td | .dcnt dlnk | k:$cnt1 | sk:s " ). $tcount

		//- バーのカラム
		. $bars
	;
	$js_k1vals[ $cnt1 ] = $name;
	++ $cnt1;
}

//.. テーブルヘッダ列文字列
if ( GET_KEY2 ) {
	//- 2D
	$head_row = ''; //- ヘッダ列
	$c = 1;
	foreach ( (array)$key2list as $n ) {
		$head_row .= _e( "th| sk:$c | #r0c$c" ). _valrep( $n, 'top' );
		++ $c;
	}
	$head_row .= _e( "th | sk:$c" ). TERM_TOTAL;

} else {
	//- 1D 棒グラフ版
	$head_row = _e( 'th | colspan:2' ). TERM_NUM_ENT;
}

//.. 隠し要素
//- 長い名前表示用の隠しボックス
$hidden =  _div( '#longkey', '' );

//- フォーカスで出るバー
if ( GET_KEY2 ) {
	foreach ( range( 1, max( $cnt1, $cnt2 ) ) as $i )
		$hidden .= _div( "#pb$i | .pbox", '' );
}

//- スクロールしたとき用の固定ヘッダ
$hidden .= _t( 'table | #fixhead', _e( 'tr| #fixtr', '' ) );

//.. main table 出力
$_simple->hdiv(
	_l( 'Statistics' ). ' - '. ( KEY2_NAME
		? KEY1_NAME. ' vs. '. KEY2_NAME
		: KEY1_NAME
	) , ''
	. _t( 'table| #maintable ', ''
		//- ヘッダ列
		. _t( 'thead', TR_TOP. _e( "th | #r0c0" ). KEY1_NAME. $head_row )
		//- 本体
		. $main_table
	)
	. _p( _kv([
		_fa( 'download' ). _l( 'Download' ) => _imp2([
			_a( _get_query([ 'download' => 'csv' ]), TERM_DL_CSV ) ,
			_a( _get_query([ 'download' => 'tsv' ]), TERM_DL_TSV ) ,
		])
	]))
	. $hidden
);

//. output
//..

//- テーブルパラメータ
$krep = [
	'submit_year'	=> 'submit',
	'temp_seg'		=> 'spec_temp',
	'reso_seg'		=> 'resolution'  ,
	'molw_seg' 		=> 'molw' 
];

//.. conf
$_simple->page_conf([
	'title' => _ej( 'EMN statistics', 'EMN 統計情報' ) ,
	'icon'	=> 'statistics' ,
	'sub'	=> _ej(
		'Statistics of 3DEM data in EMDB & PDB' ,
		'EMDBとPDBの3次元電子顕微鏡データの統計情報'
	) ,
	'openabout' => false ,
	'js'	=> [ 'stat' ] ,
	'jslib'	=> [ 'tablesorter' ] ,
	'docid' => 'about_stat' ,
])

//.. js var
->jsvar([
	'k1v' 		=> $js_k1vals ,
	'k2v' 		=> $js_k2vals ,

	//- 主キーカラムを数値としてソート？
	'sortint'	=> TABLE_DATA[ $key ][ 'mode' ] == 'INTEGER' ,

	'k1'		=> GET_KEY1 ,
	'k2'		=> GET_KEY2 ,
	'ck1'		=> 'c_'. ( $krep[ GET_KEY1 ] ?: GET_KEY1 ) ,
	'ck2'		=> 'c_'. ( $krep[ GET_KEY2 ] ?: GET_KEY2 ) ,
	'urlbase'	=> 'esearch.php?mode=table&c_title=1&' ,
	'maxval'	=> $max2
])

//.. css

->css( <<<EOD
//- テーブル
// #maintable th, #opttable th, #fixhead th { background: #488; color: white; }
// #maintable th, #opttable th, #fixhead th, #maintable td, #opttable td {
//	padding: 0 0.2em; margin: 2px;
// }

//- 固定ヘッダ
#fixhead { display:none; position: fixed; top: 1px; left: 1.5em; overflow: hidden }
#fixtr th { text-align: center }
//#fixhead tr { overflow: hidden }

//- オプションテーブル
#opttable th { white-space: nowrap }
.optitem, .optact {
	border: 1px solid #ddd; margin: 1px; padding: 1px 4px; line-height: 1.8em;
	background: white; white-space: nowrap; 
}
.optact, .optitem:hover { border: 1px solid #e55 }
.optact { box-shadow: 0 0 9px #f55; }

//- ソートカラム
#maintable th:hover{ background: #6bb; }
.headerSortUp   { background-image: url(img/arup.gif) !important; } 
.headerSortDown { background-image: url(img/ardown.gif) !important; } 
.headerSortUp, .headerSortDown { 
	background-repeat: no-repeat !important;
	background-position: left center !important;
	background-position: right center;
	padding-right: 20px !important;
} 

//- 左寄せカラム
.dcnt, .dkey { text-align: right; white-space: nowrap }
.dlnk { cursor: pointer }
.pcell, .dlnk:hover { color: blue; text-decoration:underline }

//- プロットのバー
.pbar { height: 15px; margin-left: 1px; vertical-align: middle }
.pbar:hover { opacity:0.8 }

//- 隠し列
.hrow { display:none }

//- 長いキー名
#longkey {
	display:none; border: 1px solid blue; position: absolute;
	background: white;
}

//- 2D用 プロットボックス
.pbox { display:none; background: #aaf; border: 1px solid blue; position: absolute; z-index: 1000}

EOD
)

//.. end
->out();

//. function
//.. _valrep
function _valrep( $val, $type = '' ) {
	//- $type: 左ヘッダ => 省略、上ヘッダ => 'top'、csvヘッダ => 'csv'
	if ( $val == 'N/A' )
		return 'n/a';
	
	$key = ! $type ? GET_KEY1 : GET_KEY2;
	if ( $key == 'method' )
		return MET_NAME_SHORT[ strtolower( $val ) ] ;
	if ( $key == 'temp_seg' )
		return "~ $val K";
	if ( $key == 'reso_seg' )
		return "~ $val A";
	if ( $type == 'top' && $key == 'submit_year' )
		return _span(
			'st:line-height 0.5em;' ,
			preg_replace( '/([0-9])/', '$1<br>', $val )
		);
	return $val;
}

//.. _bar
function _bar( $db, $wid, $opt = '' ) {
	return _img( ".pbar sd2| $opt | st:width:{$wid}px", [
		'emdb' => 'img/bar_green.gif' ,
		'pdb'  => 'img/bar_blue.gif' ,
	][ $db ] );
}
