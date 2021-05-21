<?php
//.. init

define( 'MET_TAGS', [
	'sas_beam.instrument_name'	=> 'e' ,
//	'sas_beam.type_of_source'	=> 'e' ,
	'sas_p_of_R_details.software_p_of_R' => 's' ,
]);
define( 'FLG_MANY_ENT', false );

_add_lang( 'quick-sasbdb' );
_add_trep( 'quick-sasbdb' );
_add_fn(   'quick-sasbdb' );
_add_url(  'quick-sasbdb' );
_add_unit( _json_load( DN_DATA . '/cif_unit.json.gz' ) );
_add_unit( 'quick-sasbdb' );

define( 'MOLDATA_EX'	, $json->sas_model != '' );

//. json 修正
//.. sas_result.SASBDB_code 消し
foreach ( (array)$json->sas_result as $j )
	unset( $j->SASBDB_code );

//.. sus buffer unit
foreach ( (array)$json->sas_buffer as $c ) {
	if ( $c->concentration && $c->unit ) {
		$c->concentration .= ' '. $c->unit;
		unset( $c->unit );
	}
}

//.. country
foreach ( (array)$json->sas_beam as $c ) {
	if ( $c->instrument_country )
		$c->instrument_country .= _country_flag( $c->instrument_country );
}

//.. いらないID削除
foreach ([
	'sas_sample.entity_id'				=> 'entity' ,
	'sas_buffer.sample_id'				=> 'sas_sample' ,
	'sas_scan.beam_id'					=> 'sas_beam' ,
	'sas_scan.detc_id'					=> 'sas_detc' ,
	'sas_scan.sample_id'				=> 'sas_sample' ,
	'sas_scan.result_id'				=> 'sas_result' ,
	'sas_p_of_R_details.intensity_id'	=> 'sas_result' ,
] as $cat_item => $idkey ) {
	list( $categ, $item ) = explode( '.', $cat_item );
	if ( 1 < count( (array)$json->$idkey ) ) {
//		_testinfo( "$idkey: multi" );
		continue;
	}
	foreach ( (array)$json->$categ as $j ) {
		unset( $j->$item );
	}
}

//.. table
foreach ( (array)$json->sas_result as $j ) {
	$data = [];
	$cat = 'sas_result';
	foreach ( $j as $item => $val ) {
		foreach ([ 'MW', 'volume' ] as $row ) {
			$col = trim( _reg_rep( $item, [ "/$row/" => '', '/_+/' => '_' ] ), ' _' );
			if ( $item == $col ) continue;
			_subtable_in( $data, compact( 'cat', 'item', 'col', 'row', 'val' ) );
		}
	}
	_subtable_out( $j, $data );
}

foreach ([
	'sas_scan',
	'sas_p_of_R_details' ,
	'sas_result'
] as $cat ) foreach ( (array)$json->$cat as $c ) {
	$from = [];
	$minmax = [];
	foreach ( $c as $item => $val ) {
		list( $row, $col ) = explode( '_from_', $item, 2 );
		if ( $col ) {
			_subtable_in( $from, compact( 'cat', 'item', 'col', 'row', 'val' ) );
			continue;
		}
		foreach ( [ 'min', 'max', 'max_error' ] as $col ) {
			if ( substr( $item, -1 * strlen( $col ) ) != $col ) continue;
			$row = trim( _reg_rep( $item, [ '/'. $col. '$/' => ''] ), '_ ' );
			_subtable_in( $minmax, compact( 'cat', 'item', 'col', 'row', 'val' ) );
		}
	}
	_subtable_out( $c, $from, 'from' );
	_subtable_out( $c, $minmax, [ 'min', 'max', 'max_error' ] );
}

//. json単純化
$json_reid = _json_reid([
	'entity'					=> [ 'ent'		, 'id' ],
	'entity_poly'				=> [ 'entp'		, 'entity_id' ] ,
	'pdbx_entity_nonpoly'		=> [ 'entp'		, 'entity_id' ] ,
	'entity_name_com'			=> [ 'entnc'	, 'entity_id' ] ,
	'entity_name_sys'			=> [ 'entns'	, 'entity_id' ] ,

	'entity_src_gen'			=> [ 'src'		, 'entity_id' ] ,
	'entity_src_nat'			=> [ 'src'		, 'entity_id' ] ,
	'pdbx_entity_src_syn'		=> [ 'src'		, 'entity_id' ] ,
	'struct_ref'				=> [ 'ref'		, 'entity_id' ] ,

	'sas_model_fitting_details'	=> [ 'fitdet'	, 'id' ] ,

	'chem_comp'					=> [ 'chem'		, 'id' ] , //- あるか?

]);

//. basic info
//.. entry,  sample
function _f( $s ) {
	return $s . _obj('wikipe')->pop_xx( $s );
}

$inf = [];
foreach ( (array)$json_reid->ent as $eid => $ent ) {
	$a = [ _f( $ent->pdbx_description ) . ' (' . $ent->type . ')' ];
	foreach ([ 'entnc', 'entns', 'src' ] as $s ) {
		foreach ( (array)$json_reid->$s->$eid as $k => $v ) {
			if ( $k == 'entity_id' ) continue;
			$a[] = $s == 'src' ? $v : _f( $v );
		}
	}
	$inf[] = _imp( _uniqfilt( $a ) );
}

$o_data->basicinfo([
	'flg_vis'        => MOLDATA_EX ,
	'flg_link'       => true ,
	'js_open_viewer' => MOLDATA_EX ? "_vw.open('$did')" : '' ,
])

->lev1( 'Sample', _f( $json->sas_sample[0]->name )
	. _ul( $inf )
	. _hdiv_focus( 'sample' )
);

//.. func_homology
$o_data->lev1( 'func_homology', _func_homology() );


//.. source
$a = [];
foreach ( (array)$json_reid->src as $child ) {
	$sn = explode( ',', ''
		. $child->pdbx_gene_src_scientific_name
		. $child->pdbx_organism_scientific
		. $child->organism_scientific
		. $child->gene_src_common_name
		. $child->common_name
		. $child->organism_common_name
	);

	//- 0に正式名、1に通称(array)
	//- コンマ区切りで複数ある奴があるので
	foreach ( $sn as $n => $s ) {
		$s = trim( $s );
		if ( $s == '' ) continue;
		
		$sl = strtolower( $s );
		$a[ $sl ][0] = $s;
		$a[ $sl ][1][] = trim( $cn[ $n ] );
	}
}

$out = [];
foreach ( $a as $a2 ) {
	$sn = $a2[0];
	if ( $sn == '' ) continue;

	$cn = [];
	foreach ( array_unique( array_filter( (array)$a2[1] ) ) as $c ) {
		if ( $c == $sn ) continue;
			$cn[] = $c;
	}
	$cn = _imp2( $cn );
	$out[] = _quick_taxo( $sn );
}

$o_data->lev1( 'Biological species', implode( BR, array_filter( (array)$out ) ) );

//.. citatioin
$o_data->lev1( 'Citation',
	(new cls_citation())->pdb_json( $json )->sasbdb_reid()->output()
);

//.. contact author
$ca = [];
foreach ( (array)$json->pdbx_contact_author as $j) {
	$ad = _imp([
		$j->address_1 ,
		$j->address_2 ,
		$j->address_3
	]);
	$ca[] = implode( ' ', array_filter([
		$j->name_salutation ,
		$j->name_first ,
		$j->name_mi,
		$j->name_last
	]) )
	. _ifnn( $ad, ' (\1)' );
}
$o_data->lev1( 'Contact author', implode( "\n", $ca ) );

//. viewer
_viewer();

//. ダウンロードとリンク
//.. data
$o_data->lev1title( 'downlink', true )
	->lev2( 'SASBDB page', _ab( _url( 'sasbdb', $id ), IC_L . $id ) )
	->end2( 'Data source' )
;

//.. related
$tabs = [];
foreach ( (array)$json->sas_model as $j ) {
	$tabs[] = [
		'ida' => 's'. $j->id ,
		'tab' => _l( 'Model' ). _sharp( $j->id ) ,
	];
}

( new cls_related([]) )
->set_omokage( $tabs[0]['ida'] )
->set_similar( $tabs )
->end();

//.. リンク
$o_data->lev2( 'test', TEST
		? [
			_ab( _url( 'json', DID ), 'JSONview' ) ,
			_ab( _url( 'txtdisp', 'sascif', DID ), 'sasCIF' ) ,
			_ab( 'txtdisp.php?a=sasbdb_kw.' .ID, 'search terms' ) ,
		]: ''
	)
	->lev2( TERM_REL_MOM, _mom_items(  ) )
	->end2( 'External links' )
;

//. models

$btn_label = IC_VIEW. _ej( 'Show in viewer', 'ビューアーで表示' );

$model = [];
$plot = [];
foreach ( (array)$json->sas_model as $j ) {
	$fid = $j->fitting_id;
	$model[ $j-> id ] = [
		'Type'				=> _met_pop( $j->type_of_model, 'm' ) ,
		'Software'			=> _met_pop( $j->software, 's' )
			. _ifnn( $j->version, ' (\1)' ) ,
		'Radius of dummy atoms' => _ifnn( $j->radius, '\1 A' ) ,
		'Symmetry'			=> $j->symmetry ,
		'Comment'			=> $j->comment ,
		'Chi-square value'	=> $json_reid->fitdet->$fid->chi_square ,
		'p-value'			=> $json_reid->fitdet->$fid->p_value ,
	];
	$plot[ $j->id ] = _plot_img(
		[ 'Fitting ID', $j->fitting_id ] ,
		[ 'plot_fit', $fid ] ,
		[ 'sas_model_fitting', 'momentum_transfer', 'intensity', 'fit' ]
	);
}

$o_data->lev1title( 'Models' );
foreach ( $model as $i => $j ) {
	$fn_plot = _fn( 'plot_fit', $i );
	$o_data->lev1( "Model|#$i|sas_model", ''
		. $plot[ $i ]
		. _img( '.mainimg', _fn( 'sasbdb_img', $i ) )
		. _btn_popviewer( "sas-$i", [ 'btn_label' => $btn_label ] )
		. BR
		. _quick_kv( $model[ $i ], [
			'Type'					=> 'sas_model.type_of_model' ,
			'Software'				=> 'sas_model.sotware' ,
			'Radius of dummy atoms' => 'sas_model.radius' ,
			'Symmetry'				=> 'sas_model.symmetry' ,
			'Comment'				=> 'sas_model.comment' ,
			'Chi-square value'		=> 'sas_model_fitting_details.chi_square' ,
			'p-value'				=> 'sas_model_fitting_details.p_value' ,
		])
		. ( $main_id->ex_vq( $i ) ? BR . _omos_link( "sas-$i" ) : '' )
	);
}

//. sample
$o_data->lev1title( 'Sample' );
_cat_prep( 'Sample'			, 'sas_sample' ); 
_cat_prep( 'Sample entity'	, 'sas_sample_entity' ); 
_cat_prep( 'Sample entities', 'sas_sample_entities' );
_cat_prep( 'Buffer'			, 'sas_buffer' );

foreach ( (array)$json_reid->ent as $eid => $child ) {
	$info = [ 'name' => '' ] + _kv_ign( $child,[ 'id' ] );
	$info[ 'name' ] = _imp([
		_f( $info[ 'name' ] ),  //- 多分無いけど
		_f( $json_reid->entnc->$eid->name ) ,
		_f( $json_reid->entns->$eid->name ) ,
	]);

	$info[ 'Source' ] = $json_reid->src->$eid->gene_src_common_name;
	
	//- ref
	$ref = $json_reid->ref->$eid;
	$out = [];
	if ( $ref->db_name == 'UniProt' ) {
		$out[] = $ref->db_code ? _obj('dbid')->pop( 'UniProt', $ref->db_code, '.' ) : '';
	} else if ( $ref->db_name != '' ){
		$out[] = _quick_kv( _kv_ign( $ref, [ 'id', 'entity_id' ] ), '.nw' );
	}

	$info[ 'References' ] = _imp2( $out );

	//- 配列
	$seq = $json_reid->entp->$eid->pdbx_seq_one_letter_code;
	if ( $seq != '' )
		$info[ 'Sequence' ] = strtr( $seq, [ ' ' => '' ]) ;

	$o_data->lev1( "Entity| #$eid|entity", $info );
}

//. plot
$plots = [];
foreach ( $json->sas_scan as $c ) {
	$plot['sas_scan'] = _plot_img(
		[ 'Scan ID', $c->id ] ,
		[ 'plot_scan', $c->id ],
		[ 'sas_scan_intensity', 'momentum_transfer', 'intensity', 'intensity_su_counting']
	);
}
foreach ( (array)$json->sas_p_of_R_details as $c ) {
	$plot['sas_p_of_R_details'] = _plot_img(
		[ 'P(R) ID', $c->id ] ,
		[ 'plot_pr', $c->id ] ,
		[ 'sas_p_of_R', 'R', 'P', 'P_error' ]
	);
	$plot['sas_result'] = _plot_img(
		[ 'P(R) ID', $c->id ] ,
		[ 'plot_prex', $c->id ] ,
		[ 'sas_p_of_R_extrapolated', 'momentum_transfer', 'intensity_reg' ]
	);
}

//. 実験情報
$o_data->lev1title( '_exp_info' );
_cat_prep( 'Beam'		, 'sas_beam' );
_cat_prep( 'Detector'	, 'sas_detc' );
_cat_prep( 'Scan'		, 'sas_scan' );
_cat_prep( '_pr'		, 'sas_p_of_R_details' );
_cat_prep( 'Result'		, 'sas_result' );

unset( $plot );

//. function
//.. _cat_prep
function _cat_prep( $title, $cat, $ign_keys = [ 'id' ] ) {
	global $json, $o_data, $plot, $table_minmax, $table_from;

	$o_data->lev2ign( $ign_keys );
	$num = 1;
	$mult = ( count( (array)$json->$cat ) > 1 );
	foreach ( (array)$json->$cat as $child ) {
		$id = $child->id;
		if ( $id == '' )
			$id = $num;
		++ $num;
		
		//... plot
		if ( $plot[ $cat ] )
			$o_data->lev2( '#div_plot', $plot[ $cat ] );
			
		//... values
		foreach ( (array)$child as $key => $val ) {
			//- 無視するキー
			if ( in_array( $key, 
				[ 'name', 'description', 'pdbx_description', 'type_of_source'] 
			))
				$val = _f( $val );
			//- met
			if ( MET_TAGS[ "$cat.$key" ] )
				$val = _met_pop( $val, MET_TAGS[ "$cat.$key" ] );
			$o_data->lev2( $key, $val, "$cat.$key" );
		}

		//... table
/*
		if ( $table_minmax[ "$cat-$id" ] || $table_from[ "$cat-$id" ] )
			$o_data->lev2( '#div_table',
				implode( BR, [ $table_minmax[ "$cat-$id" ], $table_from[ "$cat-$id" ] ] )
			);
*/
		$o_data->end2( "$title|" . ( $multi ? "|#$id" : '' ) . "|$cat" );
	}
}

//.. _kv_ign:
function _kv_ign( $in, $ign = [] ) {
	$data = [];
	foreach ( (array)$in as $key => $val ) {
//		if ( in_array( $key, $ign ) ) continue;
		if ( $key == 'id' ) continue;
		if ( substr( $key, -3 ) == '_id' ) continue;
		if ( in_array( $key, [ 'pdbx_description', 'name', 'type_of_source'] ) )
			$val = _f( $val );
		$data[ $key ] = $val;
	}
	return $data;
}

//.. function: _plot_img
function _plot_img( $id, $img, $data ) {
	list( $img_type, $img_id ) = $img;
	if ( ! file_exists( _fn( $img_type, $img_id ) ) ) return;
	list( $data_cat, $data_x, $data_y, $data_y2 ) =  $data;
	return _pop(
		_img( '.svgimg left', _url( $img_type, $img_id ) ) ,
		_table_2col([
			$id[0] => $id[1] ,
			'Data' => _cifdic_link( "SAS-CIF category - $data_cat", $data_cat ) ,
			'X' => _cifdic_link( $data_x, "$data_cat.$data_x" ) ,
			'Y' => _imp([
				_cifdic_link( $data_y, "$data_cat.$data_y" ) ,
				$data_y2 ? _cifdic_link( $data_y2, "$data_cat.$data_y2" ): ''
			])
		])
	);
}
