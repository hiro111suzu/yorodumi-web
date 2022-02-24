<?php
/*
VaProSから利用、消さない!!
*/
require( __DIR__. '/common-web.php' );
ini_set( "memory_limit", "512M" );
//. init

define( 'MODE'	, _getpost( 'mode' ) );
define( 'ID'	, _getpost( 'id' ) ?: _getpost( 'key' ) );

$ret = [];
if ( MODE == 'surflev' ) {
	$ret = _json_load2( _fn( 'emdb_new_json', $_GET[ 'id' ] ) )->map->contour[0]->level;
} else if ( MODE == 'mov_data' ) {
	$ret = _mov_data();
} else if ( MODE == 'hwork_list' ) {
	$ret = _hwork_list();
} else if ( MODE == 'inchikey2chemid' ) {
	$ret = _ezsqlite([
		'dbname' => 'chem' ,
		'where'	 =>	[ 'inchikey', strtoupper( ID ) ] ,
		'select' => 'id' ,
	]);
} else if ( MODE == 'chemid2inchikey' ) {
	$ret = _ezsqlite([
		'dbname' => 'chem' ,
		'where'	 =>	[ 'id', strtoupper( ID ) ] ,
		'select' => 'inchikey' ,
	]);
} else if ( MODE == 'unp2inchikey' ) {
	$ret = ID ? _unp2inchikey( ID, 'unp' ) : [];
} else if ( MODE == 'pdb2inchikey' ) {
	$ret = ID ? _unp2inchikey( ID, 'pdb' ) : [];
} else if ( MODE == 'vapros_unp' ) {
	_vapros_unip( ID );
} else if ( MODE == 'vapros_pdb' ) {
	_vapros_pdb( ID );
} else {
	$ret = [
		'error' => 'unknown mode: '. MODE
	];
}


//. output
if ( is_array( $ret ) ) {
	header( 'content-type: application/json;' );
//	die( json_encode( compact( 'inchi', 'inchikey' ) ) );
	die( json_encode( $ret ) );
} else {
	header( 'content-type: text/plain;' );
	die( $ret );
}

//. _vapros_unip
function _vapros_unip( $unp_id ) {
	$select = [];
	foreach ( explode( ',', $unp_id ) as $id ) {
		$select[] = "unp=\"$id\"";
	}
	$comp_dic =[];
//	_die( ( new cls_sqlite( 'vapros_unp2pdb' ) )->qcol([
//		'select' => 'pdbchain' ,
//		'where' => implode( ' OR ', $select ) ,
//	]) );
	foreach ( (array)( new cls_sqlite( 'vapros_unp2pdb' ) )->qcol([
		'select' => 'pdbchain' ,
		'where' => implode( ' OR ', $select ) ,
	]) as $pdbchain ) {
		extract( _ezsqlite([
			'dbname' => 'vapros_pdb2comp', 
			'where'  => [ 'pdbchain', $pdbchain ] ,
			'select' => [ 'comp', 'reactome' ] ,
		]) ); //- $comp, $reactome
		_add_ret( $pdbchain, $comp, $reactome );
	}
}

//. _vapros_unip
function _vapros_pdb( $idlist ) {
	$select = [];
	foreach ( explode( ',', $idlist ) as $id ) {
		$select[] = "pdb=\"$id\"";
	}
	$comp_dic =[];
	foreach ( (array)( new cls_sqlite( 'vapros_pdb2comp' ) )->qar([
		'select' => 'pdbchain, comp, reactome' ,
		'where' => implode( ' OR ', $select ) ,
	]) as $a ) {
		extract( $a ); //- $pdbchain, $comp, $reactome
		_add_ret( $pdbchain, $comp, $reactome );
	}
}

//. _add_ret
function _add_ret( $pdbchain, $comp, $reactome ) {
	global $ret;
	list( $pdb, $chain ) = explode( '-', $pdbchain );
	$ret[ $pdb ][ $chain ] = [
		'comp' => _comp_data( $comp ) ,
		'reactome' => $reactome ? explode( ',', $reactome ) : null
	];
//	_die( $ret );
}

//. _comp_data
function _comp_data( $list ) {
	global $comp_dic;
	if ( ! $list ) return null;
	$ret = [];
	foreach ( explode( ',', $list ) as $c ) {
		if ( $comp_dic[ $c ] ) {
			$ret[ $c ] = $comp_dic[ $c ];
			continue;
		}
		extract( _ezsqlite([
			'dbname' => 'chem' ,
			'where'	 =>	[ 'id', $c ] ,
			'select' => [ 'inchikey', 'idmap' ] ,
		]) ); //- $inchikey, $idmap
		if ( ! $inchikey && ! $idmap ) continue;
		$idmap = json_decode( $idmap );
		$ret[ $c ] = $comp_dic[ $c ] = [
			'inchikey'	=> $inchikey ,
			'chembl'	=> $idmap->ChEMBL   ?: [] ,
			'chebi'		=> $idmap->ChEBI    ?: [] ,
			'drugbank'	=> $idmap->DrugBank ?: [] ,
		];
	}
	return array_filter( $ret );
}



//. _unp2inchikey

function _unp2inchikey( $id, $mode ) {
	if ( $mode == 'unp' ) {
		$unp_id = strtolower( $id );

		//- pdbidリスト
		$pdb_id_list = ( new cls_sqlite( 'pdb' ) )->qcol([
			'select' => 'id' ,
			'where'  => "search_kw LIKE ". _quote( "%|un:$unp_id|%" ) ,
		]);
		if ( ! $pdb_id_list ) return []; // 'no PDB entry' ];
	} else {
		$pdb_id_list = [ strtolower( $id ) ];
		$unp_id = false;
	}

	//- main loop
	$data = [];
	$data_pre = [];
	$comp_all = [];
	foreach ( $pdb_id_list as $pdb_id ) {
		$json = _json_load2( _fn( 'pdb_json', $pdb_id ) );

		//- entity id集め
		$flg_ent_id = [];
		foreach ( (array)$json->struct_ref as $c ) {
			if ( 
				$unp_id &&
				strtolower( $c->pdbx_db_accession ) != $unp_id 
			)
				continue;
			$flg_ent_id[ $c->entity_id ] = true;
		}

		//- plusからも読み込んでみる
		if ( ! $flg_ent_id ) foreach (
			(array)_json_load2( _fn( 'pdb_plus', $pdb_id ) )->struct_ref as $c
		) {
			if ( 
				$unp_id &&
				strtolower( $c->pdbx_db_accession ) != $unp_id 
			)
				continue;
			$flg_ent_id[ $c->entity_id ] = true;
		}

		//- asym_idに変換
		$flg_asym_id = [];
		foreach ( (array)$json->struct_asym as $c ) {
			if ( $flg_ent_id[ $c->entity_id ] )
				$flg_asym_id[ $c->id ] = true;
		}
//		$data_pre[ $pdb_id ][ 'asym_ids' ] = $flg_asym_id;
		if ( ! $flg_asym_id ) continue;

		//- site2chem
		$site2comp = [];
		foreach ( (array)$json->struct_site as $c ) {
			$det = strtoupper( $c->details );
			if ( ! _instr( 'BINDING SITE FOR ', $det ) ) continue;
			list( $type, $comp ) = explode( ' ', strtr( $det, [
				'BINDING SITE FOR ' => ''
			]), 3 );
			if ( in_array( $type, [
				'RESIDUE',
				'RESIDUES',
				'MONO-SACCHARIDE' ,
			]) && $comp ) {
				$site2comp[ $c->id ] = $comp;
			}
		}

		//- asym_id2chain_id
		$asym_id2chain_id = [];
		foreach ( (array)$json->_yorodumi->id_asym2chain as $asym_id => $chain_id ) {
			if ( ! $flg_asym_id[ $asym_id ] ) continue;
			$asym_id2chain_id[ $asym_id ] = $chain_id;
			$data[ $pdb_id ][ $chain_id ] = []; //- カラ配列を作っておく
		}

		//- data
		foreach ( (array)$json->struct_site_gen as $c ) {
			$asym_id = $c->label_asym_id;
			if ( ! $flg_asym_id[ $asym_id ] ) continue;
			$comp = $site2comp[ $c->site_id ];
			$chain_id = $asym_id2chain_id[ $asym_id ] ?: $asym_id;
			$data_pre[ $pdb_id ][ $chain_id ][] = $comp;
			$comp_all[ $comp ] = true;
		}
	}
	
	//.. comp_id 2 inchikey
	$comp2inchikey = [];
	$comp2chembl = [];
	foreach ( array_keys( $comp_all ) as $comp_id ) {
		$comp2inchikey[ $comp_id ] = _ezsqlite([
			'dbname' => 'chem' ,
			'where'	 =>	[ 'id', $comp_id ] ,
			'select' => 'inchikey' ,
		]);
		$comp2ids[ $comp_id ] = json_decode( _ezsqlite([
			'dbname' => 'chem' ,
			'select' => 'idmap' ,
			'where'  => [ 'id', $comp_id ] ,
		]) );
	}

	foreach ( $data_pre as $pdb_id => $c ) foreach ( $c as $chain_id => $comp_list ) {
		foreach ( array_unique( $comp_list ) as $comp ) {
			$data[ $pdb_id ][ $chain_id ][] = [
				'comp_id'	=> $comp ,
				'inchikey'	=> $comp2inchikey[ $comp ] ,
				'chembl'	=> $comp2ids[ $comp ]->ChEMBL   ?: [] ,
				'chebi'		=> $comp2ids[ $comp ]->ChEBI    ?: [] ,
				'drugbank'	=> $comp2ids[ $comp ]->DrugBank ?: [] ,
			];
		}
	}
	return $data;
}

/*
{http://localhost:8081/emnavi/api.php?mode=unp2inchikey&id=Q16602
{http://localhost:8081/emnavi/api.php?mode=pdb2inchikey&id=6d1u

{https://pdbj.org/emnavi/api.php?mode=unp2inchikey&id=Q16602
{https://pdbj.org/emnavi/api.php?mode=pdb2inchikey&id=6d1u


*/

//. mov_data
function _mov_data() {
	$ret = [];
	foreach ( explode( '|', _getpost( 'idlist' ) ) as $id ) {
		foreach ([
			's1' 	=> 's1.py' ,
			's2' 	=> 's2.py' ,
			's3' 	=> 's3.py' ,
			's4' 	=> 's4.py' ,
			's5' 	=> 's5.py' ,
			's6' 	=> 's6.py' ,
			'obj'	=> [ '1.obj', 'ym/1.obj' ] ,
			'mtl'	=> [ '1.mtl', 'ym/1.mtl' ] ,
			'mtx'	=> [ 'matrix.txt', 'ym/matrix.txt' ] ,
		] as $type => $fns ) {
			$time = [];
			foreach ( is_array( $fns ) ? $fns : [ $fns ] as $fn ) {
				$fn = DN_EMDB_MED. "/$id/$fn";
				if ( ! file_exists( $fn ) ) continue;
				$time[] = filemtime( $fn );
			}
			if ( count( $time ) )
				$ret[ $id ][ $type ] = max( $time );
		}
	}
	return $ret;
}

//. hwork_list
function _hwork_list() {
	$data = [];
	foreach ( glob( DN_FDATA. '/hwork/*/*.map' ) as $pn ) {
		$data[ strtr( basename( $pn, '.map' ), [ 'emd_' => '' ] ) ]
			= filesize( $pn );
	}
	return $data;
}
