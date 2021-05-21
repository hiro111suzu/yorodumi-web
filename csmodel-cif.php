<?php
//. init
require( __DIR__. '/common-web.php' );
ini_set( "memory_limit", "16384M" );
ini_set( "max_execution_time", 600 );
$_id = 0;

//ob_start(); //-----------------------------------------------

//.. file open
$fn = _fn( 'mmcif', _getpost_safe( 'id' ) );
if ( ! file_exists( $fn ) )
	die( "No mmcif file for $id" );
$_fp = gzopen( $fn, 'r' );

$_out = [];
_out( fgets( $_fp ) ); //- data_xxxx 行
_out( fgets( $_fp ) ); //- # 行

//.. initmisc
$_categ = '';

$_reqcategs = [
	'entry' ,
	'database_2' ,
];


//. main
$atomf = false;
foreach ( range( 0, 10000 ) as $ln ) { //- foreach categ
	if ( $ln > 5000 )
		_die( 'おかしい' );

	//.. categ名取得
	$_buf =[];
	$l = _bufline();
	if ( trim( $l ) == 'loop_' )
		$l = _bufline();

	$a = explode( '.', $l, 2 );
	$categ = substr( $a[0], 1 );

	//.. atom_site
	if ( $categ == 'atom_site' ) {
		if ( $atomf ) continue;
		$atomf = true;
		_atom();
		continue;
	}

	//.. それ以外のカテゴリ
	$flg = in_array( $categ, $_reqcategs );
	foreach ( range( 0, 10000 ) as $othercnt ) {
		$l = _getline();
		if ( $flg ) _out( $l );
		if ( trim( $l ) == '#' || $l == '' ) break;
		if ( $othercnt > 5000 ) _die( '変だ: '. $l );
	}
	
	

}

//. function
//.. _atom
function _atom() {
	global $_buf, $_fp;
	$num = 0;
	$idindex = 1;
	$k2n = [];
	$n2k = [];
	$totalcnt = 0;

	//- k2n
	while (true) {
		$l = _next();
//		_out( "test: $l " . implode( '/', $_buf ) );
		if ( trim( $l ) == 'loop_' ) {
			_out( $l );
			continue;
		}

		//- カラム名行が終わった
		if ( substr( $l, 0, 1 ) != '_' ) {
			break;
		}

		//- カラム名行
		_out( $l );
		_ok();
		$a = explode( '.', $l, 2 );
		$n2k[ $num ] = trim( $a[1] );
		if ( trim( $a[1] ) == 'id' )
			$idindex = $num;
		++ $num;
	}

	$asymid = '';
	//- 全asym-id
	foreach ( range( 0, 10000 ) as $c1 ) {
		$atoms = [];
		$lines = [];
		//- asym-idごと
		
		foreach ( range( 0, 100000 ) as $c2 ) {

			$l = _next();

			
			//- atom_siteカテゴリ 終わり
			if ( trim( $l ) == '#' || $l == '' ) {
				_chain( $lines, $atoms, $idindex ); 
				_ok();
				return;
			}

			$ar = preg_split( '/ +/', $l, count( $n2k ) );
			extract( array_combine(
				$n2k, $ar
			) );

			//- asym切り替わり
			$nextasym = $label_asym_id;
			if ( $asymid == $nextasym || $asymid == '' ) {
				_ok();
				$asymid = $nextasym;
				
				//- 原子情報読み込み
				if ( $group_PDB != 'ATOM' ) continue;
				if ( $label_atom_id != 'CA' && $label_atom_id != 'P' ) continue;

				$lines[ $id ] = $ar;
				$atoms[ $id ] = [
					$Cartn_x ,
					$Cartn_y ,
					$Cartn_z
				];
				continue;
			} else {
				$asymid = $nextasym;
				break;
			}
		}
		_chain( $lines, $atoms, $idindex );
	}
}

//.. _chain
function _chain( $lines, $atoms, $idindex ) {
	global $_id;
	if ( count( $atoms ) == 0 ) return;
//	_out( $atoms );

	//- 重心
	$sum = [];
	foreach ( $atoms as $atom ) {
		$sum[0] += $atom[0];
		$sum[1] += $atom[1];
		$sum[2] += $atom[2];
	}
	$ac = count( $atoms );
	$cent = [
		$sum[0] / $ac ,
		$sum[1] / $ac ,
		$sum[2] / $ac ,
	];
		
	//- 一番遠い、一番近い
	$id1 = '';
	$id2 = '';
	$max = 0;
	$min = 10000000000;
	foreach ( $atoms as $id => $crd ) {
		$d = _udist( $cent, $crd );
		if ( $max < $d ) {
			$max = $d;
			$id1 = $id;
		}
		if ( $d < $min ) {
			$min = $d;
			$id2 = $id;
		}
	}

	//- 2つ目
	$id3 = '';
	$max = 0;
	foreach ( $atoms as $id => $crd ) {
		$d = _udist( $atoms[ $id1 ], $crd );
		if ( $max < $d ) {
			$max = $d;
			$id3 = $id;
		}
	}
//	_out( "id1: $id1" );
//	_out( "id2: $id2" );
//	_out( "id3: $id3" );
	++ $_id;
	$lines[ $id1 ][ $idindex ] = $_id;
	++ $_id;
	$lines[ $id2 ][ $idindex ] = $_id;
	++ $_id;
	$lines[ $id3 ][ $idindex ] = $_id;
	_out( implode( ' ', $lines[ $id1 ] ) );
	_out( implode( ' ', $lines[ $id2 ] ) );
	_out( implode( ' ', $lines[ $id3 ] ) );
}

function _udist( $c1, $c2 ) {
	return
		pow( $c1[0] - $c2[0], 2 ) +
		pow( $c1[1] - $c2[1], 2 ) +
		pow( $c1[2] - $c2[2], 2 )
	;
}



//.. _next
function _next() {
	global $_fp, $_buf;
	if ( count( $_buf ) > 0 ) {
		return array_shift( $_buf );
	}

	$l = trim( gzgets( $_fp ), "\r\n" );
	$_buf = [ $l ];
	return $l;
}

function _ok() {
	global $_buf;
	$_buf = [];
}


//.. _bufline
function _bufline() {
	global $_buf;
	$l = _getsub();
	$_buf[] = $l;
	return $l;
}

//.. _getline
function _getline() {
	global $_buf;
	if ( count( $_buf ) > 0 )
		return array_shift( $_buf );
	else
		return _getsub();
}

//.. _getsub
function _getsub() {
	global $_fp;
	$l = trim( gzgets( $_fp ), "\r\n" );
	if ( $l == '' )
		_end();
	return $l;
}

//.. _out
function _out( $s ) {
//	echo "$s\n";
	if ( is_array( $s ) )
		$s = print_r( $s, true );
	echo "$s\n";
}

//. _end 
function _end() {
	die();
}

//. old


/*

$flg_model = false;
$modelid = 0;
foreach ( explode( "\n", _gzload( $fn ) ) as $line ) {
	if ( _getval(1, 5) == 'MODEL' ) {
		$modelid = _getval(7, 20);
		$flg_model = true;
	}

	if ( _getval(1, 6) != 'ATOM' ) continue;
	$atype = _getval(13, 16);
	if ( $atype != 'CA' && $atype != 'P' ) continue;

	$models[ $modelid ][ _getval(22, 22) ][] = [
		_getval( 31, 38 ) ,
		_getval( 39, 46 ) ,
		_getval( 47, 54 ) ,
	] ;
}

$out = '';
foreach ( $models as $modelid => $chains ) {
	$aid = 0;
	$con = '';
	if ( $flg_model )
		$out .= 'MODEL ' . _lenstr( $modelid, 7, 14 ) . "\n";
	foreach ( $chains as $chainid => $atoms ) {
	//	_pause( "$chainid:" . count( $atoms ) );
		//- 重心
		$sum = [];
		foreach ( $atoms as $atom ) {
			$sum[0] += $atom[0];
			$sum[1] += $atom[1];
			$sum[2] += $atom[2];
		}
		$ac = count( $atoms );
		$cent = [
			$sum[0] / $ac ,
			$sum[1] / $ac ,
			$sum[2] / $ac ,
		];
		
		//- 一番遠い
		$atom1 = [];
		$atom2 = [];
		$max = 0;
		$min = 10000000000;
		foreach ( $atoms as $atom ) {
			$d = _udist( $cent, $atom );
			if ( $max < $d ) {
				$max = $d;
				$atom1 = $atom;
			}
			if ( $d < $min ) {
				$min = $d;
				$atom2 = $atom;
			}
		}

		//- 2つ目
		$atom3 = [];
		$max = 0;
		foreach ( $atoms as $atom ) {
			$d = _udist( $atom1, $atom );
			if ( $max < $d ) {
				$max = $d;
				$atom3 = $atom;
			}
		}
		
		++ $aid;
		$out .= _linerep( 1, $chainid, $aid, $atom1 ) . "\n";
		++ $aid;
		$out .= _linerep( 2, $chainid, $aid, $atom2 ) . "\n";
		++ $aid;
		$out .= _linerep( 3, $chainid, $aid, $atom3 ) . "\n";
		$con .= strtr( "CONECT <1> <2>\n", [
			'<1>' => _lenstr( $aid - 2, 4 ),
			'<2>' => _lenstr( $aid - 1, 4 )
		])
		. strtr( "CONECT <1> <2>\n", [
			'<1>' => _lenstr( $aid - 1, 4 ),
			'<2>' => _lenstr( $aid    , 4 )
		]);
	}
	$out .= $con;
	if ( $flg_model )
		$out .= "ENDMDL\n";
}

//file_put_contents( "msm_$id.pdb", $out );
die( $out );

//. func: _udist
function _udist( $c1, $c2 ) {
	return
		pow( $c1[0] - $c2[0], 2 ) +
		pow( $c1[1] - $c2[1], 2 ) +
		pow( $c1[2] - $c2[2], 2 )
	;
}

function _linerep( $resid, $cid, $atomid, $atom ) {
	return strtr( 'ATOM    <aid> CA   XXX <cid>   <resid>     <x> <y> <z>           0           C',
		[
			'<resid>'	=> _lenstr( $resid, 1 ) ,
			'<aid>'		=> _lenstr( $atomid, 3 ),
			'<cid>'		=> _lenstr( $cid, 1 ),
			'<x>'		=> _lenstr( $atom[0], 7 ),
			'<y>'		=> _lenstr( $atom[1], 7 ),
			'<z>'		=> _lenstr( $atom[2], 7 ),
		]
	);
}

function _lenstr( $s, $num ) {
	if ( strlen( $s ) < $num )
		return substr( '          ' . $s, $num * -1 );
	else 
		return substr( $s, 0, $num ) ;
}

function _getval( $n1, $n2 ) {
	global $line;
	return trim( substr( $line, $n1 - 1, $n2 - $n1 + 1 ) );
}


/*
ATOM   2930  CA  TYR A 642     262.834 250.562 154.631  1.00255.64           C  
ATOM      1  CA  XXX X   1      95.698  52.454  12.984           0           C
ATOM      1  CA  XXX A   1      95.698  52.454  12.984           0           C
*/
