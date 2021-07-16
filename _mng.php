<?php
//. init
/*
出力は自動
ajax応答の出力は、get/postの"ajax"を使う
*/

define( 'IMG_MODE', 'em' );
define( 'COLOR_MODE', 'mng' );
require( __DIR__. '/common-web.php' );
if ( ! TESTSV ) die();

define( 'BTN_OPEN_ALL', 
	_btn( '!_hdiv.all2(this)', _ej( 'Show/Hide all', 'すべて表示・隠す' ) ) 
);

define( 'PAGE_NAME', [
	'_mng'				=> '管理トップ', 
	'logview'			=> '管理ログ' ,
	'log'				=> '開発ログ' ,
	'hourly'			=> 'hourlyログ' ,
	'problem'			=> '問題!!'  ,
	'docs'				=> '文書' ,
	'pubmedid'			=> 'PubMed-ID' ,
	'pdbimg'			=> 'EM-PDB画像選択' ,
	'sqliteDBs'			=> 'SQLite DB管理' ,
//	'subdata'			=> 'サブデータ' ,
	'categ'				=> 'カテゴリ', 
	'todolist'			=> 'ToDoリスト',
	'mom'				=> '今月の分子 情報' ,
	'emdbunp'			=> 'EMDB-UniProt関連付け' ,

	'ids_emdb_sup'		=> 'EMDB: サプリがある' ,
	'ids_emdb_obs'		=> 'EMDB: 取り消し' ,
	'ids_empiar'		=> 'EMDB: EMPIARデータがある' ,
	'ids_no_unp'		=> 'EMDB: UniProtIDがない' ,
	'ids_pdb_rep'		=> 'PDB: ID変更' ,
	'ids_pdb_num'		=> 'PDB: 数値のみのID' ,
	'ids_pdb_fit_prerel' => 'PDB: 未公開でfit登録がある' ,
	'ids_pdb_prerel'	=> 'PDB: 未公開' ,
	'ids_pdb_rep_multi'	=> 'PDB: ID変更、複数個' ,
]);

//.. _getpost
define( 'PAGE', _getpost( 'page' ) ?: 0 );
$mode = _getpost( 'mode' );
$func_mode = "_mode_$mode";
if ( function_exists( $func_mode ) ) {
	_pg_title( $mode );
	$func_mode();
}

//.. クイック選択
$kw = _getpost( 'kw' );
if ( $kw ) foreach ( _subdata( 'pages', 'mng' ) as $key => $url ) {
	if ( _instr( strtolower( $kw ), $key ) )
		_redirect( $url );
}


//.. auto_run
$a = _getpost('auto_run');
if ( $a )
	_auto_run( $a );

//. 終了処理
if ( php_sapi_name() != 'cli' )
	register_shutdown_function( '_end' );
function _end() {
	if ( _getpost( 'ajax' ) ) return;
	$flg_hide = _simple()->contents != '';

	//.. ダッシュボード
	//- 実行中タスク
	$running = [];
	foreach ( glob( '/dev/shm/yorodumi/hist/running-*' ) as $fn ) {
		$running[ time() - filemtime( $fn ) ] = explode( '-', basename( $fn ), 3 )[1];
	}
	krsort( $running );
	foreach ( $running as $t => $name ) {
		unset( $running[ $t ] );
		$d = floor( $t / 86400 );
		$running[ trim(
			( $d ? "$d days" : '' )
			. date( ' H:i:s', ( $t - 32400 ) % 86400 ) //- 日本時間
		)] = $name;
	}

	$dn_marem = DN_PREP. '/marem';
	$fn_log = max( glob( "$dn_marem/log/*movrec.tsv" ) );

	_simple()->hdiv( 'ダッシュボード', ''
		. _simple()->hdiv( '実行中タスク', 
			$running ? _table_2col( $running ) : 'なし' ,
			[ 'type' => 'h2' ]
		)
		. _simple()->hdiv( 'marem', ''
			. _p( basename( $fn_log, '-1-movrec.tsv' ) )
			. _ul( array_reverse( array_slice(
				_file( $fn_log ),
				-20
			)))
			. _p( _kv([
				'stopフラグ' => file_exists( "$dn_marem/stop" ) ? 'あり' : 'なし' ,
				'hwork-syncフラグ' => file_exists( "$dn_marem/sync-done" )
					? '停止'
					: ( file_exists( "$dn_marem/sync-doing" ) ? '実行中' : '予約' )
			]))
			,
			[ 'type' => 'h2' ]
		)
		. _simple()->hdiv( 'mng page', _table_2col([
			'dir' => [
				_dir_link( __DIR__. '/data/' ) ,
				_dir_link( __DIR__ ) ,
				_dir_link( __DIR__. '/../managedb' ) ,
				_dir_link( DN_PREP ) ,
			] ,
			'mng' => [
				_a( '?mode=problem', '問題' ) ,
				_a( '_mng-todolist.php', 'ToDo' ) ,
				_a( '?mode=logview', 'ログ' ) ,
			] ,
			'page' => [
				_a( '.', 'emn' ),
				_a( 'view.php', 'Yorodumi' ) ,
				_a( 'ysearch.php', 'YSearch' ) 
			] ,
		]). _mng_input()
		, [ 'type' => 'h2' ] )
		,
		[ 'hide' => $flg_hide ] 
	);


	//.. 他のページ
	chdir( __DIR__ );
	$p_mng = [ _a( '_mng.php', '管理トップ' ) ];
	$p_ids = [];
	foreach ( glob( '_mng-*.php' ) as $fn )
		$p_mng[] = _a( $fn, _pg_title( $fn ) );

	$f = get_defined_functions();
	foreach ( $f[ 'user' ] as $f ) {
		if ( substr( $f, 0, 6 ) != '_mode_' ) continue;
		$s = substr( $f, 6 );
		$l = _a( "_mng.php?mode=$s", _pg_title( $s ) );
		if ( substr( $s, 0, 3 ) == 'ids' )
			$p_ids[] = $l;
		else
			$p_mng[] = $l;
	}
	_simple()->hdiv( 'すべての管理ページ'
		, ''
		. _simple()->hdiv( '管理'    , _ul( $p_mng, 0 ), [ 'type' => 'h2' ] )
		. _simple()->hdiv( 'IDリスト', _ul( $p_ids, 0 ), [ 'type' => 'h2' ] )
		,
		[ 'hide' => $flg_hide ] 
	);

	//.. 終了
	_simple()->out([
		'title' => _pg_title( _getpost( 'mode' ) ) ,
		'sub'	=> $subtitle ,
		'icon' => 'lk-opt' ,
	]);
}

//. function
//.. _daystr
function _daystr( $in ) {
	return "$in " . [ '日', '月', '火', '水', '木', '金', '土' ][ date( 'w', strtotime( $in ) ) ];
}

//.. _out: ページャが必要な場合の出力
function _out( $out, $ar = [] ) {
	$title = 'items';
	$total = 0;
	$range = 100;
	extract( $ar );
	if ( $total > 0 ) {
		$opg = new cls_pager([
			'total'	=> $total ,
			'page'	=> PAGE ,
			'range'	=> $range ,
			'pvar'	=> [ 'ajax' => true, 'mode' => _getpost( 'mode' ) ] ,
			'div'	=> '#oc_div_list'
		]);
		$out = $opg->both() . $out . $opg->btn();
	}
	if ( _getpost( 'ajax' ) )
		die( $out );
	_simple()->hdiv( $title, $out, [ 'id' => 'list' ] );
}

//.. _pg_title
function _pg_title( $fn = '' ) {
	$s = strtr(
		basename( $fn ?: $_SERVER[ 'PHP_SELF' ], '.php' ),
		[ '_mng-' => '' ]
	);
	return PAGE_NAME[ $s ] ?: $s ;
}

//. mode functions
//.. _mode_problem
function _mode_problem() {
	$dn = DN_PREP . '/problem';
	define( 'ALL', _getpost( 'all' ) != '' );
	$_known = [];
	if ( ! ALL ) {
		foreach ( _file( "$dn/_known.txt" ) as $l )
			$_known[] = $l;
		_simple()->hdiv( 'mode', 
			_p( '.red', '既知の問題は表示していない' )
			. _p( _a( '?mode=problem&all=1', 'すべて表示' ))
		);
 	} else {
		_simple()->hdiv( 'mode', _p( _a( '?mode=problem', '既知の問題は隠す' )) );
	
	}
	$data = [];
	$no_prob = [];
	$no_prob_count = 0;
	foreach ( glob( "$dn/*.txt" ) as $fn ) {
		$name = basename( $fn, '.txt' );
		if ( $name == '_known' ) continue;
		$f = _file( $fn );
		$p_array = [];
		foreach ( $f as $l ) {
			if ( ! in_array( $l, $_known ) )
				$p_array[] = $l;
		}
		
		$p_count = count( $p_array );

		//- 問題なし
		if ( $p_count == 0 ) {
			$no_prob[] = $name;
			++ $no_prob_count;
			continue;
		}

		$out = [];
		foreach ( $p_array as $line ) {
			list( $id, $line2 ) = explode( ': ', $line, 2 );
			//- Pubmed IDぽい？
			if ( $id == _numonly( $id ) && strlen( $id ) > 6 ) {
				$out[] = _ab( "jsonview.php?a=pubmed.$id", "PubMed-$id" )
					 . ': '. _span( '.red', $line2 );
			} else {
				$eid = new cls_entid( $id );
				$out[] = $eid->ex()
					? $eid->ent_item_list([ 
						'data' => [ 'Problem' => _span( '.red', $line2 ) ]
					])
					: _span( '.red', $line )
				;
			}

/*
			$out[] = $line2
				? ( $id == _numonly( $id ) && strlen( $id ) > 6
					//- Pubmed IDぽい
					? _ab( "jsonview.php?a=pubmed.$id", "PubMed-$id" ) .': '
						. _span( '.red', $line2 )
					//- エントリのIDぽい
					: ( new cls_entid( $id ) )->ent_item_list(
						[ 'data' => [ 'Problem' => _span( '.red', $line2 ) ]]
					)
				)
				: _span( '.red', $line )
			;
*/
		}
		_simple()->hdiv( "$name ($p_count)", _ul( $out, 0 ) );
	}
	_simple()->hdiv( "以下は問題なし ($no_prob_count)", _ul( $no_prob, 0 ), [ 'hide' => true ] );
}

//.. _mode_categ
function _mode_categ() {
	define( 'FN_TSV', DN_EDIT. '/categ.tsv' );
	define( 'TSV', _tsv_load2( FN_TSV ) );
	$set_id = [];

	//... 値をセット
	$set = [];
	foreach ( (array)$_GET as $k => $v ) {
		list( $k, $id ) = explode( '-', $k );
		if ( $k != 'categ' ) continue;
		if ( _instr( 'pmid', $id ) ) {
			$ids = [];
			foreach ( _ezsqlite([
				'dbname' => 'pmid' ,
				'select' => 'strid' ,
				'where'  => [ 'pmid', strtr( $id, [ 'pmid' => '' ] ) ] ,
				'flg_all' => true
			]) as $id ) {
				$ids[] = substr( $id, 0, 1 ) == 'e'
					? _numonly( $id )
					: $id
				;
			}
			//- pubmed-IDで指定
		} else {
			$ids = [ $id ];
		}
		foreach ( $ids as $id ) {
			$set_id[ $id ] = true;
			$set[ "/\b$id\t.+/" ] = "$id\t$v";
		}
	}
	if ( $set ) {
		file_put_contents( FN_TSV, _reg_rep(
			file_get_contents( FN_TSV ), $set 
		) );
		_simple()->hdiv( 'カテゴリをセット', _ul( array_values( $set ) ) );
	}


	//... main
//	$btns = [ 'na' => 'na' ];
	$btns = [];
	foreach ( array_keys( TSV[ 'name' ] ) as $c )
		if ( $c != 'uncat' ) $btns[ $c ] = $c;
	define( 'RADIO_BTN', _radiobtns( [ 'name' => 'categ-<id>' ], $btns ) );
	$json = _json_load( DN_DATA. '/emn/id2categ.json' );
//	_die( $json );

	$out = [];
	foreach ( [ 'emdb', 'pdb'] as $db ) {
		foreach ( TSV[ $db ] as $id => $cat ) {
			if ( $_GET['id'] != $id ) {
				if ( $cat != '_' ) continue;
				if ( $set_id[ $id ] ) continue;
				if ( $json[ $id ] != 'uncat' && $json[ $id ] != '' ) continue;
			}
			$out[] = ( new cls_entid( $id ) )->ent_item_list(
				[ 'data' => [ 'Categ' => strtr( RADIO_BTN, [ '<id>' => $id ]) ] ]
			);
		}
	}

	$submit = $out ? _input( 'submit' ) : '';
	_simple()->hdiv( 'カテゴリ未定エントリ'. _kakko( count( $out ) ),
		_t( 'form | method:get | action:', ''
			. $submit
			. _ul( $out, 0 )
			. _input( 'hidden', 'name:mode', 'categ' )
			. $submit
		)
	);
}

//.. _mode_log: 開発log
function _mode_log() {
	//- init
	define( 'RANGE', 20 );

	$glob = glob( DN_PREP . '/dev_log/*.json' );
	krsort( $glob );

	//- main
	$out = '';
	foreach ( array_slice( $glob, PAGE * RANGE, RANGE ) as $pn ) {
		//- データ抽出
		$out2 = [];
		$cnt = 0;
		foreach ( _json_load( $pn ) as $k=> $v ) {
			$cnt += count( $v );
			$out2[] = _kv([ $k => _imp( $v ) ]);
		}
		$out .= _simple()->hdiv(
			_daystr( basename( $pn, '.json' ) ). _kakko( $cnt ) ,
			_ul( $out2, 0 ) ,
			[ 'type' => 'h2', 'hide' => true ] 
		);
	}

	//- out
	_out( BTN_OPEN_ALL . $out, [
		'title' => 'dev. logs',
		'total' => count( $glob )  ,
		'range' => RANGE
	]);
}

//.. _mode_logview 管理log
function _mode_logview() {
	//- file
	$dn = DN_PREP . '/mnglog';

	$fns = glob( $dn . '/*.json' );
	rsort( $fns );

	$fn = $dn . '/' . $_GET[ 'd' ] . '.json';
	if ( ! file_exists( $fn ) )
		$fn = $fns[ 0 ];

	$date = basename( $fn, '.json' );

	//- data
	$out = '';
	$cnt = 0;
	foreach ( (array)_json_load2( $fn ) as $n1 => $a1 ) {
		$out .= _simple()->hdiv(
			"#$n1: ". $a1->name
			. _kakko( count( $a1->job ) . ' items' )
			. " @ " . strtr( $a1->time, [ "$date " => '' ] ) ,
			_ul( $a1->job, 0 ) ,
			[ 'type' => 'h2', 'hide' => true ]
		);
		++ $cnt;
	}	
	_simple()->hdiv( "Log on $date, $cnt jobs)", $out );


	//- links
	$data = [];
	foreach ( $fns as $f ) {
		$b = basename( $f, '.json' );
		$i = explode( '-', $b, 2 );
		$data[ $i[0] ][] = _a( "?mode=logview&d=$b", $i[1] ); 
	}

	$out = '';
	foreach ( $data as $y => $d )
		$out .= _simple()->hdiv( $y, _imp( $d ), [ 'type' => 'h2', 'hide' => true ] );

	_simple()->hdiv( 'Other logs',	$out );
}

//.. _mode_hourly: hourly log
function _mode_hourly() {
	//- file
	$dn = DN_PREP . '/hourly_log';

	$fns = [];
	foreach ( glob( $dn . '/*.txt' ) as $fn ) {
		$fns[ filemtime( $fn ) ] = $fn;
	}
	krsort( $fns );

	//- data
	$out = '';
	$cnt = 0;
	foreach ( $fns as $time => $fn ) {
		$cont = _file( $fn );
		$t = [ basename( $fn, '.txt' ), '('.count( $cont ) . ' lines)' ];
		foreach ( $cont as $line ) {
			if ( _headmatch( 'task: ', $line ) )
				$t[] = substr( $line, 6 );
		}
		$out .= _simple()->hdiv(
			_imp( $t ) ,
			'@' . date( 'Y-m-d H-i-s', $time ) .
			_t( 'pre', implode( "\n", $cont ) )
			,
			[ 'type' => 'h2', 'hide' => true ]
		);
		++ $cnt;
	}	
	_simple()->hdiv( "Log of hourlyjobs ($cnt jobs)", $out );

}



//. mode functions IDリスト
//.. _mode_ids_pdb_rep
function _mode_ids_pdb_rep() {
	define( 'RANGE', 200 );
	$ids = array_keys( _json_load( DN_DATA . '/pdb/ids_replaced.json.gz' ) );
	$out = [];
	foreach ( array_slice( $ids, PAGE*RANGE, RANGE ) as $num => $id )
		$out[] = _ab([ 'ym', $id ], $id );
	_out( _imp( $out ), [ 'title' => 'rep-ids', 'total' => count( $ids ), 'range' => RANGE ]);
}

//.. _mode_ids_pdb_rep_multi
function _mode_ids_pdb_rep_multi() {
	define( 'RANGE', 200 );
	$out = [];
	foreach ( _json_load( DN_DATA . '/pdb/ids_replaced.json.gz' ) as $i => $r ) {
		if ( count( $r ) == 1 ) continue;
		$ids[] = _ab([ 'ym', $i ], "$i => " .  _imp($r) );
	}
	_out( _imp( $ids ), [ 'title' => 'rep-ids' ] );

}

//.. _mode_ids_pdb_prerel: PDB未公開ID
function _mode_ids_pdb_prerel() {
	define( 'RANGE', 200 );
	$ids = _idlist( 'prerel' );
	$out = [];
	foreach ( array_slice( $ids, PAGE*RANGE, RANGE ) as $id )
		$out[] = _ab([ 'ym', $id ], $id );
	_out( _imp( $out ),
		[ 'title' => 'pre-rel PDB-IDs', 'total' => count( $ids ), 'range' => RANGE ]);
}

//.. _mode_ids_pdb_fit_prerelids
function _mode_ids_pdb_fit_prerel() {
	define( 'RANGE', 200 );
	$out = [];
	foreach ( (array)_json_load2( DN_PREP. '/emn/fitdb.json.gz' ) as $did => $fit) {
		if ( substr( $did, 0, 3 ) != 'pdb' ) continue;
		$id = substr( $did, -4 );
		if ( ! _inlist( $id, 'prerel' ) ) continue;
		$out[] = _ab([ 'ym', $id ], "$id: (<-" . _imp( $fit ) . ")" );
		
	}
	_out( _imp( $out ), [
		'total' => count( (array)$ids ),
		'range' => RANGE
	]);
}

//.. _mode_ids_pdb_num: 数値のみのPDB-ID
function _mode_ids_pdb_num() {
	$out = '';
	foreach ( _file( DN_DATA . '/ids/pdb.txt' ) as $id ) {
		if ( ! ctype_digit( $id ) ) continue;
		$out .= ''
			. ( new cls_entid() )->set_pdb( $id )->ent_item_list()
			. ( new cls_entid() )->set_emdb( $id )->ent_item_list()
		;
	}
	_simple()->hdiv( 'IDが数字のみのPDB', $out );
}

//.. _mode_ids_emdb_obs
function _mode_ids_emdb_obs() {
	define( 'RANGE', 100 );
	$ids = array_keys( _json_load( DN_DATA . '/emdb/emdb-obs.json.gz' ) );
	$out = [];
	foreach ( array_slice( $ids, PAGE*RANGE, RANGE ) as $id )
		$out[] = _ab([ 'ym', $id ], $id );
	_out( _imp( $out ), [ 'total' => count( $ids ), 'range' => RANGE ]);
	
}

//.. _mode_ids_emdb_sup: サプリのあるデータ
function _mode_ids_emdb_sup() {
	define( 'RANGE', 50 );
	$data =[];
	foreach ( _idlist( 'emdb' ) as $id ) {
		foreach ( glob( _fn( 'emdb_med', $id ) . '/*' ) as $pn ) {
			if ( ! is_dir( $pn ) ) continue;
			$bn = basename( $pn );
			if ( $bn == 'images' || $bn == 'mapi' || $bn == 'ym' ) continue;
			if ( $bn == 'other' ) {
				foreach ( glob( "$pn/*" ) as $pn2 ) {
					$ext = pathinfo( $pn2, PATHINFO_EXTENSION );
					if ( $ext == 'json' || $ext == 'd' || $ext == '' ) continue;
					$data[ "$bn-$ext" ][] = $id;
				}
			} else {
				$data[ $bn ][] = $id;
			}
		}
	}
	ksort( $data );
	foreach ( $data as $type => $ids ) {
		_simple()->hdiv( $type, _ent_catalog( array_unique( $ids ), [ 'mode' => 'icon' ] ) );
	}
}

//.. _mode_ids_empire: EMPIAR データあるデータ
function _mode_ids_empiar() {
	define( 'RANGE', 50 );
	$ids =[];
	foreach ( _json_load( DN_DATA . '/emdb/empiar.json.gz' ) as $i => $v ) {
		if ( strlen( $i ) == 5 ) continue;
		$ids[] = $i;
	}
	$out = '';
	foreach ( array_slice( $ids, PAGE*RANGE, RANGE ) as $id )
		$out .= ( new cls_entid( $id ) )->ent_item_img();
	_out( $out, [ 'total' => count( $ids ), 'range' => RANGE ]);
}

//.. _mode_ids_no_unp: UniProt-IDがないデータ
function _mode_ids_no_unp() {
	define( 'RANGE', 50 );
	$ids =[];
	foreach ( _tsv_load( DN_PREP. '/unp/emdb_unpids_annot.tsv')
		as $emdb_id => $annot ) 
	{
		if ( $annot != '_' ) continue;
		$ids[] = $emdb_id;
	}
	$out = '';
	foreach ( array_slice( $ids, PAGE*RANGE, RANGE ) as $id )
		$out .= ( new cls_entid( $id ) )->ent_item_img();
	_out( $out, [ 'total' => count( $ids ), 'range' => RANGE ]);
}

//.. _dir_link
function _dir_link( $path, $name = '' ) {
	$path = realpath( $path );
	return _a( '_mng-dir.php?path='. $path, $name ?: basename( $path ) );
}
//http://localhost:8081/emnavi/_mng-dir.php?path=/data/yorodumi/emnavi/data

