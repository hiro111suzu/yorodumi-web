<?php
//- simpleフレームワーク用、細かなajaxスクリプトのまとめ
//. init
define( 'AJAX', true );
require( __DIR__. '/common-web.php' );
define( 'MODE', _getpost( 'mode' ) );

if ( TESTSV ) {
	function _test_wait(){ usleep( 300000 ); }
} else {
	function _test_wait(){}
}

_define_term( <<<EOD
TERM_SEARCH_FOR
	Search for "_1_"
	「_1_」を検索
EOD
);


//. Jmolのロードコマンド生成 使っていない
// if ( MODE == 'jmolopencmd' ) {
// 	$db = _getpost( 'db' );
// 	$id = _getpost( 'id' );
// 	if ( $db == '' ) {
// 		extract( $idarray = _getidinfo() );
// 	}
// 	if ( $db != '' ) {
// 		$cmd = _jmolloadcmd( $db, $id, [ 'asb' => _getpost( 'asb' )  ] );
// 		die( json_encode(
// 			$db == 'emdb' ? [ 'cmd' => $cmd, 1 ] : [ 'cmd' => $cmd ]
// 		));
// 	} else {
// 		die( json_endoce( 'msg', 'error no id for' . $id ) ); 
// 	}
// }

//. id2img
//- IDを受け取り、画像を返す
if ( MODE == 'id2img' ) {
	$o = new cls_entid( 'get' );

	//- emnならchemcompなし
	if ( _getpost( 'img_mode' ) == 'em' && $o->db == 'chem' )
		die();

	if ( ! $o->ex() ) 
		die();

	die( $o->ent_item_list() );
}

//. randimage
if ( MODE == 'randimg' ) {
	$ids = [];
	define( 'R_MODE', _getpost( 'rmode' ) ?: 'a' );

	//.. function _rand
	//- $a は追加するIDリスト, $idsに追加する、modeがallなら5このみ追加
	function _rand( $a ) {
		global $ids;
		$a = is_string( $a ) ? _idlist( $a ) : $a;
		$c = count( $a );
		if ( $c == 0 ) return;

		$oc = R_MODE == 'a' ? 5 : 20;
		$r = $c == 1
			? [ 0 ]
			: array_rand( $a, min([ $c, $oc ]) )
		;
		foreach ( $r as $i ) 
			$ids[] = $a[ $i ];
	}
	
	//.. DBごと
	if ( R_MODE == 's' || R_MODE == 'a' ) _rand( 'sasbdb' );
	if ( R_MODE == 'e' || R_MODE == 'a' ) _rand( 'emdb'   );
	if ( R_MODE == 'p' || R_MODE == 'a' ) _rand( 'pdb'    );
	if ( R_MODE == 'c' || R_MODE == 'a' ) _rand( 'chem'   );

	if ( R_MODE == 'hlx'  ) _rand( 'helical' );
	if ( R_MODE == 'n'    ) _rand( 'nmr'     );
	if ( R_MODE == 'icos' ) _rand( 'icos'    );

	//.. hybrid
/*
	if ( R_MODE == 'h' ) {
		$m = [];
		foreach ( array_keys( _json_load( DN_PREP. '/emn/fitdb.json.gz' ) ) as $did ) {
			list( $db, $id ) = explode( '-', $did );
			if ( $db == 'emdb' ) {
				$m[] = "e$id";
			} else {
				if ( _inlist( $id, 'epdb' ) )
					$m[] = $id;
			}
		}
		_rand( $m );
	}
*/
	//.. latest-pdb
	if ( R_MODE == 'l' || R_MODE == 'a' ) {
		_rand( _file( TESTSV
			? DN_PREP. '/newids/latest_new_pdbid.txt'
			: '/kf1/PDBj/ftp/pdbj/XML/pdbmlplus/latest_new_pdbid.txt'
		) );
	}

	//.. output
	shuffle( $ids );
	$ids = array_slice( $ids, 0, 20 );

	//- クッキー
	setcookie(
		'randimg' , 
		gzcompress( json_encode([
			'i' => implode( ' ', $ids ) ,
			'm' => R_MODE ,
			'k' => $kw
		])) , 
		time()+60*60*168 //- 7日
	); 

	//- time
	$time = [];
	foreach ( (array)$_time as $n => $t )
		$time[ $n ] = round( $t, 2 );

	die( _randimg_quick( $ids, R_MODE ) );
}

//. randimg coookie
if ( MODE == 'randimgck' ) {
	list( $mode, $ids ) = explode( ';', _getpost( 'ids' ) );
	die( _randimg_quick( explode( ',', $ids ), $mode ) );
}

//.. function _randimg_quick
//- ランダム選択の文字列とカタログを返す
function _randimg_quick( $ids, $mode ) {
	$mode = $mode ?: 'a';
	return _p( ''
		. TERM_RAND_SEL_ENT
		. _kakko( _subdata( 'trep', 'rand_sel_ent' )[ $mode ] )
		. _div(
			'#randimg_ids| .clboth|'. _atr_data( 'ids', "$mode;". implode( ',', $ids ) ) ,
			''
		)
	)
	. _ent_catalog( $ids )
	;
}

//. randimghist
if ( MODE == 'randimghist' ) {
	$ids = json_decode( _getpost( 'ids' ) );
	die( $ids ? _ent_catalog( $ids ): 'No data' );
}

//. doc pop
if ( MODE == 'doc' ) {
	_test_wait();
	die( _doc_div( _getpost( 'id' ) ) );
}

//. taxo pop
if ( MODE == 'taxo' ) {
	_test_wait();
	die( _obj('taxo')->from_key( _getpost( 'k' ) )->pop_cont() );
}

//. keyword pop
if ( MODE == 'kw' ) {
	_test_wait();
	$kw = _getpost( 'kw' );
	die( _t( 'h2| .h_sub', _l( 'Keyword' ). ": $kw" ) 
		. _ul([
			_ab( [ 'ysearch', 'kw' => _quote( $kw, 2 ) ] ,
				 IC_SEARCH. _term_rep( TERM_SEARCH_FOR, $kw )
			) ,
			_obj('wikipe')->show( $kw )
		])
	);
}

//. autocomp
if ( MODE == 'acomp' ) {
	define( 'G_WORD', strtr( _getpost( 'w' ), [ '"' => '', "'" => '' ] ) );
	define( 'G_LIST', _getpost( 'l' ) );

	if ( G_LIST == 'acomp_tx' ) {
		$dbfile = 'taxo';
		$col_word = 'name';
		$col_sort = 'pdb DESC';
	} else if ( G_LIST == 'acomp_omoid' ) {
		$dbfile = 'profdb_ss';
		$col_word = 'id';
		$col_sort = 'id';
	} else {
		$dbfile = 'autocomp_' . ( _instr( 'an', G_LIST ) ? 'an' : 'kw' );
		$col_word = 'w';
		$col_sort = _instr( 'em', G_LIST ) ? 'e DESC' : 'a DESC' ;
	}

	$o_sq = new cls_sqlite( $dbfile ) ;
	$sql = [
		'select'	=> $col_word ,
		'where'		=> G_WORD == '' ? '' : "$col_word LIKE '". G_WORD. "%'" ,
		'order by'	=> $col_sort ,
		'limit'		=> 10 ,
	];

	//- 実行
	$ans = $o_sq->qcol( $sql );
	if ( count( $ans ) < 3 ) { //- ヒットが少なければ、部分一致も含める
		$sql[ 'where' ] = _like( $col_word, G_WORD );
		$ans = array_unique( array_merge( $ans, $o_sq->qcol( $sql ) ) );
	}
	sort( $ans );
	$ret = '';
	foreach ( $ans as $w )
		$ret .= '<option value="' . htmlspecialchars( $w ) . '">';
	die( $ret );
}

//. wikipedia pop-up
if ( MODE == 'wp' ) {
	_test_wait();
	die( _obj('wikipe')->get( _getpost('k') )->show() );
}

//. dbid
if ( MODE == 'dbid' ) {
	_test_wait();
	die( _obj('dbid')->pop_contents(
		_getpost( 'key' ) ,
		_getpost( 'type' )
	) );
}

//. method
if ( MODE == 'met' || MODE == 'met2') {
	_test_wait();
	die( _div( '.pop_inner', _met_data( _getpost( 'key' ), MODE == 'met2' ) ) );
}

//. chemlinks
if ( MODE == 'chemlinks' ) {
	_test_wait();
	die( _imp2( _get_chemlinks( _getpost( 'id' ) ) ) );
}

//. omo_small_search
if ( MODE == 'omokage' ) {
	require_once( __DIR__. '/omo-calc-common.php' );
	$ret = '';
	$rank = 0;
	$list = ( new cls_omo_small_search() )->do( _getpost('id') );
	foreach ( $list as $ida => $score ) {
		if ( $score < OMOPRE_SCORE_LIMIT[ $rank ] ) break;
		if ( ! $ida ) continue;
		$ret .= _pop_omoitem( $ida, [ 'score' => $score ] );
		++ $rank;
	}
	die( $ret );
}
