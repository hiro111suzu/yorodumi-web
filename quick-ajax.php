<?php
//PDB
ini_set( 'memory_limit', '4056M' );

require( __DIR__. '/common-web.php' );
require( __DIR__. '/common-pdbdet.php' );

if ( TEST ) {
	ini_set( 'display_errors', 1 );
	ini_set( 'error_reporting', E_ALL & ~E_NOTICE );
}

$o_contents = new cls_contents();

//. def
_add_lang( 'quick-ajax' );
_add_fn( 'quick-pdb' ); //- PDB共通で
_add_url( 'quick-ajax' );

_define_term(<<<EOD
TERM_SELECT_BTN
	Click this button to select this element in structure viewer
	クリックすると構造ビューア内でこの要素が選択されます

TERM_SEQSEL
	*Select sequence letters by mouse to focus corresponding region in structure viewer
	*配列文字列をマウスで選択すると、構造ビューア中で該当する部位がフォーカスされます
TERM_NO_DATA
	No corresponding data for this entity
	この要素には該当するデータがありません
TERM_DISP_CHAIN
	Display chain
	表示する鎖
TERM_OUT_OF_FRAGMENT
	Residues out of this fragment
	このフラグメントに含まれない残基
TERM_SEL_NEIGHBOR
	Select neighbor residues
	付近の残基を選択
TERM_FONTSIZE
	Font size
	文字の大きさ
TERM_SELECT_VW
	Select in structure viewer
	構造ビューアーで選択

EOD
);
define( 'TERM_RES', _ej( ' res.', '残基' ) );
define( 'I_SEL', _ic( 'select' ) );
define( 'I_BIND', _ic( 'binding' ) );

//. init
$main_id = new cls_entid( 'get' );
define( 'DID'	, $main_id->did );
define( 'ID'	, $main_id->id );
$json = $main_id->mainjson();
unset( $main_id );

$json_reid = new stdClass;
foreach ([
	'chem_comp'				=> ['chem'	, 'id'] ,
	'struct_asym'			=> ['asym'	, 'id'] ,
	'entity'				=> ['ent'	, 'id'],
	'entity_poly'			=> ['entp'	, 'entity_id'] ,
	'pdbx_entity_nonpoly'	=> ['entp'	, 'entity_id'] ,
] as $tag => $a ) {
	list( $new_name, $new_idtag ) = $a;
	foreach ( (array)$json->$tag as $child ) {
		if ( count( (array)$child ) == 0 ) continue;

		if ( ! is_object( $json_reid->$new_name ) )
			$json_reid->$new_name = new stdClass;

		$new_id = $child->$new_idtag;
		if ( $new_id == '' ) continue;
		if ( is_array( $new_id ) ) continue;
		unset( $child->$new_idtag );

		$json_reid->$new_name->$new_id = $child;
	}
}
$polysac_css_id = 0;

//.. id prep
define( 'ASYM_ID_2_CHIAN_ID' , (array)$json->_yorodumi->id_asym2chain );
define( 'CHAIN_ID_2_ASYM_ID' , (array)$json->_yorodumi->id_chain2asym );

//- object -> array
define( 'SEQ_AUTH2LABEL'     , json_decode( json_encode(
	$json->_yorodumi->seq_auth2label
), true ) );
define( 'SEQ_LABEL2AUTH'     , json_decode( json_encode(
	$json->_yorodumi->seq_label2auth
), true ) );

//- entity
foreach ( (array)$json_reid->asym as $a => $c ) {
	$e2a[ $c->entity_id ][] = $a;
	$a2e[ $a ] = $c->entity_id;
}
define( 'ENT_ID_2_ASYM_ID'   , $e2a );
define( 'ASYM_ID_2_ENT_ID'   , $a2e );
unset( $e2a, $a2e );

//.. getpost
define( 'MODE',    _getpost( 'ajax' ) );
define( 'ENT_ID',  _getpost( 'eid' ) );
define( 'ASYM_ID', _getpost( 'aid' ) ?: ENT_ID_2_ASYM_ID[ ENT_ID ][0] );
define( 'CSSID_PREFIX', MODE. ENT_ID );

//. seq
if ( MODE == 'seq' ) {
	$o_contents->show_chain_sel = true;

	//.. 2nd str etc.
	$num_res = [];
	$sec_beg = [];
	$sec_end = [];

	//... alpha-helix
	foreach ( (array)$json->struct_conf as $c ) {
		if ( ASYM_ID != $c->beg_label_asym_id ) continue;
		$t = [
			'TURN' => 'turn' ,
			'STRN' => 'beta' ,
		][ substr( $c->conf_type_id, 0, 4 ) ] ?: 'helix';
		$sec_beg[ $c->beg_label_seq_id ] = "<span class=\"$t\">";
		$sec_end[ $c->end_label_seq_id ] = '</span>';
		$num_res[ $t ] += $c->end_label_seq_id - $c->beg_label_seq_id + 1; 
	}

	//... beta-strand
	foreach ( (array)$json->struct_sheet_range as $c ) {
		if ( ASYM_ID != $c->beg_label_asym_id ) continue;
		$sec_beg[ $c->beg_label_seq_id ] .= '<span class="beta">';
		$sec_end[ $c->end_label_seq_id ] .= '</span>';
		$num_res['beta'][ $c->end_label_seq_id ]
			= $c->end_label_seq_id - $c->beg_label_seq_id + 1; 
	}
	//- 重複があるエントリがあるので、細工
	$num_res['beta'] = array_sum( (array)$num_res['beta'] );

	//... base pair
	$pair_type = [];
	$basepair = [];
	foreach ( (array)$json->ndb_struct_na_base_pair as $c ) {
		if ( ASYM_ID == $c->i_label_asym_id ) {
			$basepair[ $c->i_label_seq_id ] = true;
			$b = _basepari( $c );
			if ( $b ) {
				$pair_type[ $b ][ $c->i_label_seq_id ] = true;
			}
		}
		if ( ASYM_ID == $c->j_label_asym_id ) {
			$basepair[ $c->j_label_seq_id ] = true;
			$b = _basepari( $c );
			if ( $b ) {
				$pair_type[ $b ][ $c->j_label_seq_id ] = true;
			}
		}
	}
	if ( 0 < $c = count( $basepair ) ) 
		$num_res['bp'] = $c;
	if ( 0 < $c = count( (array)$pair_type['mismatch'] ) ) 
		$num_res['mism'] = $c;
	if ( 0 < $c = count( (array)$pair_type['dna_rna'] ) ) 
		$num_res['drna'] = $c;

	//... unobs
	$unobs = [];
	foreach ( (array)$json->pdbx_unobs_or_zero_occ_residues as $c ) {
		if ( ASYM_ID != _asym_id( $c ) ) continue;
		$unobs[ _seq_id( $c ) ] = true;
	}
	if ( 0 < $c = count( $unobs ) )
		$num_res['unob'] = $c;

	//... modified
	$modres =[];
	foreach ( (array)$json->pdbx_struct_mod_residue as $c ) {
		if ( ASYM_ID != _asym_id( $c ) ) continue;
		$modres[ _seq_id( $c ) ] =
			implode( ': ', (array)[ $c->label_comp_id, $c->details ] ) ?:
			'modified residues'
		;
		++ $num_res['modres'];
	}

	//... seq
	$seq = preg_replace( "/[\t\r\n ]/", '', 
		$json_reid->entp->{ENT_ID}->pdbx_seq_one_letter_code_can
	);
	if ( ! $seq ) $o_contents->err( 'no sequence data' );
	$num_res_total = strlen( $seq );

	//.. main loop
	$colseq = '';
	$flg_str = false; //- 2次構造とってるかフラグ
	foreach ( str_split( $seq ) as $n=>$s ) {
		$n1 = $n + 1;
		$beg = $sec_beg[ $n1 ];
		$end = $sec_end[ $n1 ];
		 //- unobserbed
		if ( $unobs[ $n1 ] )
			$s = _span( '.unob', $s );

		//- modified res.
		$m = $modres[ $n1 ];
		if ( $m != '' )
			$s = _span( ".modres|?$m", strtolower( $s ) );

		if ( $pair_type[ 'mismatch' ][ $n1 ] ) //- mismatch
			$s = _span( '.mism', $s );

		if ( $pair_type[ 'dna_rna' ][ $n1 ] ) //- mismatch
			$s = _span( '.drna', $s );

		if ( $basepair[ $n1 ] ) //- base-pair
			$s = _span( '.bp', $s );

		if ( $flg_str ) {
			if ( $beg != '' )
				$s = "</span>". $beg. $s;
			else if ( $end != '' ) {
				$s .= $end;
				$flg_str = false;
			}
		} else {
			if ( $beg != '' ) {
				$s = $beg. $s;
				$flg_str = true;
			}
		}
		if ( $n % 10 == 9 ) $s .= ' ';
		$colseq .= $s;
	}
	if ( $flg_str ) $s .= '</span>'; //- 最後に閉じて無ければ、閉じる

	//.. table
	$a = ASYM_ID;
	$n = [
		'helix'	=> '&alpha;' ,
		'beta'	=> '&beta;' ,
		'turn'	=> 'turn' ,
		'bp'	=> 'base pair' ,
		'unob'	=> 'unobserved' ,
		'modres' => 'modified' ,
		'mism'	=> 'non W/C pair' ,
		'drna'	=> 'DNA-RNA pair' ,
	];
	$table = [];
	foreach ( $num_res as $type => $num ) {
		$ratio = round( $num / $num_res_total * 100, 1 );
		$table[
			_l( $n[ $type ] ). ':'
			. _span( ".seq seq_$a $type", $type == 'modres' ? 'abc' : 'ABC' )
		] = [
			_div( ".sbar| ?$ratio %", _div( ".sbari $type| st:width:$ratio%" ) )
			,
			_span( '.right', $num )
		];
	}
	$table[ _ej( 'all', '全体' ) ] = [ '', _span( '.right', $num_res_total ) ];

	//.. end

	$fs = $num_res_total < 50 ? 1.5
		:( $num_res_total < 100 ? 1.4
		:( $num_res_total < 200 ? 1.3
		:( $num_res_total < 400 ? 1.2
		:( $num_res_total < 800 ? 1
		:( $num_res_total < 2000 ? 0.9
		:( $num_res_total < 3000 ? 0.8 :0.7
	))))));
	$o_contents->add(
		'_' ,
		_div( '#seqbox' .ENT_ID, ''
			. _div(
				".seq seq_$a| data-aid:$a| st: font-size: {$fs}rem| onmouseup:_qvw.seqsel(this)",
				$colseq 
			)
			//- 表
			. _table_2col( $table, [ 'opt' => '.seq_sample', 'multi_col'  => true] )

			//- 説明
			. _e( "input| type:range| value:$fs| min:0.5| max:2| step:0.1|"
				. "oninput:$('.seq_$a').css('font-size', this.value+'rem')" )
			. TERM_FONTSIZE
			. BR
			. _span( '.red', TERM_SEQSEL )
			. _div( '.clboth', '' )
		)
	)->out();
}

//.. func: _mismatch
function _basepari( &$c ) {
	if ([
		'DA' => 'DT' ,
		'DT' => 'DA' ,
		'DG' => 'DC' ,
		'DC' => 'DG' ,
		'A'  => 'U' ,
		'U'  => 'A' ,
		'G'  => 'C' ,
		'C'  => 'G' ,
	][ $c->i_label_comp_id ] == $c->j_label_comp_id )
		return;
	if ([
		'DA' => 'U' ,
		'DT' => 'A' ,
		'DG' => 'C' ,
		'DC' => 'G' ,
		'A'  => 'DT' ,
		'U'  => 'DA' ,
		'G'  => 'DC' ,
		'C'  => 'DG' ,
	][ $c->i_label_comp_id ] == $c->j_label_comp_id )
		return 'dna_rna';
	else
		return 'mismatch';
}

//. uniprot biol-function
if ( MODE == 'func' ) {
	$plus = _json_load2([ 'pdb_plus', ID ]);
	$o_contents->show_chain_sel = false;

	//.. init
	_def_trep( 'qajax-unp' );

	$o_contents->order([ 'func', 'sim', 'compo'	]);
	$unp_jsons = _load_unp_json();

	//.. plus
	//... prep
	$plus_source = [];
	$dbrefs = [];
	$refid_is_issue = [];
	foreach ( (array)$json->struct_ref as $c ) {
		if ( $c->entity_id == ENT_ID )
			$refid_is_issue[ $c->id ] = true;
	}

	//... plus struct_ref_src
	foreach ( (array)$plus->struct_ref_src as $c ) {
		if ( ! $c->ref_id || ! $refid_is_issue[ $c->ref_id ] ) continue;
		foreach ( (array)$c as $k => $v ) {
			if ( $k == 'ref_id' || $k == 'id' ) continue;
			foreach ( (array)explode( ', and', $v ) as $s ) {
				$plus_source[ $k ][] = _evidence_code( $s );
			}
		}
	}

	//... plus struct_ref (Pfam, cellular_location)
	foreach ( (array)$plus->struct_ref as $c ) {
		//- source - cellular_location.
		if ( is_numeric( $c->id ) ) {
			foreach ( $c as $k => $v ) {
				if ( ! $c->id || ! $refid_is_issue[ $c->id ] ) continue;
				if ( $k == 'cellular_location' )
					$plus_source[ $k ][] = _evidence_code( $v );
			}
			
		//- Pfam
		} else if ( _instr( 'PFAM_', $c->id ) ) {
			if ( $c->entity_id != ENT_ID ) continue;
			$dbrefs['Pfam'][] = _obj('dbid')->pop( 'Pfam', $c->pdbx_db_accession );
		}
	}
	foreach ( $plus_source as $type => $vals ) {
		$o_contents->add(
			'compo',
			_kv1( _trep2( $type ), '', _uniqfilt( $vals ) ) 
		);
	}

	//.. main
	//... uniprot comment
	foreach ( (array)$unp_jsons as $u_j ) foreach ( (array)$u_j['cmnt'] as $type => $c ) {
		foreach ( $c as $n => $text ) {
			$o_contents->add(
				_categ( $type ) ,
				_kv1( 'UniProt', $type, _unpstr_with_evd( $text, $u_j ) )
			);
		}
	}

	//... uniprot location
	$o = [];
	foreach ( (array)$unp_jsons as $u_j ) foreach ( (array)$u_j['loc'] as $l ) {
		$o[] = _unpstr_with_evd( $l, $u_j );
	}
	if ( $o ) {
		$o_contents->add(
			'compo',
			_kv1( 'UniProt', 'cellular_location', _uniqfilt( $o ) )
		);
	}

	//... ec
	//- PDB info
	$ec = explode( ',', $json_reid->ent->{ENT_ID}->pdbx_ec );

	//- unp info
	foreach ( (array)$unp_jsons as $j ) {
		if ( ! $j['ec'] ) continue;
		$ec = array_merge( $ec, $j['ec'] );
	}
	$ec = _uniqfilt( $ec );

	if ( $ec ) {
		$o = [];
		foreach ( $ec as $i ) {
			$o[] = _obj('dbid')->pop( 'ec', $i );
		}
		$o_contents->add( 'func', _kv1( 'EC', '', $o  ) );
		unset( $e2n );
	}

	//... GO
	$go_info = [];

	//- uniprot json
	foreach ( (array)$unp_jsons as $j ) foreach ( (array)$j['go'] as $n => $c ) {
		$n = [
			'F' => 'molecular_function' ,
			'P' => 'biological_process' ,
			'C' => 'cellular_component' ,
		][ $n ];
		$go_info[ $n ] = array_merge( (array)$go_info[ $n ], $c );
	}

	//- plus
	foreach ( (array)$plus->gene_ontology as $c ) {
		if ( ! _chainid_is_issue( $c->auth_asym_id ) ) continue;
		$go_info[ $c->namespace ][] = [ $c->goid, $c->name ];
	}

	//- マージ
	foreach ( $go_info as $namespace => $c ) {
		$o = [];
		foreach ( _uniqfilt( $c ) as $a ) {
			list( $goid, $name ) = $a;
			$o[] = _obj('dbid')->pop( 'GO', $goid, $name );
		}
		$o_contents->add(
			_categ( $namespace ),
			_kv1( 'GO', $namespace , $o )
		);
	}
	unset( $go_info );

	//... interpro
	foreach ( (array)$unp_jsons as $j ) foreach ( (array)$j['intp'] as $cat => $c ) {
		foreach ( $c as $array ) {
			$dbrefs[ "InterPro|$cat" ][] =
				_obj('dbid')->pop( 'InterPro', $array[0], $array[1] );
		}
	}

	//... その他の unp_json ref
	foreach ( (array)$unp_jsons as $j ) foreach ( (array)$j['dbref'] as $name => $c ) {
		foreach ( $c as $array ) {
			$dbrefs[ $name ][] =
				_obj('dbid')->pop( $name, $array[0], $array[1] );
		}
	}

	//... dbref まとめ
	foreach ( $dbrefs as $dbname => $array ) {
		if ( ! $array ) continue;
		$array = array_unique( $array );
		list( $dbname, $cat ) = explode( '|', $dbname );
		$o_contents->add(
			_categ( $dbname ) ,
			_kv1( $dbname, $cat, $array )
		);
	}

	//.. 出力
	//- 情報源 / test info
	foreach ( array_keys( $unp_jsons ) as $i ) {
		$o_contents
			->test_link( 'unp_xml' , $i )
			->test_link( 'unp_json', $i )
			->src( _obj('dbid')->pop( 'UniProt', $i, '.' ) )
		;
	}
	$o_contents
		->src( $plus ? MLPLUS : '' )
		->src( _doc_pop('func_homology') )
		->out()
	;
} 

//.. function
//... _categ
function _categ( $type ) {
	return [
		'function'           => 'func',
		'molecular_function' => 'func' ,
		'biological_process' => 'func' ,
		'catalytic activity' => 'func' ,
		'enzyme regulation'  => 'func' ,
		'allergen'           => 'func' ,
		'disease'            => 'func' ,
		'pharmaceutical'     => 'func' ,
		'pathway'            => 'func' ,

		'similarity'         => 'sim' ,
		'domain'             => 'sim' ,

		//- ref
		'InterPro'           => 'sim' ,
		'Pfam'               => 'sim' ,
		'PRINTS'             => 'sim' ,
		'SUPFAM'             => 'sim' ,
		'PROSITE'            => 'sim' ,
		'HAMAP'              => 'sim' ,
		'SMART'              => 'sim' ,
		'Reactome'           => 'func' ,
	][ $type ] ?: 'compo' ;
}

//... _unpstr_with_evd
function _unpstr_with_evd( $val, $json ) {
	if ( ! defined( 'PUBMED_REP' ) )
		define( 'PUBMED_REP', [ '/PubMed:([0-9]{7,9})/' => _dblink( 'PubMed', '$1' ) ]);

	if ( ! is_array( $val ) )
		return _reg_rep( $val, PUBMED_REP );
	
	//- 配列だったらevidence-code付きなので、処理
	list( $text, $evd_ids, $refs ) = $val;
	$evd_set = [];
	foreach ( (array)$evd_ids as $i ) {
		if ( ! $json['evd'][$i] ) continue;
		$evd_set[] = $json['evd'][$i];
	}
	$ref = '';
	foreach ( (array)$refs as $c ) {
		$c = explode( ':', $c );
		$ref .= _dblink( $c[0], $c[1] );
	}

	return _reg_rep( $text, PUBMED_REP )
		. ( strlen( $text ) < 30 ? _obj('wikipe')->pop_xx( $text ) : '')
		. _kakko( $ref )
		. ( $evd_set
			? _pop(
				TERM_EVIDENCE,
				_evidence_code_unp( _uniqfilt( $evd_set ) ) 
			)
			: ''
		)
	;
}

//. feature 配列領域
if ( MODE == 'fet' ) {
	$plus = _json_load2([ 'pdb_plus', ID ]);
	_def_trep( 'qajax-unp' );
	$o_sblock = new cls_seqblock();
	define( 'CHAIN_ID_LIST', explode( ',', $json_reid->entp->{ENT_ID}->pdbx_strand_id ) );

	//.. cath
	$o_contents->order([ 'chain' ]);
	$cath_exists = false;
	$db_cath = [
		'dbname' => 'cath' ,
		'key'	 =>	'id' ,
		'select' => 'data' ,
	];
	foreach ( CHAIN_ID_LIST as $chain_id ) {
		$asym_id = CHAIN_ID_2_ASYM_ID[ $chain_id ];
		foreach ( (array)json_decode( _ezsqlite( $db_cath, ID. $chain_id ) ) as $j ) {

			//... name
			list( $c1, $c2, $c3, $c4 ) = explode( '.', $j->cid );
			$name = _ezsqlite( $db_cath, $j->cid )
				?:  _ezsqlite( $db_cath, "$c1.$c2.$c3" )
				?: $j->cid
			;
			list( $name, $wikipe ) = _name_prep( $name );

			$table = [
				'CATH-ID' => _ab( _url( 'cath_sf', $j->cid ), $j->cid ) ,
				'Version/type' => $j->ver,
			];
			foreach ([ 
				'Class'						=> $c1 ,
				'Architecture'				=> "$c1.$c2" ,
				'Topology'					=> "$c1.$c2.$c3" ,
				'Homologous superfamily'	=> "$c1.$c2.$c3.$c4" ,
			] as $key => $n ) {
				$table[ $key ] =_obj('dbid')->pop( 'CATH', $n );
			}
			$table[ 'Wikipedia' ] = $wikipe;

			//... output
			foreach ( $j->seq as $s ) {
				//- マイナスとハイフンを見分ける
				list( $start, $end ) = preg_split( '/\b\-/', $s );
				$o_sblock->add([
					SEQ_AUTH2LABEL[ $asym_id ][ $start ] ,
					SEQ_AUTH2LABEL[ $asym_id ][ $end ] ,
				]);
			}
			$o_sblock->list_item([
				'categ' 	=> 'CATH domain' ,
				'asym_id'	=> $asym_id ,
				'text'		=> $name ,
				'pop'		=> _table_2col( $table ) ,
			]);
			$cath_exists = true;
		}
	}
	if ( $cath_exists )
		$o_contents->src( _ab( 'http://www.cathdb.info/', IC_L. 'CATH' ) );

	//.. antibody sacs
	$sacs_info = json_decode( _ezsqlite([
		 'dbname' => 'abinfo' ,
		 'where'  => [ 'id', ID. '-'. ENT_ID ] ,
		 'select' => 'sacs' ,
	]));
	if ( $sacs_info ) {
		$sacs_link = _ab( 'http://www.bioinf.org.uk/abs/sacs/', 'SACS' );
		$o_contents->src( IC_L. $sacs_link );
		foreach ( CHAIN_ID_LIST as $chain_id ) {
			$names = $pop = [];
			$pop = $cls_init;
			foreach ( (array)$sacs_info->cdr as $name => $c ) {
				$o_sblock->add( $c );
				$names[] = $name;
				$pop[ $name ] = $c[2];
			}
			$o_sblock->list_item([
				'categ' 	=> 'Antibody CDR' ,
				'asym_id'	=> CHAIN_ID_2_ASYM_ID[ $chain_id ] ,
				'text'		=> _ifnn( $sacs_info->cls, '\1: ' ). _imp( $names ) ,
				'pop'		=> _table_2col( $pop ). _kakko( $sacs_link )
			]);
		}
	}

	//.. kabat by seq
	if ( ! $sacs_info || TEST ) {
		$seq_info = json_decode( _ezsqlite([
			'dbname' => 'abinfo' ,
			'where'  => [ 'id', ID. '-'. ENT_ID ] ,
			'select' => 'seq' ,
		]));
	}
	if ( $seq_info ) {
//		$o_contents->src( IC_L. $sacs_link );
		foreach ( CHAIN_ID_LIST as $chain_id ) {
			$names = $pop = [];
			$pop = $cls_init;
			foreach ( (array)$seq_info as $name => $c ) {
				$o_sblock->add( $c );
				$names[] = $name;
				$pop[ $name ] = $c[2];
			}
			$o_sblock->list_item([
				'categ' 	=> 'Antibody CDR based on Kabat Numbering' ,
				'asym_id'	=> CHAIN_ID_2_ASYM_ID[ $chain_id ] ,
				'text'		=> _imp( $names ) ,
				'pop'		=> _table_2col( $pop ) ,
			]);
		}
	}

	//.. plus情報
	//... gen
	$gen = $gen_res = $gen_det = [];
	foreach ( (array)$plus->struct_site_gen as $c ) {
		$asym_id  = _asym_id( $c );
		if ( ! _asymid_is_issue( $asym_id ) ) continue;
		$site_id  = $c->site_id;
		
		//- *がついているものがある、5m8gなど
		$gen[ $site_id ][ $asym_id ][] = _numonly( _seq_id( $c ) ) ?: [
			_numonly( _seq_id( $c, 'beg_' ) ), _numonly( _seq_id( $c, 'end_' ) )
		];

		$ci = _chain_icon_plus( $asym_id );
		$gen_res[ "$site_id|$asym_id" ][] = _seq_num( $c )
			? $ci. _seq_num( $c )
			: $ci. ( _seq_num( $c, 'beg_' ) ?: '?' ). '-'
				. ( _seq_num( $c, 'end_' ) ?: '?' )
		;
		if ( $c->details )
			$gen_det[ "$site_id|$asym_id" ][] = $c->details;
	}
	if ( $gen ) {
		$o_contents->src( MLPLUS );
		ksort( $gen );
	}

	//... main
	foreach ( (array)$plus->struct_site as $c ) {
		$site_id = $c->id;
		$type = $c->info_type;
		$det = explode( '.', $c->details )[0];
		if ( ! $gen[ $site_id ] ) continue;

		//- 複数のasymにまたがることはないはずだが一応
		foreach ( (array)$gen[ $site_id ] as $asym_id => $c2 ) {
			$table = [
				'Site ID'	=> $site_id ,
				'Details'	=> $det ,
				'Num. of res.'	=> $c->pdbx_num_residues ,
				'Residues'		=> _imp( $gen_res[ "$site_id|$asym_id" ] ) ,
			];
			if ( $type == 'Swiss-Prot' ) {
				//- いきなりエビデンス文字列
				if ( substr( $det, 0, 1 ) == '{' )
					$det = '';
				$table[ 'Evidence' ] = _evidence_code_right( $c->details );
			} else if ( $type == 'prosite' ) {
				$table += [
					'Pattern' => explode( '.', $c->details, 2 )[1] ,
					'Data item' => _obj('dbid')->pop( 'pr', explode( '_', $site_id )[0] )
				];
			} else if ( $type == 'extCATRES' ) {
				$det = _imp( _uniqfilt( $gen_det[ "$site_id|$asym_id" ] ) );
				$table[ 'Details' ] = $det. BR. $c->details;
				$table += [
					'Original data' => _ent_catalog([ 'PDB-'. $c->orig_data ]) ,
					'RMSD to Orig.' => $c->orig_rmsd. ' &Aring;' ,
					'Num. of atom pairs' => $c->orig_number_of_atom_pairs ,
				];
			} else if ( $type == 'CSA' ) {
				$s = array_slice( explode( ' ', $c->details ), -1 )[0];
				if ( strlen( $s ) == 4 )
					$table['Ref. PDB'] = _ent_catalog([ 'PDB-'. $s ]);
				//- pubmed文字列
				if ( _instr( 'PubMed', $det ) ) {
					$ev = [];
					preg_match_all( '/[0-9]+/', $det, $p );
					foreach ( (array)$p[0] as $i ) {
						$ev[] = _dblink( 'PubMed', $i );
					}
					$table['Evidence'] = _imp( $ev );
					$det = preg_replace( '/, PubMed.+$/', '', $det );
				}
			}
			$table[ 'About this data' ] = 'See '. MLPLUS;

			foreach ( (array)_uniqfilt( $c2 ) as $res )
				$o_sblock->add( $res );
			$o_sblock->list_item([
				'categ'		=> $type. MLPLUS ,
				'asym_id'	=> $asym_id ,
				'text'		=> ''
					. ( $c->info_subtype && $c->info_subtype != $type
						? _trep2( $c->info_subtype ). ': '
						: ''
					)
					. ( 70 < strlen( $det )
						? substr( $det, 0, 60 ). '...'
						: $det 
					)
				,
				'pop'		=> _table_2col( $table, ['opt' => '.small'] ) ,
				
			]);
		}
	}

	//.. seq_prep
	//... seq_prep plus
	$seq_u2p_plus = [];
	foreach ( (object)$plus->struct_ref_seq as $c ) {
		if ( ! _chainid_is_issue( $c->pdbx_strand_id ) ) continue;
		if ( ! _instr( 'SIFTS_UNP', $c->align_id ) ) continue;
		(integer)$p = $c->seq_align_beg;
		foreach ( range( $c->db_align_beg, $c->db_align_end ) as $u ) {
			$seq_u2p_plus[ $c->pdbx_db_accession ][ $u ] = $p;
			++ $p;
		}
	}

	//... seq_prep PDB
	$seq_u2p_pdb = [];
	foreach ( (object)$json->struct_ref_seq as $c ) {
		if ( ! _chainid_is_issue( $c->pdbx_strand_id ) ) continue;
		(integer)$p = $c->seq_align_beg;
		foreach ( range( $c->db_align_beg, $c->db_align_end ) as $u ) {
			$seq_u2p_pdb[ $c->pdbx_db_accession ][ $u ] = $p;
			++ $p;
		}
	}

	//.. function _unp_seq_add
	function _unp_seq_add( $seq ) {
		global $_conv_seq, $item;
		
	}
	
	//.. UniProtから情報収集
	$items = [];
	$unp_ids_used = [];
	$count_name = 0;
	foreach ( _load_unp_json() as $unp_id => $unp_json ) {
		$_conv_seq = $seq_u2p_plus[ $unp_id ] ?: $seq_u2p_pdb[ $unp_id ];
		$ss_bond_num = 1;
		foreach ( (array)$unp_json['fet'] as $fet ) {
			$item = [];
			$f_type = $f_id = $f_desc = $f_evd = $f_ref = $f_var = $f_loc = null;
			extract( $fet, EXTR_PREFIX_ALL, 'f' );
			$member = '';

			//... 残基リスト
			if ( $f_type == 'disulfide bond' ) {
				$r = [ $f_loc[0], $f_loc[1] ];
			} else {
				$r = is_array( $f_loc )
					? range( $f_loc[0], $f_loc[1] )
					: [ $f_loc ]
				;
			}
			$res_set =[];
			foreach ( $r as $num ) {
				if ( array_key_exists( $num, $_conv_seq ) )
					$res_set[] = $_conv_seq[ $num ];
			}
			if ( ! $res_set ) continue; //- このUNPエントリに該当なし
			$unp_ids_used[] = $unp_id;

			//... name
			if ( $f_type == 'disulfide bond' ) {
				$name = trim( "$f_desc #$ss_bond_num" );
				++ $ss_bond_num;
			} else {
				//- 膜貫通などをひとまとめにする
				list( $name, $member ) = explode( '; Name=', $f_desc );
				$name = $name ?: '-';
			}

			if ( in_array( $f_type, [ 'transmembrane region', 'intramembrane region' ] ) ) {
				$name = _trep2( $f_type ). ": $name";
				$f_type = 'topological domain';
			} else if ( $f_type == 'repeat' ) {
				if ( is_numeric ( preg_replace( '/;.+$/', '', $name ) ) ) {
					//- 名無し
					$member = $name;
					$name = 'Repeat';
				} else {
					list( $name, $member ) = explode( ' ', $name, 2 );
				}
			}
			$key = "$f_type||". ( $name == '-' ? $f_type. ( ++ $count_name ) : $name );

			//... DB-ID情報があれば
			foreach ( (array)$unp_json['n2dbid'][ $name ] as $d )
				$items[ $key ]['dbid'][] = _obj('dbid')->pop( $d[0], $d[1] );

			//- ドメインなのに、ヒットしなければ頑張る
			if ( in_array( $f_type, [ 'domain', 'region of interest' ] )
				&& ! $items[ $key ]['dbid'] 
			) {
				$name_rep = strtolower( _reg_rep( $name, [
					'/;+$/' => '',
					'/-type/' => '',
					'/[^a-zA-Z0-9]/' => '' ,
					'/^[a-z]([A-Z])/' => '$1'
				]) );
				$a = [];
				foreach ( (array)$unp_json['n2dbid'] as $k => $v ) {
					if ( _instr(
						$name_rep,
						preg_replace( '/[^a-zA-Z0-9]/', '', $k )
					))
						$a = array_merge( $a, $v );
				}
				foreach ( $a as $d )
					$items[ $key ]['dbid'][] = _obj('dbid')->pop( $d[0], $d[1] );
			}

			//- リピート、無理やり探す
			if ( $f_type == 'repeat' ) {
				$a = [];
				foreach ( (array)$unp_json['n2dbid'] as $k => $v ) {
					if ( _instr( $name, $k ) && _instr(  '_repeat', $k ) )
						$a = array_merge( $a, $v ); 
				}
				foreach ( $a as $d )
					$items[ $key ]['dbid'][] = _obj('dbid')->pop( $d[0], $d[1] );
			}

			//... fet_id, link, etc
			$items[ $key ]['fet_id'][] = $f_id;

			preg_match( '/dbSNP:([0-9a-zA-Z]+)/', $f_desc, $a );
			if ( $a[1] ) 
				$items[ $key ]['link'][] = _dblink( 'dbSNP', $a[1] );
			
			if ( $f_var ) {
				//- 長い配列文字列に対応
				if ( 20 < strlen( $f_var ) && ! _instr( ' ', $f_var ) ) {
					$f_var = _reg_rep( $f_var, [ '/[A-Z]{10}/' => '$0 ' ,]);
				}
				$items[ $key ]['var'][] = $f_var;
			}
			
			//... evidence
			foreach ( (array)$f_evd as $ev_id ){
				$items[ $key ][ 'evd' ][] = $unp_json['evd'][$ev_id ];
			}
			foreach ( (array)$f_ref as $refid ) {
				$r = $unp_json['ref'][ $refid ];
				if ( ! $r ) continue;
				$a = '';
				foreach ( (array)$r['_ref'] as $k => $v )
					$a .= _dblink( $k, $v );
				unset( $r['_ref'] );
				$items[ $key ]['ref'][] = _kv( $r ). $a ;
			}

			//... others
			$items[ $key ][ 'res' ] = array_merge( (array)$items[ $key ][ 'res' ], $res_set );
			$items[ $key ]['member'][] = $member;
		}
	}

	//.. UniProtまとめ
	if ( ! $items )
		$o_contents->out();

	foreach ( CHAIN_ID_LIST as $chain_id ) {
		$asym_id = CHAIN_ID_2_ASYM_ID[ $chain_id ];
		foreach ( $items as $key => $item ) {
			$res = $evd = $member = [];
			$seq = $fet_id = $link = $var = $dbid = '';
			extract( $item );
			if ( ! $res ) continue; //- ないはずだが

			//- name
			list( $type, $name ) = explode( '||', $key );
			list( $name, $wikipe ) = _name_prep( $name );

			//- popup
			$member = _uniqfilt( $member );
			$pop = array_filter([
				_kv([ 'ID' => _imp( $fet_id ) ]),
				$var ? _kv([ 'Description' => $name ]) : '' , //--- おかしい？
				_imp( $link ) ,
				implode( _uniqfilt( $dbid ) ) ,
				_evidence_code_unp( _uniqfilt( $evd ) ) ,
				_imp( _uniqfilt( $ref ) ) ,
				$member ? count( $member ). ' items'. _kakko( _imp( $member ) ) : '' ,
				$wikipe ,
			]);

			//- res info
			foreach ( _start_end( $res ) as $r )
				$o_sblock->add( $r );

			//- output
			$o_sblock->list_item([
				'categ' 	=> $type ,
				'asym_id'	=> $asym_id ,
				'text'		=> _imp( $var ) ?: $name ,
				'pop'		=> 1 < count( $pop ) ? _ul( $pop ) : $pop[0]
			]);
		}
	}

	//... 情報源(UniProt)
	foreach ( _uniqfilt( $unp_ids_used ) as $i ){
		$o_contents
			->src( _obj('dbid')->pop( 'UniProt', $i, '.' ) )
			->test_link( 'unp_xml' , $i )
			->test_link( 'unp_json', $i )
		;
	}
	$o_contents->src( _doc_pop('func_homology') )->out();
}

//. DNA/RNA
if ( MODE == 'nuc' ) {
	$o_sblock = new cls_seqblock();
	_def_trep('qajax-nuc');
	$o_contents
		->ref( 'bp_12', _ab( ['ndb_doc', 'legends/saenger'], IC_L. 'Saenger Legend - ndb' ) )
		->ref( 'bp_28', _ab( ['ndb_doc', 'ndb-help'], IC_L. 'RNA Base Pair Families -ndb' ) )
	;

	//.. データ収集
	$matome = [];
	$pair_data = [];
	foreach ( (array)$json->ndb_struct_na_base_pair as $c ) {
		if ( $c->model_number != 1 ) continue;
		$i_asym = $c->i_label_asym_id;
		$j_asym = $c->j_label_asym_id;
		$i_seq  = $c->i_label_seq_id;
		$j_seq  = $c->j_label_seq_id;

		//- bond
		foreach ([
			12 => $c->hbond_type_12 ,
			28 => $c->hbond_type_28
		] as $cls => $num ) {
			if ( !$num ) continue;
			foreach ([
				$i_asym => $i_seq ,
				$j_asym => $j_seq
			] as $asym_id => $seq_id ) {
				if ( _asymid_is_issue( $asym_id ) )
					$matome[ $asym_id ][ "$cls|$num" ][] = $seq_id;
			}
		}

		//- pair
		$pair_data[ "$i_asym-$i_seq" ][] = "$j_asym-$j_seq";
		$pair_data[ "$j_asym-$j_seq" ][] = "$i_asym-$i_seq";
		$nuc_type[ "$i_asym-$i_seq" ] = strlen( $c->i_label_comp_id );
		$nuc_type[ "$j_asym-$j_seq" ] = strlen( $c->j_label_comp_id );
	}

	//.. double / triple / quad
	$flg_p = [];
	foreach ( $pair_data as $asym_seq => $partners ) {
		list( $asym_id, $seq_id ) = explode( '-', $asym_seq );
		if ( ! _asymid_is_issue( $asym_id ) ) continue;
		foreach ( array_unique( $partners ) as $member ) {
			$partners = array_merge( $partners, $pair_data[ $member ] );
		}
		$icon = I_BIND;
		$type = [];
		$flg_hybrid = false;
		foreach ( array_unique( $partners ) as $p ) {
			if ( $p == $asym_seq ) continue;
			$a = explode( '-', $p )[0];
			$icon .= _chain_icon( ASYM_ID_2_CHIAN_ID[ $a ] );
			if ( $nuc_type[ $p ] != $nuc_type[ $asym_seq ] )
				$flg_hybrid = true;
			$type[] = [ 1 => 'RNA', 2 => 'DNA', 3 => 'Other' ][ $nuc_type[ $p ] ]
				?: $nuc_type[ $p ] ?: 'hoge';
		}
		if ( $flg_hybrid ) {
			$icon = [ 1 => 'RNA', 2 => 'DNA', 3 => 'Other' ][ $nuc_type[ $asym_seq ] ]
				. $icon
				. implode( '=', _uniqfilt( $type ) )
			;
		}
		$matome[ $asym_id ][ 'Base pair|'. $icon ][] = $seq_id;
		$flg_p[ $asym_id ][ $seq_id ] = true;
	}
	
	//.. single strand
	$flg_uo = [];
	foreach ( (array)$json->pdbx_unobs_or_zero_occ_residues as $c )
		$flg_uo[ $c->label_asym_id ][ $c->label_seq_id ] = true;
	foreach ( $flg_p as $asym_id => $flg_seq ) {
		foreach ( array_keys( SEQ_LABEL2AUTH[ $asym_id ] ) as $seq_id ) {
			if ( $flg_uo[ $asym_id ][ $seq_id ] || $flg_seq[ $seq_id ] ) continue;
			$matome[ $asym_id ]['Base pair|single strand'][] = $seq_id;
		}
	}

	//.. make
	$cls2name = [
		12 => 'bp_12' ,
		28 => 'bp_28'
	];
	$name_rep = [
		12 => [
			1 	=> '#1: Cis, Watson–Crick/Watson–Crick, Antiparallel' ,
			2 	=> '#2: Trans, Watson–Crick/Watson–Crick, Parallel' ,
			3 	=> '#3: Cis, Watson–Crick/Hoogsteen, Parallel' ,
			4 	=> '#4: Trans, Watson–Crick/Hoogsteen, Antiparallel' ,
			5 	=> '#5: Cis, Watson–Crick/Sugar Edge, Antiparallel' ,
			6 	=> '#6: Trans, Watson–Crick/Sugar Edge, Parallel' ,
			7 	=> '#7: Cis, Hoogsteen/Hoogsteen, Antiparallel' ,
			8 	=> '#8: Trans, Hoogsteen/Hoogsteen, Parallel' ,
			9 	=> '#9: Cis, Hoogsteen/Sugar Edge, Parallel' ,
			10	=> '#10: Trans, Hoogsteen/Sugar Edge, Antiparallel' ,
			11	=> '#11: Cis, Sugar Edge/Sugar Edge, Antiparallel' ,
			12	=> '#12: Trans, Sugar Edge/Sugar Edge, Parallel' ,
		],
		28 => [
			1	=> 'I' ,
			2	=> 'II' ,
			3	=> 'III' ,
			4	=> 'IV' ,
			5	=> 'V' ,
			6	=> 'VI' ,
			7	=> 'VII' ,
			8	=> 'VIII' ,
			9	=> 'IX' ,
			10	=> 'X' ,
			11	=> 'XI' ,
			12	=> 'XII' ,
			13	=> 'XIII' ,
			14	=> 'XIV' ,
			15	=> 'XV' ,
			16	=> 'XVI' ,
			17	=> 'XVII' ,
			18	=> 'XVIII' ,
			19	=> 'XIX' ,
			20	=> 'XX' ,
			21	=> 'XXI' ,
			22	=> 'XXII' ,
			23	=> 'XXIII' ,
			24	=> 'XXIV' ,
			25	=> 'XXV' ,
			26	=> 'XXVI' ,
			27	=> 'XXVII' ,
			28	=> 'XXVIII' ,
		] ,
		'basepair' => [
			2 => 'double' ,
			3 => 'triple' ,
			4 => 'quadruple' ,
		]
	];

	foreach ( $matome as $asym_id => $c ) {
		$chain_id = ASYM_ID_2_CHIAN_ID[ $asym_id ];
		ksort( $c );
		foreach ( $c as $cls_num => $seq_id_list ) {
			sort( $seq_id_list );
			$seq_id_list = array_unique( $seq_id_list );
			foreach ( _start_end( $seq_id_list ) as $start_end )
				$o_sblock->add( $start_end );

			//- output
			list( $cls, $num ) = explode( '|', $cls_num, 2 );
			$o_sblock->list_item([
				'categ'		=> $cls2name[ $cls ] ?: $cls,
				'asym_id'	=> $asym_id ,
				'text'		=> $name_rep[ $cls ][ $num ] ?: $num ,
			]);
		}
	}
	$o_contents->src_cif( 'ndb_struct_na_base_pair' )->out();
}

//. site
if ( MODE == 'site' ) {
	$plus = _json_load2([ 'pdb_plus', ID ]);
	_def_trep('qajax-site');
	define( 'COMP_ID', $json_reid->entp->{ENT_ID}->comp_id );
	define( 'IS_CHEM', COMP_ID != '' );
	define( 'IS_POLYSAC', $json_reid->ent->{ENT_ID}->type == 'branched' );
	if ( IS_CHEM || IS_POLYSAC )
		$o_contents->show_chain_sel = false;

	define( 'SWISS_REP', _ej([ 
		'SITE'		=> 'Site' , 
		'ACT_SITE'	=> 'Active site' , 
		'BINDING'	=> 'Binding site' , 
		'TRANSMEM'	=> 'Transmembrane' , 
		'NP_BIND'	=> 'Nucleotide binding',
		'DNA_BIND'	=> 'DNA binding' ,
		'RNA_BIND'	=> 'RNA binding' ,
		'MET_BIND'	=> 'Metal binding' , 
		'METAL'		=> 'Metal' ,
	],[
		'SITE'		=> '部位' , 
		'ACT_SITE'	=> '活性部位' ,
		'BINDING'	=> '結合部位' ,
		'TRANSMEM'	=> '膜貫通' ,
		'NP_BIND'	=> '核酸結合' ,
		'DNA_BIND'	=> 'DNA結合' ,
		'RNA_BIND'	=> 'RNA結合' ,
		'MET_BIND'	=> '金属結合' ,
		'METAL'		=> '金属' ,
	]) );

	//.. pre gen ループ
	$site_jmol = [];
	$site_molmil = [];
	$site_res = [];
	$ex_aid = [];

	foreach ( (array)$json->struct_site_gen as $c ) {
		$site_id  = $c->site_id;
		$asym_id  = _asym_id( $c );
		$chain_id = _chain_id( $c );

		//- asymid 存在チェック用
		$ex_aid[ $site_id ][ $asym_id ] = true;

		//- jmol
		$i = _seq_num( $c );
		$site_jmol[ $site_id ][ $chain_id ][] = $i;

		//- res info
		if ( $c->label_comp_id != '' )
			$site_res[ $site_id ][ $chain_id ][] = $c->label_comp_id. $i;

		//- molmil
		$site_molmil[ $site_id ][ $asym_id ][] = _seq_id( $c );
	}

	//- 並べ直し
	foreach ( $ex_aid as $site_id => $a ) {
		ksort( $a );
		$ex_aid[ $site_id ] = $a; 
	}

	//.. site_keywords
	$kws = [];
	foreach ( (array)$json->struct_site_keywords as $c ) {
		$kws[ $c->site_id ][] = $c->text;
	}

	//.. main loop
	foreach ( (array)$json->struct_site as $c ) {
		$site_id = $c->id;
		$flg = IS_CHEM;
		foreach ( ENT_ID_2_ASYM_ID[ ENT_ID ] as $asym_id ) {
			if ( $ex_aid[ $site_id ][ $asym_id ] )
				$flg = true;
		}
		if ( ! $flg ) continue;

		//... selection data
		//- jmol
		$ar = [];
		foreach ( (array)$site_jmol[ $site_id ] as $chain_id => $a2 )
			$ar[] = "(*:$chain_id and (". implode( ' or ', $a2 ). "))";
		$jmolcmd = $ar == [] ? '' : implode( ' or ', $ar );
		
		//- molmil
		$molmilcmd = [];
		foreach ( (array)$site_molmil[ $site_id ] as $asym_id => $nums ) {
			$molmilcmd[ $asym_id ] = $nums;
		}

		//... res_info
		//- res info
		$resinfo = [];
		foreach( (array)$site_res[ $site_id ] as $chain_id => $a2 ) {
			$asym_id = CHAIN_ID_2_ASYM_ID[ $chain_id ];
			$resinfo[] = ( $asym_id != '' ? _chain_icon_plus( $asym_id ) : $chain_id )
				. _imp( $a2 );
		}

		//... details
		$det = $c->details;
		$det_noevidence = _evidence_code_left( $det );
		if ( is_object( $det ) ) $det = '';  //- 暫定対処 ------------------------
		$pop_key = $type2 ?: 'other' ;

		$pop_table = [
			'Site ID'	=> $site_id ,
			'Type'		=> '' ,
			'Details' 	=> $det_noevidence ,
			'Evidence'	=> _evidence_code_right( $det )
						. strtolower( $c->pdbx_evidence_code ),
			'Num. of res.' => $c->pdbx_num_residues ,		
			'keywords'	=> _imp( (array)$kws[ $site_id ] ) ,
			'Residues'  => _imp2( $resinfo ) ,
		];

		$icons = '';
		foreach ( array_keys( (array)$ex_aid[ $site_id ] ) as $a ) {
			$icons .= _chain_icon_plus( $a );
		}

		//.. typeごと PDB由来
		if ( _instr( 'BINDING SITE FOR ', strtoupper( $det ) ) ) {
			$det_short = strtr( $det, [
				'BINDING SITE FOR '  => '' ,
				'Binding site for '  => '' ,
				'binding site for '  => '' ,
			]);
			list( $lig_type, $comp, $chain, $seq ) = explode( ' ', $det_short );
			$pop_table['type'] = $type = _l( 'Ligand binding site' );
			if ( strtoupper( $lig_type ) == 'RESIDUE' ) {
				$comp  = $c->pdbx_auth_comp_id ?: $comp;
				$chain = $c->pdbx_auth_asym_id ?: $chain;
				$seq   = $c->pdbx_auth_seq_id  ?: $seq;
				$pop_key = I_BIND. _chemimg_s( $comp, ['nopop' => true ] );
				$pop_table['Ligand'] = _chem_ent( $comp, $chain, $seq );
			} else {
				$pop_key = I_BIND. $det_short;
			}
		}
		if (  IS_CHEM && COMP_ID != $comp ) continue;

		//.. まとめ
		$o_contents->add(
			$type ,
			_selres_btn( ['jmol' => $jmolcmd, 'molmil' => $molmilcmd ])
			. _pop(
				$icons. $pop_key,
				_table_2col( $pop_table, ['opt' => '.small'] )
			)
			,
			IS_CHEM || $ex_aid[ $site_id ][ ASYM_ID ]
		);
	}

	//.. conn
///	$o_contents->test_info([ 'end_id' => ENT_ID, 'asym_id' => ASYM_ID ]);
	//... data
	$data = [];
	foreach ( (array)$json->struct_conn as $c ) {
		$conn_id = $c->id;
		$d = [];
		foreach ( [ 1 => 'ptnr1_', 2 => 'ptnr2_' ] as $n => $p ) {
			$d[ $n ] = [
				'comp_id'	=> $comp_id ,
				'asym_id'	=> $asym_id =_asym_id( $c, $p ) ,
				'chain_id'	=> _chain_id( $c, $p ) ,
				'seq_id'	=> _seq_id( $c, $p ) ,
				'seq_num'	=> _seq_num( $c, $p ) ,
				'atom_id'	=> $c->{ $p. 'label_atom_id' } ,
				'ent_id'	=> $ent_id = ASYM_ID_2_ENT_ID[ $asym_id ] ,
				'comp_id'	=> $c->{ $p. 'label_comp_id' } ?: $c->{ $p. 'auth_comp_id' } ,
				'symm'		=> $c->{ $p. 'symmetry' } ,
				'ent_type'	=> $json_reid->ent->$ent_id->type ,
			];
		}
		if ( $d[1]['ent_id'] != ENT_ID && $d[2]['ent_id'] != ENT_ID ) continue;
		if ( $d[1]['comp_id'] == 'HOH' || $d[2]['comp_id'] == 'HOH' ) continue;
		if ( $d[1]['ent_type'] != 'polymer' && $d[2]['ent_type'] == 'polymer' )
			list( $d[1], $d[2] ) = [ $d[2], $d[1] ]; //- swap
		if ( $d[1]['ent_type'] != 'polymer' ) continue;

		$d[0] = [
			'conid'	=> $c->id ,
			'type'	=> $c->conn_type_id ,
			'det'	=> $c->details ,
			'role'	=> $c->pdbx_role ,
			'order'	=>$c->pdbx_value_order ,
			'dist'	=> $c->pdbx_dist_value ,
			'lvatom' => $c->pdbx_leaving_atom_flag ,
		];
		$data[ $d[2]['ent_type'] == 'non-polymer'
			? _imph( $d[2]['chain_id'], $d[2]['seq_num'], $c->conn_type_id )
			: $conn_id
		][] = $d;
	}
	ksort( $data );

	//... 整理
//	$o_contents->add( 'TEST', _t( 'pre', _json_pretty( $data ) ) );
	foreach ( $data as $key => $c1 ) {
		$flg_show = IS_CHEM || IS_POLYSAC;
		$molmilcmd = $icon = $table = [];
		$table = TR_TOP.TH. implode( TH, [
			'ID', '#1', '#2', 'Dist. (&Aring;)', 'leaving atom', 'N.B.'
		]);
		foreach ( $c1 as $i => $c2 ) {
			$atom = [];
			foreach ( [1, 2] as $num ) {
				$comp_id= $asym_id= $chain_id= $seq_id= $seq_num= $atom_id=
					$ent_id= $comp_id= $symm= $ent_type= null;
				extract( $c2[$num] );
				$molmilcmd[ $asym_id ][] = $seq_id;
				if ( $ent_type == 'polymer' ) {
					$ic = _imph(
						_chain_icon_plus( $asym_id ), $seq_id //, $comp_id
					);
				} else if ( $ent_type == 'non-polymer' ) {
					$ic = _chemimg_s( $comp_id )
						. _imph( _chain_icon( $chain_id ), $seq_num );
				} else {
					$ic = _polysac_img( $ent_id ). '['. $asym_id. ']';
				}
				$atom[ $num ] = _imph(
					_chain_icon( $chain_id ), $seq_num, _chemimg_s( $comp_id ), $atom_id
				);
				$icon[ $num ][] = $ic. ( $symm == '1_555' ? '' : "($symm)" );
				if ( $asym_id == ASYM_ID )
					$flg_show = true;
			}
			$conid = $type = $det = $role = $order = $dist = null;
			extract( $c2[0] );
			$table .= TR.TD. implode( TD, [
				$conid, $atom[1], $atom[2], $dist, $lvatom, _imp2([ $det, $order ])
			]);
		}

		//... まとめ
		$o_contents->add(
			_trep2( $type ). _kakko( _l( $role ) ) ,
			_selres_btn([
				'jmol' => '' ,
				'molmil' => $molmilcmd
			])
			. _pop(
				implode( '', array_unique( $icon[1] ) ). I_BIND. $icon[2][0] ,
				_div( '.pop_inner', _t( 'table| .small', $table ) )
			) ,
			$flg_show
		);
	}

	//.. end
	$o_contents->out();
}

//.. _chain_icon_plus
function _chain_icon_plus( $asym_id ) {
	global $json, $json_reid;
	$eid = $json_reid->asym->$asym_id->entity_id;
	$chain_id = $json_reid->entp->$eid->comp_id;
	//- chem
	if ( $json_reid->ent->$eid->type != 'polymer' )
		return _img( '.icon_ss', _fn( 'chem_img', $chain_id ) );

	//- ポリマー
	return _chain_icon( $json_reid->entp->$eid->pdbx_strand_id
		? ASYM_ID_2_CHIAN_ID[ $asym_id ] 
		: '*' //- 多糖
	);
}

//. modified
if ( MODE == 'mod' ) {
	$o_sblock = new cls_seqblock();
	foreach ( (array)$json->pdbx_struct_mod_residue as $c ) {
		$asym_id = _asym_id( $c );
		if ( ASYM_ID_2_ENT_ID[ $asym_id ] != ENT_ID ) continue;

		$o_sblock->add( _seq_id( $c ) );

		$det = $c->details;
		if ( strtolower( $det ) == 'modified residue' ) {
			if ( $c->label_comp_id )
				$det = $json_reid->chem->{$c->label_comp_id}->name ?: 'hoge';
		}

		$o_sblock->list_item([
			'categ'		=> _l( $det ) ,
			'asym_id'	=> $asym_id ,
			'text'		=> ''
				. $c->auth_seq_id
				. ': '
				. ( $c->parent_comp_id == '' ? '?' : _chemimg_s( $c->parent_comp_id ) )
				. ( $c->parent_comp_id == $c->label_comp_id ? ''
					: _ic( 'rightarrow' ). _chemimg_s( $c->label_comp_id )
				)
		]);
	}
	$o_contents->src_cif( 'pdbx_struct_mod_residue' )->out();
}

//. mutation
if ( MODE == 'mut' ) {
	//.. func: _block
	function _block() {
		global $o_sblock,
			$start, $start_num, $seq_num,
			$from, $to, $last
		;
		$o_sblock->add([ $start, $last['seq'] ]);
		$from = array_filter( $from );
		$to = array_filter( $to );
		$cat = $last[ 'det' ] ?: (
			! $from ? 'Insertion' : (
			! $to ? 'Deletion' : 'Mutation' )
		);
		$o_sblock->list_item([
			'categ'		=> _l( strtolower( $cat ) ) ,
			'asym_id'	=> $last['asym'] ,
			'text'		=> ''
				. implode( '-', array_unique([ $start_num, $seq_num ]) )
				. ': '
				. ( _long( implode( ' ', array_filter( $from ) ) ) ?: 'None' )
				. _ic( 'rightarrow' )
				. ( _long( implode( ' ', array_filter( $to ) ) ) ?: 'None' )
			,
		]);
		_reset();
	}

	//.. func: _reset
	function _reset() {
		global
			$start, $start_num, $seq_num,
			$from, $to, $last
		;
		$start = $start_num = $seq_num = null;
		$from = $to = $last = []; 
	}

	//.. main
	$o_sblock = new cls_seqblock();
	_reset();
	foreach ( (array)$json->struct_ref_seq_dif as $c ) {
		$det = $c->details;
		$asym_id = _asym_id( $c );
		if ( ! _asymid_is_issue( $asym_id ) ) continue;
		$seq_id = _seq_id( $c );

		if ( $start && (
			$last['asym'] != $asym_id ||
			$last['seq' ] != $seq_id - 1 ||
			$last['det' ] != $det
		))  {
			//- ブロック終了
			_block();
		}
		$seq_num = $c->pdbx_auth_seq_num . $c->pdbx_pdb_ins_code
			?: _ifnn( $c->pdbx_seq_db_seq_num, '(#\1)' )
			?: '[?]'
		;
		if ( ! $start ) {
			//- ブロック開始
			$start = $seq_id;
			$start_num = $seq_num;
		}
		$last = [
			'asym'	=> $asym_id ,
			'seq'	=> $seq_id ,
			'det'	=> $det ,
		];
		$from[] = $c->db_mon_id;
		$to[]   = $c->mon_id;
	}
	_block();
	$o_contents->src_cif( 'struct_ref_seq_dif' )->out();
}

//. validation
if ( MODE == 'valid' ) {
	$o_sblock = new cls_seqblock();
	//.. pre
	$res_molmil = $res_jmol = $res_list = [];
	$table = [];
	$jmol = $molmil = [];
	_def_trep( 'qajax-valrep' );

	//.. main start
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
		$table = $molmil = $jmol = $res_list = [];
		foreach ( (array)$json->$cat as $c ) {
			foreach (['', '_1', '_2', '_3'] as $num ) {
				$chain_id = $c->{ 'auth_asym_id'. $num };
				$asym_id = CHAIN_ID_2_ASYM_ID[ $chain_id ];
				if ( ! _asymid_is_issue( $asym_id ) ) continue;
				$seq_num = $c->{ 'auth_seq_id'. $num }. $c->{ 'PDB_ins_code'. $num };
				$seq_id = SEQ_AUTH2LABEL[ $asym_id ][ $seq_num ];
				if ( $seq_id == null ) {
//					$o_contents->test_info( "$asym_id-$seq_num: no seq_id" );
					continue; //- 水など
				}
				$molmil[ $asym_id ][] = SEQ_AUTH2LABEL[ $asym_id ][ $seq_num ];
				$jmol[ $chain_id ][] = "*:$chain_id and ". $seq_num;
				++ $res_list[ $chain_id ];

				//.. table
				if ( $table[ $chain_id ] == '' ) {
					$table[ $chain_id ] .= TR_TOP;
					foreach ( array_keys( (array)$c ) as $k ) {
						$table[ $chain_id ] .= TH. strtr( $k, [
							'PDB_model_num' => 'model' ,
							'asym_id' 		=> 'chain' ,
							'auth_' 		=> '' ,
							'_id' 			=> '' ,
							'_standard_deviation' => ' std.dev.' ,
							'_deviation'	=> ' dev.' ,
							'_'				=> ' ' ,
							'phi'			=> '&phi;' ,
							'psi'			=> '&psi;' ,
						]);
					}
				}
				$table[ $chain_id ] .= TR.TD. implode( TD, array_values( (array)$c ) );
			}
		}

		//.. まとめ
		if ( ! $res_list ) continue;
		$type = _pop_ajax(
			_trep2( $cat ) ?:
			ucfirst( strtr( $cat, [
				'pdbx_validate_' 	=> '',
				'rmsd'				=> 'RMSD' ,
				'_'					=> ' '
			] ) )
			,
			[ 'cifdic', 'q' => $cat ] 
		);
		foreach ( $res_list as $chain_id => $num ) {
			$asym_id = CHAIN_ID_2_ASYM_ID[ $chain_id ];
			foreach ( _start_end( $molmil[ $asym_id ] ) as $res )
				$o_sblock->add( $res );
			$o_sblock->list_item([
				'categ' 	=> $type ,
				'asym_id'	=> $asym_id ,
				'text'		=> _l( 'details' ) ,
				'pop'		=> _div( '.pop_inner',
					_t( 'table| .small| st:text-align:center', $table[ $chain_id ] )
				) ,
			]);
		}
		$o_contents->test_link( 'json', $cat );
	}
	$o_contents->out();
}

//. others?
_die( [ _getpost( 'id' ), MODE ] );

//. functions
//.. _imph ハイフンでimp
function _imph() {
	return implode( '-', array_filter( is_array( func_get_arg(0) )
		? func_get_arg(0) : (array)func_get_args()
	));
}

//.. _trep2 (簡易版)
function _trep2( $in ) {
	global $_trep;
	if ( _instr( '<', $in ) ) return $in; //- htmlタグがあったらやらない
	return ucfirst( strtr( 
		$_trep[ strtolower( $in ) ]
		?: _l( $in )
		?: $in
	, ['_' => ' '] ) );
}

//.. _def_trep
function _def_trep( $type ) {
	global $_trep;
	$_trep = _subdata( 'trep', $type );
}

//.. _kv1 簡易版
function _kv1( $k1, $k2, $v ) {
	$k2 = $k2 ? ' - '. _trep2( $k2 ) : '';
	return "<b>$k1$k2</b>: ". _long( $v );
}

/*
//.. _seq_id
function _seq_id_old( $label, $auth, $asym_id = '' ) {
	//- 空オブジェクトの暫定対処
	if ( is_object( $label ) ) $label = '';
	if ( is_object( $auth  ) ) $auth  = '';
	return $label ?: SEQ_AUTH2LABEL[ $asym_id ?: ASYM_ID ][ $auth ];
}
//.. _seq_num auth-seq-idを返す
function _seq_num_old( $label, $auth, $asym_id = '' ) {
	//- 空オブジェクトの暫定対処
	if ( is_object( $label ) ) $label = '';
	if ( is_object( $auth  ) ) $auth  = '';
	return $auth ?: SEQ_LABEL2AUTH[ $asym_id ?: ASYM_ID ][ $label ];
}
*/
//.. _seq_id
/*
struct_site
pdbx_auth_seq_id + pdbx_auth_ins_code
*/
//- pdbx_auth_seq_num は struct_ref_seq_dif のみにある
function _seq_id( $json, $add = '' ){
	return $json->{ $add. 'label_seq_id' } 
		?? SEQ_AUTH2LABEL[ ASYM_ID ][
			$json->pdbx_auth_seq_num . $json->pdbx_pdb_ins_code
			?? $json->pdbx_auth_seq_id. $json->pdbx_auth_ins_code
			?? $json->{ $add. 'auth_seq_id' }. $json->{ $add. 'PDB_ins_code' }
		]
	;
}

//.. _seq_num
function _seq_num( $json, $add = '' ){
	return $json->{ $add. 'auth_seq_id' }. $json->{ $add. 'PDB_ins_code' }
		?? $json->pdbx_auth_seq_num. $json->pdbx_pdb_ins_code
		//- struct_site
		?? $json->pdbx_auth_seq_id. $json->pdbx_auth_ins_code
		?? SEQ_LABEL2AUTH[ ASYM_ID ][ $json->{ $add. 'label_seq_id' } ]
	;
}

//.. _asym_id
function _asym_id( $json, $pre = '' ) {
	return $json->{ $pre. 'label_asym_id' }
		?: CHAIN_ID_2_ASYM_ID[
			$json->pdbx_pdb_strand_id ?: $json->{ $pre. 'auth_asym_id' }
		]
	;
}

//.. _chain_id
function _chain_id( $json, $pre = '' ) {
	return $json->pdbx_pdb_strand_id
		?: $json->{ $pre. 'auth_asym_id' }
		?: ASYM_ID_2_CHIAN_ID[ $json->{ $pre. 'label_asym_id' } ]
	;
}

//.. _selres_btn
function _selres_btn( $o ) {
	extract( $o ); //$jmol, $molmil, $btn_cls, $no_label, $btn_type, $btn_label
	return _btn_popviewer( DID, [
		'btn_type'  => $btn_type ,
		'btn_label'	=> $btn_label ?: ( $no_label ? '' : I_SEL ) ,
		'btn_cls'	=> $btn_cls ?: 'select_btn' ,
		'btn_tip'   => TERM_SELECT_BTN ,
		'jmol'		=> [ 'select', $jmol ?: _molmil2jmol( $molmil ) ] ,
		'molmil'	=> [
			//- 20残基以下なら側鎖表示
			count( $molmil, COUNT_RECURSIVE ) < 30 ? 'focus_res' : 'focus_res_ns' , $molmil
		],
	]);
}

//.. _molmil2jmol
function _molmil2jmol( $molmil ) {
	$ret = [];
	foreach ( $molmil as $asym_id => $r ) {
		foreach( _start_end( $r ) as $s ) {
			list( $start, $end ) = $s;
			$ret[] = ''
				. SEQ_LABEL2AUTH[ $asym_id ][ $start ] .'-'
				. SEQ_LABEL2AUTH[ $asym_id ][ $end ] .':'
				. ASYM_ID_2_CHIAN_ID[ $asym_id ]
			;
		}
	}
	return implode( ' or ', $ret );
}

//.. _chainid_is_issue
function _chainid_is_issue( $chain_id ) {
	global $json_reid;
	if ( ! defined( 'ISSUE_CHAINIDS' ) ) {
		define( 'ISSUE_CHAINIDS', array_fill_keys(
			explode( ',', $json_reid->entp->{ENT_ID}->pdbx_strand_id ) ,
			true
		));
	}
	return $chain_id && ISSUE_CHAINIDS[ $chain_id ];
}

//.. _asymid_is_issue
function _asymid_is_issue( $asym_id ) {
	return ASYM_ID_2_ENT_ID[ $asym_id ] == ENT_ID;
}

/*
//.. _unobserved 見えてる残基か
function _unobserved( $chain_id, $res_id ) {
	global $json;
	if ( ! defined( 'UNOBSERVED_RES' ) ) {
		$u = [];
		foreach ( (array)$json->pdbx_unobs_or_zero_occ_residues as $c )
			$u[ _chain_id( $c ) ][ _seq_id( $c ) ] = true;
		define( 'UNOBSERVED_RES', $u );
	}
	return UNOBSERVED_RES[ $chain_id ][ $res_id ];
}
*/

//.. _load_unp_json
function _load_unp_json() {
	global $json, $plus;
	$json_set = [];
	$ids = [];
	//- PDBx情報
	foreach ( (array)$json->struct_ref as $c ) {
		if ( $c->entity_id != ENT_ID ) continue;
		if ( $c->db_name != 'UNP' ) continue;
		$ids[] = $c->pdbx_db_accession ?: $c->db_code;
	}
	//- Plus情報
	foreach ( (array)$plus->struct_ref as $c ) {
		if ( $c->entity_id != ENT_ID ) continue;
		if ( $c->db_name != 'SIFTS_UNP' ) continue;
		$ids[] = $c->pdbx_db_accession ?: $c->db_code;
	}
	$ids = array_values( array_unique( $ids ) );

	//- json読み込み
	foreach ( $ids as $i ) {
		$json_set[ $i ] = _json_load([ 'unp_json', $i ]);
	}
	return $json_set;
}

//.. _start_end
function _start_end( $in ) {
	sort( $in );
	$ret = [];
	$start = $last = '?';
	foreach ( _uniqfilt( $in ) as $i ) {
		if ( $start == '?' ) {
			$start = $last = $i;
			continue;
		}
		if ( $i != $last + 1 ) {
			//- 数字が飛んだ
			$ret[] = [ $start, $last ];
			$start = $i;
		}
		$last = $i;
	}
	$ret[] = [ $start, $last ];
	return $ret;
}

//.. _name_prep: 名称変換とwikipe情報と分けて
function _name_prep( $name ) {
	$o_wikipe = ( new cls_wikipe )->term( $name );
	$wikipe = $o_wikipe->show();
	$name = ( $wikipe ? IC_WIKIPE : '' )
		. $name
		. _kakko( $o_wikipe->e2j() )
	;
	return [ $name, $wikipe ];
}


//.. _polysac_img
function _polysac_img( $ent_id ) {
	global $json, $polysac_css_id;//, $o_contents;
	$desc = '';
	foreach ( (array)$json->pdbx_entity_branch_descriptor as $b ) {
		if ( $b->entity_id != $ent_id ) continue;
		if ( $b->program != 'GMML' ) continue;
		$desc = $b->descriptor;
	}
	$res = _ezsqlite([
		'dbname' => 'polysac' ,
		'select' => [ 'svg', 'comp', 'pdb' ] ,
		'where'  => [ 'desc', $desc ?: ID. '-'. $ent_id ]
	]);
	if ( $res[ 'svg' ] ) {
		++ $polysac_css_id;
//		$o_contents->test_info([ 'id="'. ID. '-'. $ent_id => 'id="p'. $polysac_css_id ]);
		return _t(
			'object |st:vertical-align: middle; display:inline-block;'
			. 'transform: rotateZ(180deg);'
			,
			strtr( $res['svg'], [
				$res['pdb'] => 'p'. CSSID_PREFIX. '-'. $polysac_css_id
			])
		);
	} else {
		$ret = '';
		foreach ( explode( '|', $res['comp'] ) as $comp )
			$ret .= _chemimg_s( $comp );
		return $ret;
	}
}

//. cls_seqblock
class cls_seqblock {
	protected
		$unobs_blocks = []  ,
		$is_unobs = [] ,
		$seq_block_factor = null ,
		$res_info = [] ,
		$seq = [] ,
		$asym_id
	;

//.. __construct
function __construct() {
	global $json_reid, $json;
	//- init
	$this->seq_block_factor = strlen( strtr(
		$json_reid->entp->{ENT_ID}->pdbx_seq_one_letter_code_can,
		[ "\n" => '', "\r" => '', ' ' => '' ]
	)) / 100;

	//- unobs
	$uo = [];
	foreach ( (array)$json->pdbx_unobs_or_zero_occ_residues as $c ) {
		if ( ! _asymid_is_issue( $c->label_asym_id ) ) continue;
		$uo[ $c->label_asym_id ][] = $c->label_seq_id;
	}
	foreach ( $uo as $asym_id => $ids ) foreach ( _start_end( $ids ) as $se ) {
		list( $start, $end ) = $se;
		-- $start;
		$this->unobs_blocks[ $asym_id ] .= _pop(
			'' ,
			_ul([
				TERM_UNOBS_RES, ''
				. '#'  . ( SEQ_LABEL2AUTH[ $asym_id ][ $start + 1 ] ?: '?' )
				. ' - '. ( SEQ_LABEL2AUTH[ $asym_id ][ $end ] )
				. ' ' . _kakko( ( $end - $start ). TERM_RES )
				,
				_selres_btn([
					'molmil' => [ $asym_id => [ $start - 1 , $end + 1  ] ] ,
					'btn_label' => I_SEL. TERM_SEL_NEIGHBOR
				])
			]) ,
			[
				'type' => 'div' ,
				'trgopt' => ".seq_unobs poptrg|". $this->css( $start, $end )
			]
		);
	}
	foreach ( $uo as $asym_id => $list ) {
		$this->is_unobs[ $asym_id ] = array_fill_keys( $list, true );
	}

	//- 配列文字列準備
	$this->seq = strtr( $json_reid->entp->{ENT_ID}->pdbx_seq_one_letter_code_can, [ ' ' => '' ] );
}

//.. list_item リストアイテムにまとめる
function list_item( $o ) {
	global $o_contents;
	$categ = $asym_id = $text = $pop = null;
	extract( $o );
	$this->asym_id = $asym_id;

	//... 個々のブロック
	$blocks = '';
	$resnum_all = 0;
	$obs_all = $block_info = [];
	foreach ( $this->res_info as $r ) {
		list( $start, $end ) = $r;
		$res_count = $end - $start + 1;
		$obs = [];
		foreach ( range( $start, $end ) as $i ) {
			if ( $this->is_unobs[ $asym_id ][ $i ] ) continue;
			$obs[] = $i;
			$obs_all[] = $i;
		}
		$seq_num = implode( ' - ', array_unique([
			SEQ_LABEL2AUTH[ $asym_id ][ $start ] ,
			SEQ_LABEL2AUTH[ $asym_id ][ $end ]
		]));
		$seq = substr( $this->seq, $start - 1, $end - $start + 1 );
		if ( 50 < strlen( $seq ) )
			$seq = substr( $seq, 0, 20 ). ' ... '. substr( $seq, -20 );
		$blocks .= _pop(
			'',
			$this->info_table([
				'obs' => $obs ,
				'res_count' => $res_count ,
				'table' => [
					'Seq. num.' => "#$seq_num" ,
					'Sequcence' => $seq ,
				]
			]) ,
			[
				'type' => 'div' ,
				'trgopt' => '.seq_block|'. $this->css( $start, $end )
			]
		);
		$res_count_all += $res_count;
		$block_info["#$seq_num"] = $seq;
	}

	//... 全体
	$cnt_obs = count( $obs_all );
	$o_contents->add(
		$categ
		,
		_selres_btn([
			'molmil'	=> [ $asym_id => $obs_all ] ,
			'btn_type'	=> $cnt_obs ? '' : 'button| disabled' ,
			'btn_label'	=> $cnt_obs ? '' : '--' ,
		])
		. _chain_icon( ASYM_ID_2_CHIAN_ID[ $asym_id ] )
		. _div( '.seq_block_outer', 
			_div( '.seq_block_line', '' )
			. $blocks
			. $this->unobs_blocks[ $asym_id ]
		)
		. _pop(
			"[$res_count_all]" ,
			$this->info_table([
				'obs'		=> $obs_all ,
				'res_count'	=> $res_count_all ,
				'table'		=> $block_info
			])
		)
		. ' ' 
		. ( $pop ? _pop( $text, $pop ) : $text )
		,
		ASYM_ID == $asym_id
	);
	$this->molmil = [];
	$this->res_info = [];
}

//.. info_table
function info_table( $a ) {
	//- $obs, $res_count, $table
	extract( $a );
	$cnt_obs = count( $obs );
	$ret = ( $obs ? _selres_btn([
			'molmil' => [ $this->asym_id => $obs ] ,
			'btn_label' => I_SEL. TERM_SELECT_VW ,
		]): '' )
		. _table_2col( array_merge([
			'Num. of res.'	=> $res_count ,
			'Unobserved'	=> $res_count == $cnt_obs ? '' : (
				$res_count - $cnt_obs
				. _kakko( $cnt_obs ? '' :'All residues' )
			) ,
		], $table ) )
	;
	return 5 < count( $table ) ? _div( '.pop_inner', $ret ) : $ret ;
}

//.. add
function add( $in ) {
	if ( $in == null || $in == [ null, null ] ) return;
	list( $start, $end )  = is_array( $in ) ? $in : [ $in, $in ];
	$this->res_info[] = [ $start, $end ];
}

//.. css
protected function css( $start, $end ) {
	$start = _numonly( $start ) ?: 0;
	$end = _numonly( $end ) ?: 0;
	$left = min(
		round( $start / $this->seq_block_factor, 2 ) ?: 0 ,
		100
	);
	$len = min(
		max( round( ( $end - $start ) / $this->seq_block_factor, 2 ), 2 ) ,
		100 - $left
	);
	return "st:left:{$left}%; width:{$len}%";
}
}

//. class cls_contents
class cls_contents {
//- $test_link: jsonviewなどへのリンク
//- $test_info: テスト情報
//- $srcinfo:  情報源リンク
public
	$data      = [] ,
	$ref		= [], 
	$data_less = [] ,
	$srcinfo   = [] ,
	$test_info = [] ,
	$test_link = [] ,
	$show_chain_sel = 'auto' ,
	$num = 0
;

//.. order
function order( $in ) {
	foreach ( $in as $key ) {
		$this->data[ $key ] = [];
		$this->data_less[ $key ] = [];
	}
}

//.. add
function add( $key, $data, $flg = false) {
	$this->data[ $key ][] = $data;
	if ( $flg )
		$this->data_less[ $key ][] = $data;
	return $this;
}

//.. ref
function ref( $key, $ref ) {
	$this->ref[ $key ] = $ref;
	return $this;
}

//.. src
function src( $in ) {
	$this->srcinfo[] = $in;
	return $this;
}

//.. src_cif
function src_cif( $in ) {
	$this->srcinfo[] = IC_L. _pop_ajax( $in, [ 'cifdic', 'q' => $in ] );
	$this->test_link( 'json', $in );
	return $this;
}

//.. test_link
function test_link( $db, $id ) {
	if ( TEST ) {
		$this->test_link[] = $db == 'json'
			? _ab([ 'jsonview', 'pdb.'. ID. ".$id" ], $id )
			: _ab([ $db, $id, ], "$db-$id" )
		;
	}
	return $this;
}

//.. test_info
function test_info( $val, $key = '_' ) {
	if ( TEST )
		$this->test_info[ $key ][] = $val;
	return $this;
}

//.. out
//- キー '_'だけの配列 => リスト、連想配列 => 階層リスト
function out() {

	//... data
	$data = array_filter( $this->data );
	$data_less = array_filter( $this->data_less );
	$flg_many = 15 < count( $this->data, COUNT_RECURSIVE );
	$data =  $flg_many ? ( $data_less ?: $data ) : $data;

	if ( $data['_'] ) {
		//- 単独の配列
		$d = _uniqfilt( $data['_'] );
		$data = count( $d ) == 1 ? $d[0] : _ul( $d );
	} else {
		//- 連想配列
		$o = '';
		foreach ( $data as $key => $ar ) {
			$ar = _uniqfilt( $ar );
			if ( ! $ar ) continue;
			$o .= _t( 'h2|.h_sub', _trep2( $key )
				. ( $this->ref[ $key ] ? _div( '.dettab_ref', $this->ref[ $key ] ) : '' )
			). _ul( $ar );
		}
		$data = $o;
	}

	//... チェーン選択ボタン
	$chain_sel_btn = '';
//	$this->test_info( count( ENT_ID_2_ASYM_ID[ ENT_ID ] ), 'asym_id num' );
	if (
		( $this->show_chain_sel === 'auto' ? $flg_many : $this->show_chain_sel )
		&&
		1 < count( ENT_ID_2_ASYM_ID[ ENT_ID ] )
	) {
		$btns = '';
		foreach ( ENT_ID_2_ASYM_ID[ ENT_ID ] as $asym_id ) {
			$btns .= _btn( 
				$asym_id == ASYM_ID
					? 'disabled:disabled | .shine'
					: "!_dettab.reget('". MODE. "',". ENT_ID. ",'$asym_id')" 
				,
				_chain_icon( ASYM_ID_2_CHIAN_ID[ $asym_id ] )
			);
		}
		$chain_sel_btn = _p( TERM_DISP_CHAIN. ": $btns" );
	}

	//... 出力
	die( ''
		. ( $data && $this->srcinfo
			? _div( '.right', implode( _uniqfilt( $this->srcinfo ) ) )
			: ''
		)
		. $chain_sel_btn
		. ( $data ?: _p( TERM_NO_DATA ) )
		. ( $this->test_link
			? _p( '.red small', 'test: '. _imp( $this->test_link ) )
			: ''
		)
		. ( $this->test_info
			? _div( 'pre', _json_pretty( $this->test_info ) )
			: ''
		)
	);
}

//.. err
function err( $s ) {
	die( TEST ? $s : 'no data' );
}

}

