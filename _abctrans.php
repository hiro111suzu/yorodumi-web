<?php

//. init
define( 'COLOR_MODE', 'ym' );
require( __DIR__. '/common-web.php' );

//.. conf
define( 'RANGE', 50 );

$where = [];
foreach ( [
'IPR000412' ,
'IPR000515' ,
'IPR000522' ,
'IPR002491' ,
'IPR003339' ,
'IPR003439' ,
'IPR003760' ,
'IPR003838' ,
'IPR005586' ,
'IPR005673' ,
'IPR005768' ,
'IPR005770' ,
'IPR005894' ,
'IPR005897' ,
'IPR005948' ,
'IPR005950' ,
'IPR005967' ,
'IPR006060' ,
'IPR007210' ,
'IPR007487' ,
'IPR010065' ,
'IPR011527' ,
'IPR011917' ,
'IPR011980' ,
'IPR012692' ,
'IPR013305' ,
'IPR013456' ,
'IPR013459' ,
'IPR013525' ,
'IPR013563' ,
'IPR014337' ,
'IPR015853' ,
'IPR015854' ,
'IPR015855' ,
'IPR015856' ,
'IPR015863' ,
'IPR017195' ,
'IPR017637' ,
'IPR017783' ,
'IPR017797' ,
'IPR017871' ,
'IPR017908' ,
'IPR017911' ,
'IPR019195' ,
'IPR019196' ,
'IPR019957' ,
'IPR022287' ,
'IPR022498' ,
'IPR023544' ,
'IPR023691' ,
'IPR023693' ,
'IPR026082' ,
'IPR027024' ,
'IPR030159' ,
'IPR030679' ,
'IPR030836' ,
'IPR030921' ,
'IPR030922' ,
'IPR030923' ,
'IPR030970' ,
'IPR032410' ,
'IPR032524' ,
'IPR032781' ,
'IPR033893' ,
'IPR036640' ,
'IPR037294' ,
'IPR037297'
] as $i ) {
	$where[] = "search_kw LIKE \"%in:$i%\"";
}
$sqo = new cls_sqlite( 'pdb' );

$res = $sqo->qobj([
	'select' => 'id, reso, method' ,
	'where' => implode( ' OR ', $where )
]);
//print_r( $ids );
$tsv = [ implode( "\t", [
	'PDBID', 'chain数', '構造決定方法', '解像度', 'HETNAM'
]) ];
$out = '';
foreach ( $res as $o ) {
	$id = 'pdb-' . $o->id;
	$o_id = new cls_entid;
	$qinfo = _json_load2( _fn( 'qinfo', $o->id  ) );
//	_die( $qinfo );
//	PDBID, chain数, 構造決定方法、解像度、HETNAM
	$tsv[] = implode( "\t", [
		$o->id ,
		$qinfo->num_chain ,
		$o->method ,
		$o->reso ,
		_imp( $qinfo->chemid ),
	]);
	$out .= $o_id->set_pdb( $o->id )->ent_item_list();
}
file_put_contents( 'temp.tsv', implode( "\n", $tsv ) );

$_simple->hdiv( count( $res ). ' entries', $out )->out([]);
