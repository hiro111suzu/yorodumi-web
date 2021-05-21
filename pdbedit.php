<?php
ini_set('memory_limit', '512M');
$id = strtolower( substr( $_GET[ 'id' ], -4 ) );
if ( $id == '' )
	$id = '100d';
$num = $_GET[ 'num' ];
if ( $num == '0' )
	die( file_get_contents(
		"/kf1/PDBj/ftp/pdbj/pub/pdb/data/biounit/coordinates/all/$id.pdb$num.gz"
	) );

if ( $num == '' )
	$num = 1;
echo preg_replace( 
	array(
		'/^ATOM +[0-9]+ +(CA|P) .+\n/m',
		'/^ATOM.+\n/m',
		'/^_/m',
		'/^(HEADER|TITLE|COMPND|KEYWDS|EXPDTA|AUTHOR|JRNL|REMARK   1 |SEQRES|FORMUL|SITE|HETNAM|HETATM|ANISOU|) .+\n/m'
	),
	array(
		'_\0',
		'',
		'',
		'',
	),
	implode( gzfile(
		"/kf1/PDBj/ftp/pdbj/pub/pdb/data/biounit/coordinates/all/$id.pdb$num.gz"
	) )
);
