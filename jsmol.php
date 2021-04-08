<?php
//. php function
//.. _t() misc tag
$tagrep_in  = [ '/^#/', '/^\./' , '/^\?/' , '/^st:/', '/^!/'    , '/^([a-z]+):(.+)/' ];
$tagrep_out = [	'id:' , 'class:', 'title:', 'style:', 'onclick:', '$1="$2"'          ];

function _t( $tag, $str = '' ) {
	global $tagrep_in, $tagrep_out;
	$ar = preg_split( '/ *\| */', trim( $tag ), 0, PREG_SPLIT_NO_EMPTY );
	return '<'
		. implode( ' ', preg_replace( $tagrep_in, $tagrep_out, $ar ) )
		. ">$str</{$ar[0]}>";
}

//. init
$id = substr( rtrim( strtolower( $_GET[ 'id' ] . $_GET[ 'PDBID' ] ) ) , -4 );
if ( $id == '' )
	$id = '100d';

$init = <<<EOD
set ambientPercent 30; set diffusePercent 90;
set specular ON; set specularPower 80; set specularExponent 5; set specularPercent 60;
select all; cartoon ONLY; color monomer;
set selectHydrogen ON;
set zshade on; set zshadepower 1; set zslab 60;
select rna and dna; ribbon ONLY;
select hetero; wireframe 0.5; spacefill 50%; color CPK;
hide hydrogen or water;select all;
EOD
;
/*
set antialiasDisplay ON;
set ribbonBorder ON; 
*/

$jsmoldn = is_dir( '_jmol' )
	? '_jmol/jmol-14.0.13/jsmol'
	: '../jmol/jsmol'
;

$use = strtolower( $_GET[ 'use' ] );
if ( stripos( $use, 'ja' ) !== false )
	$use = 'JAVA';
else if ( stripos( $use, 'gl' ) !== false )
	$use = 'WebGL';
else
	$use = 'html5';

define( 'VIEWER', $use );



$i = "pdb$id.ent";
$pdbpath = is_dir( '_pdb' )
	? "_ungz.php?i=$id" 
	: ( VIEWER == 'jsmol'
		? "../pdb_nc/$i"
		: "../pdb_all/$i.gz"
	)
;

$jmolobj = json_encode([
	'width'		=> 400 ,
	'height'	=> 400 ,
	'use'		=> VIEWER, //JSMOL ? 'html5': 'JAVA' ,
	'isSigned'	=> true ,
	'jarFile'	=> 'JmolAppletSigned.jar' ,
	'j2sPath'	=> "$jsmoldn/j2s" ,
	'jarPath'	=> "$jsmoldn/java" ,
	'script'	=> "load \"PDB::$pdbpath\"; $init"
]);

//. output
echo ''

//.. head
. '<!DOCTYPE HTML><html><head>'
. '<meta http-equiv="content-type" content="text/html; charset=UTF-8">'
. _t( 'title', "Jmol/JSmol @PDBj Mine - PDB-$id" )
. _t( 'style', <<<EOD
#jmolbox{ border: 1px solid gray; width: 400px; height: 400px; }

EOD
)
. _t( "script | src:$jsmoldn/JSmol.min.js", '' ) 
. ( VIEWER == 'WebGL' ? ''
	. _t( "script | src:$jsmoldn/js/JSmolThree.js", '' ) 
	. _t( "script | src:$jsmoldn/js/JSmolGLmol.js", '' ) 
: '')

//.. js
. _t( 'script', <<<EOD

function j( s ) { document.jmol.script( 'hide water;' + s ); }
function _defo() { 
	j( 'cartoon ONLY; select rna and dna; ribbon ONLY;'
		+ 'select hetero; wireframe 0.5; spacefill 50%; hide hydrogen or water;select all;' ); 
}
function _c_defo() {
	j( 'color monomer; select hetero; color CPK; select all;' ); 
}

EOD
)
. '</head><body>'
. _t( 'form|method:get|action:',  ''
	. _e( "input|type:text|name:id|size:10|value:{$id}" )
	. _t( 'select|name:use', ''
		. _t( 'option | value:ja' . ( VIEWER == 'JAVA'  ? '|selected:selected' : '' ), 'JAVA' )
		. _t( 'option | value:js' . ( VIEWER == 'html5' ? '|selected:selected' : '' ), 'html5' )
		. _t( 'option | value:gl' . ( VIEWER == 'WebGL' ? '|selected:selected' : '' ), 'WebGL' )
	)
	. _t( 'input|type:submit|value:Ok'  )
)
. _t( 'p', "PDB-$id / Jmol in $use mode" )
	
. _t( 'div | #jmolbox', ''
	. _t( 'script', "var jmolApplet0=Jmol.getApplet('jmolApplet0', $jmolobj)" )
)
. '</body></html>'
;
