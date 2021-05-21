<?php
ini_set('memory_limit', '512M');
$u = $argv[ 1 ];
//if ( $u == '' )
//	$u = '100d.pdb1.gz';
copy( $u, 'temp.gz' );
echo implode( gzfile( 'temp.gz' ) );

echo $u;
