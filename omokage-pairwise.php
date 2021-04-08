<?php
require( __DIR__. '/common-web.php' );
require( __DIR__. '/omo-web-common.php' );
require( __DIR__. '/omo-calc-common.php' );

//.. ID決定
$o_ids = [];
$in = preg_split( "/[,.| :\t]+/", trim( _getpost( 'id' ) ), 2 );
foreach ( [0, 1] as $n ) {
	$o_ids[ $n ] = new cls_omoid( $in[ $n ] );
}

//.. sqlite db
_mkdir( '/tmp/emn' );

if ( TESTSV ) {
	$tmpdn = '_temp';
	$dbfn = DN_DATA . '/profdb_s.sqlite';
} else {
	_mkdir( 'temp' );
	$tmpdn = 'temp';
	exec( 'php clean-dir.php temp 100 > /dev/null &' );
	exec( 'php clean-dir.php /tmp/emn 20 > /dev/null &' );

	$dbfn = DN_DATA . '/profdb_s.sqlite';
}

//. データ取得
$prof = [];
//$info = [];
$psiz = [];
$sq_prof = new cls_sqlite( 'profdb_s' );

$compos = [];
$sq_compos = new cls_sqlite( 'profdb_k' );
foreach ( $o_ids as $n => $o ) {
	//- DB data
	$prof[ $n ] = _bin2prof( 
		$sq_prof->q([ 'select' => 'data', 'where' => "id is \"{$o->ida}\"" ])
			->fetchColumn() 
	);
	$compos[ $n ] = _bin2compos(
		$sq_compos->q([ 'select' => 'compos', 'where' => "id is \"{$o->ida}\"" ])
			->fetchColumn() 
	);
}

//. スコア
$score = -1;
if ( count( $prof[0] ) > 0 and count( $prof[1] ) > 0 ) {
	$score = _getscore( $prof[0], $prof[1] ); 
}
$pvaltable = _json_load( DN_DATA. '/omo_pval.json.gz' );
if ( $score > -1 )
	$pval = $pvaltable[ floor( $score * 10000 ) ];

die( $score );
