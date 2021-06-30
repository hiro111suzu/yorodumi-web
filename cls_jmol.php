<?php
class cls_jmol {

//. jsobj
//- jsオブジェクト文字列を作って返す
function jsobj( $a ) {
	extract( $a ); //- $hq, $size, $use, $init, $jmolid, $autostart, $db, $id
	$q = $hq
		? 'set antialiasDisplay ON; set antialiasTranslucent ON;set ribbonBorder ON;'
		: 'set antialiasDisplay OFF; set antialiasTranslucent OFF;set ribbonBorder OFF;'
	;
	$size = $size ?: 250;
	$jmolid = $jmolid ?: '0';

	$obj = json_encode([
		'width'		=> $size ,
		'height'	=> $size ,
		'color'		=> 'white' ,
		'use'		=> $use ?: 'JAVA HTML5' ,
		'isSigned'	=> true ,
		'jarFile'	=> 'JmolAppletSigned.jar' ,
		'j2sPath'	=> JMOLPATH. '/j2s' ,
		'jarPath'	=> JMOLPATH. '/java' ,
		'serverURL' => JMOLPATH. '/php/jsmol.php' ,
		'script'	=> ''
			. 'set ambientPercent 20; set diffusePercent 70;'
			. 'set specular ON; set specularPower 80; set specularExponent 5;'
			. 'set specularPercent 70;'
			. $q
			. 'set MessageCallback "_jmolmsg";'
			. 'set languageTranslation OFF;'
			. ( $id ? $this->loadcmd( $db, $id ) : '' )
			. $init
	]);

	if ( $autostart )
		return _t( 'script',
			'Jmol._alertNoBinary = false;' .
			"Jmol.getApplet('jmol$jmolid', $obj);" 
		);
	else
		return 
			'Jmol.setDocument(0);' .
			'Jmol._alertNoBinary = false;' .
			"Jmol.getApplet('jmol$jmolid', $obj);"
		;
}

//. loadcmd
function loadcmd( $db, $id, $opt = [] )  {
	return implode( ';', $this->params( $db, $id, $opt ) );
}

//. params
function params( $db, $id, $opt = [] ) {
	if ( $db == '' ) return;
	$d = URL_DATA;
	$zshade = 'set zshade on; set zshadepower 1;';

	//... chem
	if ( $db == 'chem' ) {
		return [
			'load'	=> "load \"$d/chem/cif/$id.cif.gz\"" ,
			'init'	=> 'select all; wireframe 0.25; spacefill 33%; color CPK; rotate best;$zshade'
		];
	}

	//... EMDB
	if ( $db == 'emdb' ) {
		$dn = "emdb/media/$id/ym";
		$insideout = file_exists( DN_DATA. "/$dn/insideout1" ) ? 'insideout' : '';
		return [
			'load' 	=> "load \"$d/$dn/pg1.pdb\"" ,
			'init'	=> "isosurface s1 $insideout file \"$d/$dn/o1.zip|o1.jvxl\";"
				. " isosurface s1 OPAQUE [x77ee77];$zshade"
		];
	}

	//... VQ
	if ( $db == 'vq' ) {
		//- vq idは ida形式 1oel-1, e1003, s100
		$f = substr( $id, 0, 1 );
		if ( $f == 'e' ) {
			$id = _numonly( $id );
			$u1 = URL_DATA. "/emdb/vq/$id-30.pdb";
			$u2 = URL_DATA. "/emdb/vq/$id-50.pdb";
		} else if ( $f == 's' ) {
			$id = _numonly( $id );
			$u1 = URL_DATA. "/sas/vq/$id-vq30.pdb";
			$u2 = URL_DATA. "/sas/vq/$id-vq50.pdb";
		} else {
			$u1 = URL_DATA. "/pdb/vq/$id-30.pdb";
			$u2 = URL_DATA. "/pdb/vq/$id-50.pdb";
		}
		return [
			'load' => "load append \"$u1\";load append \"$u2\";" ,
			'init' => $zshade
		];
	}


	//... SASBDB
	if ( $db == 'sasbdb-model' ) {
		$j = _json_load2( _fn( 'sas_json', _sas_info( 'mid2id', $id ) ) )->sas_model;

		//- ダミー原子なら CPK
		$init = 'spacefill only; color CPK;';;
		foreach ( $j as $c ) {
			if ( $c->id != $id ) continue;
			if ( $c->type_of_model == 'atomic' )
				$init = $this->init_style( 'chain' );
			break;
		}
		$u = URL_DATA. "/sas/splitcif/$id.cif";
		return [
			'load' 	=> "load \"$u\"" ,
			'init'	=> $init. $zshade
		];
	}

	if ( $db == 'sasbdb' ) {
		$j = _json_load2( _fn( 'sas_json', $id ) )->sas_model[0];
		$mid = $j->id;
		$init = ( $j->type_of_model == 'atomic' )
			? $this->init_style( 'chain' )
			: 'spacefill only; color CPK;'
		;
		$u = URL_DATA. "/sas/splitcif/$mid.cif";
		return [
			'load' 	=> "load \"$u\"" ,
			'init'	=> $init. $zshade
		];
	}

	//... PDB

	if ( $opt[ 'csmodel' ] ) {
		return [
			'load' => "load \"csmodel.php?id=$id". _ifnn( $opt[ 'asb' ], '-\1' ). '"',
			'init' => $init. '; trace 1000 only;  color chain; model all;'.  $zshade
		];
	}


	$filt = [];
	if ( $opt[ 'asb' ] != '' )
		$filt[] = 'biomolecule '. $opt[ 'asb' ];
	if ( $opt[ 'bb' ] )
		$filt[] = '*.CA,*.P';
	$filt = count( $filt ) == 0
		? ''
		: ' filter "'. implode( ',', $filt ). '"'
	;

	$init = $this->init_style(
		$db == 'pdb-mono' ||
		$db == 'pdb-chain' ||
		_inlist( $id, 'large' ) ||
		_inlist( $id, 'multic' )
		? 'monomer'
		: 'chain'
	);

	return [
		'load' 	=> "load \"". _url( 'mmcif_download', $id ). "\"" ,
		'init'	=> $init. $zshade
	];

}

//. inti_style
function init_style( $color = 'chain' ) {
	if ( $color != 'chain' )
		$color = 'monomer';
	return 	''
		. 'define _nonpoly ligand or solvent or ((dna or rna) and hetero);'
		. 'define _carbon_etc (carbon | (*.P & ! ligand) | (backbone & (dna|rna)) );'
		. "select !unk; cartoon ONLY; "
		. "select connected(0,0) and (!hetero);cpk 70%; backbone 200;"
		. "select (unk and !sidechain); wireframe 0.3;cpk 50%; backbone 200; color $color;"
		. "select !unk; color $color; color (! _carbon_etc) CPK; "
		. 'select _nonpoly; wireframe 0.25; spacefill 33%; color CPK; '
		. 'hide water;'
		. 'select all;'
	;
}

}
