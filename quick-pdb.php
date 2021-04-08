<?php
$_simple->time( 'quick common' );

//PDB
require( __DIR__. '/common-pdbdet.php' );
$_simple->time( 'pdb-det' );

//. init
//- ムービー
define( 'MOV_EX', $main_id->ex_mov() );

//.. subdata
_add_trep( 'quick-pdb' );
_add_lang( 'quick-pdb' );
_add_lang( 'bird' );
_add_url(  'quick-pdb' );
_add_fn(   'quick-pdb' );
_add_unit( _json_load( DN_DATA . '/cif_unit.json.gz' ) );
_add_unit( 'quick-pdb' );

//.. term
_define_term( <<<EOD
TERM_NUM_ENTRIES
	_1_ entries
	_1_エントリ
TERM_NUM_COMFORMERS_S_C
	Number of conformers (submitted / calculated)
	コンフォーマー数 (登録 / 計算)
TERM_NUM_MODELS
	Number of models
	モデル数
TERM_ASB_DEF_AUTH
	defined by author
	登録者が定義した集合体
TERM_ASB_DEF_SOFT
	defined by software
	ソフトウェアが定義した集合体
TERM_ASB_DEF_A_S
	defined by author&software
	登録者・ソフトウェアが定義した集合体
TERM_ASB_IDENT
	Idetical with deposited unit
	登録構造と同一
TERM_ASB_DIST_COORD
	Idetical with deposited unit in distinct coordinate
	登録構造と同一（異なる座標系）
TERM_DL_STR_DATA
	Download structure data
	構造データをダウンロード
TERM_SHOW_LARGE_TABLE
	Show large table
	大きな表を表示
TERM_HIDE_LARGE_TABLE
	Hide large table
	大きな表を隠す
TERM_CIF_DIC
	PDBx/mmCIF dictionary
	PDBx/mmCIF 辞書
TERM_DOC_PDB
	PDB Contents Guide
	PDB形式の解説
TERM_SHOW_WATER
	Show water
	水を表示
TERM_HIDE_WATER
	Hide water
	水を非表示

EOD
);

//.. tag_name
define( 'TAG_NAME', [
	'feature_type' => 'pdbx_entity_instance_feature.feature_type' ,
]);

//.. plus file
$plus = _json_load2( _fn( 'pdb_plus', $id ) );
_cif_rep( $plus );

foreach ( (array)$plus->exptl_crystal_grow as $c ) {
	if ( $c->temp_unit && $c->temp ) {
		$c->temp .= ' '. $c->temp_unit;
		unset( $c->temp_unit );
	}
	if ( $c->temp_unit && ! $c->temp ) {
		unset( $c->temp_unit );
	}
}
$_simple->time( 'load plus' );

//.. json 修正
_cif_rep( $json );

//... src 冗長な属名を消去
foreach ( (array)$json->entity_src_gen as $c ) {
	if (
		_headmatch( $c->gene_src_genus . ' ', $c->gene_src_species )  ||
		_headmatch( $c->gene_src_genus . ' ', $c->pdbx_gene_src_scientific_name ) 
	)
		unset( $c->gene_src_genus );
	if ( $c->gene_src_species == $c->pdbx_gene_src_scientific_name )
		unset( $c->gene_src_species );

	if ( 
		_headmatch( $c->host_org_genus . ' ', $c->host_org_species ) ||
		_headmatch( $c->host_org_genus . ' ', $c->pdbx_host_org_scientific_name )
	)
		unset( $c->host_org_genus );
	if ( $c->host_org_species == $c->pdbx_host_org_scientific_name )
		unset( $c->host_org_species );
}

foreach ( (array)$json->entity_src_nat as $c ) {
	if (
		_headmatch( $c->genus . ' ', $c->species ) ||
		_headmatch( $c->genus . ' ', $c->pdbx_organism_scientific )
	)
		unset( $c->genus );
	if ( $c->species == $c->pdbx_organism_scientific )
		unset( $c->species );
}

//... beg -> end xxx_id 連結
foreach ([
	'struct_ncs_dom_lim',
	'pdbx_refine_tls_group'
] as $cat ) {
	foreach ( (array)$json->$cat as $j ) {
		foreach ([
			'auth_asym_id' ,
			'label_asym_id' ,
			'auth_seq_id' ,
			'label_seq_id' ,
		] as $tag ) {
			if ( $j->{"beg_$tag"} != null ) {
				$v = $j->{"beg_$tag"};
				if ( $j->{"end_$tag"} != null && $j->{"end_$tag"} != $v )
					$v = "$v - " . $j->{"end_$tag"} ;
				$j->$tag = $v;
				unset( $j->{"beg_$tag"} , $j->{"end_$tag"}  );
			}
		}
	}
}

//... struct_ncs_oper マトリックス整理
foreach ( (array)$json->struct_ncs_oper as $j ) {
	if ( $j->matrix11 == null ) continue;
	//- matrix
	$m1 = _imp([ (real)$j->matrix11, (real)$j->matrix12, (real)$j->matrix13 ]);
	$m2 = _imp([ (real)$j->matrix21, (real)$j->matrix22, (real)$j->matrix23 ]);
	$m3 = _imp([ (real)$j->matrix31, (real)$j->matrix32, (real)$j->matrix33 ]);
	unset(
		$j->matrix11, $j->matrix12, $j->matrix13 ,
		$j->matrix21, $j->matrix22, $j->matrix23 ,
		$j->matrix31, $j->matrix32, $j->matrix33
	);
	if ( $m1 || $m2 || $m3 )
		$j->matrix = "($m1),<wbr> ($m2),<wbr> ($m3)";

	//- vector
	$v = _imp( (real)$j->vector1, (real)$j->vector2, (real)$j->vector3 );
	unset( $j->vector1, $j->vector2, $j->vector3 );
	if ( $v )
		$j->vector = "<wbr>$v";
}

//... struct_ncs_dom.details 長いやつがある
foreach ([
	'struct_ncs_dom_lim' => 'selection_details' ,
	'struct_ncs_dom' => 'details'
] as $k => $v ) {
	foreach ( (array)$json->$k as $j ) {
		if ( strlen( $j->$v ) < 100 ) continue;
		$j->$v = implode( ' ', array_slice(
			explode( ' ', $j->$v ) ,
			0, 10
		)) . '...';
	}
}
//... details を最後へ
foreach ([
	'refine',
	'em_image_recording' ,
	'em_particle_selection' ,
	'em_ctf_correction ' ,
	'em_buffer' ,
	'em_specimen' ,
	'diffrn_detector' ,
	'em_3d_fitting' ,

] as $cat ) foreach ( (array)$json->$cat as $c ) {
	if ( ! $c->details ) continue;
	$d = $c->details;
	unset( $c->details );
	$c->details = $d;
}

//... em_entity_assembly.entity_id_list, 連続する数字をまとめる
foreach ( (array)$json->em_entity_assembly as $c ) {
	if ( ! $c->entity_id_list ) continue;
	$out = [];
	$prev = $from = $to = null;
	foreach ( explode( ',', trim( $c->entity_id_list,  ', ' ) ) as $num ) {
		$num = trim( $num );
		if ( ! $from ) {
			$from = _sharp( $num );
		} else if ( $prev == $num -1 ) {
			$to = _sharp( $num );
		} else {
			$out[] = implode( '-', array_filter([ $from, $to ]) );
			$to = null;
			$from = _sharp( $num );
		}
		$prev = $num;
	}
	$out[] = implode( '-', array_filter([ $from, $to ]) );
	$c->entity_id_list = _imp( $out );

}

//.. json単純化
$json_reid = _json_reid([

	'struct_asym'				=> [ 'asym'		, 'id' ] ,
	'entity'					=> [ 'ent'		, 'id' ],
	'entity_poly'				=> [ 'entp'		, 'entity_id' ] ,
	'pdbx_entity_nonpoly'		=> [ 'entp'		, 'entity_id' ] ,
	'entity_name_com'			=> [ 'entNameCom'	, 'entity_id' ] ,
	'entity_name_sys'			=> [ 'entNameSys'	, 'entity_id' ] ,
	'entity_keywords'			=> [ 'ent_kw'	, 'entity_id' ],

	'pdbx_struct_assembly_gen'	=> [ 'asbgen'	, 'assembly_id', true ],
	'pdbx_struct_assembly' 		=> [ 'asb' 		, 'id' ] ,
	'pdbx_struct_oper_list'		=> [ 'oprlist' 	, 'id'] ,
	'struct_ref'				=> [ 'struct_ref'	, 'entity_id', true ] ,

	'entity_src_gen'			=> [ 'src'		, 'entity_id', true ] ,
	'entity_src_nat'			=> [ 'src'		, 'entity_id', true ] ,
	'pdbx_entity_src_syn'		=> [ 'src'		, 'entity_id', true ] ,

	'chem_comp'					=> [ 'chem'		, 'id' ] ,
	
	'pdbx_entity_branch'			=> [ 'brc'		, 'entity_id' ],
	'pdbx_entity_branch_descriptor'	=> [ 'brc_desc'	, 'entity_id', true ],
	'pdbx_entity_branch_list'		=> [ 'brc_list' , 'entity_id', true ],
	'pdbx_entity_branch_link'		=> [ 'brc_link' , 'entity_id', true ] ,

	'pdbx_chem_comp_identifier'	=> [ 'chem_idtf' , 'comp_id', true ],
	'pdbx_molecule_features'	=> [ 'mol_fet', 'prd_id' ] ,


]);

$_simple->time( 'json-reid' );

//.. asymid2chainid / chainid2asymid
$asymid2chainid = (array)$json->_yorodumi->id_asym2chain;
$chainid2asymid = (array)$json->_yorodumi->id_chain2asym;
foreach ( $asymid2chainid as $asym_id => $chain_id ) {
	$json_reid->asym->$asym_id->cid = $chain_id;
}

//.. pdbx_nonpoly_schemeから
//- 多糖用
//- コメントアウトは多分Jmol用だったもの
//$eid2comp = [];
// $asymid2sel = [];
//$asymid2eid = [];
$eid2asymid = [];
foreach ( (array)$json->pdbx_nonpoly_scheme as $c ) {
	$mid = $c->pdb_mon_id ?: $c->mon_id ?: $c->auth_mon_id;
	if ( $mid == 'HOH' ) continue;

	$asym_id = $c->asym_id;
	if ( !in_array( $asym_id, (array)$eid2asymid[ $c->entity_id ] ) )
		$eid2asymid[ $c->entity_id ][] = $asym_id;

	$json_reid->asym->$asym_id->cid    = $c->pdb_strand_id;
	$json_reid->asym->$asym_id->seqnum = $c->pdb_seq_num;
}
unset( $json->pdbx_nonpoly_scheme );

//.. pdbx_branch_schemeから
$brc_ent2asym = $brc_asym2chain = [];

$brc_ent2chain = [];
foreach ( (array)$json->pdbx_branch_scheme as $c ) {
	$brc_ent2asym[ $c->entity_id ][] = $c->asym_id;
	$chain_id = $c->auth_asym_id;
	if ( $chain_id && $c->asym_id != $chain_id )
		$brc_asym2chain[ $c->asym_id ] = $chain_id;
}
unset( $json->pdbx_branch_scheme );

//.. name grouping
define ( 'FLG_MANY_ENT', count( (array)$json_reid->ent ) > 5 );
$group_names = [];
if ( FLG_MANY_ENT ) {
	$names = [];
	foreach ( (array)$json_reid->ent as $c ) {
		$names[] = $c->pdbx_description;
	}
	$group_names = _group_name( $names );
}

//. basic info
//.. Components
$out = [];
$desc_nonp = [];
foreach ( (array)$json_reid->ent as $c ) {
	$t = $c->pdbx_description;
	if ( $c->type != 'polymer' ) {
		$desc_nonp[] = $t;
		continue;
	}
	$match = strtr( $t, [ '*' => '*<wbr>' ] );
	foreach( (array)$group_names as $g ) {
		if ( !_headmatch( $g, $t ) ) continue;
		$match = "$g ...";
		break;
	}
	++ $out[ $match ];
}

//- グループ化
$out2 = [];
foreach ( $out ?: $desc_nonp as $t => $cnt ) {
	$w = _obj('wikipe')->pop_xx( $t );
	$out2[] = $cnt == 1 ? $t.$w : "($t$w) x $cnt";
}
sort( $out2 );

$compo_digest = count( $out2 ) < 2
	? _imp( $out2 )
	: _ul( $out2 );
;
unset( $out, $out2 );

//.. authors
$auth = [];
foreach ( $json->audit_author as $c )
	$auth[] = $c->name;

//.. date / revision
$hist = [];

//- obs/spr
foreach ( (array)$json->pdbx_database_PDB_obs_spr as $c ) {
	$p = [];
	foreach ( explode( ' ', (string)$c->replace_pdb_id ) as $i )
		$p[] = _ab([ 'quick', 'id' => $i ], $i );
	$hist[] = array_filter([
		'date'  => $c->date ,
		'event' => 'Supersession|pdbx_database_PDB_obs_spr' ,
		'ID||pdbx_database_PDB_obs_spr.replace_pdb_id' => _imp( $p ) ,
		'details||pdbx_database_PDB_obs_spr.details' => $c->details ,
		'show'	=> true,
	]);
}

//- Deposition
$c = $json->pdbx_database_status[0];
$hist[] = array_filter([
	'event' => 'Deposition|pdbx_database_status.recvd_initial_deposition_date' ,
	'date'  => $c->recvd_initial_deposition_date ,
	'deposit_site||pdbx_database_status.deposit_site' => $c->deposit_site ,
	'process_site||pdbx_database_status.process_site' => $c->process_site ,
	'show' => true ,
]);

//- revision
foreach ( (array)$json->pdbx_audit_revision_history as $c ) {
	$hist[ 'rev'. $c->ordinal ] = [
		'date'  => $c->revision_date ,
		'event' => _l( 'Revision' ). ' '. $c->major_revision. '.'. $c->minor_revision ,
		'show'  => $c->minor_revision == '0'
	];
}
foreach ([
	'pdbx_audit_revision_history' ,
	'pdbx_audit_revision_group' ,
	'pdbx_audit_revision_category' ,
	'pdbx_audit_revision_item' ,
	'pdbx_audit_revision_details' ,
] as $categ ) {
	foreach ( (array)$json->$categ as $c2 ) {
		$ordinal = 'rev'. ( $c2->revision_ordinal ?: $c2->ordinal );
		foreach ( $c2 as $k => $v ) {
			if ( in_array( $k, [
				'ordinal', 'revision_ordinal', 'revision_date',
				'major_revision', 'minor_revision'
			])) continue;
			if ( "$k|$v" == 'data_content_type|Structure model' ) continue;			
			$hist[ $ordinal ][ "$k||$categ.$k" ][] = $v;
//			$hist[ $ordinal ][ 'show' ] = $ordinal == 'rev1';
		}
	}
}


/*
$cols = [
	'revision'			=> 'revision|pdbx_audit_revision_history',
	'date'				=> 'date|pdbx_audit_revision_history.revision_date',
	'data_content_type'	=> 'data_content_type|pdbx_audit_revision_history.data_content_type'
];
$table = [];

//- 情報収集
foreach ([
	'pdbx_audit_revision_history' ,
	'pdbx_audit_revision_group' ,
	'pdbx_audit_revision_category' ,
	'pdbx_audit_revision_item' ,
	'pdbx_audit_revision_details' ,
] as $categ ) {
	foreach ( (array)$json->$categ as $c2 ) {
		$ordinal = $c2->revision_ordinal ?: $c2->ordinal;
		foreach ( $c2 as $k => $v ) {
			if ( in_array( $k, [
				'ordinal', 'revision_ordinal', 'revision_date',
				'major_revision', 'minor_revision'
			])) continue;
			$table[ $ordinal ][ $k ][] = $v;
			if ( ! $cols[ $k ] ) {
				$cols[ $k ] = "$k|$categ.$k";
			}
		}
		if ( $categ == 'pdbx_audit_revision_history' ) {
			$table[ $ordinal ][ 'revision' ] =
				(integer)$c2->major_revision .'.'. 
				(integer)$c2->minor_revision
			;
			$table[ $ordinal ][ 'date' ] = _datestr( $c2->revision_date );
		}
	}
}

//- テーブル作成
$tbl_data = '';
if ( $table ) {
	$rows = [];
	foreach ( $table as $ordinal => $data ) {
		$a = [];
		foreach ( array_keys( $cols ) as $col )
			$a[] = _imp2( _uniqfilt( $data[ $col ] ) );
		$rows[]= $a;
	}
	$tbl_data = _more( _table_toph( $cols, $rows ) );
}

//- まとめ
$date = _quick_kv([
	'Deposition||pdbx_database_status.recvd_initial_deposition_date' => _datestr(
		$json->pdbx_database_status[0]->recvd_initial_deposition_date 
	) ,
	'Release||pdbx_audit_revision_history.revision_date' => _datestr(
		$json->pdbx_audit_revision_history[0]->revision_date
	)
]) . $tbl_data;

unset( $table, $tbl_data );
*/
//.. source
$data = [];
//- data[xxx][0]に正式名、[1]に通称(array)
//- コンマ区切りで複数ある奴があるので

foreach ( (array)$json_reid->src as $c1 ) foreach ( $c1 as $c ) {
	$sn = explode( ',', ''
		. $c->pdbx_gene_src_scientific_name
		. $c->pdbx_organism_scientific
		. $c->organism_scientific
	);
	foreach ( $sn as $n => $s ) {
		if ( $s )
			$data[] = trim( preg_replace( '/\(.*\)/', '', $s ) ); //- カッコを消す
	}
}

$srcout = [];
foreach ( _uniqfilt( $data ) as $sn ) {
	$srcout[] = _quick_taxo( $sn );
}
$srcout = implode( BR, ( (array)$srcout ) );

//.. method
$met = [];

//... pdbx_method_to_determine_struct
foreach ([
	'exptl'				=> 'method' ,
	'diffrn_source'		=> 'source',
//	'em_vitrification'	=> 'instrument',
	'em_experiment'		=> 'reconstruction_method' ,
	'refine'			=> 'pdbx_method_to_determine_struct' ,
	'phasing'			=> 'method' ,
	'pdbx_nmr_refine'	=> 'method' ,
] as $categ => $item ) foreach ( (array)$json->$categ as $c ) {
	$i = $c->$item;
	if ( $categ == 'diffrn_source' ) {
		if ( in_array( strtolower( $i ), [
			'nuclear reactor', 'synchrotron', 'free electron laser' 
		]))
			 $met[] = _met_pop( $i, 'e' );
	} else if ( $categ == 'em_vitrification' ) {
		if ( $i && $i != 'none' )
			$met[] = _met_pop( 'cryo EM' );
	} else {
		$met[] = _met_pop( $i );
	}
}
if ( _emn_json( 'addinfo', $did )->stained )
	$met[] = _met_pop( 'negative staining', 'm' );

if ( _emn_json( 'addinfo', $did )->cryo )
	$met[] = _met_pop( 'cryo EM', 'm' );

//- resolution
$r = _reso();
if ( $r > 0 )
	$met[] =  _quick_kv(['resolution' => "$r &Aring;" ]);

//.. funding
$funding = [];
$flags = [];
$num = 0;
$country_jname = _subdata( 'e2j', 'country' );

foreach ( (array)$json->pdbx_audit_support as $c ) {
	$f = ''
		. _country_flag( $c->country )
		. _ej(
			$c->country ,
			$country_jname[ $c->country ] ?: $c->country
		)
	;
	$funding[] = [
		$c->funding_organization ,
		$c->grant_number ,
		$f
	];
	++ $num;
	$flags[] = $f;
}

if ( $funding ) {
	$flags[] = $num. _ej( 'items', '件' );
	$funding = _imp( _uniqfilt( $flags ) ) . ' '
		. _more( _table_toph([
			'Organization|pdbx_audit_support.funding_organization' ,
			'Grant number|pdbx_audit_support.grant_number' ,
			'Country|pdbx_audit_support' 
		], $funding, [ 'opt' => '.smaller' ] ))
	;
}
unset( $flags, $country_jname );

//.. output
$o_data->basicinfo([
	'flg_vis'        => true ,
	'flg_link'       => true ,
	'js_open_viewer' => "_vw.open('$did')" ,
])
->lev1ar([
	'Title||struct.title' 	=> $json->struct[0]->title ,
	'Components'			=> $compo_digest . _hdiv_focus( 'components' ) ,
	'Keywords||struct_keywords'
		=> _keywords( implode( ', ', (array)$json->struct_keywords[0] ) ) ,
	'func_homology'			=> _func_homology(), 
	'EMN category' 			=> _emn_categ() ,
	'Biological species' 	=> $srcout ,
	'Method' 				=> _imp2( _uniqfilt( $met ) ). _hdiv_focus( 'experimental' ),
	'Model details'			=> _long( $json->struct[0]->pdbx_model_details ) ,
	'Model type details'	=> _long( $json->struct[0]->pdbx_model_type_details ) ,
	'Authors||audit_author'	=> _authlist( $auth ) ,
	'Funding support||pdbx_audit_support' => $funding ,
	'Citation'				=> (new cls_citation())->pdb_json( $json )->output() ,
	'Validation Report'		=> _validation_rep('pdb') ,
//	'Date||pdbx_audit_revision_history' => $date ,
	'History||pdbx_audit_revision_history' => _history_table( $hist ),
]);
unset( $auth, $date, $compo_digest, $funding );

//.. remarks
foreach ( (array)$json->pdbx_database_remark as $c ) {
	$o_data->lev1( "Remark {$c->id}||pdbx_database_remark", _long( $c->text ) );
}

$_simple->time( 'bascic' );

//. visualization
_viewer();
$_simple->time( 'vis' );

//. ダウンロードとリンク
$o_data->lev1title( 'downlink', true );

//.. ダウンロード
$o_data
	->lev2( 'PDBx/mmcif format', [
		_a([ 'mmcif', $id ], IC_DL. "$id.cif.gz" ) ,
		_ab([ 'txtdisp', 'a' => "mmcif.$id" ], _l( 'Display in browser' ) ) ,
		_ab([ 'cif_tree', $id ], IC_L. _l( 'Tree view' ) ) ,
		_ab( _url( _ej( 'doc_cif_wwpdb', 'doc_cif_pdbj' ) ), IC_HELP. TERM_CIF_DIC )
	])
	->lev2( 'PDB format',
		$json->pdbx_database_status[0]->pdb_format_compatible == 'Y'
		? [
			_a([ 'pdb', $id  ], IC_DL . "pdb$id.ent.gz" ) ,
			_ab([ 'pdbnc', $id ], _l( 'Display in browser' ) ),
			_ab([ 'doc_pdb_format', '' ], IC_HELP. TERM_DOC_PDB )
		]
		: ''

		)
	->lev2( 'PDBML Plus', [
		_a([ 'mlplus_ad', $id  ], IC_DL . "$id-add.xml.gz" ) ,
		_ab([ 'txtdisp', 'a' => "mlplus_ad.$id" ], _l( 'Display in browser' ) ) ,
		MLPLUS
	])
	->lev2( 'Others'	, _ab([ 'mine-dl', $id ], IC_L. _l( 'Other downloads' ) ) )

	->end2( 'Download' )
;
$_simple->time( 'downloads' );

//.. related
/*
http://mmcif.wwpdb.org/dictionaries/mmcif_pdbx_v40.dic/Items/_pdbx_database_related.db_name.html
PDB  - Protein Databank 
NDB  - Nucleic Acid Database 
BMRB - BioMagResBank
EMDB - Electron Microscopy Database
BMCD - Biological Macromolecule Crystallization Database
TargetTrack - Target Registration and Protocol Database
SASBDB - Small Angle Scattering Biological Data Bank
*/

//... group dep
//- 複数あるわけはないが形式上foreach に
foreach ( (array)$json->pdbx_deposit_group as $c ) {
	$j = _json_load2( DN_DATA. '/pdb/grp_dep.json.gz' )->{ $c->group_id };
	$o_data
		->lev2(
			_cifdic_link( 'ID', 'pdbx_deposit_group.group_id' ) ,
			_ab(
				[ 'ysearch', 'kw' => 'grp_dep:'. $c->group_id ],
				$c->group_id. _kakko( _term_rep( TERM_NUM_ENTRIES, $j->num ) )
			)
			. BR
			. _ent_catalog( $j->ids, [ 'mode' => 'icon' ] )
		)
		->lev2(
			_cifdic_link( 'Title', 'pdbx_deposit_group.group_title' ) , 
			$c->group_title
		)
		->lev2(
			_cifdic_link( 'Type', 'pdbx_deposit_group.group_type' ) ,
			$c->group_type
		)
		->lev2(
			_cifdic_link( 'Description', 'pdbx_deposit_group.group_description' ) ,
			$c->group_description
		)
		->end2( 'Group deposition||pdbx_deposit_group' )
	;
}

//... main
_related_out( $json->pdbx_deposit_group != '' );

//... Experimental data
foreach ( (array)$json->pdbx_related_exp_data_set as $c ) {
	$o = [];
	foreach ( $c as $key => $val ) {
		if ( $key == 'ordinal' ) continue;
		if ( _instr( 'http', $val ) ) {
			$val = preg_replace(
				'/https?:\/\/[a-zA-Z0-9_\.\/\-]+/' ,
				_ab( '$0', '$0' ) ,
				$val
			);
		}
		if ( $key == 'data_reference' )
			$val = _ab([ 'doi', $val ], IC_L. $val );
		else if ( _instr( 'doi:', $val )  ) {
			$val = preg_replace(
				'/doi:([a-zA-Z0-9_\.\/\-]+)/' ,
				_ab([ 'doi', '$1' ], '$0' ) ,
				$val
			);
		}
		$o[ _cifdic_link( $key, "pdbx_related_exp_data_set.$key" ) ] = $val;
//		$o[ $key ] = $val;
	}
	$o_data->lev2( _sharp( $c->ordinal ), $o );
}
$o_data->end2( 'Experimental dataset||pdbx_related_exp_data_set' );
$_simple->time( 'related' );

//.. mom keyword
$kw = [ $json->struct_keywords[0]->pdbx_keywords ];
foreach ( (array)preg_split( '/[;,]/', $json->struct_keywords[0]->text ) as $t )
	$kw[] = trim( $t );
//_testinfo( $kw );

$kw[] = [ 
	'ribosome-c'	=> 'ribosome' ,
	'70s'			=> '70S Ribosomes' ,
	'80s'			=> 'ribosome' ,
	'amyloid' 		=> 'amyloid' ,
][ _ezsqlite([
	'dbname' => 'main' ,
	'select' => 'categ' ,
	'where'  => [ 'db_id', DID ]
]) ];

//- antibody
$flg_ab = _ezsqlite([
	'dbname' => 'abinfo' ,
	'where'  => "pdb_id = '". ID. "' AND ig_like = '0'" ,
	'select' => [ 'id' ]
]);
if ( $flg_ab )
	$kw[] = "antibody";

//.. リンク
$o_data
	//- テスト用
	->lev2( _span( '.red', 'test info' ) , _test( _imp2(
		_ab([ 'json', DID ], 'JSONview' ) ,
		_ab([ '_categ_size', 'id' => ID ], 'categ size' ) ,
		$plus
			? _ab([ 'jsonv_plus', ID ] , 'Plus-json' ) 
			: _span( '.red bld', 'No plus-json file' )
		,
		$main_id->is_em()
			? _fa( 'folder' ) . _ab([ 'dir_pdb_med', ID ], 'media dir' )
			: ''
		,
		_ab([ 'txtdisp'	, 'a' => 'pdb_kw.'.ID ], 'search terms' ) 
		,
		_ab([ 'jsonview', 'a' => 'pdb_met.'.ID ], 'met-json' )
		,
		_ab([ 'prime'	, 'id' => ID ], 'prime' )
	)))
	->lev2( 'PDB pages', _imp2([
		_ab([ 'mine-sum', $id ],	_ic( 'pdbj' ) . 'PDBj' ),
//		_ab([ 'rcsb'    , $id ],	IC_L. 'RCSB PDB' ),
//		_ab([ 'pdbe'    , $id ],	IC_L. 'PDBe' ) ,
		_ab([ 'wwpdb_landing', $id ],	IC_L. 'wwPDB' ) ,
		_ab([ 'ncbi_str', $id ],	IC_L. 'NCBI' ) ,
	]))
	->lev2( TERM_REL_MOM, _mom_items( $kw ) )

	->end2( 'Links' )
;
$_simple->time( 'link' );

//. assembly pre

//.. mol weight
$sum_d = [];
foreach ( $json_reid->ent as $i => $ent ) {
	$t = $ent->type;

	//- sugar? 多糖をポリマーと呼ぶのはやめる
	if ( _instr( 'sugar (', $ent->pdbx_description ) )
		$t = $json_reid->ent->$i->type = 'sugar';

	$n = $ent->pdbx_number_of_molecules;
	$m = $ent->formula_weight * $n;

	//- water
	if ( $t == 'water' ) {
		$sum_d[ 'mass_wat' ] += $m;
		$sum_d[ 'cnt_wat' ] += $n;
		continue;
	}
	
	//- total
	$sum_d[ 'mass_total' ] += $m;
	$sum_d[ 'cnt_total'  ] += $n;
	
	//- poly
	if ( $t != 'polymer' ) continue;
	$sum_d[ 'mass_poly' ] += $m;
	$sum_d[ 'cnt_poly'  ] += $n;
}

//.. asymbar
define( 'ASYMBAR_TOTALSIZE', 300 );
//define( 'ASYMBAR_TOTALSIZE', 90 );

/*
.asymbar:  サイズ可変バー
.chainicon: 中にchian-IDを書く方
*/

$asymbar = [];
$asymbar_box = '';
$ent_icon = [];
$btns_chain_select = [];
foreach ( $json_reid->asym as $asym_id => $child ) {
	$chain_id = $child->cid;
	if ( $chain_id == '' ) continue;
	if ( $child->seqnum ) continue; //- chainじゃない

	$ent = $json_reid->ent->{ $child->entity_id };
	$w = _asymbar_size( $ent->formula_weight );
	$d = "$chain_id: " . $ent->pdbx_description;
	$col = _chain_color( $chain_id );

	$asymbar[ $asym_id ] = _t( "div|.asymbar | ?$d | st:width:{$w}px;$col", '' )
//		. _div( '.asymbar_tx|st: display: inline-box', $d )
		. _span( '.asymbar_tx', $d . BR )
	;
	$asymbar_box .= $asymbar[ $asym_id ];

	$ic = _chain_icon( $chain_id );
	$ent_icon[ $child->entity_id ][] = $ic;
	$btns_chain_select[ $child->entity_id ][] = _btn_popviewer( DID, [
		'btn_label'	=> I_SEL . $ic,
		'btn_cls'	=> 'select_btn' ,
		'jmol'		=> [ 'select', "*:$chain_id" ] ,
		'molmil'	=> [ 'focus_chain', $asym_id ]
	]);
}

function _asymbar_het( $mass ) {
	global $sum_d;
	if ( $mass == 0 ) return;
	$m = _asymbar_size( $mass );
	$h = _ej( 'hetero molecules', 'ヘテロ分子' );
	return _div( ".asymbar | ?$h | st:width:{$m}px;", '' )
			. _span( '.asymbar_tx', $h . BR )
	;
}

function _asymbar_size( $mass ) {
	global $sum_d;
	return round( $mass / $sum_d[ 'mass_total' ] * ASYMBAR_TOTALSIZE );
}

$asymbar_box = _div( '.asymbar_box',
	$asymbar_box . _asymbar_het( $sum_d[ 'mass_total' ] - $sum_d[ 'mass_poly' ] ) 
);

//.. pdbx_coordinate_model
//- Calpha-onlyとか
$t = [];
foreach ( (array)$json->pdbx_coordinate_model as $c )
	$t[ $c->type ] .= _chain_icon( $asymid2chainid[ $c->asym_id ] );

$info = [ _mass_sum( $sum_d ) ];
foreach ( $t as $k => $v )
	$info[] = _quick_kv([ $k => $v ]);

$info[] = _omos_link( "$id-d" );

//.. 登録構造
$o_data->lev1title( 'Assembly' )
->lev1( 'Deposited unit', ''
	. $asymbar_box
	. BR
	. _btn_popviewer( DID, [
		'btn_label'	=> IC_VIEW. TERM_SHOW_IN_VIEWER,
		'btn_cls'	=> 'asb_btn' ,
		'jmol'		=> [ 'reload' ] ,
		'molmil'	=> [ 'asb', -1 ]
	])
	. _simple_tabs([
		'tab' => 'Summary' ,
		'div' => _img( '.mainimg', [ 'pdb_img_dep', ID ] )
			. _ul( $info, 0 ). _div( '.clboth', '' )
	],[
		'tab' => 'Component details',
		'div' => _mass_det( $sum_d )
	])
);
unset( $cmodel_table );

//.. 事前の整理
$reps = [
	'author_defined_assembly'              => _span( '.green bld', TERM_ASB_DEF_AUTH ) ,
	'software_defined_assembly'            => _span( '.red bld',   TERM_ASB_DEF_SOFT ) ,
	'author_and_software_defined_assembly' => _span( '.blue bld',  TERM_ASB_DEF_A_S )
];

//- 全asym-id
$asymidlist = _sort_commastr( array_keys( (array)$json_reid->asym ) );

//- 文字列をコンマで分割してソートしてもどす
function _sort_commastr( $a ) {
	if ( $a == '' ) return;
	if ( is_string( $a ) )
		$a = explode( ',', $a );
	sort( $a );
	return implode( ',', $a );
}

//... prop
$asb_prop = [];
foreach ( (array)$json->pdbx_struct_assembly_prop as $c ) {
	$asb_prop[ $c->biol_id ][
		_trep( $c->type, [ 'tag'=> 'pdbx_struct_assembly_prop.type' ] )
	] = _valprep( $c->value, $c->type );
}

//... pdbx_struct_assembly_auth_evidence
$asb_evidence = [];
foreach ( (array)$json->pdbx_struct_assembly_auth_evidence as $c ) {
	foreach ( [ 'experimental_support', 'details' ] as $item ) {
		if ( ! $c->$item ) continue;
		$asb_evidence[ $c->assembly_id ][] = $item == 'experimental_support'
			? _met_pop( $c->$item ) : $c->$item
		;
	}
}

//. assembly main
foreach ( (array)$json_reid->asb as $abid => $child ) {
	if ( $child == '' ) continue;

	$asbgen = $json_reid->asbgen->$abid;

	//.. 登録構造と同じ?
	$same = '';
	if (
		_sort_commastr( $asbgen[0]->asym_id_list ) == $asymidlist 
		&& count( $asbgen ) == 1
	) {
		$t = $json_reid->oprlist->{ $asbgen[0]->oper_expression }->type;
		if ( $t == 'identity operation' )
			$same = TERM_ASB_IDENT;
		else if ( $t != ''  )
			$same = TERM_ASB_DIST_COORD;
	}

	//.. 対称操作、数とID
	//- opexの数
//	$opexc = 1;
//	$ops = '';
	$opexc = [];
	$opids = [];
	foreach ( (array)$asbgen as $i => $ag ) {
		$oc = [];
		foreach ( explode( ')(', $ag->oper_expression ) as $n => $f1 ) {
			foreach ( explode( ',', trim( $f1, '()' ) ) as $f2 ) {
				if ( _instr( '-', $f2 ) ) {
					//- a-b 表記
					$a = explode( '-', $f2 );
					$r = range( $a[0], $a[1] );
					$oc[ $n ] += count( $r );
					$opids = array_merge( $opids, $r );
				} else {
					++ $oc[ $n ];
					$opids[] = $f2;
				}
			}
		}
		$c = $oc[0];
		if ( $oc[1] > 0 )
			$c *= $oc[1];
		if ( $oc[2] > 0 )
			$c *= $oc[2];
		$opexc[ $i ] = $c;
	}

	//.. asymbar box
	//- アイコン、数と重さも計算
	$asymbarbox = [];
	$sum_total =[];
	if ( ! $same ) {
		foreach ( (array)$asbgen as $i => $ag ) {
			$sum = [];
			$bars = '';
			foreach ( explode( ',', $ag->asym_id_list ) as $asym_id ) {
				$e = $json_reid->ent->{ $json_reid->asym->$asym_id->entity_id };
				$w = $e->formula_weight * $opexc[ $i ];

				//- 水
				if ( $e->type == 'water' ) {
					$sum[ 'mass_wat' ] += $w;
					$sum[ 'cnt_wat' ] += $opexc[ $i ];
					continue;
				}

				//- total
				$sum[ 'mass_total' ] += $w;
				$sum[ 'cnt_total' ] += $opexc[ $i ];
				if ( $e->type != 'polymer' ) continue;

				//- poly
				$bars .= $asymbar[ $asym_id ];
				$sum[ 'mass_poly' ] += $w;
				$sum[ 'cnt_poly'  ] += $opexc[ $i ];
			}

			$bars .= _asymbar_het(
				( $sum[ 'mass_total' ] - $sum[ 'mass_poly' ] ) / $opexc[ $i ]
			);

			$asymbarbox[] = ( $opexc[ $i ] > 4
				? _t( 'div | .asymbar_box', $bars ) . ' x ' . $opexc[ $i ]
				: _t( 'div | .asymbar_box', implode( BR, array_fill( 1, $opexc[ $i ], $bars ) ) )
			);
			foreach ( $sum as $k => $v ) {
				$sum_total[ $k ] += $v;
			}
		}
	}

	//.. タブ整理
	$tabs = [];

	//.. summary	
	$odet = $child->oligomeric_details;

	//- 画像
	$n  = _fn( 'pdbimg_asb', ID, $abid );
	$img = file_exists( $n ) ? _img( '.mainimg', $n ) : '';

	$tabs[] = [
		'tab' => 'Summary' ,
		'div' => $img. _ul([
			$same , //- 登録構造と同じ？
			$reps[ $child->details ] ?: $child->details ,
			$asb_evidence[ $abid ]
				? _quick_kv([ 'Evidence' => _imp( $asb_evidence[ $abid ] ) ])
				: ''
			,
			substr( $odet, -5 ) != 'meric' ? $odet : '' , //- - dimerとかなら書かない
			$sum_total == [] ? '' : _mass_sum( $sum_total ) ,
			$main_id->ex_bufile( $abid )
				? _ab([ 'ftp-bu', $id, $abid ], IC_DL. TERM_DL_STR_DATA )
				: ''
			,
			strval( $abid ) > 0 && ! $same && $main_id->ex_vq( $abid )
				? _omos_link( "$id-$abid" ) : ''
		], 0 )
		. _div( '.clboth', '' )
	];

	//.. compo det
	if ( $sum_total != [] ) $tabs[] = [
		'tab' => 'Component details',
		'div' => _mass_det( $sum_total )
	];

	//.. sym operation
	$a = [];
	foreach ( (array)$opids as $i ) {
		$j = $json_reid->oprlist->$i;
		++ $a[ implode( '@', [ $j->type, $j->name, $j->symmetry_operation ] ) ];
	}
	$t = [];
	foreach ( $a as $k => $n )
		$t[] = explode( '@', "$k@$n" );

	$tabs[] =[
		'tab' => 'Symmetry operations' ,
		'div' => _table_toph(
			[ 'Type', 'Name', 'Symmetry operation', 'Number' ] ,
			$t
		)
	];

	//.. asb property
	if ( $child->method_details ) {
		$asb_prop[ $abid ][
			_trep( 'Method', [ 'tag' => 'pdbx_struct_assembly.method_details' ] )
		] = _met_pop( $child->method_details, 's' );
	}
	if ( $asb_prop[ $abid ] ) $tabs[] = [
		'tab' => 'Calculated values' ,
		'div' => _table_2col( $asb_prop[ $abid ] )
	];

	//.. output
	//- ビューア表示ボタン (asymbarboxに続けて表示)
	$asymbarbox[] = _btn_popviewer( DID, [
		'btn_label' => IC_VIEW. TERM_SHOW_IN_VIEWER ,
		'btn_cls'   => 'asb_btn' ,
		'jmol'      => [ 'asb', $abid ] ,
		'molmil'    => [ 'asb', $abid ]
	]);

	$o_data->lev1( _sharp( $abid ) ,
		implode( BR, array_filter( $asymbarbox ))
		. _simple_tabs( $tabs )
	);
}

//. assmbly post
//.. ユニットセル
$is_xtal = false;
foreach ( $json->exptl as $v ) {
	if ( in_array( $v->method, [ 'X-RAY DIFFRACTION', 'NEUTRON DIFFRACTION', 'FIBER DIFFRACTION','ELECTRON CRYSTALLOGRAPHY' ] ) )
		$is_xtal = true;
}
$j = $json->cell[0];
if ( $j && $is_xtal ) {

	//... unit cell image
	//-----
//	$j->length_a = 10;
//	$j->length_b = 10;
//	$j->length_c = 10;
//	$j->angle_alpha = 50;
//	$j->angle_beta = 60;
//	$j->angle_gamma = 60;
	//-----

	$len_a = $j->length_a;
	$len_b = $j->length_b;
	$len_c = $j->length_c;
	$sum =  250 / ( $len_a + $len_b +$len_c );
	$clen_a = $sum * $len_a;
	$clen_b = $sum * $len_b;
	$clen_c = $sum * $len_c;

	//- angles;
	$alpha = $j->angle_alpha;
	$beta  = $j->angle_beta;
	$gamma = $j->angle_gamma;
	$skew_alpha = 90 - $alpha;
	$skew_beta  = 90 - $beta;
	$skew_gamma = 90 - $gamma;

	$dhang_alpha = rad2deg( acos(
		( _cos($beta) - ( _cos($alpha) * _cos($gamma) ) )
		/ ( _sin( $alpha) * _sin($gamma) )
	));

	//- length
	$gamma_a = $clen_a * _sin( $gamma );
	$beta_c  = $clen_c * _sin( $beta  );
	$alpha_c = $clen_c * _sin( $alpha );


	//- alpha plane
	$st_alpha = "st: width: {$clen_b}px; height: {$alpha_c}px; transform:"
		. " rotateX({$dhang_alpha}deg)"
		. ( $skew_alpha == 0 ? '' : " skew({$skew_alpha}deg) " )
	;

	//- beta plane
	$dhang_beta = 180 - rad2deg( acos(
		( _cos( $alpha ) - ( _cos( $beta ) * _cos( $gamma ) ) )
		/ ( _sin($beta) * _sin($gamma) )
	));

	$a = 90 - $beta;
	$st_beta  = "st: width: {$clen_a}px; height: {$beta_c}px; transform:"
		. " rotateZ({$gamma}deg) rotateX({$dhang_beta}deg)"
		. ( $skew_beta == 0 ? '' : " skew({$skew_beta}deg) " )
	;

	//- gamma plane
	$st_gamma = "st: width: {$clen_b}px; height: {$gamma_a}px; transform:"
		. ( $skew_gamma == 0 ? '' : " skew({$skew_gamma}deg)" )
	;

	//... param
	$param = [
		'Length a, b, c (&Aring;)' => _imp( $len_a, $len_b, $len_c ) ,
		'Angle &alpha;, &beta;, &gamma; (deg.)' => _imp( $alpha, $beta, $gamma ) 
	]; 
	foreach ( $json->symmetry as $c1 ) {
		foreach ( $c1 as $k => $v ) {
			if ( is_object( $v ) ) continue; //- なぜかオブジェクトのデータが有る
			$param[ $k ] = _valprep( $v, $k  );
		}
	}

	//... space_group_symop
	$sym_op = [];
	foreach ( (array)$json->space_group_symop as $c ) {
		$sym_op [] = _kv([ _sharp( $c->id ) => $c->operation_xyz ]);
	}
	if ( $sym_op ) {
		$param[ 'symmetry_operation' ] = implode( BR, $sym_op );
	}

	//... output
	$o_data->lev1( 'unit cell', ''
		. _div( '#uc_outer| .left', _div( '#uc_inner', _div( '#uc_inner2', ''
			. _div( ".uc_pl pl_gamma| $st_gamma", '&gamma;' )
			. _div( ".uc_pl pl_alpha| $st_alpha", '&alpha;' )
			. _div( ".uc_pl pl_beta | $st_beta" , '&beta;' )
		)))
		. _table_2col( $param )
/*
		. _quick_kv( array_merge([
			'Length a, b, c (&Aring;)' => _imp( $len_a, $len_b, $len_c ) ,
			'Angle &alpha;, &beta;, &gamma; (deg.)' => _imp( $alpha, $beta, $gamma ) 
		], $symmetry) )
*/
	);
	
}


//.. atom_sites_footnote
$o = [];
foreach ( (array)$json->atom_sites_footnote as $c) {
	$o[ $c->id ] = $c->text;
}
$o_data->lev1( 'Atom site foot note', $o );

//.. NMR アンサンブル
//- モデル数
/*
$models = [];
foreach ([
	'ndb_struct_na_base_pair' ,
	'ndb_struct_na_base_pair_step' ,
	'pdbx_distant_solvent_atoms' ,
	'pdbx_struct_special_symmetry' ,
	'pdbx_unobs_or_zero_occ_atoms' ,
	'pdbx_unobs_or_zero_occ_residues' ,
	'pdbx_validate_chiral' ,
	'pdbx_validate_close_contact' ,
	'pdbx_validate_main_chain_plane' ,
	'pdbx_validate_peptide_omega' ,
	'pdbx_validate_planes' ,
	'pdbx_validate_polymer_linkage' ,
	'pdbx_validate_rmsd_angle' ,
	'pdbx_validate_rmsd_bond' ,
	'pdbx_validate_symm_contact' ,
	'pdbx_validate_torsion' ,
	'struct_mon_prot_cis' ,
] as $c ) foreach ( (array)$json->$c as $c1 ) {
	if ( $c1->PDB_model_num != '' )
		$models[ $c1->PDB_model_num ] = 1;
}
$models = array_keys( $models );
*/

$ens = $json->pdbx_nmr_ensemble[0];
$rep = $json->pdbx_nmr_representative[0];
$tnum  = $ens->conformers_submitted_total_number;

//- モデル数
$model_count = count( $json->_yorodumi->model_list );

//... NMRじゃないけどコンフォーマー
if ( $ens == '' && $model_count > 1 ) {
	$o_data->lev1( TERM_NUM_MODELS, $model_count );
}

//... nmrアンサンブル 共通部
if ( $ens ) {
	//- タイトル
	$o_data->lev1(
		( $ens != '' ? 'NMR ensembles' : 'Conformers' ) ,
		_table_2col([
			'' => [ 'Data', 'Criteria' ] ,
			TERM_NUM_COMFORMERS_S_C => [
				( $ens->conformers_submitted_total_number ?: $model_count ?: '-' )
				. ' / '
				. ( $ens->conformers_calculated_total_number ?: '-' )
				,
				$ens->conformer_selection_criteria
			] ,
			'Representative' => [
				$rep->conformer_id ? _l( 'Model' ). ' #'. $rep->conformer_id : '',
				$rep->selection_criteria
			]
		], [ 'topth' => true ])
	);

/*
	//... モデル選択jv用
	if ( $model_count < $tnum ) {
		$models = [];
		foreach ( range( 1, $tnum ) as $i )
			$models[] = $i;
		$model_count = $tnum;
	}
	$min = min( $models	);
	$max = max( $models );
	$ret[ 'nmr_jv' ] = ''
		. _bt( _ej( "All $model_count models", "全 {$model_count}モデル" ), '_model()')
		. _bt( '<-', "_nmodel(0,$min,$max)" )
		. ' ' . _l( 'Model' ) . ': ' . _span( '#cmodel|.blue', 'all' ) . ' '
		. _bt( '->', "_nmodel(1,$min,$max)" )
	;

	//... jmol用
	$n = ( $tnum > $model_count ) ? $tnum : $model_count ;
	if ( $n > 1 ) {
		$ret[ 'nmr_jmol' ] = ''
			. _bt( _ej( "All $n models", "全 {$n}モデル" ) ,
					"aps( 'model all' )" )
			. _bt( _ic( 'play' ) .'|'. _l( 'Play as animation' ), 
					"aps( 'animation mode LOOP 0 0; frame play;' )" )
			. _bt( _ic( 'pause' ) .'|'. _l( 'Pause' ), "aps( 'frame pause;' )" )
			. _bt( '<-', "aps( 'frame pause;model previous' )" )
			. _bt( '->', "aps( 'frame pause;model next' )" )
		;
	}

	//... FIt robot
	if ( file_exists( $n = "data/pdb/fit_robo/$id.json" ) ) {
		$o = '';
		foreach ( _json_load( $n ) as $f => $d ) {
			$fid = strtr( $f, [ '.pdb' => '', $id . '_' => '' ] );
			$l = "data/pdb/fit_robo/$f.gz";
			$o .=  _p( ''
				. "$fid "
				. _bt(
					_span( '.b_asb', IC_ASB . _l( 'Display' ) ),
					"_loadans('$l',this)"
				)
				. _bt(
					_span( "#frb_sel_$fid", I_SEL . _ej( 'Fit residues', 'フィットした残基' ) ) ,
					"_select( '" . $d[ 'sel' ] . "',this )" 
					,
					'?' . _ej( "Select fit residues", "フィットした残基を選択" )
				)
				. _bt( _l( 'Details' ), "$('#frb_det_$fid').slideToggle('fast')"  )
				. _a( $l, IC_DL . _l( 'Download' ) )
			)
			. _div( "#frb_det_$fid | .hide", strtr( $d[ 'det' ], [ "\n" => BR ]  ) )
			;
		}
		$s = _ab(
			'http://bmrbdep.pdbj.org/en/nmr_tool_box.html' ,
			'Fit robot'
		) 
			. ' (ver 0.22.15, ' 
			. _ab( 'http://www.ncbi.nlm.nih.gov/pubmed/24384868', 'PubMed' )
			. ')'
		;
		$s = _p( ''
			. _ej(
				"Ensemble arrangements calculated by $s: " ,
				"{$s}によるアンサンブルの配置: "
			)
			. $o
		);
		$ret[ 'nmr_jmol' ] .= $s;
		$ret[ 'nmr_jv' ]   .= $s;
	}
*/
}

//.. point / helical symmetry
foreach ( (array)$json->pdbx_point_symmetry as $j ) {
	if ( ! $j->Schoenflies_symbol ) continue;
	if ( $j->circular_symmetry ) {
		$j->Schoenflies_symbol .= $j->circular_symmetry;
		unset( $j->circular_symmetry );
	}
	$j->Schoenflies_symbol = _symmetry_text( $j->Schoenflies_symbol );
}
_pdbml_common([
	'pdbx_point_symmetry' ,
	'pdbx_helical_symmetry' ,
],[],[]);
$o_data->end2( 'Symmetry' );

//.. pdbx_struct_special_symmetry
$t = [];
foreach ( (array)$json->pdbx_struct_special_symmetry as $c ) {
	$t[] = [
		$c->id ,
		$c->PDB_model_num ,
		implode( '-', [
			_chain_icon( $c->auth_asym_id ?: $c->label_asym_id ) ,
			( $c->auth_seq_id ?: $c->label_seq_id ) ,
			_chemimg_s( $c->auth_comp_id ?: $c->label_comp_id )
		])
	];
}
if ( $t ) {
	$o_data->lev1(
		'components on special symmetry positions||pdbx_struct_special_symmetry'
		, 
		_table_toph([
			'ID|pdbx_struct_special_symmetry.id' ,
			'Model|pdbx_struct_special_symmetry.PDB_model_num' ,
			'Components'
		], $t ) 
	);		
}
//.. function _mass_sum/ _mass_det: 質量計算
function _mass_sum( $a ) {
	extract( $a ); //- $mass_total, $mass_poly, $cnt_total, $cnt_poly
	$t = $mass_total;
	$v = 1;
	while ( $t / $v >= 1000 )
		$v *= 10;
	$t = round( $t / $v ) * $v;
	if ( $t >= 1000000 )
		$t = ( $t / 1000000 ) . ' MDa';
	else if ( $t >= 1000 )
		$t = ( $t / 1000 ) . ' kDa';
	else
		$t = "$t Da";

	return  "$t, $cnt_poly " . _ej( 'polymers', 'ポリマー' );
}

function _mass_det( $a ) {
	extract( $a ); //- $mass_total, $mass_poly, $cnt_total, $cnt_poly
	return _table_2col( [
		'' => [ 'Theoretical mass', 'Number of molelcules' ] ,
		'Total (without water)' => [
			number_format( $mass_total ) ,
			$cnt_total
		] ,
		'Polymers' => [
			number_format( $mass_poly ) ,
			$cnt_poly
		] ,
		'Non-polymers' => [
			number_format( (float)$mass_total - (float)$mass_poly ) ,
			(float)$cnt_total  - (float)$cnt_poly
		] ,
		'Water' => [
			number_format( $mass_wat ) ,
			$cnt_wat
		]
	], [ 'topth' => true, 'opt' => '.numtable' ] ); 
}

//.. NCS
_pdbml_common([
	'struct_ncs_dom' ,
	'struct_ncs_dom_lim' ,
	'struct_ncs_ens' ,
	'struct_ncs_oper' ,
],[
	
],[
	
]);
$o_data->end2( 'NCS' );


//.. details
$out = [];
foreach ( (array)$json->struct_biol as $c ) {
	$out[] = _ifnn( $c->pdbx_parent_biol_id, "<b>\1</b>: " ) . $c->details;
}
$o_data->lev1( 'Details', $out );
unset( $out );
$_simple->time( 'assembly' );


//. entity pre
//.. misc
define( 'TERM_POLYCAT', [
	'polydeoxyribonucleotide'	=> 'DNA chain' ,
	'polyribonucleotide'		=> 'RNA chain' ,
	'polydeoxyribonucleotide/polyribonucleotide hybrid'
								=> 'DNA/RNA hybrid'	,
]);

define( 'TAGS_SRC', [
	'genus' ,
	'species' ,
	'strain' ,
	'tissue' ,
	'organ' ,
	'tissue_fraction' ,
	'cellular_location' ,
	'secretion' ,
	'cell' ,
	'cell_line' ,
	'culture_collection' ,
	'organelle' ,
	'gene' ,
	'fragment' ,
	'plasmid_details' ,
	'plasmid_name' ,
	'variant' ,
	'details' ,
	'description' ,
//	'seq_type' ,
]);

//.. bird
$bird_id = [];
foreach ( (array)$json->pdbx_molecule as $c )
	$bird_id[ $json_reid->asym->{ $c->asym_id }->entity_id ] = $c->prd_id ;
//_testinfo( $bird_id, 'bird' );

//.. siteinfo 数えるだけ
$cnt_siteinfo = [];
/*
foreach ( array_merge(
	(array)$json->struct_site_gen ,
	(array)$plus->struct_site_gen
) as $c ) {
*/
foreach ( (array)$json->struct_site_gen as $c ) {
	$asym_id = $c->label_asym_id ?: $chainid2asymid[ $c->auth_asym_id ];
	if ( $asym_id != '' ) {
		$ent_id = $json_reid->asym->$asym_id->entity_id;
		$cnt_siteinfo[ $ent_id ][ $c->site_id ] = true;
	}
}
foreach ( (array)$json->struct_conn as $c ) {
	//- 水は無視
	if ( $c->ptnr1_label_comp_id == 'HOH' || $c->ptnr2_label_comp_id == 'HOH' ) continue;
	$e1 = $json_reid->asym->{ $c->ptnr1_label_asym_id }->entity_id;
	$e2 = $json_reid->asym->{ $c->ptnr2_label_asym_id }->entity_id;
	$cnt_siteinfo[ $e1 ][ $c->id ] = true;
	$cnt_siteinfo[ $e2 ][ $c->id ] = true;
}



//.. modified
$cnt_modres = [];
foreach ( (array)$json->pdbx_struct_mod_residue as $c ) {
	$asym_id = $c->label_asym_id ?: $chainid2asymid[ $c->auth_asym_id ];
	$ent_id = $json_reid->asym->$asym_id->entity_id;
	++ $cnt_modres[ $ent_id ];
}

//.. mutation
$cnt_mutres = [];
foreach ( (array)$json->struct_ref_seq_dif as $c ) {
	if ( 'modified residue' == strtolower( $c->details ) )
		continue;
	$asym_id = $c->label_asym_id ?: $chainid2asymid[ $c->pdbx_pdb_strand_id ];
	$ent_id = $json_reid->asym->$asym_id->entity_id;
	++ $cnt_mutres[ $ent_id ];
}

//.. func: _det_tab 詳細タブ
function _det_tab( $mode, $title, $num = -1 ) {
	global $tabs, $ent_id;
	if ( $num == 0 ) return;
	$tabs[] = [
		'tab' => _trep( $title ) . ( $num == -1 ? '' : _kakko( $num ) ) ,
		'js'  => "_dettab.get('$mode',$ent_id)",
		'div' => LOADING . _div( "#box$mode$ent_id", '' )
	];
}

//.. Calpha only 
$coord_model = [];
foreach ( (array)$json->pdbx_coordinate_model as $c ) {
	$ent_id = $json_reid->asym->{$c->asym_id}->entity_id;
	$coord_model[ $ent_id ][ 'type' ] = _trep( $c->type );
	$coord_model[ $ent_id ][ 'cid' ][] = $asymid2chainid[ $c->asym_id ];
}

foreach ( (array)$coord_model as $ent_id => $c ) {
	$t = $coord_model[ $ent_id ][ 'type' ];
	$coord_model[ $ent_id ] = 
		count( $c[ 'cid' ] ) == $json_reid->ent->$ent_id->pdbx_number_of_molecules //- 全部？
			? $t
			: "$t (Chain-" . _imp( $c[ 'cid' ] ) . ')'
	;
}

//.. validation 数えるだけ
$validate_cats = [];
foreach ([ 
	'pdbx_validate_close_contact' ,
	'pdbx_validate_symm_contact' ,
	'pdbx_validate_rmsd_bond' ,
	'pdbx_validate_rmsd_angle' ,
	'pdbx_validate_torsion' ,
	'pdbx_validate_peptide_omega' ,
	'pdbx_validate_chiral' ,
	'pdbx_validate_planes' ,
	'pdbx_validate_planes_atom' ,
	'pdbx_validate_main_chain_plane' ,
	'pdbx_validate_polymer_linkage' ,
] as $cat ) {
	foreach ( (array)$json->$cat as $c ) {
		foreach ([ '', '_1', '_2', '_3' ] as $num ) {
			$asym_id = $chainid2asymid[ $c->{ 'auth_asym_id' . $num } ];
			if ( $asym_id == '' ) continue;
			$validate_cats[ $json_reid->asym->$asym_id->entity_id ][ $cat ] = true;
		}
	}
}

//.. plus-json struct_ref
$plus_unp_ids = [];
foreach ( (array)$plus->struct_ref as $c ) {
	if ( $c->db_name != 'SIFTS_UNP' ) continue;
	$plus_unp_ids[ $c->entity_id ][] = $c->pdbx_db_accession;
}

//.. 抗体
/*
$is_antibody = [];
foreach ( (array)$json->entity_poly as $c ) {
	foreach ( (array)json_decode( _ezsqlite([
		'dbname' => 'cath' ,
		'where'  => [ 'id', ID. explode( ',', $c->pdbx_strand_id )[0] ] ,
		'select' => 'data'
	])) as $c2 ) {
		if ( $c2->cid != '2.60.40.10' ) continue;
		$is_antibody[ $c->entity_id ] = true;
		break;
	}
}

$sacs_json = [];
foreach ( (array)$json->entity as $c ) {
	//- SACS
	$sacs_json[ $c->id ] = json_decode( _ezsqlite([
		 'dbname' => 'sacs' ,
		 'where'  => [ 'id', ID. '-'. $c->id ] ,
		 'select' => 'json' ,
	]));
	if ( $sacs_json[ $c->id ] )
		$is_antibody[ $c->id ] = true;
	if ( $is_antibody[ $c->id ] ) continue;

	//- by name
	foreach ([
		[
			'/(heavy|light|lambda|kappa) chain/i',
			'/(mab\b|fab|antibody|antigen binding)/i'
		] ,
		[ '/\bFAB\b/i', '/\bVH\b|\bVL\b/i' ] ,
		[ '/\bnanobody\b/i' ] ,
		[ '/Immunoglobulin G/i' ] ,
		[ '/\bVHH[\- ]/i'] ,
		[ '/\bscFv[\b0-9]/' ] ,
		[ '/Single-chain Fv/i' ]
	] as $term_set ) {
		$hit = false;
		foreach ( $term_set as $term ) {
			//- setは一個でもヒットしないのがあるとダメ
			if ( preg_match( $term, $c->pdbx_description ) === 0 ) {
				$hit = false;
				break;
			}
			$hit = true;
		}
		if ( $hit ) {
			$is_antibody[ $c->id ] = true;
			break;
		}
	}
}

//... kabat

foreach ( (array)$json->entity_poly as $c ) {
	if ( $is_antibody[ $c->entity_id ] ) continue;
	$seq = preg_replace( '/[^A-Z]/', '', $c->pdbx_seq_one_letter_code_can );
	if (
		preg_match( '/(C)(.{10,17})(W.{14})(.{7})(.{30,34}C)(.{7,11})(FG.G)/', $seq ) ||
		preg_match( '/(C...)(.{10,12})(W.{13})(.{16,19})([KR][LIVFTA][TSIA].{26}C..)(.{3,25})(WG.G)/', $seq )
	)
		$is_antibody[ $c->entity_id ] = true;
}
*/

//.. COMP_SUGAR
/*
define( 'COMP_SUGAR', [
	'NAG', 'NBG', //- アセチルグルコサミン
	'BMA', 'MAN', //- マンノース
	'BGC', 'GLC', //- グルコース
	'FUC', 'FUL', 'FCA', //- フコース
	'SIA', 'SI2', //- シアル酸
	'BGC', 'GLC', 
]);
*/
//. entity メイン
$ent_info = [];
$ent_out = [];
$chemid2snfg =[];

foreach ( $json_reid->ent as $ent_id => $ent ) {
	$entp = $json_reid->entp->$ent_id;

	//.. basic info
	$chemid		= $entp->comp_id;
	$is_chem	= $chemid != '';
	$is_sugar	= _instr( 'SUGAR (', $ent->pdbx_description ) 
		|| _instr( 'saccharide', $json_reid->chem->$chemid->type )
		|| $ent->type == 'branched'
	;
	$is_poly	= $ent->type == 'polymer';
	$is_polysac = $ent->type == 'branched';
	$is_water	= $chemid == 'HOH';

	$mol_fet = $json_reid->mol_fet->{ $bird_id[ $ent_id ] };
	if ( $mol_fet ) {
		$o = [];
		foreach ( explode( ',', $mol_fet->class ) as $c )
			$o[] = _l( $c ). _obj('wikipe')->pop_xx( trim( $c ) );
		$mol_fet->class = _imp( $o );
	}

	$desc = [
		_t( 'span| .bld', $is_polysac
			? _long( $ent->pdbx_description )
			: _breakable( $ent->pdbx_description ) 
		) ,
		( $is_poly ? _obj('wikipe')->pop_xx( $ent->pdbx_description ) : '' ) ,
		$json_reid->entNameSys->$ent_id->name ,
		_long( _comma_rep( _unredun( $json_reid->entNameCom->$ent_id->name ) ) ) ,
		_obj('wikipe')->pop_xx( $mol_fet->name ) ,
	];

	//.. 化合物
	$chem_icon = '';
	$chem_imgs = '';

	$chem_comp = (object)[];
	$chem_type = [];
	if ( $is_chem ) {
		$chem_comp = $json_reid->chem->$chemid;
		$desc[] = strtr( $chem_comp->pdbx_synonyms, [ '; ' => SEP ] );

		foreach ( explode( ',', $chem_comp->type ) as $s ) {
			$s = trim( $s );
			if ( in_array( $s, [ 'non-polymer' ] ) ) continue;
			$chem_type[] = $s;
		}

		//- 日化辞
		$desc[] = _nikkaji_name( $chemid );

		//- wikipe
		$desc[] = _obj('wikipe')->chem( $chemid )->pop();

		//- chem icon
		if ( file_exists( $if = _fn( 'chem_img', $chemid ) ) ) {
			$chem_imgs = _ab( "?id=$chemid", _img( '.mainimg', $if ) );
			$chem_icon = _img( '.icon_ss', $if );
		}
		if ( !$is_water && file_exists( $if2 = _fn( 'chem_img2', $chemid ) ) )
			$chem_imgs .= _img( '.chemsvg clearfix| width:100| height:100', $if2 );
	}
	if ( $bird_id[ $ent_id ] && ! $chem_imgs ) {
		$i = _numonly( $bird_id[ $ent_id ] );
		$chem_imgs = (
			file_exists( $f1 = _fn( 'bird_img', $i ) )
				? _ab( "?id=bird-prd_$i", _img( '.mainimg', $f1 ) )
				: ''
		). (
			file_exists( $f2 = _fn( 'bird_img2', $i ) )
				? _img( '.chemsvg clearfix| width:100| height:100', $f2 )
				: ''
		);
	}
	if ( $chem_imgs )
		$chem_imgs .= _div( '.clboth', '' );

	//.. antibody
	list( $is_antibody, $like_ig ) = array_values( _ezsqlite([
		'dbname' => 'abinfo' ,
		'where'  => [ 'id', ID. '-'. $ent_id ] ,
		'select' => [ 'id', 'ig_like' ] ,
	]) );
	$is_antibody = $is_antibody && ! $like_ig;

	//.. エンティティをまとめるためのカテゴリ
	$type_of_poly = _trep(
		( $is_antibody ? 'Antibody' : '' ) ?:
		TERM_POLYCAT[ $entp->type ] ?:
		( $entp->type
			? ( 50 < strlen( $entp->pdbx_seq_one_letter_code_can )
				? 'Protein'
				: $entp->type
			)
			: ''
		) ?:
		( $is_polysac ? 'Polysaccharide'	: '' ) ?:
		( $is_sugar ? 'Sugar'	: '' ) ?:
		( $is_water ? 'Water'	: '' ) ?:
		( $is_chem  ? 'Chemical'	: '' ) ?:
		'Unknown'
	);
	$categ = 'Others';
	if ( FLG_MANY_ENT ) {
		if ( $is_sugar ) {
			//- 糖
			$categ = 'Sugars'; 
		} else if ( ! $is_poly ) {
			//- ノンポリ
			$categ = 'Non-polymers' ;
		} else if ( $is_antibody ) {
			$categ = 'Antibody';
		} else {
			//- ポリ
			if ( $categ == 'Others' )
				$categ = $type_of_poly;
			foreach( (array)$group_names as $nm ) {
				if ( !_headmatch( $nm, $ent->pdbx_description ) ) continue;
				$categ = "$nm ...";
				break;
			}
		}
	}
	
	/*
	//- 長けりゃタンパク質としてみる
	if ( $categ == 'polypeptide(L)' ) {
		if ( 30 < strlen( $entp->pdbx_seq_one_letter_code_can ) )
			$categ = 'Protein';
		else
			_testinfo( $entp->pdbx_seq_one_letter_code_can, $entid );
	} else {
		_testinfo( $categ, $entid );
	}
	*/
	

	//.. 多糖
	$polysc = '';
	if ( $is_polysac ) {
		//... comp list
		$comp = [];
		foreach ( (array)$json->pdbx_entity_branch_list as $c ) {
			if ( $c->entity_id != $ent_id ) continue;
			++ $comp[ $c->comp_id ];
		}
		if ( ! $chem_parent ) {
			$chem_parent = _subdata( 'chem', 'parent' );
		}
		$icon_ref = [];
		foreach ( $comp as $i => $num ) {
			$s = $chem_parent[ $i ] ?: $i;
			$icon_ref[] = "$num x ". _img( _url( 'snfg_icon', $s ) ). _chemimg_s( $i )
				. $json_reid->chem->$i->name
			;
		}

		//... svg img
		$d = '';
		foreach ( (array)$json_reid->brc_desc->$ent_id as $c ) {
			if ( $c->program != 'GMML' ) continue;
			$d = $c->descriptor;
		}
		$svg = _ezsqlite([
			'dbname' => 'polysac' ,
			'select' => 'svg' ,
			'where'  => [ 'desc', $d ?: ID. '-'. $ent_id ]
		]);
		
		//... まとめ
		$polysc .= _pop(
			$svg ,
			_div( '.right', _doc_pop( 'polysac' ) ). BR
			. implode( BR, $icon_ref ) ,
			[ 'type' => 'div' ]
		);
/*
		$polysc .= _div( '', ''
			. _div( '.inlineblk', $svg )
			. _div( '.inlineblk', implode(
				! $svg || 4 < count( $icon_ref ) ? SEP : BR ,
				$icon_ref 
			) )
			. _doc_pop( 'polysac' )
		);
*/
	}

	//.. details
	$type = [];
	foreach ( _uniqfilt( array_merge( $chem_type, [
		$json_reid->brc->$ent_id->type,
		$mol_fet->type
	] )) as $c )
		$type[] = $c. _obj('wikipe')->pop_xx( $c );

	$details = [
		'type'						=> _imp( $type ),
		'class'						=> $mol_fet->class ,
		'formula_weight'			=> _ifnn( $ent->formula_weight, '\1 Da' ),
		'pdbx_number_of_molecules'	=> $ent->pdbx_number_of_molecules ,
		'pdbx_fragment' 			=> $ent->pdbx_fragment ,
		'pdbx_mutation' 			=> $ent->pdbx_mutation ,
		'src_method'				=> _trep( $ent->src_method ) ,
		'formula'					=> _chemform2html( $chem_comp->formula ) ,
		'details'					=> _imp( $ent->details, $mol_fet->details ) ,
	];
//	_testinfo( $json_reid->brc->$ent_id, 'hoge' );

	//.. 由来
//	$srcs = '';
	$o = [];
	$lg = _ej( 'gene. exp.', '組換発現' );
	$ls = _ej( 'synth.', '合成' );
	$ln = _ej( 'natural', '天然' );
	foreach ( (array)$json_reid->src->$ent_id as $c ) {
		$g = $c->pdbx_gene_src_scientific_name;
		$s = $c->organism_scientific;
		$n = $c->pdbx_organism_scientific;
		if ( $g ) $o[] = "($lg) " . _quick_taxo( $g );
		if ( $s ) $o[] = "($ls) " . _quick_taxo( $s );
		if ( $n ) $o[] = "($ln) " . _quick_taxo( $n );
	}
	$o = _uniqfilt( $o );
	$details[ 'source' ] =_imp( $o );

	//.. 由来付加情報
	$o = [];
	foreach ( (array)$json_reid->src->$ent_id as $c ) {
		foreach ( $c as $key => $val ) {
			$key = strtr( $key, [ 'pdbx_' => '', 'gene_src_' => '' ] );
			if ( !in_array( $key, TAGS_SRC ) ) continue; 
			$o[ $key ][] = $val. ( in_array( $key, [
				'genus' ,
				'species' ,
				'strain' ,
				'tissue' ,
				'organ' ,
				'cellular_location' ,
				'organelle' 
			]) ? _obj('wikipe')->pop_xx( $val ) : '' );
		}
	}
	foreach ( $o as $k => $v ) {
		if ( !$v ) continue;
		$details[ $k ] = _imp( _uniqfilt( $v ) );
	}

	//.. 発現系
	$o = [];
	foreach ( (array)$json_reid->src->$ent_id as $c ) {
		foreach ( $c as $key => $val ) {
			if ( ! _instr( 'host_org_', $key ) ) continue;
			$key = strtr( $key, [ 'pdbx_' => '', 'host_org_' => '' ] );
			if ( in_array( $key, TAGS_SRC ) )
				$o[ _trep( $key ). _kakko( 'production host' ) ][] = $val;
			else if ( $key == 'scientific_name' ) {
				$o[ 'production host' ] = _quick_taxo( $val );
			}
		}
	}
	foreach ( $o as $k => $v ) {
		if ( !$v ) continue;
		$details[ $k ] = _imp( _uniqfilt( $v ) );
	}

	//.. キーワード
	$details[ 'Keywords' ] = $json_reid->ent_kw->$ent_id->text;

	//.. SUBJECT OF INVESTIGATION
	//- SUBJECT OF INVESTIGATION
	foreach ( (array)$json->pdbx_entity_instance_feature as $c ) {
		if ( $c->comp_id != $chemid ) continue;
		$details[ 'feature_type' ] = _l( $c->feature_type );
	}

	//.. リンク、参照
	$links = [];
	$unp_ids = [];
	foreach ( (array)$json_reid->struct_ref->$ent_id as $c ) {
		$d = trim( $c->db_name );
		$D = strtoupper( $c->db_name ) ;
		$i = trim( $c->pdbx_db_accession ?: $c->db_code );
		if ( $D == 'PDB' ) {
			//- PDB
			if ( strtolower( $i ) == ID ) continue;
			$links[] = _ab([ 'id'=> "pdb-$i" ], IC_YM. "PDB-$i" ); 
		} else if ( $D == 'UNP' ) {
			//- UniProt
			$links[] = _obj('dbid')->pop( 'UniProt', $i, '.' );
			$unp_ids[] = $i;
		} else {
			//- others
			$dbname = [
				'GB' => 'GenBank' ,
				'TREMBL' => 'TrEMBL' ,
			][ $D ] ?: $D;
			$links[] = _obj('dbid')->pop( $dbname, $i );
		}
	}
	if ( $bird_id[ $ent_id ] ) {
		$links[] = _obj('dbid')->pop( 'BIRD', $bird_id[ $ent_id ] );
	}

	//... plus_unp_ids
	foreach ( array_unique( (array)$plus_unp_ids[ $ent_id ] ) as $i ) {
		if ( in_array( $i, $unp_ids ) ) continue;
		$unp_ids[] = $i;
		$links[] = _obj('dbid')->pop( 'UniProt', $i, '.' ) . MLPLUS;
	}
	
	//... EC name
	if ( $ent->pdbx_ec != '' ) {
		foreach ( _uniqfilt( explode( ',', $ent->pdbx_ec ) ) as $i ) {
			$i = trim( $i );
			if ( $i == '' ) continue;
			
			$links[ 'EC' ][] = _obj('dbid')->pop( 'EC', $i );
			++ $cnt;
		}
	}
	$details[ 'references' ] = _imp( array_unique( $links ) );

	//.. antibody
	if ( $c = $sacs_json[ $ent_id ]->cls ) {
		$details[ 'Antibody class' ] = $c. _kakko( _ab( _url( 'sacs' ), 'SACS' ) );
	}

	//.. 詳細タブ
	$tabs = [];
	$details[ 'Comment' ] = _ym_annot_chem( $chemid );
	$tag_name = TAG_NAME;
	foreach ( $details as $k => $v ) {
		foreach ( [ 'entity', 'entity_poly', 'pdbx_entity_nonpoly', 'chem_comp' ] as $c ) {
			if ( $json->$c[0]->$k )
				$tag_name[ $k ] = "$c.$k";
		}
	}

	$details = _quick_kv( $details, $tag_name );
	if ( $details ) {
		$tabs[] = [
			'tab' => 'Details', //.$hoge,
			'div' => ''
				. $chem_imgs
				. $polysc
				. $details
		];
	}
	unset( $details, $chem_imgs, $details );

	//.. 化合物 identifier
	if ( $a = $json_reid->chem_idtf->$chemid ) {
		$table = [];
		foreach ( $a as $child ) {
			$table[] = [
				$child->identifier, 
				$child->type, 
				implode( ' ', [ $child->program, $child->program_version ] ) 
			];
		}
		$tabs[] = [
			'tab' => 'Identifier' ,
			'div' => _table_toph( [ 'Identifier', 'Type', 'Program', ], $table )
		];
	}

	//.. 多糖 descriptor
//	_testinfo( $json_reid->brc_desc->$eid, $eid );
	if ( $a = $json_reid->brc_desc->$ent_id ) {
		$table = [];
		foreach ( $a as $child ) {
			$table[] = [
				$child->descriptor, 
				$child->type, 
				implode( ' ', [ $child->program, $child->program_version ] ) 
			];
		}
		$tabs[] = [
			'tab' => 'Descriptor' ,
			'div' => _table_toph( [ 'Descriptor', 'Type', 'Program', ], $table )
		];
	}

	//.. 化合物リンク
	if ( $is_chem ) {
		$tabs[] = [
			'tab' => _trep('External DB') ,
			'js'  => "_dettab.get_chemlink('$chemid')" ,
			'div' => LOADING. _div( "#chemlink_$chemid", '' )
		];
	}

	//.. その他タブ
	//- 配列
	if ( ! $is_sugar && $is_poly )
		_det_tab( 'seq', 'Sequence' );

	//- 配列領域
	if (
		$unp_ids || $sacs_json[ $ent_id ] || _ezsqlite([
			'dbname' => 'cath' ,
			'where'	 =>	[ 'id', $id. explode( ',', $entp->pdbx_strand_id )[0] ],
			'select' => 'id' ,
		]) ||
		$is_antibody || $like_ig
	){
		_det_tab( 'fet', 'Seq. region', -1 );
	}

	//- DNA/RNA 配列領域
	if ( _instr( 'nucleotide', $entp->type ) ) {
		_det_tab( 'nuc', 'Seq. region', -1 );
	}

	//- func
	if ( $unp_ids )
		_det_tab( 'func', 'Function', -1 );

	//- サイト
	if ( $is_water || $chemid == 'DOD' )
		$n = 0;
	else if ( $is_poly )
		$n = count( (array)$cnt_siteinfo[ $ent_id ] );
	else
		$n = -1;
	_det_tab( 'site', 'Str. site', $n );

	//- misc
	_det_tab( 'mut', 'Mutation', $cnt_mutres[ $ent_id ] ); //- 変異
	_det_tab( 'mod', 'Modified', $cnt_modres[ $ent_id ] ); //- 修飾残基
	//- バリデーション
	_det_tab( 'valid', 'Validation', count( (array)$validate_cats[ $ent_id ] ) );

	//.. 選択ボタン
	$btns = (array)$btns_chain_select[ $ent_id ];
	if ( 20 < count( $btns ) ) {
		$btns = array_slice( $btns, 0, 19 );
		$btns[] = ' ...' ;
	}
	if ( $is_water ) {
		//- 水
		$btns = _btn_popviewer( DID, [
			'btn_label'	=> IC_VIEW. TERM_SHOW_WATER ,
			'jmol'		=> 'display add hoh',
			'molmil'	=> [ 'water' , 1 ],
		]) . _btn_popviewer( DID, [
			'btn_label'	=> IC_VIEW. TERM_HIDE_WATER ,
			'jmol'		=> 'hide hoh',
			'molmil'	=> [ 'water' , 0 ],
		]);
	}
	if ( $is_chem ) { //- 化合物
		$asym_id_list = array_unique( (array)$eid2asymid[ $ent_id ] );
		foreach ( array_slice( $asym_id_list, 0, 20 ) as $asym_id ) {
			$chain_id = $json_reid->asym->$asym_id->cid;
			$seqnum   = $json_reid->asym->$asym_id->seqnum;
			$btns[] = _btn_popviewer( DID, [
				'btn_label'	=> I_SEL. _chain_icon( $chain_id ) .'-'. $seqnum,
				'btn_cls'	=> 'select_btn btn_small' ,
				'jmol'		=> [ 'select', "$seqnum:$chain_id" ] ,
				'molmil'	=> [ 'focus_chain', $asym_id ],
			]);
		}
		if ( 20 < count( $asym_id_list ) )
			$btns[] = '...';
	}
	
	if ( $is_polysac ) { //- 多糖
		$asym_id_list = _uniqfilt( (array)$brc_ent2asym[ $ent_id ] );
		foreach ( array_slice( $asym_id_list, 0, 20 ) as $asym_id ) {
			$btns[] = _btn_popviewer( DID, [
				'btn_label'	=> I_SEL. ( $brc_asym2chain[ $asym_id ]
					? _chain_icon( $brc_asym2chain[ $asym_id ] ). ' - '
					: ''
				). "[$asym_id]" ,
				'btn_cls'	=> 'select_btn btn_small' ,
				'jmol'		=> [] ,
				'molmil'	=> [ 'focus_chain', $asym_id ],
			]);
		}
		if ( 20 < count( $asym_id_list ) )
			$btns[] = '...';
	}
	
	

	//.. 仮データまとめ、集計
	$ent_out[ $categ ][ "#$ent_id: $type_of_poly" ] = ''
		. implode( '<wbr>', (array)$btns )

		. ( 3 < count( (array)$btns ) ? BR : ' ' )

		. _imp2( array_merge(
			[ $is_chem ? _ab([ 'ym', $chemid ], "ChemComp-$chemid" ) : '' ] ,
			$desc ,
			[ _kv([ 'Coordinate model' => $coord_model[ $ent_id ] ]) ]
		))

		. ( $tabs ? _simple_tabs( $tabs ) : '' )

		. ( $is_poly ? _jsonview_links(
			'struct_site',
			'struct_site_gen',
			'plus:struct_site',
			'plus:struct_site_gen'
		): '' )
	;
	unset( $tabs );

	++ $ent_info[ $categ ][ 'num_ent' ];
	$ent_info[ $categ ][ 'num_mol' ] += $ent->pdbx_number_of_molecules;
	$ent_info[ $categ ][ 'icons' ] = array_filter( array_merge(
		(array)$ent_info[ $categ ][ 'icons' ] ,
		(array)$ent_icon[ $ent_id ] ,
		(array)$chem_icon
	))
	;
}

//.. details "詳細"情報
foreach( (array)$json->pdbx_entry_details as $c1 ) {
	foreach( $c1 as $n2 => $c2 ) {
		foreach( preg_split( '/\n +\n/', $c2 ) as $n3 =>$c3 ) {
			$ent_out[ 'Details' ][
				_trep( "$n2|pdbx_entry_details.$n2" )
			][] = _long( _reg_rep( $c3, [
				"/\n/"			=> BR,
				"/[A-Z]{10}/"	=> '$0<wbr>'
			]));
		}
	}
}
unset( $cnt_mutres, $cnt_modres );

//. entity 整理
//.. 孤独なエンティティを抽出
//$singles = array_merge( [], (array)$ent_out[ 'Others' ] );
$singles = (array)$ent_out[ 'Others' ];
$single_categ = [];
foreach ( $ent_out as $categ => $ar ) {
	if ( $categ == 'Others' || $categ == 'Details' ) continue;
	if ( 1 < count( $ar ) ) continue;
	$singles = array_merge( (array)$singles, $ar );
	$single_categ[] = $categ;
}

//- 孤独エンティティが複数あったら、Othersとする
$others_categ = [];
if ( count( $singles ) > 1 ) {
	$e = 0;
	$m = 0;
	$i = [];
	foreach ( $single_categ as $d ) {
		unset( $ent_out[ $d ] );
		$e += $ent_info[ $d ][ 'num_ent' ];
		$m += $ent_info[ $d ][ 'num_mol' ];
		$i = array_merge( $i, $ent_info[ $d ][ 'icons' ] );
		$others_categ[] = _trep( $d );
	}
	ksort( $singles );
	$ent_out[ 'Others' ] = $singles;
	$ent_info[ 'Others' ] = [ 'num_mol' => $m, 'num_ent' => $e, 'icons' => $i ];
}

//.. 書き出し
if ( count( $ent_out ) == 1 && $ent_out[ 'Others' ] != '' )  {
	//- others１種類なら、カテゴリ分けしない
	$o_data->lev1title( 'Components' )
		->lev1ar( $ent_out[ 'Others' ] );

} else if ( ! FLG_MANY_ENT ) {
	//- othersとdetailsだけならカテゴリ分けしない
	$o_data->lev1title( 'Components' )
		->lev1ar( array_merge( $ent_out[ 'Others' ], $ent_out[ 'Details' ] ) );
	
} else {
	//- 分子個数、アイコンなどをカテゴリ名に付加
	$sorted = [];
	foreach ( $ent_out as $categ => $cont ) {
		//- アイコン
		$ei = $ent_info[ $categ ];
		$icons = $ei[ 'icons' ];
		if ( count( (array)$icons ) > 32 ) {
			$icons = array_slice( $icons, 0, 30 );
			$icons[] = '...';
		}

		//- non-polyなどを後回しにするため
		$order = [
			'Antibody'		=> 2,
			'Others'		=> 3,
			'Sugars'		=> 4,
			'Non-polymers'	=> 5,
			'Details'		=> 6
		][ $categ ] ?: 1 ;

		//- Othres用のカテゴリ名
		if ( $categ == 'Others' && $others_categ )
			$categ = _imp2( $others_categ );

		$add = $categ == 'Details' ? ''
			: '|' . _span( '.h_addstr', ''
				. ', ' . $ei[ 'num_ent' ] . _ej( ' types', '種' )
				. ', ' . $ei[ 'num_mol' ] . _ej( ' molecules', '分子' )
				. ' ' . implode( '<wbr>', (array)$icons )
			)
		;
		$sorted[ $order ][ $categ . $add ] = $cont;
	}

	$o_data->lev1title( 'Components', true )->lev1ar( array_merge(
		(array)$sorted[1], //- 
		(array)$sorted[2], //- Antibody
		(array)$sorted[3], //- others
		(array)$sorted[4], //- Sugar
		(array)$sorted[5], //- non-polymers
		(array)$sorted[6], //- Details
	));
}

unset( $sorted, $ent_out, $single_categ, $singles );
$_simple->time( 'entity' );


//. 実験情報
$o_data->lev1title( 'Experimental details', true, false );
//.. $wikipe_tags
$wikipe_tags = [
	'pdbx_method_to_determine_struct' ,
	'detector' ,
	'method' ,
	'mode' ,
	'name' ,
	'electron_source',
	'source',
	'reconstruction_method' ,
	'category' ,
	'formula' ,
];

//.. $met_tags
$met_tags = [
	//- 共通
	'exptl.method'							=> 'm',
	'software.name'							=> 's' ,

	//- EM
	'em_experiment.reconstruction_method'	=> 'm' ,
	'em_imaging.microscope_model'			=> 'e' ,
	'em_imaging.electron_source'			=> 'e' ,
	'em_software.name'						=> 's' ,
	'em_vitrification.instrument'			=> 'e' ,
	'em_image_recording.film_or_detector_model' => 'e' ,
	'em_image_recording.detector_mode'		=> 'm' ,
	'em_imaging_optics.energyfilter_name'	=> 'm' ,
	'em_sample_support.grid_type'			=> 'm' ,
	'em_sample_support.grid_material'		=> 'm' ,
	'em_imaging_optics.phase_plate'			=> 'm' ,

	//- x-ray
	'exptl_crystal_grow.method'				=> 'm' ,
	'diffrn_source.source'					=> 'e' ,
	'diffrn_source.type'					=> 'e' ,
	'diffrn_source.pdbx_synchrotron_site'	=> 'e' ,
	'diffrn_detector.type'					=> 'e' ,
	'diffrn_detector.detector'				=> 'e' ,
	'refine.pdbx_method_to_determine_struct'=> 'm' ,
	'phasing.method'						=> 'm' ,

	//- NMR
	'pdbx_nmr_spectrometer.spectrometer'	=> 'e' ,
	'pdbx_nmr_spectrometer.type'			=> 'e' ,
	'pdbx_nmr_refine.method'				=> 'm' ,
	'pdbx_nmr_software.name'				=> 's' ,
//	'pdbx_nmr_exptl.type'					=> 'm' ,
];

//.. json 修正

//... em_software 空列消去
/*
使っていないプロセス「カテゴリ」の行もできてしまう問題を解決
processingとかimagingとか一種類だけならIDは省略
*/
if ( $json->em_software ) {
	$ids = [];
	foreach ( (array)$json->em_software as $num => $c ) {
		//- IDアイテムの種類を数える
		foreach ( [ 'image_processing_id', 'imaging_id', 'fitting_id' ] as $item ) {
			if ( $c->$item == '' ) continue;
			$ids[ $item ][ $c->$item ] = true;
		}
		//- ソフト名とバージョンがなければ消去
		if ( $c->name . $c->version == '' ) {
			unset( $json->em_software[ $num ] );
		}
	}
	//- 一種類しかないIDのアイテムを消去
	foreach ( (array)$json->em_software as $num => $c ) {
		foreach ( $ids as $item => $c ) {
			if ( count( $c ) != 1 ) continue;
			unset( $json->em_software[ $num ]->$item );
		}
	}
	//- 中身がなくなっていたら、カテゴリそのものを削除
	if ( count( $json->em_software ) == 0 )
		unset( $json->em_software );
}

//... em_entity_assembly: 生物種
foreach ([
	'em_entity_assembly_naturalsource',
	'em_entity_assembly_recombinant',
] as $k ) foreach ( (array)$json->$k as $c ) {
	if ( $c->organism )
		$c->organism = _quick_taxo( $c->organism );
}

//... point_symmetry: single particle対称性
foreach ( (array)$json->em_single_particle_entity as $c ) {
	if ( $c->point_symmetry )
		$c->point_symmetry = _symmetry_text( $c->point_symmetry );
}
	
//... pdbx_nmr_spectrometer
foreach ( (array)$json->pdbx_nmr_spectrometer as $c ) {
	if ( !$c->manufacturer || !$c->model || $c->type) continue;
	$c->type = $c->manufacturer .' '. $c->model;
}

//... val + unit
foreach ([
	'pdbx_exptl_crystal_grow_sol volume volume_units' ,
	'pdbx_exptl_crystal_grow_comp conc conc_units' ,
	'exptl_crystal_grow_comp conc conc_unit' ,
	'pdbx_buffer_components conc conc_units' ,
	'em_entity_assembly_molwt value units' ,
	'em_buffer_component concentration concentration_units' ,
	'em_crystal_formation time time_unit' ,
	'pdbx_nmr_exptl_sample concentration concentration_units' ,
	'pdbx_nmr_exptl_sample_conditions pressure pressure_units',
	'pdbx_nmr_exptl_sample_conditions temperature temperature_units',
	'pdbx_nmr_exptl_sample_conditions ionic_strength ionic_strength_units',
	'pdbx_nmr_exptl_sample_conditions pH pH_units',
] as $s ) {
	list( $cat, $val, $unit ) = explode( ' ', $s );
	foreach ( [$json, $plus] as $j ) {
		foreach ( (array)$j->$cat as $c ) {
			if ( $c->$val == null ) continue;
			$c->$val .= ' '. ([
				'MEGADALTONS' => 'MDa' ,
				'KILODALTONS' => 'kDa' ,
				'KILODALTONS/NANOMETER' => 'kDa/nm' ,
				'microliter'	=> 'µL' ,
				'milliliter'	=> 'mL' ,
				'millimolar'	=> 'mM' ,
				'molar'			=> 'M' ,
				'percent_weight_by_volume' => '%(w/v)',
				'pH'		=> ' ' ,
			][ $c->$unit ] ?: $c->$unit );
			unset( $c->$unit ); 
		}
	}
}

//... formula
foreach([
	'em_buffer_component formula'
] as $s ) {
	list( $cat, $itm ) = explode( ' ', $s );
	foreach ( (array)$json->$cat as $c ) {
		if ( ! $c->$itm ) continue;
		$c->$itm = _reg_rep( $c->$itm, [ '/[0-9]+/' => '<sub>$0</sub>' ] );
	}
}

//... synchrotron
//- typeが単純な site-beamline なら消しとく
foreach  ( (array)$json->diffrn_source as $c ) {
	if ( strtolower( $c->type ) == strtolower( 
		$c->pdbx_synchrotron_site. ' beamline '. $c->pdbx_synchrotron_beamline
	) )
		unset( $c->type );
}

//... NMR exp met
if ( $json->pdbx_nmr_exptl ) {
	$ar = _file( DN_DATA. '/pdb/nmr_exptype.txt' );

	//- 多重に変換しないように、一旦ダミー文字列にする
	$rep = [];
	foreach ( $ar as $num => $s ) {
		$rep[ '/\b'. $s. '\b/i' ] = '{@'. $num. '}';
	}
	foreach ( $ar as $num => $s ) {
		$rep[ '/{@'. $num. '}/' ] = _met_pop( $s, 'm' );
	}

	foreach ( $json->pdbx_nmr_exptl as $s ) {
		$s->type = _reg_rep( $s->type, $rep );
	}
}

//... refine ls [subtable]
$cols = [
	'R_factor',
	'number_reflns' ,
	'percent_reflns' ,
	'ls_R_factor',
	'ls_number_reflns' ,
	'ls_percent_reflns' ,
];
$rows = [
	'_R_free' ,
	'_R_work' ,
	'_all' ,
	'_obs' ,
];

foreach ([ 'refine', 'refine_ls_shell' ] as $cat ) {
	if ( count( (array)$json->$cat ) > 1 ) continue;
	foreach ( (array)$json->$cat as $j ) {
		$data = [];
		foreach ( $cols as $col ) foreach ( $rows as $row ) {
			$item = $col.$row;
			$val = $j->$item;
			if ( $val == null ) continue;
			_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
		}
		$item = 'pdbx_R_Free_selection_details';
		$val = $j->$item;
		if ( $data && $val != null ) {
			$col = 'selection_details';
			$row = '_R_free';
			_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
		}
		_subtable_out( $j, $data );
	}
}

//... aniso [subtable]
if ( count( (array)$json->refine ) == 1 ) {
	$data = [];
	$j = $json->refine[0];
	$cat = 'refine';
	foreach ( $j as $item => $val ) {
		if ( substr( $item, 0, 7 ) != 'aniso_B' ) continue;
		$col = 'aniso_B-' . substr( $item, -1 );
		$row = substr( $item, -2, 1 ). '-';
		_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
	}
	_subtable_out( $j, $data, [], 'anisob' );
}

//... pdbx_phasing_MR [subtable]
if ( count( (array)$json->pdbx_phasing_MR ) == 1 ) {
	$data = [];
	$j = $json->pdbx_phasing_MR[0];
	$cat = 'pdbx_phasing_MR';
	foreach ( $j as $item => $val ) {
		foreach ( [ '_rotation', '_translation' ] as $row ) {
			$col = strtr( $item, [ $row => '' ] );
			if ( $col == $item ) continue;
			$row = trim( $row, '_' );
			_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
		}
	}
	_subtable_out( $j, $data );
}


//... refine_hist [subtable]
$types = [
	'_protein' ,
	'_nucleic_acid' ,
	'_ligand' ,
	'_solvent' ,
	'_total' ,
	'_free' ,
	'_obs' ,
];

foreach ([
	'refine_analyze' ,
	'refine_hist'
] as $cat ) {
	if ( 2 < count( (array)$json->$cat ) ) continue;
	foreach ( (array)$json->$cat as $j ) {
		$data = [];
		foreach ( $j as $item => $val ) {
			foreach ( $types as $type ) {
				if ( ! _instr( $type, $item ) ) continue;
				$col = trim( $type, '_' );
				$row = strtr( $item, [ $type => '', 'pdbx_' => '' ] );
				_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
			}
		}
		_subtable_out( $j, $data,
			[ 'protein', 'nucleic_acid', 'ligand', 'solvent', 'total' ]
		);
	}
}

foreach ( (array)$json->refine_hist as $j ) {
	if ( $j->cycle_id )
		$j->cycle = $j->cycle_id;
}

//... pdbx_refine_tls [subtable]
$cat = 'pdbx_refine_tls';
if ( count( (array)$json->$cat ) == 1 ) {
	$data = [];
	foreach ( $json->pdbx_refine_tls[0] as $item => $val ) {
		$row = substr( $item, 0, 1 );
		if ( ! in_array( $row, [ 'T', 'L', 'S' ] ) ) continue;
		$col = substr( $item, -2 );
		_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
	}
	$json->pdbx_refine_tls[0]->_ = _subtable_out(
		$json->pdbx_refine_tls[0] ,
		$data,
		[ 'T', 'L', 'S', 11, 12, 13, 21, 22, 23, 31, 32, 33 ]
	);
}

//... software table 「その他」情報まとめ
$flg = false;
if ( 1 < count( (array)$json->software ) ) {
	foreach ( (array)$json->software as $c ) {
		if ( count( (array)$c ) < 4 ) continue;
		$flg = true;
		break;
	}
}
if ( $flg ) foreach ( (array)$json->software as $c ) {
	$table = [];
	foreach ( $c as $key => $val ) {
		if ( in_array( $key, [ 'name', 'classification', 'version', 'pdbx_ordinal' ] ) ) continue;
		$table[ _trep( $key, [ 'tag' => "software.$key"] ) ] = $val;
		unset( $c->$key );
	}
	if ( $table )
		$c->NB = _pop( 'Show', _table_2col( $table ), [ 'type' => 'button' ]);
}
unset( $table );

//... high-res ~ low-res
foreach ([
	'reflns'				=> 'd_resolution' ,
	'reflns_shell'			=> 'd_res' ,

	'phasing_MIR'			=> 'd_res' ,
	'phasing_MIR_shell' 	=> 'd_res' ,
	'phasing_MIR_der'		=> 'd_res' ,
	'pdbx_phasing_dm'		=> 'd_res' ,
	'pdbx_phasing_dm_shell' => 'd_res' ,
	'phasing_MAD'			=> 'd_res' ,
	'phasing_MAD_set'		=> 'd_res' ,
	'pdbx_phasing_MAD_shell' => 'd_res' ,
	'pdbx_phasing_MAD_set_shell' => 'd_res' ,

	'refine'				=> 'ls_d_res' ,
	'refine_ls_shell' 		=> 'd_res' ,
	'refine_hist'			=> 'd_res' ,
	'em_diffraction_shell'	=> '#em' ,
	'em_diffraction_stats'	=> '#em' ,
] as $cat => $type ) {
	foreach ( (array)$json->$cat as $j ) {
		if ( $type == '#em' ) {
			$high = 'high_resolution';
			$low  = 'low_resolution';
		} else {
			$high = $type. '_high';
			$low  = $type. '_low';
		}
		if ( $j->$high == null || $j->$low == null ) continue;
		$j->resolution = (real)$j->$high. '&rarr;'. (real)$j->$low. ' &Aring;';
		unset( $j->$high, $j->$low );
	}
}

//... 和訳
foreach ([
	'software.classification' ,
	'em_software.category' ,
	'pdbx_nmr_software.classification'
] as $s ) {
	list( $cat, $item ) = explode( '.', $s );
	foreach ( (array)$json->$cat as $c ) {
		if ( ! $c->$item ) continue;
		$c->$item = _l( $c->$item );
	}
}

//.. 実験情報概要
_pdbml_common([
	'exptl',
	'em_experiment' ,
	'pdbx_nmr_exptl' ,
	'pdbx_nmr_details' ,
	'em_2d_crystal_entity' ,
], [
], [
	'id'
]);
$o_data->end2( 'Experiment' );

//.. サンプル
_pdbml_common([
	//... categs
	'em_assembly',
	'em_entity_assembly',
	'em_entity_assembly_list',
	'em_entity_assembly_molwt' ,
	'em_entity_assembly_naturalsource' ,
	'em_entity_assembly_recombinant' ,
	
	
	'em_virus_entity',
	'em_virus_natural_host' ,
	'em_virus_shell' ,

	'em_2d_crystal_grow',
	'em_crystal_formation' ,

	'em_buffer',
	'em_buffer_components',
	'em_buffer_component' ,
	'em_sample_preparation',

	'em_specimen' ,
	'em_staining' ,
	'em_tomography_specimen' ,
	'em_sample_support',
	'em_support_film' ,
	'em_grid_pretreatment' ,

	'em_embedding' ,
	'em_vitrification',
	'em_high_pressure_freezing' ,
	'em_shadowing' ,
	'em_focused_ion_beam' ,
	'em_ultramicrotomy' ,
	'em_fiducial_markers' ,

	'pdbx_buffer' ,
	'pdbx_buffer_components' ,

	'exptl_crystal',
	'exptl_crystal_grow',
	'exptl_crystal_grow_comp',
	'pdbx_exptl_crystal_grow_comp',
	'pdbx_exptl_crystal_grow_sol' ,
	'pdbx_exptl_crystal_cryo_treatment' ,

	'pdbx_nmr_sample_details' ,
	'pdbx_nmr_exptl_sample' ,
	'pdbx_nmr_exptl_sample_conditions',

], [
	//... order
	'id' ,
	'entity_assembly_id' ,

	'instrument' ,
	'cryogen_name' ,
	'temp',
	'temp_unit' ,
	'humidity',

	'conc' ,
	'concentration' ,
	'value' ,
	'units' ,
	'conc_unit' ,
	'concentration_units' ,

	'name' ,
	'common_name' ,
	'comp_name' ,
	'aggregation_state' ,
	
//	'name' ,
	'formula' ,
	'type' ,
	'component' ,
	'isotopic_labeling' ,
	'concentration' ,
	'concentration_range' ,
	'concentration_units' ,

	'organism',
	'ncbi_tax_id',
	'strain',
	'cell',
	'plasmid',

//	'details' ,
], [
	//... ignore
	'id'
]);
$o_data->end2( 'Sample preparation' );

//.. データ収集
//- 装置画像
$imgs = [];
foreach ( (array)$json->em_imaging as $c ) {
	$imgs[] = _eqimg( $c->microscope_model );
}
if ( $imgs != '' ) {
	$o_data->lev2( 'Experimental equipment', array_unique( $imgs ) );
}
unset( $imgs );

_pdbml_common([
	//... categ / subcateg
	'em_imaging' =>[
		'Microscopy' => [
			'microscope_model' ,
			'date' ,
			'details' ,
			'detector_id' ,
			'scans_id', 
			'specimen_id' ,
			'microscope_id',
			'citation_id' ,
		],
		'Electron gun' => [
			'electron_source' ,
			'accelerating_voltage' ,
			'electron_dose' ,
			'illumination_mode' ,
			'electron_beam_tilt_params',
		],
		'Electron lens' => [
			'mode',
			'nominal_magnification',
			'calibrated_magnification',
			'nominal_defocus_max',
			'nominal_defocus_min',
			'calibrated_defocus_min' ,
			'calibrated_defocus_max' ,
			'nominal_cs',
			'c2_aperture_diameter' ,
			'astigmatism',
			'alignment_procedure' ,
			'energy_filter',
			'energy_window',
			'detector_distance' ,
		],
		'Specimen holder' => [
			'cryogen' ,
			'specimen_holder_model',
			'specimen_holder_type',		
			'temperature',
			'recording_temperature_maximum',
			'recording_temperature_minimum',
			'tilt_angle_max',
			'tilt_angle_min',
			'residual_tilt' ,
			'sample_support_id' ,
		],
	],
	'em_detector',
	'em_image_recording' ,
	'em_imaging_optics' ,
	'em_electron_diffraction',
	'em_electron_diffraction_pattern',
	'em_electron_diffraction_phase',
	'em_image_scans' ,
	'em_diffraction' ,
	'em_diffraction_shell' ,
	'em_diffraction_stats' ,
	'em_tomography' ,

	'diffrn',
	'diffrn_source',
	'diffrn_detector',
	'diffrn_measurement',
	'diffrn_radiation',
	'diffrn_radiation_wavelength',
	'diffrn_refln',
	'diffrn_reflns' ,
	
	'pdbx_diffrn_reflns_shell',
	'pdbx_reflns_twin' ,
	'refln' ,
	'refln_sys_abs' ,
	'reflns' ,
	'reflns_scale' ,
	'reflns_shell' ,
	'cell_measurement' ,
	'pdbx_serial_crystallography_measurement' ,
	'pdbx_serial_crystallography_sample_delivery' ,
	'pdbx_serial_crystallography_sample_delivery_injection' ,
	'pdbx_serial_crystallography_sample_delivery_fixed_target' ,
	'pdbx_serial_crystallography_data_reduction' ,
	
	'pdbx_nmr_spectrometer' ,
	
	'pdbx_soln_scatter' ,
	
], [
	//... order

//- x-ray
	'resolution' ,
	'd_res_high' ,
	'd_res_low' ,
	'd_resolution_high' ,
	'd_resolution_low' ,
	
	'source' ,
	'pdbx_synchrotron_site' ,
	'pdbx_synchrotron_beamline' ,
	'type' ,
	'number_all' ,
	'number_obs' ,
	'percent_possible_obs' ,
	'observed_criterion_sigma_F' ,
	'observed_criterion_sigma_I' ,
	'pdbx_redundancy' ,
	
	
//- NMR
	'manufacturer' ,
	'model' ,
	'field_strength' ,

//- EM
	'sampling_size' ,
	'dimension_width' ,
	'dimension_height',
	'frames_per_image' ,
	'used_frames_per_image' ,
	
	'energyfilter_name' ,
	'energyfilter_upper'  ,
	'energyfilter_lower' ,
	
], [
	//... ignore
	'id', 'ordinal', 'pdbx_ordinal' 
]);

//- セクションタイトル
$n = 'Data collection';
if ( count( $json->exptl ) == 1 ) {
	$m = $json->exptl[0]->method;
	if ( $m ==  'ELECTRON MICROSCOPY' )
		$n = 'Electron microscopy imaging';
	else if ( _instr( 'NMR', $m ) )
		$n = 'NMR measurement';
}

$o_data->end2( $n );

//.. phasing
_pdbml_common([
	'phasing' ,
	'phasing_set' ,
	'phasing_MAD' ,
	'pdbx_phasing_MAD_set' ,
	'phasing_MAD_set' ,
	'pdbx_phasing_MAD_set_shell' ,
	'pdbx_phasing_MAD_set_site' ,
	'pdbx_phasing_MAD_shell' ,
	'phasing_MAD_clust' ,
	'phasing_MAD_expt' ,
	'pdbx_phasing_MR' ,
	'pdbx_phasing_dm' ,
	'pdbx_phasing_dm_shell' ,
	'phasing_MIR' ,
	'phasing_MIR_der' ,
	'phasing_MIR_der_shell' ,
	'phasing_MIR_der_site' ,
	'phasing_MIR_shell' ,
],[
	'id' ,
	'resolution' ,
],[
]);
$o_data->end2( 'Phasing' );

//.. 解析
_pdbml_common([
	//... categ
	'software' ,
	'em_software' ,
	'computing' ,

	'em_image_processing' ,
	'em_2d_projection_selection',
	'em_2d_crystal_entity' ,
	'em_3d_crystal_entity' ,
	'em_ctf_correction' ,
	'em_helical_entity',
	'em_particle_selection' ,
	'em_icos_virus_shells',
	'em_single_particle_entity',
	'em_3d_reconstruction',
	'em_start_model' ,
	'em_euler_angle_assignment' ,
	'em_euler_angle_distribution',
	'em_final_classification' ,
	'em_volume_selection' ,
	'em_3d_fitting',
	'em_3d_fitting_list',

	'refine' =>[
		'Solvent computation' => [
			'pdbx_solvent_ion_probe_radii',
			'pdbx_solvent_shrinkage_radii',
			'pdbx_solvent_vdw_probe_radii',
			'solvent_model_details',
			'solvent_model_param_bsol',
			'solvent_model_param_ksol',
		],
		'Displacement parameters' => [
			'B_iso_max'		,//=> 'B value, max',
			'B_iso_mean' 	,//=> 'mean',
			'B_iso_min'		,//=> 'min',
			'#notag_anisob' ,//=> aniso_B table
			'aniso_B11'		,//=> 'anisotropic, [1][1]' ,
			'aniso_B12'		,//=> '[1][2]',
			'aniso_B13'		,//=> '[1][3]',
			'aniso_B22'		,//=> '[2][2]',
			'aniso_B23'		,//=> '[2][3]',
			'aniso_B33'		,//=> '[3][3]',
		] ,
	],

	'refine_B_iso' ,
	'refine_analyze' ,
	'refine_funct_minimized' ,
	'refine_hist' ,
	'refine_ls_restr' ,
	'refine_ls_restr_ncs' ,
	'refine_ls_shell' ,
	'pdbx_refine_tls' ,
	'pdbx_refine_tls_group' ,
	'pdbx_xplor_file' ,

	'pdbx_nmr_computing' ,
	'pdbx_nmr_spectral_peak_list' ,
	'pdbx_nmr_spectral_peak_software' ,

	'pdbx_nmr_software' ,
	'pdbx_nmr_software_task' ,
	'pdbx_nmr_refine' ,
	'pdbx_nmr_force_constants' ,
	'pdbx_nmr_assigned_chem_shift_list' ,
	'pdbx_nmr_chem_shift_experiment' ,
	'pdbx_nmr_chem_shift_ref' ,
	'pdbx_nmr_chem_shift_reference' ,
	'pdbx_nmr_chem_shift_software' ,
	'pdbx_nmr_systematic_chem_shift_offset' ,
	'pdbx_nmr_constraints' ,
	'pdbx_nmr_constraint_file' ,
	'pdbx_nmr_representative' ,
	'pdbx_nmr_ensemble' ,
	'pdbx_nmr_ensemble_rms' ,
	'pdbx_nmr_upload' ,

	'pdbx_soln_scatter_model' ,
], [
	//... order
	//- IDぽいやつ
	'pdbx_ens_id' ,
	'dom_id' ,

	//- x-ray
	'pdbx_method_to_determine_struct' ,
	'pdbx_starting_model' ,
	'cycle' ,

	//- software
	'id' ,
	'name' ,
	'version' ,
	'authors' ,
	'classification' ,
	
	//- em
	'method' ,
	'software' ,
	'software_name' ,
	'resolution' ,
	'resolution_method' ,
	'num_particles',
	'nominal_pixel_size' ,
	'actual_pixel_size' ,
	'magnification_calibration' ,
	
	'pdb_entry_id' ,
	'pdb_chain_id' ,
	
	//- refine
	'resolution' ,
	'd_res_high',
	'd_res_low' ,
	'R_factor_R_free',
	'number_reflns_R_free' ,
	'percent_reflns_R_free' ,
	'R_factor_R_work' ,
	'number_reflns_R_work' ,
], [

	//... ignore
//	'id',
	'ordinal',
	'pdbx_ordinal' ,

]);
$o_data->end2( 'Processing' );


//. function : _pdbml_common
//- ラッパー関数
function _pdbml_common( $cats, $order = [], $ign = [] ) {
	global $plus;
	_pdbml_common_main( false, $cats, $order, $ign );
	if ( ! $plus ) return;
	_pdbml_common_main( true, $cats, $order, $ign );
}

//- 実行関数
function _pdbml_common_main( $plus_flg, $cats, $order = [], $ign = [] ) {
	global $json, $plus, $o_data, $trans, $_pdb_categ;
	$cjson = $plus_flg ? $plus : $json;
	$plus_mark = $plus_flg ? _div( '.right', MLPLUS ) : '';
	$plusinfo = [];

	//.. カテゴリごとのループ
	foreach ( $cats as $cat => $seprow_info ) {
		if ( is_numeric( $cat ) )
			$cat = $seprow_info; //- カテゴリ分割無し
		$_pdb_categ = $cat;
		$cnt = count( (array)$cjson->$cat );

		if ( $plus_flg && $cnt > 0 ) {
			$plusinfo[] = $cat;
		}

		//.. テーブルモード
		if ( $cnt > 1 ) {
			$colvals = [];
			$item_keys = [];
			$univals = [];
			//- カラムの順番
			$cols = [];
			foreach ( $order as $o )
				$cols[ $o ] = false;

			//... アイテムの種類を抽出
			foreach ( $cjson->$cat as $num => $child ) {
				++ $item_keys[ $num ];
				foreach ( $child as $k => $v ) {
					if ( in_array( $k, [ 'ordinal', 'pdbx_ordinal' ]))
						continue;
					//- なぜだか$vに配列が来ることがある、
					//- pdbx_phasing_dm_shell.reflns
					//- pdb.3n1b.pdbx_phasing_dm_shell.reflns
					if ( is_array( $v ) || is_object( $v ) ) 
						$v = _imp( (array)$v );

					++ $colvals[ $k ][ $v == '' ? '_' : $v ];
					if ( $v == '' ) continue;
					$cols[ $k ] = true;
				}
			}
			$num_row = count( $item_keys );
			
			//... 一種類しか値がないカラムは、別で書く
			if ( 7 < count( $colvals ) ) {
				foreach ( $colvals as $k => $a ) {
					if ( count( $a ) != 1 ) continue; //- １種類以上ある
					if ( array_sum( $a ) != $num_row ) continue; //- １種類以上ある
					
					$cols[ $k ] = false;
					$univals[ _icon_title( "$k||$cat.$k" ) ]
						= _valprep( implode( '', array_keys( $a ) ), $k, $cat );
				}
			}

			//... main
			$tbl = TR_TOP;
			$cols = array_keys( array_filter( $cols ) );
			$num_col = count( $cols );
			$num_row = count( $cjson->$cat );
			$hide  = $num_col > 15 || $num_row > 15 ;
			$small = $num_col > 5  || $num_row > 10 ? '.small' : '' ;
			foreach ( $cols as $c ) {
				$u = _kakko( _get_unit( $c, $cat ) );
				$tbl .= TH . _icon_title( "$c|$u|$cat.$c" );
			}
			foreach ( $cjson->$cat as $child ) {
				$tbl .= TR;
				foreach ( $cols as $c ) {
					$s = $child->$c;
					if ( is_array( $s ) || is_object( $s ) ) $s = _imp( $s );

					//- 解像度カラム
					$s = strtr( $s, [ '&rarr;' => '-', ' &Aring;' => '' ] );
//					$s = strlen( $s ) < 15 ? $s : preg_replace( '/\b/', '<wbr>', $s );
					if ( ! is_numeric( $s ) && ! _instr( '-', $s ) )
						$s = _breakable( $s );
					$tbl .= TD. (
						is_numeric( $s ) ? (real)$s : _valprep( $s, $c, $cat )
					);
				}
			}
			
			//- テーブル整形
			$tbl = _t( "table|$small", $tbl );
			$o_data->lev2( "$cat||$cat", ''
				. $plus_mark
				. ( $univals != [] ? _p( _kv( $univals ) ) : '' )
				. ( $hide ? _more( $tbl, [
					'btn'  => TERM_SHOW_LARGE_TABLE. " ($num_col x $num_row)" ,
					'btn2' => TERM_HIDE_LARGE_TABLE
				]) : $tbl )
			);
			continue;
		}

		//.. キー・バリューモード
		$multi = $cnt > 1;
		foreach ( (array)$cjson->$cat as $num => $child ) {
			unset( $child->id ); //- 単独カテゴリはIDアイテム消去
			$data2 = [];
			$data2tag = [];
			$to_subcat_key = [];
			$idtag = '';

			//... 分離データ用の情報を整理
			if ( is_array( $seprow_info ) ) {
				foreach ( $seprow_info as $subcat => $c ) {
					//- IDに利用するタグ
					if ( $subcat == 'id' ) {
						$idtag = $c;
						continue;
					}
					foreach ( $c as $key => $newkey ) {
						if ( is_numeric( $key ) )
							$key = $newkey;
						$data2[ $subcat ][ $newkey ] = ''; //- order
						$to_subcat_key[ $key ] = [
							'subcat' => $subcat ,
							'newkey' => $newkey ,
						];
					}
				}
			}

			//... メイン
			$o_data->lev3order( $order )->lev3ign( $ign );
			foreach ( (array)$child as $k=>$v ) {
				if ( ! _instr( 'pdb_', $k ) && substr( $k, -3 ) == '_id' ) continue;
				$t = "$cat.$k";
				if ( $to_subcat_key[ $k ] == [] ) {
					$o_data->lev3( $k, $v, $t );
				} else {
					$a = $to_subcat_key[ $k ];
					$data2[ $a[ 'subcat' ] ][ $a[ 'newkey' ] ] = $v;
					$data2tag[ $a[ 'subcat'] ][ $a[ 'newkey' ] ] = $t;
				}
			}
			$i = '';
			if ( $idtag != '' )
				$i = '#' . $child->$idtag;
			else if ( $multi )
				$i = '#' . ( $num + 1 );

			if ( $plus_flg )
				$o_data->lev3( '#div_r', MLPLUS );

			$o_data->end3( "$cat|$i|$cat" );

			//... 分離データ
			foreach ( $data2 as $k => $v ) {
				foreach ( $v as $k2 => $v2 )
					$o_data->lev3( $k2, $v2, $data2tag[ $k ][ $k2 ] );
				if ( $plus_flg )
					$o_data->lev3( '#div_r', MLPLUS );

				$o_data->end3( _cifdic_link( _trep( $k ). ( $i ? " $i" : '' ), $cat ) );
			}
		}
	}
}

//. sin/cos
function _cos( $deg ) {
	return cos( deg2rad( $deg ) );
}
function _sin( $deg ) {
	return sin( deg2rad( $deg ) );
}

$_simple->time( 'exp.det' );
