<?php
/*
以下で共有
quick-pdb
quick-ajax
*/

//. init def
define( 'MLPLUS', _doc_pop( 'mlplus', [ 'label' => _span( '.annot', '*PLUS' ) ]));

//.. _add_url
_add_url( 'common-pdbdet' );
_add_fn(  'common-pdbdet' );

//.. Jmol chain col
define( 'CHAIN_COLOR', [
	"A" => "C0D0FF",
	"B" => "B0FFB0",
	"C" => "FFC0C8",
	"D" => "FFFF80",
	"E" => "FFC0FF",
	"F" => "B0F0F0",
	"G" => "FFD070",
	"H" => "F08080",
	"I" => "F5DEB3",
	"J" => "00BFFF",
	"K" => "CD5C5C",
	"L" => "66CDAA",
	"M" => "9ACD32",
	"N" => "EE82EE",
	"O" => "00CED1",
	"P" => "00FF7F",
	"Q" => "3CB371",
	"R" => "00008B",
	"S" => "BDB76B",
	"T" => "006400",
	"U" => "800000",
	"V" => "808000",
	"W" => "800080",
	"X" => "008080",
	"Y" => "B8860B",
	"Z" => "B22222",
	"0" => "00FF7F",
	"1" => "3CB371",
	"2" => "00008B",
	"3" => "BDB76B",
	"4" => "006400",
	"5" => "800000",
	"6" => "808000",
	"7" => "800080",
	"8" => "008080",
	"9" => "B8860B",
	'*' => 'fff'
]);

define( 'CHAIN_WHITE_IDS', [
	"K" => true,
	"Q" => true,
	"R" => true,
	"T" => true,
	"U" => true,
	"V" => true,
	"W" => true,
	"X" => true,
	"Y" => true,
	"Z" => true,
	"1" => true,
	"2" => true,
	"4" => true,
	"5" => true,
	"6" => true,
	"7" => true,
	"8" => true,
	"9" => true,
]);

//.. _define_term
_define_term(<<<EOD
TERM_EVIDENCE
	[evidence]
	[根拠]
TERM_UNOBS_RES
	[unobserved]
	[未観測残基]
EOD
);

/*
TERM_RESIDUES
	residues
	残基
*/

//. functions
//.. _reso: resolution 
function _reso() {
	global $json;
	return floatval(
		$json->em_3d_reconstruction[0]->resolution ?:
		$json->refine[0]->ls_d_res_high ?:
		$json->reflns[0]->d_resolution_high
	);
}

//.. _chain_color
function _chain_color( $cid ) {
	return 'background:#' . CHAIN_COLOR[ strtoupper( substr( $cid, -1 ) ) ];
}

//.. _chain_icon
function _chain_icon( $cid, $cls = '' ) {
	return _span(
		'.chainicon'
			. ( CHAIN_WHITE_IDS[ strtoupper( substr( $cid, -1 ) ) ] ? ' white' : '' )
		. '| st:' . _chain_color( $cid )
		, 
		$cid 
	);
}
/*
色を調節するときはこれを復活させる
function _temp_chainicon_color() {

	$out = '';
	$samples = '';
	foreach ( CHAIN_COLOR as $cid => $col ) {
		if ( 
			( hexdec( substr( $col, -6, 2 ) ) / 255 ) //- R
			+
			pow( hexdec( substr( $col, -4, 2 ) ) / 255, 2 ) //- G
			+
			pow( hexdec( substr( $col, -2, 2 ) ) / 255, 0.7 )	//- B
		 < 1.5 ) $out .= "\"$cid\" => true," . BR
		;
		$samples .= _chain_icon( $cid );
	}
	_testinfo( _p( $samples ) . $out, 'chainicon color' );
}
*/

//.. _chemimg_s
/*
function _chemimg_s( $chemid ) {
	return _ab( "?id=$chemid",
		_img( '.chemimg_s', _fn( 'chem_img', $chemid ) ) . $chemid
	);
}
*/
function _chemimg_s( $chemid, $opt = [] ) {
	global $json_reid;
	$name = '';
	if ( $json_reid != '' ) {
		if ( is_object( $json_reid->chem->$chemid ) )
			$name = $json_reid->chem->$chemid->name;
	}

	//- ポップアップなし
	if ( $opt[ 'nopop' ] )
		return _div( '.chemimg_s', 
			_img( _fn( 'chem_img', $chemid ) ) . _p( $chemid ) 
		);

	return _pop(
		_img( _fn( 'chem_img', $chemid ) )
		. _p( $opt[ 'snfg' ] ?: $chemid )
		,
		_ab( _url( 'ym', "chem-$chemid" ), "ChemComp-$chemid" )
			. ( $name ? ": $name" : '' )
		,
		[
		 	'type' => 'div' , 
		 	'trgopt' => ".chemimg_s"
		]
	);
}

//.. _chem_ent
function _chem_ent( $chem_id, $chain_id, $seq_num ) {
	global $json_reid, $cid2aid;
	$aid = $cid2aid[ $chain_id ];
	return ( $aid != '' ? _chain_icon_plus( $cid2aid[ $chain_id ] ) : "[$chain_id]" )
		. '-'. $seq_num
		. _chemimg_s( $chem_id, [ 'nopop' ] ) . $json_reid->chem->$chem_id->name
	;
}
//.. _capital: 先頭大文字残りは小文字
//- $arに例外文字列
function _capital( $str, $ar = '' ) {
	$str = strtolower( $str );
	if ( is_array( $ar ) ) {
		$rep = [];
		foreach ( $ar as $c ) {
			$rep[ strtolower( $c ) ] = $c;
		}
		$str = strtr( $str, $rep );
	}
	return ucfirst( $str );
}

//.. _evidence_code - pdbml-plus形式の表記
//- hoge {ECO:000111|PubMed:1111} という文字列を何とかする
function _evidence_code( $in ) {
	if ( !_instr( '{', $in ) )
		return $in . _obj('wikipe')->pop_xx( $in );

	$l = _evidence_code_left( $in );
	return  $l. _obj('wikipe')->pop_xx( $l ) .
		_pop(
			_ej('[evidence]', '[根拠]' ) ,
			_evidence_code_right( $in ) 
		)
	; 
}

//.. _evidence_code_left
function _evidence_code_left( $in ) {
	return trim( preg_replace( 
		[ '/[ \.]*{.+}\.?/', '/[ \.]*{[^}]+$/' ], //- カッコが閉じていないやつもある
		[ '', '' ] ,
		$in 
	) );
}

//.. _evidence_code_right
function _evidence_code_right( $in ) {
	if ( !_instr( '{', $in ) ) return;
	$in = trim( preg_replace( [ '/^.*{/', '/}.*$/' ], [ '', '' ], $in ), '. ' );
	$ret = [];
	foreach ( explode( ',', $in ) as $s ) {
		list( $eco, $ref ) = explode( '|', $s );
		list( $db, $id ) = explode( ':', $ref );
		$ret[] = [ trim( $eco ), trim( $db ), trim( $id ) ];
	}
	return _evidence_code_unp( $ret );
}

//.. _evidence_code_unp
function _evidence_code_unp( $in ) {
	//- ecごとにまとめ
	$data = [];
	foreach ( $in as $raw ) {
		if ( ! implode( '', $raw ) ) continue; //- 多分無いが
		list( $eco, $db, $id ) = $raw;
		//- ref
		if ( $db == 'PDB' ) {
			$data[ $eco  ][] = strtolower( "$db-$id" ) == DID
				? 'This PDB entry'
				: _ab( "?id=pdb-$id", "PDB-$id" )
			;
		} else if ( $db == 'Pfam' || $db == 'PROSITE' ) {
			$data[ $eco ][] = _obj('dbid')->pop( $db, $id );

		} else { //- others
			$data[ $eco ][] = $ref = $db && $id ? _dblink( $db, $id ) : $db.$id;
		}
	}
	if ( ! $data ) return; //- 多分無いが
	$ret = [];
	foreach ( $data as $eco => $refs ) {
		$ret[] = implode( ': ', [
			$eco ? _ab( _url( 'quick_go', $eco ) ,
				_json_cache( _fn( 'evcode_json' ) )->{$eco}
				?: $eco
			): ''
		,
			_imp( $refs )
		]);
	}
	return _span( '.bld', _l( 'Evidence' ) ) . _ul( $ret );
}

