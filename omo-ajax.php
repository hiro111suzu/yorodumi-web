<?php

//. init
ini_set( "memory_limit", "512M" );
define( 'AJAX', true );
require( __DIR__. '/common-web.php' );
require( __DIR__. '/omo-web-common.php' );
_robokill();

//.. term
_define_term( <<<EOD
TERM_ERR_VQ
	Failed to generate vectors. Please, check the structure data, again.
	ベクトル量子化に失敗しました。構造データを再確認してください。
TERM_ERR_BADVQ
	Numbers of vectors is wrong. Coordinate files with 30 and 50 points are required.
	ベクトル数が不正です。30個と50個の点を持つ座標が必要です。
TERM_ERR_NODATA
	No prepared data for _1_
	データエントリ '_1_' の準備データがありません。
TERM_ERR_NOSIM1
	No similar structure found (stage1)
	類似形状データが見つかりませんでした。(stage1)
TERM_ERR_NOSIM2
	No similar structure found (stage2)
	類似形状データが見つかりませんでした。(stage2)
TERM_ERR_DB
	Database process is busy. Please, retry later.
	現在データベースのプロセスが混み合っています。後ほど再度お試しください。
TERM_GMFIT_RERANK_RES
	Result of gmfit re-ranking
	gmfitによる再順位付けの結果
TERM_BACK_GMM_RERANK
	Back to the result before re-ranking
	再順位付け前の結果に戻る

TERM_CSV_DOWNLOAD
	Download CSV file (for MS-EXCEL, etc.)
	CSVファイルをダウンロード (EXCELなどに)

TERM_RERANK_TOPX
	Re-rank top _1_  data by _2__
	上位 _1_ データを_2_で再順位付け_

FOUND_FROM_ALL
	_found from all _1_ structures
	全_1_構造データから見つかった

EOD
);

//.. ID決定
$o_omoid = new cls_omoid();
extract( $o_omoid->get() ); //- $id, $db, $asb

define( 'ID', $id );
define( 'DB', $db );

//.. getpost
define( 'FLG_GMREF', (boolean)_getpost( 'gmref' ) );

//. 簡単な AJAX reply

//.. subject data
if ( _getpost( 'subj' ) ) {
	die( $_simple->hdiv( TERM_SUBJECT_STR,
		$db == 'user'
			? _l( 'Uploaded data' ). BR. (
				file_exists( $j = _fn( 'user_json', $id, ADD_PRE ) )
					? _table_2col( _json_load( $j ) )
					: _l( 'Loading...' )
			)
			: _databox_subject( $o_omoid )
		,
	[ 'id' => 'subject', 'return' => true ] ));
}

//.. おすすめキーワードを帰す
if ( _getpost( 'kw_recom' ) ) {
	$a = [];
	if ( $o_omoid->db == 'pdb' ) {
		$a = (array)$o_omoid->o_id->mainjson()->struct_keywords[0];
	} else if ( $o_omoid->db == 'emdb' ) {
		$a = [
			$o_omoid->o_id->mainjson()->deposition->keywords
		];
	} else if ( $o_omoid->db == 'sasbdb' ) {
		foreach ( (array)$o_omoid->o_id->mainjson()->entity as $c ) {
			$a[] = $c->pdbx_description;
		}
	}
	$kws = [];
	foreach ( $a as $k ) {
		$kws = array_merge( $kws, explode( ',', strtr( $k, [ ';' => ',' ] ) ) );
	}
	foreach ( $kws as $i => $k )
		$kws[$i] = trim( $k );
	$out = [];
	foreach ( _uniqfilt( $kws ) as $k ) {
		$out[] = _btn( '!_kw_suggest.add(this)|type:button', trim( $k ) );
	}
	die( implode( ' ', $out ) ?: 'not found' );
}

//.. IDボックスに入力
if ( _getpost( 'ent' ) ) {
	//- ヒット無し
	if ( ! $o_omoid->ex_vq() ) {
		$k = _getpost( 'id' );
		die( _ab( [ 'ysearch', 'kw' => $k ] ,
			IC_SEARCH. _term_rep( TERM_KW_SEARCH, $k )
		));
	}
	$o = [];
	if ( $o_omoid->db == 'pdb' ) {
		//- PDBデータなら、アセンブリー画像も表示
		$i4 = $o_omoid->id;
		$str = _term_rep( TERM_ASB_IMGS, $i4 );
		$url = $o_omoid->u_quick() . '#h_assembly';
		foreach ( [ 'd' ] + range( 0, 20 ) as $i ) {
			$oi = new cls_omoid( "$i4-$i" );
			if ( ! $oi->ex_vq() ) continue;
			$o[] = _div( ".enticon enticon_cap | !_idbox.set('$i4-$i')" ,
				_img( $oi->imgfile ) . _p( $i == 'd' ? _l( 'deposited' ) : "#$i" )
			);
		}
	} else if ( $o_omoid->db == 'sasbdb' ) {
		//- SASBDBデータなら、モデル画像も表示
		$sid = $o_omoid->info[ 'ID' ];
		$str = _term_rep( TERM_SASMODEL_IMGS, $sid );
		$url = $o_omoid->u_quick() . '#h_models';
		foreach ( (array)_sas_info( 'id2mid', $sid ) as $mid ) {
			$oi = new cls_omoid( "s$mid" );
			if ( ! $oi->ex_vq() ) continue;
			$o[] = _div( ".enticon enticon_cap | !_idbox.set('s$mid')" ,
				_img( $oi->imgfile ) . _p( "#$mid" )
			);
		}
	}
	die(
		_databox_subject( $o_omoid, true ) 
		. ( count( $o ) > 1 ? 
			_p( _ab( $url, $str ) ). implode( $o )
		: '' )
	);
}
//. init #2 検索条件とファイル名
$gmrefnum = _getpost( 'gmrefn' );

//.. フィルタ条件
$c = _getpost( 'mode_compos' );
define( 'MODE_COMPOS'  , $c == 'no' ? '' : $c  );
define( 'LIM_COMPOS'   , _getpost( 'lim_compos' ) ?: 0.1 );
define( 'KW_ORIG'      , _getpost( 'kw' ) );
define( 'DBID_INC'     , _getpost( 'dbid_inc' ) );
define( 'DBID_EXC'     , _getpost( 'dbid_exc' ) );
$d = _getpost( 'mode_db' );
define( 'MODE_DB'     , $d == 'all' ? '' :  $d );

//.. ディレクトリ
$bn_res = $id
	. ( MODE_COMPOS == '' ? ''
		: '_m='. MODE_COMPOS . (round( LIM_COMPOS * 65535 ) )
	)
	. ( KW_ORIG. DBID_INC. DBID_EXC == '' ? ''
		: '_kw='. md5( implode( '|', [ KW_ORIG. DBID_INC. DBID_EXC ] ) )
	)
	. ( MODE_DB == '' ? ''
		: '_db='. MODE_DB
	)
;

define( 'DN_RES' , DN_OMO. '/results'  . ( TESTSV ? '_pre' : '' ) );
define( 'DN_USER', DN_OMO. '/userdata' . ( TESTSV ? '_pre' : '' ) );

define( 'FN_RES',    DN_RES. "/$bn_res.json.gz" );
define( 'FN_ERR',    DN_RES. "/$bn_res-err.txt" );
define( 'FN_STDOUT', DN_RES. "/$bn_res-stdout.txt" );
define( 'FN_GF_RES', DN_RES. "/$bn_res-gmref-$gmrefnum.json.gz" ); //- gmfit リファイン
define( 'FN_STATUS', DN_RES. "/$bn_res.txt" ); //- ステータスファイル
define( 'TIME_DB',   filemtime( DN_DATA . '/profdb_s.sqlite' ) ); //- DBタイムスタンプ

//.. コマンド
define( 'CMD_SEARCH', TESTSV
//	? 'php ' .DN_OMO. "/search.php"
	? 'php ' .DN_OMO. "/search_test.php"
	: "ssh filesv3-p php /data/yorodumi/omokage/search.php"
);
define( 'CMD_GMCONV', DN_GMFIT_BIN. '/run_gmconvert.cgi' );
define( 'CMD_GMREF', 'php omo_gmref.php' );

//.. DBより古かったら 結果ファイルを消す
foreach ( [ FN_RES, FN_ERR, FN_GF_RES, FN_STATUS ] as $fn ) {
	if ( ! file_exists( $fn ) ) continue;
	if ( TIME_DB > filemtime( $fn ) )
		unlink( $fn );
}

//. 実行系
//.. dbid
if ( _getpost( 'dbid' ) ) {
	if ( ! file_exists( FN_RES ) ) die( 'no result: ' .FN_RES );
	$json = _json_load( FN_RES );
	if ( $json['e'] ) die('no entry');
	$items = [];

	//... ヒットエントリごと、スコアで重み付け
	foreach ( $json['s'] as $id => $score ) {
		$score -= 0.4;
		if ( $score < 0 ) break;
		//- IDを変換
		$id = substr( $i, 0, 1 ) == 's' 
			? _sas_info( 'mid2id', $id )
			: explode( '-', $id )[0]
		;
		foreach ( _obj('dbid')->strid2keys( $id ) as $i ) { 
			$items[ $i ] += $score;
		}
	}

	//... dbidアイムごと、構造データ数で重み付け
	foreach ( $items as $key => $num ) {
		$count = _ezsqlite([
			'dbname' => 'dbid' ,
			'select' => 'num' ,
			'where'	 => [ 'db_id', $key ] ,
		]);
		if ( ! $count ) {
			$items[ $key ] = 0;
		} else {
			$count = pow( $count, 0.5 );
			$items[ $key ] = $items[ $key ] / $count;
		}
	}
	arsort( $items );

	//... 結果
	$ret = [];
	foreach ( array_keys( array_slice( array_filter( $items ), 0, 100 )) as $key ) {
		$ret[] = _obj('dbid')->pop_pre( $key, 
			TERM_DBID_FILT. ': '
			. _selopt(
				_attr( 'onchange', "_dbid.filt(\"$key\",this)" ) ,
				[
					0 => '-' ,
					1 => TERM_INC_FILTER ,
					2 => TERM_EXC_FILTER ,
				]
			)
			. _input( 'submit', 'form:form_filt' )
		);
	}
	die( _imp2( $ret ) );
}


//.. エラーを吐いている
if ( TEST && file_exists(FN_ERR) && filesize(FN_ERR) > 0 ) {
	$err = file_get_contents( FN_ERR );
	unlink( FN_ERR );
	//- qvolはうまくいっていても 2>にメッセージを吐く
	if ( ! _instr( '...qvol> ', $err ) && ! _instr( '...qpdb> ', $err ) ) {
		_out([
			'end' => true ,
			'out' => $_simple->hdiv(
				'Error on comp system' ,
				strtr( $err, [ "\n" => BR.BR ] ) ,
				[ 'return' => true ]
			) ,
			'test' => $testinfo ,
		]);
	}
}

//.. status を返して終了
if ( ! file_exists( FN_RES ) && file_exists( FN_STATUS ) ) {
	$status = _file( FN_STATUS );
	$sec = time() - array_shift( $status );//- 1行目は時刻

	$gmstat = '';
	//- GMM
	if ( $o_omoid->db == 'user' ) {
		if ( file_exists( $fn = DN_USER. "/$id.gmmlog" ) ) {
			$gmmlog = strtr(
				implode( BR, _file( $fn ) ),
				[ DN_OMO => '?', 
					'/var/www/html/gmfit/cgi-bin/' => '?' ]
			);
			if ( _instr( 'ERROR', $gmmlog ) ) {
				$s = 'ERROR !' . BR . $gmmlog;
			} else {
				$s = LOADING . 'in progress...';
				$s .= BR . $gmmlog;
				if ( file_exists( $fn = DN_USER. "/$id.gmm" ) )
					$s = 'finished!';
			}
		} else {
			$s = LOADING . 'Starting...';
		} 
		$gmstat = "GMM conversion: $s". BR. '----------' .BR;
	}

	//- レベル表示
	$p = LOADING;
	if ( is_numeric( $status[0] ) ) {
		$p = _levelbar( array_shift( $status ) );
	}

	if ( $sec > 900 ) {
		unlink( FN_STATUS );
	} else {
		_out([
			'end' => false ,
			'out' => $_simple->hdiv(
				'Search status' ,
				$gmstat . implode( BR, $status ). $p ,
				[ 'id' => 'status', 'return' => true ]
			),
			'test' => "" ,
		]);
	}
}
//.. 検索開始

if ( ! file_exists( FN_RES ) ) {
	$cmd = _run_program( CMD_SEARCH, [
		'id'			=> $id ,
		'bn'			=> $bn_res ,
		'thr'			=> _getpost('thr') ,
		'mode_compos'	=> MODE_COMPOS ,
		'lim_compos'	=> LIM_COMPOS ,
		'kw'			=> urlencode( KW_ORIG ) ,
		'dbid_inc'		=> urlencode( DBID_INC ) ,
		'dbid_exc'		=> urlencode( DBID_EXC ) ,
		'db'			=> MODE_DB == 'all' ? '' : MODE_DB ,
		'db_pre'		=> TESTSV ? 1 : '',
		'2>'. FN_ERR ,
//		'> /dev/null &',
		'>'. FN_STDOUT. ' &'
	]);

	//- GMM コンバート発動
	if ( substr( $id, 0, 1 ) == '_' && ! TESTSV ) {
		//- 解凍中だったら待ってみる
		$gfn = DN_USER. "/$id*.gz";
		sleep( 3 );
		foreach ( range( 0, 10 ) as $d ) {
			if ( count( glob( $gfn ) ) == 0 ) break;
			sleep( 1 );
		}
/*
		$cmd = DN_GMFIT_BIN. '/run_gmconvert.cgi dir=omokage job=start '
			. 'M=' . ( $thr == '' ? 'A2G ' : 'V2G ' )  //- 原子モデルか、マップか
			. "id=$id "
			. ( $thr == '' ? '' : "level=$thr " )
			. ' > /dev/null &'
		;
		exec( $cmd, $cmd_out2 );
*/
		$cmd2 = _run_program( CMD_GMCONV, [
			'dir '	=> 'omokage' ,
			'job'	=> 'start' ,
			'M'		=> ( $thr == '' ? 'A2G ' : 'V2G ' ),  //- 原子モデルか、マップか
			'id'	=> $id ,
			'level'	=> $thr ,
			' > /dev/null &'
		], );

	}

	_out([
		'end' => false ,
		'out' => $_simple->hdiv(
			'Search status' ,
			'Starting search system' . LOADING ,
			[ 'return' => true ]
		) ,
		'test' => _simple_table([
			'comp serv' => gethostname() ,
			'cmd'		=> $cmd ,
			'GMM conv'	=> $gmmlog ,
			'out'		=> $cmd_out ,

			'cmd2'		=> $cmd2 ,
			'out2'		=> implode( BR, (array)$cmd_out2  ) ,
			'file name' => FN_RES ,
//			'ERROR msg' => ( file_exists( FN_ERR )
//				? file_get_contents( FN_ERR )
//				: 'no error'
//			)
		])
	]);
}

//.. csvダウンロード
if ( _getpost( 'csv' ) || _getpost( 'tsv' ) ) {
	$tsv = _getpost( 'tsv' );
	$sep = _getpost( 'sep' ) ?: ',' ;
	if ( _getpost( 'gmref' ) ) {
		//- gmmリファイン
		$out[] = [ 'rank', 'corr coeff', 'database', 'id', 'assembly', 'title' ];
		$json = _json_load( FN_GF_RES );
		$j = $json[ 'cc' ];
		arsort( $j );
	} else {
		//- 検索結果
		$out[] = [ 'rank', 'score', 'database', 'id', 'assembly', 'title' ];
		$json = _json_load( FN_RES );
		$j = $json[ 's' ];
	}

	//- まとめ
	$rank = 1;
	foreach ( $j as $i => $sc ) {
		$o_omoid = new cls_omoid( $i );
		$out[] = [
			$rank,
			$sc,
			strtoupper( $o_omoid->db ),
			$o_omoid->id,
			$o_omoid->asb ,
			$tsv ? $o_omoid->title() : _quote( $o_omoid->title(), '"' ) 
		];
		++ $rank;
	}
	_csv( "omokage_$id." . ( $tsv ? 'tsv' : 'csv' ), $out, $tsv ? "\t" : ',' );
}

//.. 表示モード、ページ定義
//- 表示モード
$g = _getpost( 'list' );
if ( $g == '' ) {
	define( 'LISTMODE', $_COOKIE[ 'omo_ls' ] );
} else {
	define( 'LISTMODE', $g == 1 ); //- 解除は2
	if ( LISTMODE ) {
		setcookie( 'omo_ls', '1', time()+60*60*24*365 ) ;
	} else {
		setcookie( 'omo_ls', '', time() - 3600 ) ;
	}
}

//- 1ページごとのデータ数
define( 'DATA_PER_PAGE', 100 );

//- 現在のページ
define( 'CUR_PAGE'	, _getpost( 'pg' ) ?: 0 );
define( 'D_START'	, CUR_PAGE * DATA_PER_PAGE );
define( 'D_END'		, D_START + DATA_PER_PAGE );

//.. gmfit refine
if ( FLG_GMREF ) {
	//- 検索結果ファイルがない
	if ( ! file_exists( FN_RES ) ) {
		_out([
			'end' => true ,
			'out' => "No serch result for $id" 
		]);
	}

	//- ログファイルが古ければ消す
	if ( file_exists( FN_GF_RES ) ) {
		if ( TIME_DB > filemtime( FN_GF_RES ) )
			_del( FN_GF_RES );
	}
	//- 強制再計算
//	_del( FN_GF_RES );

	//... ログファルがない、計算開始
	$fn_err = 'err.txt';
	if ( ! file_exists( FN_GF_RES ) ) {
		touch( FN_GF_RES );
		exec( 'chmod 777 ' . FN_GF_RES );
//		$er_out = TEST ? "2> $fn_err" : '';
		$er_out = '';
		$cmd = _run_program( CMD_GMREF,
			[ $id, $gmrefnum, $bn_res, $er_out, '> /dev/null &' ] 
		);
		_out([
			'end' => false,
			'out' => $_simple->hdiv(
				TERM_STATUS_ANALYSIS ,
				'Starting analysis '. LOADING ,
				[ 'id' => 'status', 'return' => true ]
			),
			'test' => $cmd
		]);
	}

	//... 計算中、進捗出力
	if ( file_exists( $fn_err ) && filesize( $fn_err ) > 5 ) {
		$e = file_get_contents( $fn_err );
		_del( $fn_err );
		_out([
			'end' => true,
			'out' => TEST
				? 'error in omo_gmref' . _t( 'pre', $e )
				: 'Computation error'
		]);
	}
//	die();
	$cnt = $num = $status = $cc = $rnk = '';
	extract( (array)_json_load( FN_GF_RES ) );
		//- 計算中なら( $cnt, $num, $status ) 
		//- おわってたら ( $cc, $rnk )
	if ( $cc == '' ) {
		_out([
			'end' => false ,
			'out' => $_simple->hdiv(
				TERM_STATUS_ANALYSIS ,
				$num
					? "$cnt / $num data analyzed" . BR . _levelbar( $status )
					: 'no result info: ' . ( TEST ? FN_GF_RES : '' )
				,
				[ 'id' => 'status', 'return' => true ]
			)
		]);
	}

	//... 計算終了、結果出力
	$_out = '';
	arsort( $cc );
	$rank = 0;
	foreach ( $cc as $i => $sc ) {
		++ $rank;
		if ( $rank <= D_START || D_END < $rank ) continue;
		$o = new cls_omoid( $i );
		$_out .= _databox_result( $o, [
			'rank'   => $rank,
			'cc'     => $sc ?: '_' ,
			'fprank' => $rnk[ $i ] //- もとのランク
		]);
	}

	_out([
		'end' => true ,
		'out' => $_simple->hdiv(
			TERM_GMFIT_RERANK_RES ,
			_resultbox( $_out, count( $cc ) ) ,
			[ 'return' => true ]
		) ,
		'test' => ''
	]);
}

//.. 検索結果
//- ステータスファイル消せてなかったら、消しておく
if ( file_exists( FN_STATUS ) )
	unlink( FN_STATUS );

$json = _json_load( FN_RES );
$num_passed	= $json[ 'n' ];
$num_all	= $json[ 'a' ];
$testinfo	= _simple_table( $json[ 'i' ] );

//... error
if ( $json[ 'e' ] != '' || $num_passed == '' ) {
	_out([
		'end' => true ,
		'out' => $_simple->hdiv( 'Error message' ,
			(
				[
					'vq'     => TERM_ERR_VQ ,
					'badvq'  => _term_rep( TERM_ERR_BADVQ, ID ) ,
					'nodata' => _term_rep( TERM_ERR_NODATA, ID ) ,
					'nosim1' => TERM_ERR_NOSIM1 ,
					'nosim2' => TERM_ERR_NOSIM2 ,
					'db'	 => TERM_ERR_DB ,
				][ $json[ 'e' ] ] ?: _l( 'Unknown error' ) 
			)
			. ( MODE_DB. KW_ORIG == '' ? '' :
				_filter_form([ 'no_kw_suggest' => true ])
			) ,
			[ 'return' => true ]
		)
		,
		'test' => $testinfo ,
	]);
}

//... result
$rank = 0;
foreach ( $json[ 's' ] as $i => $sc ) {
	++ $rank;
	if ( $rank <= D_START || D_END < $rank ) continue;
	$o = new cls_omoid( $i );
	$_out .= _databox_result( $o, [ 'rank' => $rank, 'score' => $sc ] );
}

_out([
	'end' => true,
	'out' => $_simple->hdiv(
		'Search result' ,
		_resultbox( $_out, $num_passed, $num_all ) ,
		[ 'id' => 'result', 'return' => true ] 
	),
	'test' => $testinfo
]);

//. function
//.. _out
function _out( $a ) {
	die( json_encode( $a ) );
}

//.. _databox_subject
function _databox_subject( $o_id, $flg_submit_btn = false ) {
	//- IDボックスなら、submitボタンも付ける
	$btn = $flg_submit_btn
		? _input( 'submit', 'form:id_form', _l( 'Search' ) )
		: ''
	;
	
	return _div( '.clearfix',
		$o_id->img_link( $o_id->u_quick() )  //- 画像
		. _div( '', $o_id->desc() . $btn ) 
	);
}
//.. _databox_result
function _databox_result( $o_id, $data = [] ) {
	$rank = $score = $cc = $fprank = '';
	extract( $data );

	//... 表示情報
	$this_omokage = _ab([ 'omoview', 'id' => $_GET['id']. ",$o_id"] , TERM_DETAIL_OMOCOMP );
	//- ランクとスコア
	if ( $cc == '' ) {
		//- omokage結果
		$rankscore = [
			_span( '.rank', "#$rank" ) ,
			_levelbar( $score * 100 ) ,
			_l( 'Score' ) .": $score "
		];
		$items_comp = [
			_gmfit( $_GET['id'], $o_id ) ,
			$this_omokage ,
		];

	} else if ( $cc == '_' ) {
		//- gmfit 準備中
		$rankscore = [ _p( '.green', TERM_UNDER_PREP ) ];
		$items_comp = [
			$this_omokage ,
		];
	} else {
		//- gmfit 結果
		$rankscore = [
			_span( '.rank', "#$rank" ) ,
			_levelbar( $cc * 100 ) ,
			_test( " (<-$fprank) " ) ,
			TERM_CC. ": $cc " ,
		];
		$items_comp =[
			_gmfit( $_GET['id'], $o_id, TERM_DO_FITTING ) ,
			$this_omokage
		];
	}


	//... まとめ
	$div = _pop(
		implode( ' ', $rankscore ) .BR. _kv( $o_id->info )
		, ''
		. _t( 'h2| .h_sub', TERM_STR_COMPARISON )
		. _ul( $items_comp )
		. _t( 'h2| .h_sub', TERM_DATA_ENTRY )
		. _ul( $o_id->items_entry() )
	)
	. _p( $o_id->title() )
	;

	//... 出力
	return LISTMODE
		? _div( '.clearfix topline', ''
			. $o_id->img_link([ 'omoview', 'id' => $_GET['id']. ",$o_id" ])
			. _div( '', $div )
		)
		: _pop( $o_id->imgfile, $div, [
			'type' => 'img',
			'trgopt' => ".iimg enticon | ?"
					. strtoupper( $o_id->db ) . "-{$o_id->id}: {$o_id->title()}"
		])
	;
}

//.. _httpq
function _httpq( $ar, $del = [] ) {
	$get = $_GET;
	unset( $get[ 'lang' ], $get[ 'do' ] );
	foreach ( (array)$del as $k ) {
		unset( $get[ $k ] );
	}
	$ret = [];
	foreach ( array_merge( $get, $ar ) as $k => $v ) {
		$ret[$k] = strip_tags( $v );
	}
	return '?' . http_build_query( $ret );
}

//.. _resultbox
function _resultbox( $result, $num_data, $num_all = 0 ) {
	global $id, $gmrefnum;

	//... ページオプション
	$pgop .=
		_p( _kv([ 'Display' => 
			_btn( LISTMODE ? '!_result.mode(2)' : '.'. BTN_ACTIVE ,  
				_fa( 'th-large' ). _l( 'images only' ) )
			. _btn( LISTMODE ? '.'. BTN_ACTIVE : '!_result.mode(1)' ,
				_fa( 'list-ul' ). _l( 'as list' ) )
		])) .
		_p( _kv([ 'Download' =>
			_a( URL_OMOAJAX. "?csv=1&id=$id"
				. ( $num_all == 0 ? "&gmref=true&gmrefn=$gmrefnum" : '' ) ,
			_fa( 'download' ). TERM_CSV_DOWNLOAD )
		]))
	;

	//... gmfit再整列のフォーム
	$refine = '';
	if ( $num_all > 0 ) {
		$refine = _t( 'form | method:post '
			. '| action:' . _httpq(
				[ 'id' => ID , 'gmref' => 'true', 'pg' => 0 ],
				[ 'gmrefn' ]
			)
			, ''
			. _term_rep( TERM_RERANK_TOPX ,
				_selopt( 'name:gmrefn', [ 
					10=> 10, 20=> 20, 50=> 50, 100=> 100, 200=> 200
				], 50 ) ,
				_doc_pop( 'gmfit', [ 'noicon' => true ] )
			)
			. _input( 'submit', '', _l( 'Run' ) )
			. ' '
			. _doc_pop( 'gmfit_rerank', [ 'label' => _l( 'Detail' ) ] )
		);
	}

	//... filter
	$filter_str = '';
	if ( MODE_COMPOS . KW_ORIG . MODE_DB != '' ) {
		$s = [
			'sim' => _l( 'similar ones only' ) ,
			'dis' => _l( 'different ones only' ) ,
			'e'   => 'EMDB' ,
			'p'   => 'PDB' ,
			's'   => 'SASBDB' ,
		];

		$filter_str = _p( '.bld', _l( 'Used filters' ) )
		. _table_2col([
			'Database'					=> $s[ MODE_DB ] ,
			'Composition similarity'	=> $s[ MODE_COMPOS ] ,
			'Keywords'					=> KW_ORIG == '' ? ''
				: _ab([ 'ysearch', 'kw' => KW_ORIG ], KW_ORIG )
			,
		]);
	}

	$filter_opt = _filter_form([ 'no_kw_suggest' => DB == 'user' ]);

	//... pager
	$opg = new cls_pager([
		'str'		=> $num_all == 0 ? ''
			: _term_rep( FOUND_FROM_ALL, number_format( $num_all ) )
		,
		'total'		=> $num_data  ,
		'page'		=> CUR_PAGE ,
		'range'		=> DATA_PER_PAGE ,
		'func_name'	=> '_result.page' ,
	]);

	//... gmfit再整列の結果
	$term_gmf = '';
	if ( FLG_GMREF ) {
		$term_gmf = _p( _kakko(
			TERM_GMFIT_RERANK_RES. ', '
			. _a( _httpq( [], [ 'gmref', 'gmrefn' ] ) ,
				_fa( 'arrow-left' ). TERM_BACK_GMM_RERANK
			)
		) );
	}

	//... 整理して返す
	$actab = _getpost( 'actab' );
	$tab_arr = [[
		'tab' => [ 'info', 'Summary' ],
		'div' => $opg->msg() . $term_gmf . $filter_str 
	], [
		'tab'    => [ 'eye', 'Display' ],
		'div'    => $pgop ,
		'active' => $actab == 'display' ,
	]];

	if ( ! FLG_GMREF ) {
		$tab_arr[] = [
			'tab' => [ 'filter', 'Filter' ] ,
			'div' => $filter_opt ,
			'js'  => "_kw_suggest.get('" .ID. "');" ,
	//		'active' => $actab == 'filter' ,
		];
		if ( TEST ) {
			$tab_arr[] = [
				'tab' => [ 'database', 'Functions & homology' ] ,
				'div' => _div( '#func_items', LOADING ) ,
				'js'  => "_dbid.getitems('" .ID. "');" ,
			];
		}
		$tab_arr[] =[
			'tab' => [ 'repeat', 'Refinement' ],
			'div' => $refine
		];
	}
	
	return _simple_tabs( $tab_arr )
		. $result . $opg->btn()
	;
}

//.. _levelbar
function _levelbar( $v ) {
	return _div( '.sbar', // . ( $wid != '' ? ' | st:width:200px' : '' ) ,
		_div( ".sbari | st:width:$v%" ) );
}

//.. _run_program
function _run_program( $cmd, $opt, $flg_test = false ) {
	$o = [ $cmd ];
	foreach ( array_filter( $opt ) as $k => $v )
		$o[] = is_numeric( $k ) ? $v : "$k=$v";
	$o = implode( ' ', $o );
	if ( ! $flg_test )
		exec( $o );
	return $o;
}

