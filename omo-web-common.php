<?php
/*

*/
//. misc define
define( 'DN_OMO', TESTSV
	? realpath( __DIR__ . '/../omokage' )
	: '/filesv3/yorodumi/omokage'
);
define( 'ADD_PRE', TESTSV ? '_pre' : '' );
define( 'DN_GMFIT_BIN', '../gmfit/cgi-bin' );

//.. subdata
_add_lang( 'omokage' );
_add_fn(   'omokage' );

$icon = _ic( 'omokage' );
_define_term( <<<EOD
TERM_GMFIT_LINK
	3D structure fitting by gmfit
	gmfitによる立体構造あてはめ
TERM_YM_LINK
	Detail information (Yorodumi)
	構造データの詳細情報(万見)
TERM_STR_VIEW
	Structure visualization
	構造の表示
TERM_OMOKAGE_THIS
	{$icon}Omokage search for this structure
	{$icon}この構造データでOmokage検索
TERM_UNDER_PREP
	Data of gmfit for this entry is under preparation
	このエントリのgmfitのデータは準備中です
TERM_DATA_ENTRY
	Data entry
	データエントリ
TERM_STR_COMPARISON
	Structure comparison
	構造比較
TERM_DO_FITTING
	Comparison details and structure fitting by gmfit
	gmfitによる比較の詳細と構造のフィッティング
TERM_DETAIL_OMOCOMP
	Omokage comparison details
	Omokage比較の詳細
TERM_SUBJECT_STR
	Subject structure
	検索の基準とする構造
TERM_CC
	corr. coeff.
	総関係数
TERM_SEARCH_FILTER
	Filters to narrow the search result
	検索結果を絞り込むためのフィルター
TERM_ASB_IMGS
	Assemblies of PDB-_1_
	PDB-_1_の集合体
TERM_SASMODEL_IMGS
	Models of _1_
	_1_のモデル
TERM_KW_SEARCH
	Keyword search for "_1_"
	「_1_」をキーワード検索
TERM_STATUS_ANALYSIS
	Status of analysis
	解析の進捗
TERM_KW_THIS_ENT
	Related keywords for this data entry
	このデータエントリに関連するキーワード
TERM_DBID_FILT
	Filter serarch result by this property
	この属性で検索結果を絞り込み
TERM_INC_FILTER
	Include-filter
	含むもののみ
TERM_EXC_FILTER
	Exclude-filter
	含まないもののみ
EOD
);

//. function
//.. _filter_form
function _filter_form( $opt = [] ) {
	$no_kw_suggest = false;
	extract( $opt );
	return _t( 'form| #form_filt', ''
		. _p( TERM_SEARCH_FILTER )
		. _table_2col([
			'Databases' => _radiobtns([
				'name'	=> 'mode_db',
				'on'	=> MODE_DB ?: 'all' ,
			], [
				'all'	=> 'All' ,
				'e'		=> 'EMDB' ,
				'p'		=> 'PDB' ,
				's'		=> 'SASBDB' ,
			]) ,

			'Composition similarity' => ( DB != 'pdb' ? '' :
				_radiobtns([
					'name'	=> 'mode_compos',
					'on'	=> MODE_COMPOS ?: 'no' ,
				], [
					'no'	=> 'none' ,
					'sim'	=> 'similar only'  ,
					'dis'	=> 'different only' ,
				])
			) ,

			'Keywords' =>
				_input(
					'search' ,
					'#inp_filtkw|.acomp| name:kw| list:acomp_kw| st: width:100%' ,
					KW_ORIG
				)
				. ( $no_kw_suggest ? '' : 
					BR. TERM_KW_THIS_ENT. ': '. _span( '#kw_recom', LOADING )
				)
			,
		])
		//- hidden
		. _input( 'hidden', '#inp_dbid_inc| name: dbid_inc' )
		. _input( 'hidden', '#inp_dbid_exc| name: dbid_exc' )
		. _input( 'hidden', 'name:id', ID )
		. _input( 'submit' )
	);
}


//. class cls_omoid
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
