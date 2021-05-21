<?php
class cls_omoid {
public $o_id, $ida, $id, $db, $asb, $imgfile, $info;

//.. construct
function __construct( $id = '' ) {
	$this->set( $id );
	return $this;
}

//.. get
function get() {
	return array_filter([
		'id'	=> $this->ida ,
		'db'	=> $this->db ,
		'asb'	=> $this->asb ,
	]);
}

//.. toString
function __toString() {
	return (string)$this->ida;
}

//.. set
/*
$id  => 1oel, 1003, 100
$ida => 1oel-d, e1003, s100
$db => db (pdb / emdb / sasbdb / user)
*/
function set( $id = '' ) {
	if ( $id == '' || $id == 'get' )
		$id = _getpost( 'id' );
	$id = strtolower( $id );

	//... 前処理、最初の文字でどのDBか分かるようにする
	if ( ctype_digit( $id ) ) {
		//- 数字のみ
		$id = ( strlen( $id ) > 3 )
			? "e$id" //- EMDB
			: "s$id" //- SASBDB
		;
	} 
	$fl = substr( $id, 0, 1 );

	//... ユーザーデータ
	if ( $fl == '_' ) {
		$this->id = $id;
		$this->ida = $id;
		$this->db = 'user';
		$this->info = [
			'Data type' => 'User data' ,
			'ID' => $id
		];

	//... EMDB
	} else if ( $fl == 'e' ) {
		$n = _numonly( $id );
		$this->o_id = ( new cls_entid )->set_emdb( $n );
		$this->ida = "e$n";
		$this->id = $n;
		$this->db = 'emdb';
		$this->imgfile = $this->o_id->imgfile();
		$this->info = [
			'Database'	=> 'EMDB' ,
			'ID'		=> $n
		];

	//... SASBDB
	} else if ( $fl == 's' ) {
		$i = _sas_info( 'id2mid', strtoupper( $id ) );
		//- SASBDB-IDかmodel-ID
		$mid = $i ? $i[0] : _numonly( $id );
		$this->o_id = ( new cls_entid )->set_sasmodel( $mid );
		$this->ida = 's'. $mid;
		$this->id = $mid;
		$this->db = 'sasbdb';
		$this->imgfile = $this->o_id->imgfile();
		$this->info = [
			'Database' 	=> 'SASBDB' ,
			'ID'		=> _sas_info( 'mid2id', $mid ),
			'Model ID'	=> $mid ,
		];

	//... PDB
	} else {
		$a = array_reverse( explode( '-', $id ) );
		$asb = 'd';
		if ( count( $a ) > 1 ) {
			if ( strlen( $a[0] ) < 3 and ctype_digit( $a[0] ) ) {
				$asb = $a[0];
				unset( $a[0] );
				$id = implode( '-', array_reverse( $a ) );
			} else if ( in_array( $a[0], [ 'd', 'asym', 'a' ] ) ) {
				$asb = 'd';
				unset( $a[0] );
				$id = implode( '-', array_reverse( $a ) );
			}
		}
		$id4 = substr( $id, -4 );
		$this->o_id = ( new cls_entid )->set_pdb( $id4 );
		$this->id	= $id4;
		$this->db	= 'pdb';
		$this->asb	= $asb;
		$this->ida	= "$id4-$asb";

		$d = _fn( 'pdbimg_dep', $id );
		$a = _fn( 'pdbimg_asb', $id, $asb );
		$this->imgfile = $asb == 'd'
			? $d
			: ( file_exists( $a ) ? $a : $d )
		;
		$this->info = [
			'Database'	=> 'PDB' ,
			'ID'		=> $id ,
			'Assembly'	=> $asb == 'd'
				? _l( 'Deposited unit' )
				: _l( 'Biological Unit' ). " #$asb"
		];
	}
	return $this;
}

//.. ex_vq
function ex_vq() {
	return file_exists( $this->vqfn() );
/*
	return $this->db == 'user'
		? false
		: file_exists( $this->vqfn() )
	;
*/
}

//.. vqfn
function vqfn() {
	return _fn( $this->db . '_vq50', $this->id, $this->asb );
}

//.. u_quick
function u_quick() {
	return _url( 'ym', $db == 'sasbdb'
		? _sas_info( 'mid2id', $this->id )
		: $this->db . '-' . $this->id
	);
}

//.. title
function title() {
	return $this->db == 'user' 
		? _kv( (array)_json_load( _fn( 'user_json', $this->id ) ) ) 
		: $this->o_id->title()
	;
}

//.. img_link
function img_link( $url ) {
	//- エントリ情報につける画像、アンカー
	return $this->imgfile
		? _ab( $url, _img( '.iimg left' , $this->imgfile ) )
		: '[No image]'
	;
}

//.. desc
function desc() {
	$i = _kv( $this->info );
	return 
		( $this->db == 'user' ? $i : _ab( $this->u_quick(), $i ) )
		. $this->btn_viewer()
		. BR
		. $this->title()
	;
}
	
//.. items_entry ポップアプの中に書く、アイテムリスト
function items_entry() {
	return [
		( $this->db == 'user' ? '' : _ab( $this->u_quick(), TERM_YM_LINK ) ) ,
		TERM_STR_VIEW. ': '. $this->btn_viewer() ,
		_ab( _url( 'omos', $this->ida ), TERM_OMOKAGE_THIS )
	];
}

//.. btn_viewer ビューアのボタン
function btn_viewer() {
	//- movie
	$ret = $this->db != 'user' && $this->o_id->ex_mov()
		? _btn_popmov( $this->o_id->did )
		: ''
	;

	//- str viewer
	if ( $this->db == 'pdb' ) {
		$ret .= _btn_popviewer(
			$this->o_id->did, 
			[ 'molmil' => [ 'asb', $this->asb ] ]
		);
	} else if ( $this->db == 'emdb' && $this->o_id->ex_polygon() ) {
		$ret .= _btn_popviewer( $this->o_id->did );
	} else if ( $this->db == 'sasbdb' ) {
		$ret .= _btn_popviewer( 'sas-' . $this->id );
	}
	return $ret;
}
	
//.. desc2
function desc2() {
	return _pop(
		_kv( $this->info ), 
		_p( '.bld', TERM_DATA_ENTRY ). _ul( $this->items_entry() )
	).BR. $this->title();
}
}
