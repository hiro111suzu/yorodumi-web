<?php
require( __DIR__. '/common-web.php' );
require( __DIR__. '/omo-web-common.php' );
require( __DIR__. '/omo-calc-common.php' );

//. init
//define( 'SCNORM', 2.17938653227261 );

_define_term( <<<EOD
TERM_START_VIEWER
	Start viewer
	ビューアを開始
TERM_CLOSE_VIEWER
	Close viewer
	ビューアを終了
TERM_MODEL_COLOR
	Blue: 50-point model, yellow: 30-point model
	青: 50点モデル, 黄色: 30点モデル
TERM_OMOKAGE_SCORE
	Omokage score
	Omokageスコア
TERM_OMOKAGE_VERSION
	(Omokage versin: 0.0)
	(Omokageバージョン: 0.0)
TERM_SCORE_HISTOGRAM
	Score histogram of randomly chosen 90,000 pair structure data
	スコアヒストグラム (無作為に選出した90,000ペアの構造から算出)
TERM_P_VALUE
	<i>p</i>-value
	<i>p</i>値
TERM_DEF_STAT
	definition / statistics
	定義・統計
TERM_SCORE
	score
	スコア
TERM_SCORE_MAX
	maximum value (for identical structures)
	最大値 (同一構造)
TERM_SCORE_HALF
	expected value (mean of randomly chosen pairs)
	期待値 (無作為抽出ペアの平均値)
TERM_SCORE_MIN
	minimum value (for infinitely different structures)
	最小値 (無限に異なる構造)

EOD
);

_add_lang([
	'Score'			=> 'スコア' ,
	'Score values' 	=> 'スコアの値' ,
	'Similarity score' => '類似度スコア' ,
	'Structures'	 => '構造' ,
	'Query'			=> 'クエリ' ,
	'Profiles'		=> 'プロファイル' ,
	'30-dot model'	=> '30点モデル' ,
	'50-dot model'	=> '50点モデル' ,
]);

//.. ID決定
$o_ids = [];
$in = preg_split( "/[,.| :\t]+/", trim( _getpost( 'id' ) ), 2 );
foreach ( [0, 1] as $n ) {
//	extract( _omo_getid( $in[ $n ] ) ) ;//- $db, $id
	$o_ids[ $n ] = new cls_omoid( $in[ $n ] );
//	$ids[ $n ] = $o_ids[ $n ]->ida;
//	extract( $o_omoid->get() ); //- $id, $db, $asb
//	 = new cls_omoid(

//	$ids[ $n ] = $id;
//	$dbs[ $n ] = $db;
}

//.. sqlite db
_mkdir( '/tmp/emn' );

if ( TESTSV ) {
	$tmpdn = '_temp';
	$dbfn = DN_DATA . '/profdb_s.sqlite';
} else {
	_mkdir( 'temp' );
	$tmpdn = 'temp';
	exec( 'php clean-dir.php temp 100 > /dev/null &' );
	exec( 'php clean-dir.php /tmp/emn 20 > /dev/null &' );

	$dbfn = DN_DATA . '/profdb_s.sqlite';
}


//.. gnuplot
define( 'PLOTTMP', "/tmp/emn/plot.png" );

define( 'PLOTSTR', 
	"echo \"set term png; set output '<imgfn>'; set yrange [ 0: ];"
	. "plot '/tmp/emn/p0.txt' title '{$o_ids[0]->ida}' with lines lw 4,"
	.      "'/tmp/emn/p1.txt' title '{$o_ids[1]->ida}' with lines lw 4 lc rgb '#008B8B'"
	. "\"| gnuplot"
);

//.. misc
define( 'G_COMPOS', _getpost('compos') ? true : false );

//. データ取得
$prof = [];
//$info = [];
$psiz = [];
$sq_prof = new cls_sqlite( 'profdb_s' );

$compos = [];
$sq_compos = new cls_sqlite( 'profdb_k' );
foreach ( $o_ids as $n => $o ) {
	if ( $o->db == 'user' ) {
		//- user data
		$a = _file( DN_OMO. "/userdata/{$o->id}.txt" );
		if ( count( $a ) > 0 ) {
			$prof[ $n ] = [
				_shrink( $a[0] ) ,
				_shrink( $a[1] ) ,
				_shrink( $a[2] ) ,
				[ $a[3], $a[4], $a[5] ]
			];
		}
	} else {
		//- DB data
		$prof[ $n ] = _bin2prof( 
			$sq_prof->q([ 'select' => 'data', 'where' => "id is \"{$o->ida}\"" ])
				->fetchColumn() 
		);
		$compos[ $n ] = _bin2compos(
			$sq_compos->q([ 'select' => 'compos', 'where' => "id is \"{$o->ida}\"" ])
				->fetchColumn() 
		);
	}

	//- jmolで書くときの玉の大きさ
	$v = round( ( $prof[ $n ][3][0] + $prof[ $n ][3][1] + $prof[ $n ][3][2] ) * 2 );
	$psiz[ $n ] = $v > 200 ? 200 : $v;
}

//.. shrink
function _shrink( $in ) {
	$in = explode( ',', $in );
	$cnt = count( $in );

	//- 何個に一個残すか
	$step = 3;
	if ( $cnt > 400  ) $step = 4;
	if ( $cnt > 1200 ) $step = 12;

	$out = [];
	$sum = 0;
	foreach ( $in as $i => $val ) {
		$sum += $val;
		if ( $i % $step > 0 ) continue;
		$out[] = $sum / $step;
		$sum = 0;
	}
	return $out;
}

//. スコア
$score = -1;
if ( count( $prof[0] ) > 0 and count( $prof[1] ) > 0 ) {
	$score = _getscore( $prof[0], $prof[1] ); 
}
define( 'PVAL_TABLE', _json_load( DN_DATA . '/omo_pval.json.gz' ) );
if ( $score > -1 )
	$pval = PVAL_TABLE[ floor( $score * 10000 ) ];

//. css
$_simple->css( <<<EOD
.ilb { display: inline-block; vertical-align: top; max-width: 500px;}
.jmole { display: none }
.dbox { clear: both; }
.plt { display: inline-block; }
.plt img { width: 250px; }
#inf0 { color: red }
#inf1 { color: #008B8B }
#jmolbox0,#jmolbox1 {
	border: 1px solid $col_medium; width: 250px; height: 250px; 
	display: inline-block;
}
.sbar {
	border: 1px solid #aaa; padding: 0px; margin: 0; display: inline-block;
	height: 0.7em; width: 10em;
}
.sbari { height: 100%; background: #777; }

EOD
);

//. contents
//.. form
$_simple->hdiv( 'Query' ,
	_t( 'form| method:get', ''
		. _input( 'search', '#idbox| name:id| size:20', _getpost_safe( 'id' ) )	
		. _input( 'submit' )
	)
	,
	[ 'hide' => count( $o_ids ) < 2 ]
);

//.. 構造
$col1 = 'yellow';
$col2 = 'blue';
$cmd_common = ';'
. "select 1.0; color $col1;"
. "select 2.0; color $col2;"
. 'model all; center all; select all; rotate best;'
;

$compos_table = [];
$t = [ '', 'alpha', 'beta', 'nuc', '1st', '2nd', '3rd' ];
foreach ( $compos as $n => $c ) {
	if ( $c == '' ) continue;
	$s = '';
	foreach ( $c as $k => $v ) {
		$v = $v /65535;
		$s .= TR.TH. $t[$k] .TD. round( $v, 4 ) .TD. _levelbar( $v * 100 ) ;
	}
	$compos_table[ $n ] = _t( 'table', $s );
}
$o_jmol = new cls_jmol;

$_simple->hdiv( 'Structures', ''
	. _btn( '#jmolshowbtn | !_jmolshow(1)', TERM_START_VIEWER )
	. _btn( '.jmole | !_jmolshow(0)'      , TERM_CLOSE_VIEWER )
	. _gmfit( $o_ids[0]->ida, $o_ids[1]->ida, '' )
	. _t( 'table | st:table-layout:fixed;width:100%', ''
		//- エントリ情報列
		.TR 
		.TD. _databox( $o_ids[0], 0 )
		.TD. _databox( $o_ids[1], 1 )

		//- compos
		. ( G_COMPOS ? 
			TR
			.TD. $compos_table[0]
			.TD. $compos_table[1]
		: '' )

		//- Jmol とコントローラー列
		. _e( 'tr | .jmole' )
		. _e( 'td | st:text-align:right' )
		. _div( '#jmolbox0', $o_jmol->jsobj([
			'db'	    => 'vq' ,
			'id'	    => $o_ids[0]->ida ,
			'init'	    => 'select 1.0 or 2.0; cpk -' . $psiz[0] . $cmd_common ,
			'jmolid'    => 'vq0',
			'autostart' => 1
		]))
/*
		. _div( '#ctrl0', ''
			. _md( 0, 30 )
			. _md( 1, 50 )
		)
*/
		. TD
		. _div( '#jmolbox1', $o_jmol->jsobj([
			'db' 	=> 'vq',
			'id' 	=> $o_ids[1]->ida ,
			'init' 	=> 'select 1.0 or 2.0; cpk -' . $psiz[1] . $cmd_common ,
			'jmolid' => 'vq1',
			'autostart' => 1
		]))
/*
		. _div( '#ctrl1', ''
			. _md( 0, 30 )
			. _md( 1, 50 )
		)
*/
	)
	. TERM_MODEL_COLOR
);

//.. score
$wid_scimg = 300;
$c = $wid_scimg / 2;
$x = round( ( $score * $c ) + $c );
if ( $score > -1 ) { 
	$_simple->hdiv( 'Similarity score', ''
		. _p( _span( '.bld', TERM_OMOKAGE_SCORE )
			. ':'
			. _span( 'st:font-size:x-large', $score )
		)
		. _p( TERM_OMOKAGE_VERSION )
		. _p( _span( '.bld', _l( 'Statistics' ) ) )
		. _div( '#sc_box', ''
			. _img( '#sc_img', DN_DATA. '/plot_omosc.png' )
			. _div( "#sc_bar| st:left: {$x}px" ) 
			. _div( '#sc_m1', '-1' )
			. _div( '#sc_0', '0' )
			. _div( '#sc_1', '1' )
		)
		. _p( TERM_SCORE_HISTOGRAM )
		. _p(
			_span( '.bld', TERM_P_VALUE ). ': '
			. _span( 'st:font-size:x-large', "$pval %" )
		)
		. _table_2col([
			TERM_SCORE	=> TERM_DEF_STAT ,
			'1'			=> TERM_SCORE_MAX ,
			'0.9'		=> PVAL_TABLE[9000]. ' %' ,
			'0.85'		=> PVAL_TABLE[8500]. ' %' ,
			'0.8'		=> PVAL_TABLE[8000]. ' %' ,
			'0.7'		=> PVAL_TABLE[7000]. ' %' ,
			'0.6'		=> PVAL_TABLE[6000]. ' %' ,
			'0'			=> TERM_SCORE_HALF ,
			'-1'		=> TERM_SCORE_MIN ,
		], [ 'topth' => true ])
	);
}

$_simple->css( <<<EOD
#sc_box { width: {$wid_scimg}px; height: 100px; border: 1px solid $col_medium;
	position: relative; margin-bottom: 1em;
}
#sc_img { width: 100%; height: 100% }
#sc_m1 { position: absolute; top: 100px; left: 0;}
#sc_0  { position: absolute; top: 100px; left: 50%;}
#sc_1  { position: absolute; top: 100px; right: 0;}
#sc_bar { position: absolute; height: 100%; width: 1px; top: 0;
	border: 1px solid rgba( 255, 255, 255, 0.5); background: red;}
EOD
);


//.. profiles

define( 'PROFSTR', _ej( [
	"30-dot model" ,
	"50-dot model" ,
	"25-dot of outer 50-dot model" ,
	"sigma values of PCA"
], [
	"30点モデル" ,
	"50点モデル" ,
	"50点モデルの外側25点" ,
	"主成分の標準偏差"
]) );


$plots = '';
if ( count( $prof[0] ) ) {
	foreach ( $prof[0] as $n => $p ) {
		//- プロファイルデータ保存
		foreach ( [0,1] as $i ) {
			file_put_contents(
				"/tmp/emn/p$i.txt", 
				implode( "\n", (array)$prof[ $i ][ $n ] ) . "\n"
			);
		}
		$fn = "$tmpdn/plot-$n-" . $o_ids[0]->ida .'-'. $o_ids[1]->ida . '.png';
		//- gnuplot 実行
		exec( $cmd = strtr( PLOTSTR, [ '<imgfn>' =>  $fn ] ) );
		$plots .= _div( '.plt', _ab( $fn, _img( $fn ) ) . BR
			. "<b>#$n</b>: " . $sc[ $n ] . BR
			. PROFSTR[ $n ] . BR
//			. $cmd
		);
	}
}

$_simple->hdiv(
	_ej( 'Profiles', 'プロファイル' ), $plots, [ 'hide' => true ]
);



//.. 
function _md( $n, $v ) {
	return _btn( "!_j('model $n')", _l( "$v-dot model" )  );
}


//. js
$js = [ <<<EOD
var o_jmole = $( '.jmole' );

function _j( c ) {
	Jmol.script( 'jmolvq0', c ); 
	Jmol.script( 'jmolvq1', c ); 
}
function _jmolshow( f ) {
	if ( f ) {
		o_jmole.show();
		$( '#jmolshowbtn' ).hide();
	} else {
		o_jmole.hide();
		$( '#jmolshowbtn' ).show();
	}
}

EOD
];
//. about
$_simple->about = _ej([
	'Results of pariwise shape comparison by Omokage are shown.' ,
	'Documentation is under construction.'
],[
	'Omokageによる形状比較の結果を表示するページです' ,
	'解説は現在作成中です'
]);

//. output
$_simple->out([
	'title' => _ej( 'Omokage pariwise', 'Omokage比較' ) ,
	'sub'	=> _ej( 'Shape comparison by Omokage', 'Omokageによる概形比較' ) ,
	'icon'	=> 'omokage' ,
	'js'	=> $js ,
	'jslib' => [ 'jmol', 'jmol3', 'jmolgl' ]
]);

//. function
//.. _databox
function _databox( $o, $n ) {
	if ( $o == '' ) return;
	return ''
		. ( $o->imgfile != '' ? _img( '.left', $o->imgfile ) : '' )
		. _span( "#inf$n", ''
			. $o->desc()
			. BR
			. _links([
				'id' => $o->id, 'ida' => $o->ida, 'db' => $o->db,
				'pages'  => 'omos'
			])
		)
	;
}

//.. _levelbar
function _levelbar( $v ) {
	return _div( '.sbar', // . ( $wid != '' ? ' | st:width:200px' : '' ) ,
		_div( ".sbari | st:width:$v%" ) );
}
