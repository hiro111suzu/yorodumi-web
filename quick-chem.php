<?php
//. init
_define_term( <<<EOD
TERM_CHEM_COMP
	PDB chemical components
	PDB化学物質要素
TERM_BIRD
	Biologically Interesting Molecule Reference Dictionary (BIRD)
	生物学的重要分子辞書 (BIRD)
EOD
);
define( 'IS_BIRD', DB == 'bird' );
_add_url(  'quick-chem' );
_add_lang( 'quick-chem' );
_add_trep( 'quick-chem' );
$dbjson = json_decode( _ezsqlite([
	'dbname' => 'chem' ,
	'where'	 =>	[ 'id', $id ] ,
	'select' => 'json' ,
]), 1 );

//.. jsonなど整理
if ( IS_BIRD ) {
	$json_bird = $json;
	$chem_id = $json->pdbx_reference_molecule[0]->chem_comp_id;
	$json_chem = $chem_id ? _json_load2([ 'chem_json', $chem_id ]) : [];
	$cc = $json->chem_comp[0] ?: $json_chem->chem_comp;
} else {
	$json_chem = $json;
	$bird_id = $dbjson['bird'][0];
	if ( $bird_id )
		$json_bird = _json_load2([ 'bird_json', _numonly( $bird_id ) ]);
	$cc = $json->chem_comp;
}

//. basic
//- 名前を小文字に
$name = IS_BIRD
	? $json->pdbx_reference_molecule[0]->name
	: preg_replace_callback(
		'/[A-Z]{2,}/' ,
		function ( $matches ) {
			return strtolower( $matches[0] );
		} ,
		$cc->name
	)
;

$o_data->lev1title( 'Basic information' )
//.. entry
->lev1( 'Entry', _img( '.mainimg', _fn( DB. '_img', $id ) )
	. _kv([
		'Database' => IS_BIRD ? TERM_BIRD : TERM_CHEM_COMP ,
		'ID' => IS_BIRD ? "PRD_$id" : $id
	])
	. BR
	. _btn_popviewer( DID )
)

//.. status
->lev1( 'Status', _cc([
	'status' => $cc->pdbx_release_status == 'REL'
		? '' : _trep( $cc->pdbx_release_status ) ,
	'pdbx_replaced_by'
]))

//.. name
->lev1( 'Name', IS_BIRD
	? $name
	: _cc([
		'name' => _breakable( $name ) ,
		'pdbx_synonyms' ,
	])
	+ [ '日化辞名称' => _nikkaji_name( ID ) ]
);

//.. wikipedia
$o_data->lev1( 'Wikipedia', IS_BIRD
	? _obj('wikipe')->show( $name )
	: _obj('wikipe')->chem(ID)->show()
);
$o_data->lev1( 'Comment', _ym_annot_chem( ID ) );

//. bird情報
if ( $json_bird ) {

	$fam = $json_bird->pdbx_reference_molecule_family[0];
	$ref = $json_bird->pdbx_reference_molecule[0];
	_add_lang( 'bird' );
	_add_url( 'quick-chem' );
	_add_url( 'bird' );

	$type = [];
	foreach ( _uniqfilt( array_merge(
		[ $ref->type ], explode( ', ', $ref->class ) 
	)) as $s ) {
		$type[] = _l( $s ) . _obj('wikipe')->icon_pop($s);
	}

	$o_data->lev1title( 'BIRD information' )->lev1ar([
		_span( '.red', 'test' ) => TEST ? [
			_kv([ 'represent_as' => $ref->represent_as ]) ,
			_ab([ 'jsonview', 'a' => 'prd.'. _numonly( $id ) ], 'JSON view' ) ,
		]: '' ,
		'Type'		=> _imp2( $type ) ,

		'Details'	=> _ul([
			$ref->compound_details ,
			$ref->description ,
		]) ,
		'Downloads' => _imp2([
			_ab([ 'prd_cif', $id ]  , IC_L. 'Molecular definition' ) ,
			_ab([ 'prdcc_cif', $id ], IC_L. 'Chemical definition' ) ,
			$fam->family_prd_id
				? _ab([ 'prdfam_cif', $fam->family_prd_id ], IC_L. 'Family definition' )
				: ''
		]) ,
	]);

	//.. synonyms
	$o = [];
	foreach ( (array)$json_bird->pdbx_reference_molecule_synonyms as $c ) {
		$o[ $c->name ][] = $c->source;
	}
	ksort( $o );
	foreach ( $o as $n => $s ) {
		$o[ $n ] = $n. _obj('wikipe')->pop_xx( $n ). _kakko( _imp( $s ) );  
	}
	$o_data->lev1( 'Synonyms', _ul( $o ) );

	//.. annotation
	$o = [];
	foreach ( (array)$json_bird->pdbx_reference_molecule_annotation as $c ) {
		if ( substr( $c->source, 0, 4 ) == 'http' )
			$c->source = _ab( $c->source, $c->source );
		if ( substr( $c->source, 0, 5 ) == 'PMID:' )
			$c->source = _dblink( 'PubMed', _numonly( $c->source ) );
		if ( _instr( ' EC:', $c->text ) ) {
			$ec = preg_replace( '/^.+ EC:([0-9\.]+).*/', '$1', $c->text );
			$c->text = strtr( $c->text, [ " EC:$ec" => '' ] )
				._obj('dbid')->pop( 'EC', $ec, '.' );
		}
		$o[] = _kv([
			$c->type => _l( $c->text ). _obj('wikipe')->pop_xx( $c->text ) ,
			'Info source' => $c->source
		]);
	}
	$o_data->lev1( 'Annotation', _ul( $o, 20 ) );

	//.. source
	$o = [];
	foreach ( (array)$json_bird->pdbx_reference_entity_src_nat as $c ) {
		if ( ! $c->organism_scientific ) continue;
		$o[] = _imp(
			_obj('taxo')->item( $c->organism_scientific ) ,
			_dblink( $c->db_name, $c->db_code )
		);
	}
	$o_data->lev1( 'Source', _ul( $o, 20 ) );

	//.. external information
	$o = $i = $u = [];
	foreach ( (array)$json_bird->pdbx_reference_molecule_features as $c ) {
		$source = $value = $type = '';
		extract( (array)$c );
		if ( $type == 'Image' )
			$i[] = _img( 'st:height:5em', $value ). " $source";
		else if ( $type == 'URL' )
			$u[] = _ab( $value, IC_L. "$source: ". basename( $value, '.html' ) );
		else {
			$o[] = ''
				. ( strtolower( $type ) == 'external_reference_id' ? '' : "[$type] " )
				. ( _url( strtolower( $source ) )
					? _dblink( $source, $value ) : _kv([ $source => $value ])
				)
			;
		}
	}
	sort( $o );
	$o_data->lev1( 'External info', _ul( array_merge( $o, $u, $i ), 20 ) ); 

	//.. descriptor
/*
	foreach ( (array)$json_bird->pdbx_descriptor as $c ) {
		$ret['Descriptor'][] = _span( '.bld', $c->type )
			. _kakko( _imp( $c->program, $c->program_version ) )
			. ': '
			. _long( _p( $c->descriptor ), 50 )
		;
	}
*/
	//.. family
	$mem = [];
	foreach ( (array)$json_bird->pdbx_reference_molecule_list as $c ) {
		if ( $id == $c->prd_id ) continue;
		$mem[] = _obj('dbid')->pop( 'BIRD', $c->prd_id );
	}
	$o_data->lev1( 'Family',
		$fam->name. _obj('wikipe')->pop_xx( $fam->name )
 		. ( $mem ? _p( _long( $mem ) ) : '' )
 	);

	//.. related
	$cit = [];
	foreach ( (array)$json_bird->citation as $c ) {
		$p = $c->pdbx_database_id_PubMed;
		$d = $c->pdbx_database_id_DOI;
		$cit[ $c->id ] = _imp2(
			$p ? _ab([ 'pubmed', $p ], IC_L. 'PubMed' ) : '',
			$c ? _ab([ 'doi',    $d ], IC_L. 'Reference' ) : ''
		);
	}
	$o = [];
	foreach ( (array)$json_bird->pdbx_reference_molecule_related_structures as $c ) {
		$o[] = _imp2([
			$c->name ? $c->name. _obj('wikipe')->pop_xx( $c->name ) : '',
			_url( strtolower( $c->db_name ) )
				? _dblink( $c->db_name, $c->db_code ) 
				: implode( ': ', array_filter( [ $c->db_name, $c->db_code ] ) )
			,
			$cit[ $c->citation_id ]
		]);
	}
	$o_data->lev1( 'Related structures', _ul( $o ) );
}

//. chem info
//- img2
$img2 = file_exists( $img2fn = _fn( DB. '_img2', ID ) )
	? _img( '.chemsvg| width: 100px| height:100px', $img2fn )
	: ''
;


$o_data->lev1title( 'Chemical information' )
//.. formula
->lev1( 'Composition', _cc([
	'#div'				=> $img2 ,
	'formula'			=> _chemform2html( $cc->formula ) ,
	'Number of atoms'	=> $json->atom ? count( $json->atom ) : '',
	'formula_weight'	=> $cc->formula_weight ,
	'pdbx_formal_charge' => $cc->pdbx_formal_charge ,
	
]) )

//.. others
->lev1( 'Others', _cc([
	'type' ,
	'pdbx_type' ,
	'one_letter_code' ,
	'three_letter_code' ,
	'pdbx_ideal_coordinates_details' ,
	'pdbx_model_coordinates_db_code' ,
	'pdbx_model_coordinates_details' ,
	'pdbx_ambiguous_flag'  => $cc->pdbx_ambiguous_flag == 'Y' ? 'Yes' : '' ,
	'mon_nstd_parent_comp_id' ,
	'pdbx_subcomponent_list' ,
	'pdbx_replaces' ,
]) )
;

//.. date
$t = [];
foreach ( (array)$json->pdbx_audit as $c ) {
	$t[] = [
		'date'	=> $c->date ,
		'event' => $c->action_type ,
		'show'	=> true
	];
//	$o_data->lev2( $c->action_type, _datestr( $c->date ) ); 
}
$o_data->lev1( 'History', _history_table( $t ));

//.. links
$inchikey = '';
foreach ( (array)$json->pdbx_descriptor as $c ) {
	if ( $c->type == 'InChIKey' ) {
		$inchikey = $c->descriptor;
		break;
	}
}

$o_data->lev1( 'External links', array_merge(
	_get_chemlinks( $id, $inchikey ), 
	[ TEST ? _ab([ 'json', IS_BIRD ? "bird-$id": "chem-$id" ], 'JSON view' ) : '' ]
) );

//. ビューア
_viewer();

//. 詳細
$d = [
	'SMILES' => [],
	'SMILES_CANONICAL' => [],
	'InChI' => [],
	'InChIKey' => [],
];
foreach ( array_merge(
	(array)$json->pdbx_descriptor ?: (array)$json_chem->pdbx_descriptor, 
	(array)$json->pdbx_identifier ?: (array)$json_chem->pdbx_identifier, 
) as $c ) {
	$d[ $c->type ][ implode( ' ', [ $c->program, $c->program_version ] ) ]
		= _wrappable( $c->identifier . $c->descriptor );
}

$o_data->lev1title( 'Details', true )->lev1ar( $d );
/*
foreach ( [ $json->pdbx_descriptor, $json->pdbx_identifier ] as $o ) {
	foreach ( (array)$o as  $c ) {
		$o_data->lev1(
			strtr( $c->type, '_', ' ' ),
			_wrappable( trim( $c->descriptor . $c->identifier ) ) 
		); 
	}
}
*/
//. PDBs
$o_data->lev1title( 'PDB entries' )->lev1direct( _div( '#pdbs', _chem2pdbs() ) );


//. functions
//.. _wrappable 改行できるようにする
function _wrappable( $s ) {
	return preg_replace( '/\b/', '<wbr>', $s );
}

//.. chem_compカテゴリの内容
function _cc( $ar ) {
	global $cc;
	$icons = [];

	$ret = [];
	foreach ( $ar as $k => $v ) {
		if ( is_numeric( $k ) ) {
			$k = $v;
			$v = $cc->$v;
		}
		if ( $v == '' ) continue;
		if ( $k == 'pdbx_model_coordinates_db_code' ) {
			$icons[] = "pdb-$v";
			$v = _ab([ 'id' => $v ], $v );
		}
		if ( in_array( $k, [
			'mon_nstd_parent_comp_id' ,
			'pdbx_subcomponent_list' ,
			'pdbx_replaced_by' ,
			'pdbx_replaces' ,
		]) ) {
			$o = [];
			foreach ( preg_split( '/[ ,]+/', $v ) as $i ) {
				$o[] = _ab([ 'id' => $i ], $i );
				$icons[] = "chem-$i";
			}
			$v = _imp( $o );
		}
		$ret[ "$k||chem_comp.$k" ] = $v;
	}
	if ( $icons )
		$ret[ '#div' ] = _ent_catalog( $icons, [ 'mode'=>'icon' ] );
	return $ret;
}

//. 情報
/*
                                    id: 29,128
                    pdbx_modified_date: 29,128
                  pdbx_processing_site: 29,128
   pdbx_ideal_coordinates_missing_flag: 29,128
                        formula_weight: 29,128
                   pdbx_release_status: 29,128
   pdbx_model_coordinates_missing_flag: 29,128
                     pdbx_initial_date: 29,128
                    pdbx_formal_charge: 29,128
                                  type: 29,128
                               formula: 29,127
                                  name: 29,121
                     three_letter_code: 29,028
                             pdbx_type: 28,850
                   pdbx_ambiguous_flag: 27,886
        pdbx_model_coordinates_db_code: 27,167
        pdbx_ideal_coordinates_details: 22,696
                         pdbx_synonyms: 5,428
               mon_nstd_parent_comp_id: 1,547
                       one_letter_code: 1,459
                pdbx_subcomponent_list: 474
                      pdbx_replaced_by: 347
                         pdbx_replaces: 269
        pdbx_model_coordinates_details: 157
*/
