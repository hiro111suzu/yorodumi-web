<?php

$id = substr( rtrim( strtolower( $_GET[ 'id' ] . $_GET[ 'PDBID' ] ) ) , -4 );
if ( $id == '' ) $id = '100d';
$init = <<<EOD
set ambientPercent 30; set diffusePercent 90;
set specular ON; set specularPower 80; set specularExponent 5; set specularPercent 60;
set antialiasDisplay ON;
set ribbonBorder ON; select all; cartoon ONLY; color monomer;
set selectHydrogen ON;
set zshade on; set zshadepower 1; set zslab 60;
select rna and dna; ribbon ONLY;
select hetero; wireframe 0.5; spacefill 50%; color CPK;
hide hydrogen or water;select all;
EOD
;

$title = ( strtolower( $_GET[ 'ref' ] ) == 'x' or strtolower( $_GET[ 'REF' ] ) == 'x' )
	? "xPSSS Jmol : $id"
	: "PDBj Mine Jmol : $id"
;
/*
if ( is_dir( '/var/www/html/emnavi' ) ) {
	//- on kw1 ?
	$jmol = 'JmolApplet.jar';
//	$codebase = '../jmol';
} else {
	$jmol = 'JmolAppletSigned.jar';
};
*/
$jmol = 'JmolAppletSigned.jar';
$codebase = is_dir( '_jmol' )
	? '_jmol/jmol-14.2.7/jsmol/java'
	: '../jmol/jsmol/java'
;

?>
<html>
<script language="JavaScript">
function j( s ) { document.jmol.script( 'hide water;' + s ); }
function _defo() { 
	j( 'cartoon ONLY; select rna and dna; ribbon ONLY;'
		+ 'select hetero; wireframe 0.5; spacefill 50%; hide hydrogen or water;select all;' ); 
}
function _c_defo() {
	j( 'color monomer; select hetero; color CPK; select all;' ); 
}
</script>
<head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title;?></title>
<link rel="shortcut icon" href="//pdbj.org/favicon.ico" />
</head>
<body>
<center>
<applet name="jmol" id="jmol" code="JmolApplet" archive="<?php echo $jmol;?>"
	 codebase="<?php echo $codebase;?>" type="application/x-java-applet" width="500" height="500" mayscript="true" />
<param name="progressbar" value="true" />
<param name="progresscolor" value="blue" />
<param name="boxbgcolor" value="black" />
<param name="boxfgcolor" value="white" />
<param name="load" value="PDB:://pdbj.org/pdb_nc/pdb<?php echo $id; ?>.ent" />
<param name="script" value='<?php echo $init;?>' />
</applet>
<br>
<font color="#cc0000">
<b><a target="_blank" href="http://jmol.sourceforge.net/">Jmol</a>  version 14</b>
</font>
<br>
<form name="option_form">
<table>
<tr>
<td align="right">Style:</td>
<td><input type="radio" name="style" onclick="_defo();" checked>Default</td>
<td><input type="radio" name="style" onclick="j('select not hetero;cartoon only; hide hydrogen or water; select all;')">Cartoon</td>
<td><input type="radio" name="style" onclick="j('select not hetero;rocket only; hide hydrogen or water; select all;');">Rocket</td>
<td><input type="radio" name="style" onclick="j('wireframe only;  hide hydrogen or water;');">Wireframe</td>
<td><input type="radio" name="style" onclick="j('cpk only; hide hydrogen or water;');">CPK_without_water</td>
<td><input type="radio" name="style" onclick="j('display all; cpk only; hide hydrogen;' );">CPK</td>
</tr>
<tr>
<td align="right">Color:</td>
<td><input type="radio" name="color" onclick="_c_defo();" checked>Default</td>
<td><input type="radio" name="color" onclick="j('color group')">Group</td>
<!--td><input type="radio" name="color" onclick="j('color monomer')">Monomer</td-->
<td><input type="radio" name="color" onclick="j('color chain')">Chain</td>
<td><input type="radio" name="color" onclick="j('color cpk');">Atom</td>
</tr>
</table>
</form>
</center>
</body>
</html>
