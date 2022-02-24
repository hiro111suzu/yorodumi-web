<?php
//. init
require( __DIR__ . '/_mng.php' );
define( 'STATUS_JSON', _json_load( DN_PREP. '/emn/status.json.gz' ) );
define( 'FITDB', _json_load( DN_PREP. '/fit_confirmed.json.gz' ) );
$stat = [];

define( 'MET_SHORT', [
	't' => 'Tomo' ,
	'a' => 'Subtomo' ,
	's' => 'SPA' ,
	'h' => 'Helic' ,
	'2' => 'Xtal' ,
]);

$_filenames += [
	'mmcif'	=> DN_FDATA. '/mmcif/<id>.cif.gz' ,
	'pdb'	=> DN_FDATA. '/pdb/dep/pdb<id>.ent.gz'
];

//. mode
define( 'FILTER', _getpost( 'filter' ) ?: 'all' );
$links = [];
foreach ([
	'all' => '全部' ,
	's2_only' => 'S2のみ' ,
	'fit_s2_only' => 'fitがあるs2のみ' ,
] as $key => $name ) {
	$links[] = FILTER == $key
		? _span( '.bld', "[$name]" )
		: _a( "?filter=$key", $name )
	;
}

$_simple->hdiv( 'Mode', _imp2( $links ) );


//. data
$ids = ( new cls_sqlite( 'main' ) )->qcol([
	'where' => [ 'release = '. _quote( _release_date() ), "database = 'EMDB'" ] ,
	'select' => 'id' ,
	'order by' => 'sort_sub DESC'
]);

foreach ( _file( DN_PREP. '/problem/movie-6-check.txt' ) as $line ) {
	$id = _reg_rep( $line, [ '/:.+$/' => '' ] );
	if ( _numonly( $id ) < 1000 ) continue;
	$ids[] = $id;
}
$ids = _uniqfilt( $ids );

$data = [
	'Todo hard' => [] ,
	'Todo' => [] ,
	'Done' => [] ,
];
foreach ( $ids as $id ) {
	$contour = _json_load2([ 'emdb_new_json', $id ])->map->contour[0];
	$mov_json = _json_load([ 'movinfo', $id ]);
	$add_json = _json_load([ 'emdb_add', $id ]);
	$status = STATUS_JSON[ "emdb-$id" ];
	$met = MET_SHORT[ $add_json[ 'met' ] ];

	//.. todo
	$todo = [];
	foreach ( [1, 2] as $num )
		$todo[ "#$num" ] = is_array( $mov_json[ $num ] );

	//- polygon
	if ( $met != 'Tomo'  ) {
		$todo[ 'polygon' ] = $status[ 'pg1' ];
		if ( ( $todo['#1'] || $todo['#2'] ) && !$status[ 'matrix' ] )
			$todo[ 'matrix' ] = false;
	}

	//- fit
	$in_mov = [];
	foreach ( (array)$mov_json as $mi )
		$in_mov = array_merge( (array)$in_mov, (array)$mi[ 'fittedpdb' ] );

	$pdb_id_list = [];
	foreach ( (array)FITDB[ "emdb-$id" ] as $pdb_id ) {
		$pdb_id = strtr( $pdb_id, [ 'pdb-' => ''  ] );
		if ( ! _inlist( $pdb_id, 'epdb' ) ) continue;
		$pdb_id_list[] = $pdb_id;
		$todo[ $pdb_id ] = in_array( $pdb_id, $in_mov );
	}

	//.. info
	$hard = false;

	//- size
	$j = _emn_json( 'mapinfo', "emdb-$id" );
	$xyz = [ $j->MX, $j->MY, $j->MZ ];
	$size = $xyz[0] == $xyz[1] && $xyz[0] == $xyz[2]
		? $xyz[0]. '<sup>3</sup>'
		: implode( ' x ', $xyz )
	;

	$info = [
		'met'	=> _hard( $met, $met == 'Tomo' ) ,
		'size' 	=> _hard( $size, 1000 < max( $xyz ) ) ,
		'sym'	=> //$add_json['sym'] ,
			_hard( $add_json['sym'], $add_json['sym'] == 'I' ) ,
		'reso'	=> $add_json['reso'] ? $add_json['reso']. ' &Aring;': '' ,
		'level' => _kv([
			$contour->source ?: 'XML' => $contour->level ,
			'mov2' => _json_load([ 'movinfo', $id ])[2]['threshold']
		 ])
	];

	//.. pdb
	$pdb = [];
	foreach ( $pdb_id_list as $pdb_id ) {
		$fn = _fn( 'pdb', $pdb_id );
		if ( file_exists( $fn ) ) {
			$flg_cifonly = false;
		} else {
			$fn = _fn( 'mmcif', $pdb_id );
			$flg_cifonly = true;
		}
		$sym = _json_load2([ 'qinfo', $pdb_id ])->sym;
		$h_atom = in_array(
			'H',
			_branch( _json_load2([ 'pdb_json', $pdb_id ]), 'atom_type->symbol' )
		) ? _span( '.red bld', BR. '*H atom' ) : '';
		$size = file_exists( $fn ) ? filesize( $fn ) : 0;
		$pdb[ _ab( "quick.php?id=pdb-$pdb_id", $pdb_id ) ] = _hard(
			_format_bytes( $size ) . _kakko( $flg_cifonly ? 'cif' : 'pdb' ) ,
			$flg_cifonly || 1500000 < $size
		)
		. ( $sym ? SEP. _hard( $sym, $sym == 'icos' ) : '' )
		. $h_atom
		;
	}

	//.. 出力
	$cls = 'Done';
	foreach ( $todo as $val ) {
		if ( $val ) continue;
		$cls = $hard ? 'Todo hard' : 'Todo';
		break;
	}
	if ( $cls != 'Done' ) {
		if ( ( FILTER == 's2_only' || FILTER == 'fit_s2_only' ) && $todo['#2'] ) {
			$cls = 'Pending - #2 done';
		}
		if ( FILTER == 'fit_s2_only' && ! $pdb_id_list ) {
			$cls = 'Pending - no pdb';
		}
	}

	$data[ $cls ][ $id ] = [
		'todo'	=> $todo , 
		'info'	=> array_filter( $info ) ,
		'pdb'	=> $pdb,
	];
}

//.. function
function _hard( $in, $flg = false ) {
	global $hard;
	if ( $flg ) {
		$hard = true;
		$in = _span( '.red', $in );
	}
	return $in;
}


//. table
$idlist = [];
foreach ( $data as $cls => $items ) {
//	if ( ! $items ) continue;
	//- クラスごと
	$table = '';
	foreach ( $items as $id => $info ) {
		//- エントリごと
		$todo = [];
		foreach ( $info['todo'] as $name => $val )
			$todo[] = $val
				? _t( 'strike', $name )
				: _span( '.red bld', $name )
			;

		$table .= TR. TH. ( new cls_entid( 'emdb-'. $id ) )->ent_item_img()
			.TD. _ab( 'pap.php?str='. "e$id", _ic('article'). _l('article') )
			.TD. implode( BR, $todo )
			.TD. _table_2col( (array)$info[ 'info' ] )
			.TD. _table_2col( (array)$info[ 'pdb' ] )
		;
		$idlist[] = $id;
	}

	$_simple->hdiv(
		$cls. _kakko( count( $items ) ?: '0' ) , 
		$items ? _t( 'table', $table ) : '' ,
		[
			'hide' => $cls == 'Done'
		]
	);
}
_json_save( DN_PREP. '/emn/todolist.json', $idlist );
