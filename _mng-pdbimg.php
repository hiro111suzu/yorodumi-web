<?php
//. init
require( __DIR__ . '/_mng.php' );
define( 'TIMESTR', '?' . time());
//.. フラグ書き込み
$_proc_msg = [];
//- 方位決定

foreach ( $_GET as $key => $val ) {
	list( $type, $id, $name ) = explode( '-', $key );
	if ( $type != 'set' ) continue;

	//- delete
	if ( $val == 'del' ) {
		touch( _fn_flg( 'del' ) );
		_proc_msg( '削除指定' );
		continue;
	}

	//- 削除指定
	if ( $val == 'undel' ) {
		_del( _fn_flg( 'del' ) );
		_proc_msg( '削除指定を解除' );
		continue;
	}

	//- ng指定
	if ( $val == 'ng' ) {
		touch( _fn_flg( 'ng' ) );
		_del( _fn_flg( 'ori.txt' ) );
		_proc_msg( '「使わない」指定' );
		continue;
	}

	//- ng取り消し
	if ( $val == 'unng' ) {
		_del( _fn_flg( 'ng' ) );
		_proc_msg( '「使わない」指定を解除' );
		continue;
	}

	//- ng-delete 指定
	if ( $val == 'del_mov' ) {
		touch( _fn_flg( 'del_mov' ) );
		_proc_msg( '「動画削除」指定' );
		continue;
	}

	//- ok指定
	if ( $name == 'ok' ) {
		touch( _fn_flg( 'ok' ) );
		_proc_msg( '継続使用設定' );
		_unset( 'ng' );
		continue;
	}

	//- 動画指定
	$fn_out = _fn_flg( 'ori.txt' );
	$fn_in  = _fn_flg( "ori_$val.txt" );
	if ( file_exists( $fn_in ) )
		$val = file_get_contents( $fn_in );
	if ( file_put_contents( $fn_out, $val ) === false )
		_proc_msg( "書き込み失敗!". $fn_out );
	else
		_proc_msg( '画像を選択: '. $val );

	_unset( 'ok' );
	_unset( 'ng' );
}

function _fn_flg( $fn ) {
	global $id, $name;
	return DN_PDB_MED . "/$id/pre_$name/$fn";
}

function _unset( $type ) {
	$fn = _fn_flg( $type );
	if ( file_exists( $fn ) ) {
		unlink( $nf );
		_proc_msg( strtoupper( $type ). '解除' );
	}
}

function _proc_msg( $msg ) {
	global $id, $name, $_proc_msg;
	$_proc_msg[] = _ab( '?id=' . $id, "$id - $name" ) . ': ' . $msg;
}


//.. get
define( 'ONLYID', _getpost( 'id' ) );
define( 'ALL'	, _getpost( 'all' ) );
define( 'START'	, _getpost( 'start' ) );
define( 'MORE'	, _getpost( 'more' ) );

//- 全表示？(開始ID)
$start = $_GET[ 'all' ];

/*
define( 'ADR', '?' . http_build_query([
	'id'    => ONLYID ,
	'all'   => ALL ,
	'more' => MORE ,
	'start' => START
]) . '&' );
*/
$start = ALL ?: START ;


//. output

//.. css
$_simple->css( '.img { width:80px;height:80px;border:none;}' );

//.. top
$_simple->hdiv( 'Display', ''
	. _imp([
		ALL ? 'All data mode ' : '' ,
		MORE
			? _a( '?', 'EMDBムービー情報あるやつのみ' )
			: _a( '?more=1', 'EMDBムービー情報ないやつも' )
		,
		_a( '?', 'Reset ' ) ,
		( ONLYID != ''  ? _a( '?id=' . ONLYID, 'Reload' ) : '' )
	])
	. _p( _ab( '_mng.php?auto_run=pmo', 'pmo開始' ) )
);
if ( $_proc_msg ) {
	$_simple->hdiv( '処理', _ul( $_proc_msg ) );
}

//.. tables
$cnt =0;
$started = ( strlen( $start ) == 1 ) ? 1: 0;
$_out = '';
foreach ( _idlist( 'epdb' ) as $id ) {

	//... そのIDやるかやらないか
	//- 全表示なら、startIDが来るまで表示無し
	if ( $start ) {
		if ( ! $started and $start != $id )
			continue;
		$started = true;
	}

	//- ID指定なら、そのID以外表示しない
	if ( ONLYID != '' && $id != ONLYID ) continue;
	$dn_entry = DN_PDB_MED . "/$id";

	//- 方向が手動で指定してあるデータか？
	$rot_exists = file_exists( "$dn_entry/rot.txt" );

	//- 「ID指定」でないなら、「方向が決まっている奴」は表示なし
	if ( !ALL && $rot_exists ) continue; 

	$out = [];
	
	//... assebmlyごとのループ
	foreach ( glob( "$dn_entry/pre_*" ) as $dn_pre ) {
		if ( ! is_dir( $dn_pre ) ) continue; //- 多分ない
		$movid = strtr( basename( $dn_pre ), [ 'pre_' => '' ] );
		$fn_ori = "$dn_pre/ori.txt";
		$flg_ng  = file_exists( "$dn_pre/ng"  );
		$flg_del = file_exists( "$dn_pre/del" );
		$flg_del_mov = file_exists( "$dn_pre/del_mov" );
		$flg_ok  = file_exists( "$dn_pre/ok"  );

		$fn_curimg = _fn( 'pdb_snap', $id, "s$movid" );
		$flg_curimg_exists = file_exists( $fn_curimg )  && ! $flg_del;
		$flg_tobe_deleted = false;

		//- 既存画像があるのに、NG指定
		if ( $flg_curimg_exists && $flg_ng && !$flg_del_mov) {
			$flg_tobe_deleted = true;
		}

		//- 表示するかやめるか
		if ( ! ONLYID && ! $flg_tobe_deleted && ( 
			file_exists( $fn_ori ) || $flg_ng || $flg_del  || $flg_ok || $flg_del_mov
		)) continue;

		//- 方向決まっているか？
		$ori = file_exists( $fn_ori ) ? file_get_contents( $fn_ori ) : -1;


		//... 画像ごとのループ
		$json = _json_load2( "$dn_pre/orient.json" );

		//- EMDBムービーからの方向情報がないやつはスキップ
		$line = '';
		if ( ! MORE && ! ONLYID && count( (array)$json ) == 7 ) {
//			$out[] = TH. 'skip'. TD. MORE. TD. ONLYID. TD. count( (array)$json );
			continue;
		}

		foreach( glob( "$dn_pre/*.jpg" ) as $fn ) {
			$num = basename( $fn, '.jpg' ); //- 画像ファイル名から候補ID
			$img = $json->$num->name. _imgtag( $fn );
			$line .= TD . ( $rot_exists
				? '[マニュアル指定]'. $img
				: ( $num == $ori 
					? _span( '.red bld', ' <選択>' ). $img
					: _radio_item( $num,  $img )
				)
			);
		}

		//... ヘッダカラムと既存画像付け足し
		if ( ! $line ) continue;

		$out[] = ''
			//- ヘッダカラム
			.TH. "#$movid"
			. ( $movid == 'dep' ? '' : _p( $flg_ng
				? _span( '.red', '[使わない指定] ' ) //. _a( ADR. "unng=$id.$movid", '解除' )
					. _radio_item( 'unng', '解除' )
				: _radio_item( 'ng', '使わない' ) 
			))
			. _p( $flg_del
				? _span( '.red', '[作り直し指定] ' ) //. _a( ADR. "undel=$id.$movid", '解除' )
					. _radio_item( 'undel', '解除' )
				//:_a( ADR. "del=$id.$movid", '作り直し指定' )
				: _radio_item( 'del', '作り直し指定' )
			)

			//- 既存画像カラム
			.TD. ( file_exists( $fn_curimg )
				? ( $flg_ok
					? _span( '.blue bld', '継続利用' ) . _imgtag( $fn_curimg )
					//: _a( ADR. "ok=$id.$movid", '現在の画像' . _imgtag( $fn_curimg ) )
					: _radio_item( 'ok', '現在の画像' )
				) . ( $flg_tobe_deleted
					//? _p( '.red bld', _a( ADR. "del_mov=$id.$movid", 'ムービー削除' ) )
					? _radio_item( 'del_mov', _span( '.red bld', 'ムービー削除' ) )
					: ''
				)
				: _span( '.green bld', '新規' )
			)

			//- 候補カラム
			. $line
		;
	
	}

	//... 書き出し
	if ( $out ) {
		$_out .= $_simple->hdiv( $id, ''
			. _p( _imp([
				_ab([ 'ym', $id ], _ic( 'miru' ) . '万見' ) ,
				_ab([ 'dir_pdb_med', $id ], _fa( 'folder' ) . 'media dir' ) ,
				( $rot_exists
					? _span( '.red', ' <b>マニュアル指定の方向</b>' )
					: ''
				)
			]))
			. _t( 'table', TR . implode( TR, $out ) )
			,
			[ 'type' => 'h2' ]
		);
		++ $cnt;
	}
	if ( $cnt > 20 ) break;
}


//. 終了
$hidden = '';
foreach ([
	'id'    => ONLYID ,
	'all'   => ALL ,
	'more'  => MORE ,
	'start' => START
] as $key => $val )
	$hidden .= _input( 'hidden', "name:$key", $val );

$_simple->hdiv( 'data', $_out
	? _t( 'form| method:get ', ''
		. _input( 'submit' )
		. $_out
		. $hidden
		. _input( 'submit' )
	)
	: 'no data'
);

//. function
//.. _imgtag：相対パスにして画像urlとして参照できるようにする
function _imgtag( $pn ) {
	return BR. _img( '.img', strtr( $pn, [ realpath('.') . '/' => '' ] ) . TIMESTR );
}

function _radio_item( $num, $label ) {
	global $id, $movid;
	$i = implode( '-', [ $id, $movid, $num ] );
	return _e( "input |id: $i| type:radio |name:set-$id-$movid |value:$num" )
		. _t( "label |for:$i", $label )
	;
}
