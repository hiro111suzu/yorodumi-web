<?php
//. init
_add_url( 'quick-nohit' );
_add_lang( 'quick-nohit' );

_define_term(<<<EOD
TERM_UNREL_PDB
	Unreleased PDB entry
	未公開PDBエントリ
TERM_REPLASED
	Replaced entry
	変更されたエントリ
TERM_REP_TO
	 is replaced by following entries
	 は次のエントリに置き換えられました
TERM_ENT_SEQ
	Sequence of entity #_1_
	構成要素#_1_の配列
TERM_OBS_EMDB
	Obsolete EMDB entry
	取り消されたEMDBエントリ
EOD
);

//. PDB-prerel
if ( $unrel_type == 'prerel' ) {
	//- ステータスコード
	$statc2stat = L_EN ? [
		'POLC' => 'waiting for a policy decision',
		'AUTH' => 'processed, waiting for author review and approval',
		'REFI' => 're-refined entry',
		'HOLD' => 'hold until a certain date',
		'HPUB' => 'hold until publication',
		'WAIT' => 'processing started, waiting for author input to continue processing',
		'REPL' => 'author sent new coordinates, entry to be reprocessed',
		'PROC' => 'to be processed',
		'AUCO' => 'author corrections pending review',
		'TRSF' => 'entry tansferred to another data repository' ,
		'OBSLTE' => 'Obsolete entry',
	] : [
		'POLC' => '方針の決定待ち',
		'AUTH' => '編集処理済、登録者の確認・同意待ち',
		'REFI' => '再精密化構造、論文の公開まで処理を中断',
		'HOLD' => '編集処理完了、指定の時期まで公開待ち',
		'HPUB' => '編集処理完了、論文の公開まで公開待ち',
		'WAIT' => '編集処理開始、登録者からの連絡待ち',
		'REPL' => '登録者より新しい座標を受理、再処理が必要',
		'PROC' => '編集処理前',
		'AUCO' => '登録者による修正待ち',
		'TRSF' => '他のデータベースに移行されたエントリ', //- ないけども
		'OBSLTE' => '公開停止エントリ',
	];
	$_subtitle = TERM_UNREL_PDB;

	//.. basic
	$json = _json_load2( DN_DATA. '/pdb/prerel.json.gz' )->$id;
	$o_data->lev1title( 'Basic information' )->lev1ar([
		'Entry' => implode( BR, array_filter([
			_kv([
				'Database'	=> TERM_UNREL_PDB ,
				'ID'		=> $id
			]) ,
			( $json->stat == 'OBSLTE'
				? _ab([ 'pdbj_obs', $id ], _l( 'Details' ) ) : '' 
			) ,
			_test( BR. _ab([ 'prerel_json', $id ], 'json_view' ) )
		]) ),

		'Title'				=> $json->title ,
		'Status'			=> $statc2stat[ $json->stat ]. _kakko( $json->stat ),
		'Deposition date'	=> $json->ddep ,
		'Date'				=> $json->date ,
		'Hold until'		=> _datestr( $json->dhold ) ,
		'Authors'			=> _authlist( $json->auth ) ,
	]);
	
	//.. seq
	$a = (array)$json->seq;
	if ( $a != [] ) {
		ksort( $a );
		foreach ( (array)$a as $eid => $seq ) {
			$o_data->lev1(
				_term_rep( TERM_ENT_SEQ, $eid ) ,
				_seqstr( $seq )
			);
		}
	}

	//.. related
	$ids = array_merge(
		(array)_json_load2( DN_DATA . '/pdb/prerel_related.json.gz' )->$id ,
		(array)_emn_json( 'related', $did ) ,
		(array)_emn_json( 'fit', $did )
	);
	if ( $ids )
		$o_data->lev1title( 'Related entries' )->lev1direct( _ent_catalog( $ids ) );

	//. 変更PDB
} else if ( $unrel_type == 'pdb_rep' ) {
	$_subtitle = TERM_REPLASED;

	$o_data->lev1title( TERM_REPLASED )
	->lev1direct( 
		_span( '.bld', "PDB-$id" ). TERM_REP_TO
	);

	foreach ( $main_id->replaced() as $id ) {
		$o = ( new cls_entid() )->set_pdb( $id );
		$o_data->lev1title( "PDB-$id" )->lev1( 'Entry', $o->ent_item_list() );

		$num = 1;
		foreach ( $o->mainjson()->pdbx_database_PDB_obs_spr as $c ) {
			$o_data
				->lev2( 'Date', _datestr( $c->date ) )
				->lev2(
					'ID',
					strtolower( $c->replace_pdb_id ). ' &rarr; '. strtolower( $c->pdb_id )
				)
				->lev2( 'Details', $c->details )
				->end2( "#$num" )
			;
		}
	}

	//. 変更EMDB
} else if ( $unrel_type == 'emdb_obs' ) {
	$_subtitle = TERM_OBS_EMDB;
	$json = _json_cache( DN_DATA. '/emdb/emdb-obs.json.gz' )->$id;
	
	$rep = '';
	foreach ( (array)$json->repids as $i )
		$rep .= ( new cls_entid )->set_emdb( $i )->ent_item_list();

	$ftpu = _url( 'emdb_ftp', ID );

	$o_data
		->lev1title( 'Basic information' )
		->lev1ar([
			'Entry'		=> _quick_kv([
				'Database'	=> 'EMDB obsolete entry' ,
				'ID'		=> ID
			]) ,
			'Title'		=> $json->title ,
			'Sample'	=> $json->sample ,
			'Map data'	=> $json->map ,
			'Authors'	=> $json->authors ,
			'History'	=> _history_table([
				[
					'event' => 'Deposition' ,
					'date'	=> $json->date_dep ,
					'show'	=> true ,
				], [
					'event' => 'Obsoleted' ,
					'date'	=> $json->date_obs  ,
					'show'  => true ,
				]
			]) ,
			'Details'	=> $json->det ,
			'New ID'	=> $rep ,
			'Downloads' => _quick_kv([
				'TEST' => _test( _ab(['disp', 'emdb_obs.'. ID ], 'XML') ) ,
				'Header file' => _ab( $ftpu. "/header/emd-$id.xml", "emd-$id.xml" ) ,
				'FTP directory'	=> _ab( $ftpu, $ftpu )
			]) ,
		])
	;
}

