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
		'Status'			=> _subdata( 'status_code', L_EN ? 'en' : 'ja' )[ $json->stat ]
								. _kakko( $json->stat ),
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
	$arch = new cls_archive( $id );
	$arch_xml = $arch->get('emdb_obs_xml');
	$arch_dir = $arch->get('emdb_obs_dir');

	$rep = '';
	foreach ( (array)$json->repids as $i )
		$rep .= ( new cls_entid )->set_emdb( $i )->ent_item_list();

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
				$arch_xml['name'] => _imp2(
					$arch_xml['dl']. _kakko( $arch_xml['size'] ) ,
					$arch_xml['disp'] ,
					$arch_xml['doc']
				),
				$arch_dir['name'] => BR. $arch_dir['dl'],
			]) ,
		])
	;
}

