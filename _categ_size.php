<?php 
//. init
ini_set( 'memory_limit', '4056M' );
define( 'COLOR_MODE', 'ym' );
require( __DIR__. '/common-web.php' );

define( 'MODE_PRE', $_GET['pre'] == 1 );

//. エントリID解決
$main_id = new cls_entid( 'get' );
$id = $main_id->id;

$json = _json_load2( _fn( MODE_PRE ? 'pdb_json_pre' : 'pdb_json', $id ) );

$total = _json_size( $json );
$sizes = [];
foreach ( $json as $categ => $data ) {
	$s = round( _json_size( $data ) / $total * 100, 1 );
	if ( 0.3 < $s )
		$sizes[ $categ ] = $s;
}

function _json_size( $j ) {
	return strlen( json_encode( $j ) );
}
arsort( $sizes );
$table = TR_TOP.TH. 'Cagegory' .TH. 'size(%)' .TH. '-' ;
foreach ( $sizes as $categ => $size ) {
	$w = $size * 4;
	$table .= TR.TH. _ab( 'jsonview.php?a=pdb.' . $id .'.'. $categ, $categ )
		.TD. $size .TD. _div( ".bar| st:width:{$w}px", '-' )
	;
}

//. file size
$size_pre = filesize( _fn( 'pdb_json_pre', $id ) );
$size_out = filesize( _fn( 'pdb_json', $id ) );

$size = ''
	. ( MODE_PRE ? '[pre]' : _a( "?id=$id&pre=1", 'pre' ) ) . ': '
	. number_format( $size_pre )
	. ' => '
	. ( MODE_PRE ? _a( "?id=$id", 'out' ) : '[out]'  ) . ': '
	. number_format( $size_out )
	. ' ('
	. round( $size_out / $size_pre * 100, 1 )
	. '%)'
;

//. output
$_simple
->page_conf([
	'title' => 'JSON size' ,
	'icon'	=> 'lk-article.gif' ,
])
->css(<<<EOD
.bar {background: $col_dark; display: inline-block }
EOD
)
->hdiv(
	'PDB entry',
	$main_id->ent_item_list()
)
->hdiv(
	'data', ''
	. _p( $size )
	. _t( 'table', $table )
)
->out()
;

