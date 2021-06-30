<?php
//- web mng 共通スクリプト

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//. 設定
if ( ! defined( 'FLG_MNG' ) ) 
	define( 'FLG_MNG', false ); //- webサービスからならfalse

if ( ! defined( 'TEST' ) ) 
	define( 'TEST', true ); //- mng はテストモード


//. ディレクトリ定義
define( 'DN_EMDB_MED'	, DN_DATA . '/emdb/media' );
define( 'DN_PDB_MED'	, DN_DATA . '/pdb/media' );

//. _url関数用データ
$_urls = [];

//. _fn関数用データ
$_filenames = [
	//.. EMDB
	'emdb_json'		=> DN_DATA      . '/emdb/json/<id>.json.gz' ,
	'emdb_json3'	=> DN_DATA      . '/emdb/json3/<id>.json.gz' ,

	'emdb_old_json'	=> DN_DATA      . '/emdb/json/<id>.json.gz' ,
	'emdb_new_json'	=> DN_DATA      . '/emdb/json3/<id>.json.gz' ,

	'emdb_add' 		=> DN_PREP		. '/emn/emdb_add/emd-<id>-add.json' ,
	'movinfo'		=> DN_PREP		. '/emn/movinfo/<id>.json' ,
	'mapinfo'		=> DN_PREP		. '/emn/mapinfo/<id>.json' ,
	'filelist'		=> DN_PREP		. '/emn/filelist/<id>.json' ,

	'emdb_med'		=> DN_EMDB_MED 	. '/<id>' ,
	'emdb_snap'		=> DN_EMDB_MED	. '/<id>/snap<s1>.jpg' ,	// s1:size+num 
	'emdb_mp4'		=> DN_EMDB_MED	. '/<id>/movie<s1>.mp4' ,	// s1:size+num
	'emdb_webm'		=> DN_EMDB_MED	. '/<id>/movie<s1>.webm' ,	// s1:size+num
	'jvxl'			=> DN_EMDB_MED	. '/<id>/ym/o1.zip' ,
	'session'		=> DN_EMDB_MED	. '/<id>/s<s1>.py' ,
	'session-old'	=> DN_EMDB_MED	. '/<id>/session<s1>.py' ,

	//.. epdb
	'epdb_json'		=> DN_PREP		. '/emn/epdb_json/<id>.json.gz' ,
	'pdb_add'		=> DN_PREP      . '/emn/pdb_add/<id>-add.json' ,
	'pdb_med'		=> DN_PDB_MED	. '/<id>' ,
	'pdb_snap'		=> DN_PDB_MED	. '/<id>/snap<s1>.jpg' ,	// size+num 
	'pdb_mp4'		=> DN_PDB_MED	. '/<id>/movie<s1>.mp4' ,	// size+num
	'pdb_webm'		=> DN_PDB_MED	. '/<id>/movie<s1>.webm' ,	// size+num

	//.. allpdb
	'pdb_json'		=> DN_DATA 		. '/pdb/json/<id>.json.gz' ,
	'pdb_json_pre'	=> DN_PREP 		. '/pdb_json_pre/<id>.json.gz' ,
	'pdb_plus'		=> DN_DATA 		. '/pdb/plus/<id>.json.gz' ,
	'pdb_img'		=> DN_DATA 		. '/pdb/img/<id>.jpg' ,
	'pdb_imgasb'	=> DN_DATA 		. '/pdb/img_asb/<id>_<s1>.jpg' ,
	'pdb_imgdep'	=> DN_DATA 		. '/pdb/img_dep/<id>.jpg' ,
	'qinfo'			=> DN_PREP		. '/pdb_qinfo/<id>.json' ,

	//.. chem
	'chem_cif'		=> DN_DATA		. "/chem/cif/<id>.cif.gz" ,
	'chem_json'		=> DN_DATA		. "/chem/json/<id>.json.gz" ,
	'chem_img'		=> DN_DATA		. "/chem/img/<id>.gif" ,
	'chem_img2'		=> DN_DATA		. "/chem/img/<id>.svg" ,

	//.. bird
	'bird_json'		=> DN_DATA		. '/bird/json/<id>.json.gz' ,

	//.. sas
	'sas_json'		=> DN_DATA		. '/sas/json/<id>.json.gz' ,
	'sas_img'		=> DN_DATA		. '/sas/img/<id>.jpg' ,
];

//. ファイル読み書き系
//.. _json save / load
function _json_save( $fn, $data ) {
	return _gzsave(
		_prepfn( $fn ) ,
		json_encode( $data, JSON_UNESCAPED_SLASHES )
	);
}

function _json_load( $fn, $opt = true ) {
	$fn = _prepfn( $fn ); 
	if ( ! file_exists( $fn ) ) return;
	return json_decode( _gzload( $fn ), $opt );
}

//- オブジェクトで返す
function _json_load2( $fn ) {
	return _json_load( $fn, false );
}

function _to_json( $data ) {
	return json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

function _json_pretty( $data ) {
	return json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES 
		| JSON_PRETTY_PRINT);
}

//.. _json_cache: 使ったらキャッシュしておくjson load
$json_cache = [];
function _json_cache( $fn ) {
	global $json_cache;
	$fn = realpath( $fn );
	if ( $json_cache[ $fn ] == '' )
		$json_cache[ $fn ] = _json_load2( $fn );
	return $json_cache[ $fn ];
}

//.. _tsv_load2; 2階層になるバージョン
function _tsv_load2( $fn, $no_trim = false ) {
	if ( ! file_exists( $fn ) ) {
		if ( function_exists( '_problem' ) ) {
			_problem( "ファイルがない: $fn" );
			return;
		} else {
			if ( TEST )
				die( 'no file: '. $fn );
			else
				return [];
		}
	}
	$ret = [];
	$current_categ = 'undefined';
	foreach ( _file( $fn ) as $l ) {
		if ( substr( $l, 0, 2 ) ==  '//' ) continue;
		$l = preg_replace( '/[ \t]\/\/.*$/', '', $l ); //- コメント消し
		list( $key, $val ) = explode( "\t", $l, 3 );

		//- trim
		if ( ! $no_trim ) {
			$key = trim( $key );
			$val = substr( $val, 0, 1 ) == '"'
				? trim( $val, '"' )
				: trim( $val )
			;
		}
		if ( $key == '' || $key == '..' || $key == '...' ) continue;
		if ( $key == '.' )
			$current_categ = $val;
		else
			$ret[ $current_categ ][ $key] = $val;
	}
	return $ret;
}

//.. _tsv_load3; 3階層になるバージョン
function _tsv_load3( $fn ) {
	if ( ! file_exists( $fn ) ) {
		_problem( "ファイルがない: $fn" );
		return;
	}
	$ret = [];
	$current_categ1 = 'undefined';
	$current_categ2 = 'undefined';
	foreach ( _file( $fn ) as $l ) {
		if ( substr( $l, 0, 2 ) ==  '//' ) continue;
		$l = preg_replace( '/[ \t]\/\/.*$/', '', $l ); //- コメント消し
		list( $key, $val ) = explode( "\t", $l, 3 );
		$key = trim( $key );
		if ( $key == '' || $key == '...' ) continue;
		$val = substr( $val, 0, 1 ) == '"' ? trim( $val, '"' ) : trim( $val );
		if ( $key == '.' )
			$current_categ1 = $val;
		else if ( $key == '..' )
			$current_categ2 = $val;
		else
			$ret[ $current_categ1 ][ $current_categ2 ][ $key] = $val;
	}
	return $ret;
}

//.. _tsv_save 
function _tsv_save( $fn, $data ) {
	$out = '';
	foreach ( $data as $name => $val )
		$out .= "$name\t$val\n";
	return file_put_contents( $fn, $out );
}

//.. _tsv_save2 階層式
function _tsv_save2( $fn, $data ) {
	$out = '';
	foreach ( $data as $section => $child ) {
		$out .= ".\t$section\n";
		foreach ( $child as $name => $val )
			$out .= "$name\t$val\n";
		$out .= "\n";
	}
	return file_put_contents( $fn, $out );
}

//.. _prepfn: arrayなら_fn、それ以外はそのまま
//- 例 _json_load([ 'emdb_json', '1003' ]) 
function _prepfn( $s ) {
	return is_array( $s ) ? _fn( $s[0], $s[1] ) : $s;
}

//.. _file
//- file()のラッパー: 改行文字・空行を消す、配列に読み込む
//- 配列として返す
function _file( $s ) {
	return file_exists( $s )
		? file( $s, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES )
		: []
	;
}

//.. _is_gz: 拡張子が .gz ?
function _is_gz( $fn ) {
	return substr( $fn, -3 ) == '.gz';
}

//.. _gzload : 拡張子が.gzなら圧縮解除して読み込み
function _gzload( $fn ) {
	return _is_gz( $fn )
		? implode( '', gzfile( $fn ) )
		: file_get_contents( $fn )
	;
}

//.. _gzsave 
function _gzsave( $fn, $cont ) {
	return file_put_contents( $fn, _is_gz( $fn ) ? gzencode( $cont ) : $cont );
}

//.. _del
//- ファイル削除
function _del() {
	$num = 0;
	foreach ( func_get_args() as $fn ) {
		if ( ! file_exists( $fn ) ) continue;
		unlink( $fn );
		++ $num;
	}
	return $num;
}

//.. _auto_run
function _auto_run( $name ) {
	if ( function_exists( '_line' ) )
		_line( 'auto_run 登録', $name );
	touch( DN_PREP. "/auto_run/$name" );
}

//. DB関係ない系
//.. _reg_rep
function _reg_rep( $in, $array ) {
	return preg_replace(
		array_keys( (array)$array ) ,
		array_values( (array)$array ) ,
		$in
	);
}

//.. _imp: implodeのラッパー
//- コンマ区切りにして返す
//- 配列で受け取っても、引数の羅列で受け取ってもOK
function _imp() {
	return implode( ', ', _armix( func_get_args() ) );
}

//.. _armix: 配列やら変数やらの集まりを一つの配列へ
function _armix( $in ) {
	$ret = [];
	foreach ( array_filter( (array)$in ) as $a ) {
		if ( is_array( $a ) || is_object( $a ) ) {
			$ret = array_merge( $ret, _armix( $a ) );
		} else {
			$ret[] = $a;
		}
	}
	return array_filter( $ret );
}

//.. _uniqfilt: array_unique + array_filter
function _uniqfilt( $in ) {
	return array_unique( array_filter( (array)$in ), SORT_REGULAR );
}

//.. _nn: 最初のnullでない文字列を返す
function _nn() {
	foreach ( func_get_args() as $s )
		if ( $s != '' ) return $s;
}

//.. _ifnn: $s1がnullでなかったら、$s2を返す
function _ifnn( $s1, $s2, $s3 = '' ) {
	return $s1 != '' ? strtr( $s2, [ '\1' => $s1 ] ) : $s3 ;
}

//.. _inlist
//- json.gz形式 リストファイルにあるか
function _inlist( $id, $list ) {
	global $_idlist;
	if ( ! is_array( $_idlist[ $list ] ) ) {
		if ( file_exists( $fn = DN_DATA . "/ids/$list.txt" ) )
			$_idlist[ $list ] = array_fill_keys( _file( $fn ), true );
	}
	//- リストファイルがない場合、テストサーバー上では、die
	if ( TESTSV && !is_array( $_idlist[ $list ] ) )
		die( "No list file: ids/$list.txt" );
	return $_idlist[ $list ][ $id ];
}

//.. _idlist
//- テキストのIDリストからid一覧を得る
function _idlist( $name ) {
	$fn = DN_DATA . "/ids/$name.txt";
	if ( file_exists( $fn ) )
		return _file( $fn );
}

//.. _numonly 数字だけ取り出す
function _numonly( $s ) {
	return preg_replace( '/[^0-9]/', '', $s );
}

//.. _same_str: 大文字小文字関係なし比較
function _same_str( $s1, $s2 ) {
	return strtolower( $s1 ) == strtolower( $s2 );
}

//. sqlite関連
//.. _quote
function _quote( $s, $q = "'" ) {
	if ( $q == 2 )
		$q = '"';
	return $q. strtr( $s, [ $q => $q.$q ] ). $q ;
}

//.. _sql_eq
function _sql_eq( $key, $val ) {
	if ( ! is_array( $val ) )
		return "$key='". strtr( $val, [ "'" => "''" ] ). "'";
	$r = [];
	foreach ( (array)$val as $v )
		$r[] = "$key='". strtr( $v, [ "'" => "''" ]). "'";
	return '('. implode( ' OR ', $r ). ')';
}

//.. _sql_like
function _sql_like( $key, $val, $sep = '' ) {
	if ( ! is_array( $val ) )
		return "$key LIKE '%$sep". strtr( $val, [ "'" => "''" ] ). "$sep%'";
	$r = [];
	foreach ( (array)$val as $v )
		$r[] = "$key LIKE '%$sep". strtr( $v, [ "'" => "''" ] ). "$sep%'";
	return '('. implode( ' OR ', $r ). ')';
}

//. DBデータ用
//.. _cif_rep: tsvデータによる置換
//- mode: [ lc: 小文字化, reg: 正規表現 ]
function _cif_rep( &$json ){
	foreach ( _tsv_load2( DN_DATA. '/pdb/cif_rep.tsv' ) as $cat_item => $rep ) {
		list( $cat, $item, $mode ) = explode( '.', $cat_item );
		foreach ( (array)$json->$cat as $c ) {
			if ( $c->$item == '' ) continue;
			if ( $mode == 'lc' )
				$c->$item = strtolower( $c->$item );
			$c->$item = $mode == 'reg'
				? _reg_rep( $c->$item, $rep )
				: strtr( $c->$item, $rep )
			;
		}
	}
}

//.. _cif2pdb
//- 簡易型 mmcifコンバータ、水は消す、atomセクションのみ
//- 改行付きのarrayとして返す (file関数で開いたのと同じ状態)
//- $opt: allmodel 
function _cif2pdb( $in, $opt = [] ) {
	_m( 'cif2pdb' );
	//- atomセクション取り出し
	$sec = [];
	$hit = false;
	foreach ( (array)$in as $line ) {
		if ( trim( $line ) == '#' ) {
			if ( $hit )
				break;
			else
				$sec = [];
			continue;
		}
		if ( strpos( $line, '_atom_site.' ) === 0 )
			$hit = true;
		$sec[] = $line;
	}

	//- セクションが見つからない?
	if ( ! $hit ) {
		_m( 'atom_site セクションが見つからない' );
		return [];
	} else {
		_m( 'atom_site セクション発見: ' . count( $sec ) . '行' );
	}
	
	$cname2num = []; //- カラム名
	$num = 0;
	$ret = [];
	$spc = str_repeat( ' ', 20 );
	foreach ( $sec as $line ) {
		//- カラム情報収集
		if ( strpos( $line, '_atom_site.' ) === 0 ) {
			$cname2num[ strtr( trim( $line ), [ '_atom_site.' => '' ] ) ] = $num;
			++ $num;
			continue;
		}
		
		if ( _instr( 'HOH', $line ) ) continue; //- 水は消す

		$d = preg_split( '/ +/', $line );
		if ( count( $d ) < 10 ) continue;
		
		//- モデル1のみ
		if (
			$d[ $cname2num[ 'pdbx_PDB_model_num' ] ] > 1 &&
			! $opt[ 'allmodel' ]
		) 
			continue;

		$out = '';
		foreach ([
			[  1,  6, 'group_PDB'		] , //- ATOM / HETATM
			[  7, 11, 'id'				] , //- atom id
			[ 13, 16, 'label_atom_id'	] , //- type_symbol
			[ 17, 17, 'label_alt_id'	] , //- altloc
			[ 18, 20, 'label_comp_id'	] , //- compid
			[ 22, 22, 'label_asym_id'	] , //- chain-id; (chain-id)
			[ 23, 26, 'label_seq_id'	] , //- seq-id
			[ 27, 27, 'pdbx_PDB_ins_code' ] , //- ins
			[ 31, 38, 'Cartn_x'			] , //- x
			[ 39, 46, 'Cartn_y'			] , //- y
			[ 47, 54, 'Cartn_z'			] , //- z
			[ 55, 60, 'occupancy'		] , //- ocup
			[ 61, 66, 'B_iso_or_equiv'	] , //- temp
			[ 77, 78, 'type_symbol'		] , //- elem
			[ 79, 80, 'pdbx_formal_charge' ] , //- charge
		] as $a ) {
			$v = $d[ $cname2num[ $a[2] ] ];
			if ( $v == '?' || $v == '.' ) continue;
			$len = $a[1] - $a[0] + 1;
			$out = substr( $out . $spc, 0, $a[0] - 1 )
				. substr( $v, 0, $len );
		}
		$ret[] = $out . "\n";
	}
//	print_r( $cname2num );
	return $ret;
}

//.. _load_trep_tsv
function _load_trep_tsv() {
	$data = [];
	$categ = '_';
	foreach ( _file( '../emnavi/trep.tsv' ) as $line ) {
		list( $key, $ja, $en ) = explode( "\t", $line, 4 );
		$key = trim( $key );
		if ( substr( $key, 0, 2 ) == '//' || $key == '' || $key == '..' ) continue;
		if ( $key == '.' ) {
			$categ = $ja;
			continue;
		}
		if ( $ja )
			$data['ja'][ $categ ][ $key ] = $ja;
		if ( $en )
			$data['en'][ $categ ][ $key ] = $en;
	}
	return $data;
}

//.. _paper_id: pubmed-idの代わりのIDを作る
function _paper_id( $title, $journal ) {
	return $title . $journal == ''
			|| strtolower( $title ) == 'to be published'
			|| strtolower( $title ) == 'suppressed'
			|| strtolower( $journal ) == 'suppressed'
		? ''
		: '_' . md5(
			$title .'|'
			. preg_replace( '/[^a-z]/', '', strtolower( $journal ) )
		)
	;
}

//.. _reps_wikipe_terms
function _reps_wikipe_terms() {
	return [[
		'/^putative /i' => '' ,
		'/ ,putative/i' => '', 
		'/([0-9]+s |)ribosomal/i' => 'ribosome' ,
		'/ribosome protein( [a-z]+[0-9]+|)/i' => 'ribosomal protein' ,
		'/ribosome rna/i' => 'ribosomal rna'  ,

	], [
		'/ (light|heavy) chain$/i' => '' ,
		'/^(human|mouse|yeast) +/i' => '',
		'/ (from|in|at) .+$/' => '' ,
		'/ holoenzyme.*$/' => '' ,
		'/(large|small|) (component|subunit).*/' => '' ,
		'/^.*type ([iv]+) (protein |)secretion.*$/' => 'type $1 secretion' ,
//		'/\-/' => '', 
		'/^regulation of /' => '',
		'/-(like)$/' => '',
		'/^.*photosystem ([iv]+).*$/' => 'photosystem $1' ,
	], [
		'/ (complex|activity|assembly|binding|domain|family|maintenance|signature.|family signature.process)$/' => '',
		'/ protein.*/' => '' ,
		'/ system.+$/' => ' system'  ,
		'/[,;] .+$/' => '' ,
		'/( |\-)([0-9]{1,2}|i{1,3}|iv|vi?)$/' => ''
	]];
}

//.. _flg_emdb_
function _flg_emdb( $type, $json ) {
	$e = $json->experiment;
	if ( $type == 'cryo' ) {
		$s = _clean_emdb_val( $e->vitrification[0]->cryogenName );
		return $s !=  '' && $s != 'none' && ! _instr( 'stain', $s );
	} else if ( $type == 'stain' ) {
		$s = _clean_emdb_val( $e->specimenPreparation->staining );
		return $s !=  '' && $s != 'none' && ! _instr( 'cryo', $s );
	}
}

//.. _flg_pdb
function _flg_pdb( $type, $json ) {
	$s = $json->em_specimen[0];
	if ( ! $s ) return false;
	if ( $type == 'cryo' )
		return $s->vitrification_applied == 'YES';
	if ( $type == 'stain' )
		return $s->staining_applied == 'YES';
}

//.. _clean_emdb_val
function _clean_emdb_val( $s ) {
	if ( in_array( strtolower( $s ), [ '-', 'none', 'na', 'n/a' ] ) )
		return;
	return trim( $s );
}

//.. _emdb_json3_rep
function _emdb_json3_rep( &$json ) {
	//- 試しに変更してみる、確定したらxml2jsonで採用

	//... structure_determination
	foreach ( $json->structure_determination as $c ) {
		foreach ([
			'preparation', 'microscopy', 'processing'
		] as $categ ) foreach ([
			'crystallography_' ,
			'helical_' ,
			'single_particle_' ,
			'singleparticle_' ,
			'subtomogram_averaging_' ,
			'tomography_' ,
		] as $type ) {
			$tag = $type. $categ;
			if ( $c->$tag ) {
				$c->$categ = is_array( $c->$tag ) ? $c->$tag : [ $c->$tag ];
				unset( $c->$tag );
			}
		}
	}

	//... supramolecule_list
	if ( $json->sample->supramolecule_list ) {
		$json->sample->supramolecule = [];
		foreach ( $json->sample->supramolecule_list as $tag => $sup_a ) {
			foreach ( is_array( $sup_a ) ? $sup_a : [ $sup_a ] as $sup ) {
				$sup->supmol_type = strtr( $tag, [ '_supramolecule' => '' ] );
				$json->sample->supramolecule[] = $sup;
			}
		}
		unset( $json->sample->supramolecule_list );
	}

	//... macromolecule_list
	if ( $json->sample->macromolecule_list ) {
		$json->sample->macromolecule = [];
		foreach ( $json->sample->macromolecule_list as $tag => $mac_a ) {
			foreach ( is_array( $mac_a ) ? $mac_a : [ $mac_a ] as $mac ) {
				$mac->macmol_type = strtr( $tag, [ '_macromolecule' => '' ] );
				$json->sample->macromolecule[] = $mac;
			}
		}
		unset( $json->sample->macromolecule_list );
	}

	return $json;
}

//.. _emn_json : sqlite に入れたjsonデータを返す
function _emn_json( $type, $did ) {
	//- 通常 & web
	if ( ! TEST || ! is_dir( DN_PREP ) ) {
		return json_decode( _ezsqlite([
			'dbname' => 'emn' ,
			'select' => $type ,
			'where'  => [ 'did', $did ], 
		]));
	}
	
	//- テスト
	list( $db, $id ) = explode( '-', $did, 2 );
	if ( $type == 'status' )
		return _json_cache( DN_PREP. '/emn/status.json.gz' )->$did;
	if ( $type == 'movinfo' && $db == 'pdb' )
		return _json_cache( DN_PREP. '/emn/pdbmovinfo.json' )->$id;
//	if ( $type == 'assembly' )
//		return _json_cache( DN_DATA. '/prep/emn/assembly.json' )->$id;
	if ( $type == 'addinfo' )
		return _json_load2([ $db. '_add', $id ]);
	if ( $type == 'omolist' )
		return _file( DN_PREP. "/omolist/$did.txt" );
	if ( $type == 'fit' )
		return _json_cache( DN_PREP. '/emn/fitdb.json.gz' )->$did;
	if ( $type == 'related' )
		return _json_cache( DN_PREP. '/emn/related.json' )->$did;
	return _json_load2([ $type, $id ]);
}

	//.. _rep_pdbid 置き換えPDB-ID
function _rep_pdbid( $i ) {
	//- 複数のIDがある場合もあるが、とりあえず最初のを返す
	return explode( '|', _ezsqlite([
		'dbname' => 'pdbid_replaced' ,
		'select' => 'rep' ,
		'where'  => [ 'id', $i ]
	]) )[0] ?: $i;
}

//. class abs_entid
class abs_entid {
	public
		$db, $DB, $id, $did, $DID,
		$cache ,
		$title ,
		$is_em = 'uk'
	;
	
	function __construct( $s = '' ) {
		if ( $s != '' )
			$this->set( $s );
		return $this;
	}
	function __toString() { return (string)$this->did; }

	//.. set
	function set( $str ) {
		//- arrayでセット
		if ( is_array( $str ) ) {
			foreach ( $str as $k => $v )
				$this->$k = $v;
			return $this;
		}
		
		//- get/postから？
		if ( $str == 'get' ) {
			$str = _getpost( 'id' ) ?: _getpost( 'i' );
		}

		//- yorodumi用 'kw'でもいいように
		if ( $str == 'get_kw' ) {
			$str = _getpost( 'id' ) ?: _getpost( 'kw' ) ?: _getpost( 'i' );
		}

		//- a: 全角英数->半角
		$str = strtolower( mb_convert_kana( $str, 'a' ) );

		//- SAS
		if ( substr( $str, 0, 3 ) == 'sas' ) {
			if ( _instr( '-', $str ) ) {
				$this->set_sasmodel( $str );
			} else {
				$this->set_sas( $str );
			}
			return $this;
		}

		//- prd
		$s = substr( $str, 0, 4 );
		if ( $s == 'prd_' || $s == 'bird' ) {
			$this->set_bird( $str );
			return $this;
		}

		//- 4桁以上の数字
		if ( ctype_digit( $str ) && strlen( $str ) > 3 ) {
			$this->set_emdb( $str );
			return $this;
		}

		//- emdb-xxxx, pdb-xxxx 
		list( $l, $r ) = explode( '-', $str );
		if ( $r != '' ) {
			if ( $l == 'pdb'  ) {
				$this->set_pdb( $str );
				return $this;
			}
			if ( $l == 'emdb' || $l == 'emd' ) {
				$this->set_emdb( $str );
				return $this;
			}
			if ( $l == 'chem' || $l == 'chemcomp' ) {
				$this->set_chem( $str );
				return $this;
			}
		}

		//- 先頭の文字から判断 
		$len = strlen( $str );
		if ( $len > 4 ) {
			$id1 = substr( $str, 0, 1 );
			if ( $id1 == 'e' ) {
				$this->set_emdb( $str );
				return $this;
			}
			if ( $id1 == 'p' ) {
				$this->set_pdb( $str );
				return $this;
			}
			if ( $id1 == 'c' ) {
				$this->set_chem( $str );
				return $this;
			}
		}

		//- chemcomp
		if ( $len < 4 ) {
			$this->set_chem( $str );
			return $this;
		}

		//- emdbに直接ヒット
//		if ( _inlist( $str, 'emdb' ) ) {
//			$this->set_emdb( $str );
//			return $this;
//		}

		//- PDB
		$this->set_pdb( $str );
		return $this;
	}

	//.. set_xxxx
	function set_chem( $i ) {
		$i = strtoupper( preg_replace( '/^.*\-/', '', $i ) );
		$this->DB	= 'ChemComp';
		$this->db	= 'chem';
		$this->id	= $i;
		$this->did	= "Chem-$i";
		$this->DID	= "ChemComp-$i";
		return $this;
	}
	function set_bird( $i ) {
		$i = _numonly( $i );
		$this->DB	= 'BIRD';
		$this->db	= 'bird';
		$this->id	= $i;
		$this->did	= "bird-prd_$i";
		$this->DID	= "BIRD-PRD_$i";
		return $this;
	}
	function set_emdb( $i ) {
		$i = _numonly( $i ) ?: '?';
		$this->DB	= 'EMDB';
		$this->db	= 'emdb';
		$this->id	= $i;
		$this->did	= "emdb-$i";
		$this->DID	= "EMDB-$i";
		return $this;
	}
	function set_sas( $i ) {
		$i = strtoupper( $i );
		$this->DB	= 'SASBDB';
		$this->db	= 'sasbdb';
		$this->id	= $i;
		$this->did	= $i;
		$this->DID	= $i;
		return $this;
	}
	function set_sasmodel( $i ) {
		$i = _numonly( $i );
		$this->DB	= 'SASBDB-Model';
		$this->db	= 'sasbdb-model';
		$this->id	= $i;
		$this->did	= "sas-$i";
		$this->DID	= "SAS-$i";
		return $this;
	}
	function set_pdb( $i ) {
		$i = substr( $i, -4 );
		$this->DB	= 'PDB';
		$this->db	= 'pdb';
		$this->id	= $i;
		$this->did	= "pdb-$i";
		$this->DID	= "PDB-$i";
		return $this;
	}

	//.. ex: 存在するか
	function ex() {
		if ( $this->db . $this->id == '' )
			return false;
		if ( $this->is_prerel() )
			return true;
		if ( $this->db == 'sasbdb-model' )
			return file_exists( _fn( 'sasbdb_json', _sas_info( 'mid2id', $this->id ) ) );
		if ( $this->db == 'sasbdb' )
			return file_exists( _fn( 'sasbdb_json', $this->id ) );
		if ( $this->db == 'chem' )
			return file_exists( _fn( 'chem_img', $this->id ) );
		if ( $this->db == 'bird' )
			return file_exists( _fn( 'bird_json', _numonly( $this->id ) ) );
		return _inlist( $this->id , $this->db );
	}

	//.. get: _idarray の代用
	function get() {
		return [
			'db'  => $this->db,
			'DB'  => $this->DB,
			'id'  => $this->id,
			'did' => $this->did,
			'DID' => $this->DID
		];
	}

	//.. その他、主要な情報の取得
	//... is_em
	function is_em() {
		if ( $this->is_em == 'uk' )
			$this->is_em = $this->db == 'emdb' || _inlist( $this->id, 'epdb' ) ;
		return $this->is_em;
	}

	//... is_prerel
	function is_prerel() {
		if ( $this->cache[ 'is_prerel' ] == '' )
			$this->cache[ 'is_prerel' ] = $this->db == 'pdb' && _inlist( $this->id, 'prerel' );
		return $this->cache[ 'is_prerel' ];
	}

	//... emdb_obs
	function emdb_obs() {
		return _json_cache( DN_DATA . '/emdb/emdb-obs.json.gz' )->{$this->id};
	}

	//... title
	function title( $set = '___null___' ) {
		//- set
		if ( $set != '___null___' ) {
			$this->title = $set;
			return $this;
		}

		//- 既に定義されていたら返すだけ
		if ( $this->title )
			return $this->title;

		//- 定義
		$t = '';
		if ( $this->db == 'emdb' ) {
			//- EMDB
			$t = _ezsqlite([
				'dbname' => 'main',
				'select' => 'title' ,
				'where'  => [ 'db_id', $this->did ]
			]);
			if ( ! $t ) { //- 取り消しエントリ
				$o = $this->emdb_obs();
				$t = $o
					? $o->title. _kakko( _l( 'obsolete entry' ) )
					: ''
				;
			}
		} else if ( $this->db == 'pdb' ) {
			//- PDB
			$t = $this->is_prerel()
				? _json_cache( DN_DATA. '/pdb/prerel.json.gz' )->{$this->id}->title
				: _ezsqlite([
					'dbname' => 'pdb',
					'select' => 'title' ,
					'where'  => [ 'id', $this->id ]
				])
			;
		} else if ( $this->db == 'sasbdb' || $this->db == 'sasbdb-model' ) {
			$t = _sas_info( 'title', $this->id );
		} else if ( $this->db == 'bird' ) {
			$t = $this->mainjson()->pdbx_reference_molecule[0]->name;
		} else {
			$t = $this->mainjson()->chem_comp->name;
		}
		return $this->title = $t ?: _l( 'Unknown entry' );
	}

	//... replaced
	function replaced() {
		if ( $this->db == 'pdb' )
			return _json_cache( DN_DATA . '/pdb/ids_replaced.json.gz' )->{ $this->id };
	}

	//.. 各種 json
	//... mainjson
	function mainjson() {
		return _json_load2( $this->db == 'sasbdb-model'
			? _fn( 'sas_json', _sas_info( 'mid2id', $this->id ) )
			: _fn( $this->db . '_json', $this->id )
		);
	}

	//... add
	function add() {
		return _emn_json( 'addinfo', $this->did );
	}

	//... movjson
	function movjson() {
		return $this->is_em() ? _emn_json( 'movinfo', $this->did ) : [];
	}

	//... mapjson
	function mapjson() {
		return _emn_json( 'mapinfo', $this->did );
	}

	//... status
	function status() {
		return _emn_json( 'status', $this->did );
	}

	//.. ex_* 存在確認系
	//... ex_map
	function ex_map() {
		return $this->status()->map;
	}

	//... ex_mov
	function ex_mov() {
		return $this->status()->mov1 || $this->status()->movdep;
	}
	
	//... ex_polygon
	function ex_polygon() {
		return $this->status()->pg1;
	}

	//... ex_bufile: mmcif版 bu 未対応
	function ex_bufile( $n ) {
		if ( $this->db != 'pdb' ) return false;
		return file_exists( 
			( TESTSV ? DN_FDATA. '/pdb/asb' : DN_KF1BU )
			. "/{$this->id}.pdb$n.gz" 
		);
	}

	//... ex_vq: vqがあるかどうか
	//- $num はPDB-assembly-IDまたは、sas-model-id
	function ex_vq( $num = '' ) {
		_add_fn( 'omokage' );
		if ( $this->db == 'emdb' ) {
			$fn = _fn( 'emdb_vq50', $this->id );
		} else if ( $this->db == 'sasbdb-model' ) {
			$fn = _fn( 'sasbdb_vq50', $this->id );
		} else if ( $this->db == 'sasbdb' ) {
			$i = $num ?: _sas_info( 'id2mid', $this->id );
			$fn = _fn( 'sasbdb_vq50', $i );
		} else {
			$fn = _fn( 'pdb_vq50', $this->id, $num );
		}
		return file_exists( $fn );
	}
}

//. _ezsqlite: 定型パターンでデータ取り出し
$o_dbs = [];
function _ezsqlite( $in, $val = '' ) {
	global $o_dbs;
	extract( $in ); //- $dbname, $where, $key, $val, $select
	if ( is_array( $where ) )
		list( $key, $val ) = $where;
	if ( ! $o_dbs[ $dbname ] )
		$o_dbs[ $dbname ] = new cls_sqlite( $dbname );
	$sql=[
		'select' => $select ,
		'where'  => is_string( $where )
			? $where
			: "$key=". _quote( $val ) 
		,
	];
	return is_array( $select )
		? (array)$o_dbs[$dbname]->qar( $sql )[0]
		: $o_dbs[$dbname]->qcol( $sql )[0]
	;
}
/*
//. _ezsqlite: 定型パターンでデータ取り出し これはうまく動かない
$o_dbs = [];
function _ezsqlite_2( $in, $val = '', $where = '' ) {
	global $o_dbs;
	if ( $where ) {
		$dbname = $in;
		$select = $val;
	} else {
		extract( $in ); //- $dbname, $where, $key, $val, $select
	}
	if ( is_array( $where ) )
		list( $key, $val ) = $where;
	if ( ! $o_dbs[ $dbname ] )
		$o_dbs[ $dbname ] = new cls_sqlite( $dbname );
	$sql=[
		'select' => $select ,
		'where'  => is_string( $where )
			? $where
			: "$key=". _quote( $val ) 
		,
	];
	return is_array( $select )
		? (array)$o_dbs[$dbname]->qar( $sql )[0]
		: $o_dbs[$dbname]->qcol( $sql )[0]
	;
}
*/
//. class sqlite
class cls_sqlite {
	protected $pdo, $wh, $sql, $fn_db, $flg_mng, $flg_persistent;
	function __construct( $s = 'main', $flg = false ) { //- $flg: manageモードか？
		$this->set( $s );
		$this->flg_mng = $flg;
		if ( FLG_MNG )
			_m( 'SQLite database file: ' . $this->fn_db );
		return $this;
	}

	//.. set
	function set( $db_name = 'main', $flg_persistent = false ) {
		//- sqliteファイルをローカルにコピー
		//- $flg_persistent: ATTR_PERSISTENT フラグ
		$this->flg_persistent = $flg_persistent;

		//... フルパス指定
		if ( _instr( '/', $db_name ) ) {
			$this->log( basename( $db_name, '.sqlite' ), 'direct', $fn );
			return $this->set_PDO( $db_name );
		}

		//... doc: docだけはemnavi/docにある
		$fn = [
			'doc'		=> DN_EMNAVI. '/doc.sqlite' ,
			'subdata'	=> DN_EMNAVI. '/subdata.sqlite'
		][ $db_name ]
			?: DN_DATA. "/$db_name.sqlite"
		;

		//... テストサーバー: そのまま使う
		if ( TESTSV || FLG_MNG || is_dir( 'portable_data' ) ) {
			$this->log( $db_name, 'local', $fn );
			return $this->set_PDO( $fn );
		}

		//... 本番サーバー: DBファイルをコピーする
		$dn = '/dev/shm/emnavi';
		if ( ! is_dir( $dn ) )
			mkdir( $dn );
		$dest = "$dn/$db_name.sqlite";
//		$flg_persistent = false;
		if ( ! file_exists( $dest ) ) {
			copy( $fn, $dest );
			touch( $dest, filemtime( $fn ) );
			$this->log( $fn, 'new, copied', "$fn -> $dest" );
		} else if ( filemtime( $fn ) != filemtime( $dest ) ) {
			copy( $fn, $dest );
			touch( $dest, filemtime( $fn ) );
			$this->log( $fn, 'changed, copied', "$fn -> $dest" );
		} else {
			$this->log( $fn, 'same', $fn );
		}
		return $this->set_PDO( $dest );
	}

	//.. set_PDO
	function set_PDO( $fn_db ) {
		if ( ! file_exists( $fn_db ) ) {
			die( TESTSV || FLG_MNG
				? "no db file: $fn_db"
				: 'Database error'
			);
		}
		$this->pdo = new PDO(
			'sqlite:' . realpath( $fn_db ),
			'', '',
			[ PDO::ATTR_PERSISTENT => $this->flg_persistent ] 
		);
		$this->fn_db = $fn_db;
		return $this;
	}

	//.. getsql()
	function getsql() {
		return $this->sql;
	}

	//.. setsql
	function setsql( $q ) {
		if ( is_array( $q ) ) {
			$q = array_change_key_case( $q );
			//- デフォルト値設定、select, from, の順番になるようにする
			$q = array_merge([
				'select'	=> $q[ 'select' ] ?: 'count(*)' ,
				'from'		=> $q[ 'from' ]   ?: 'main' ,
				'where'		=> $this->wh
			], $q );

			$qa = [];
			foreach ( $q as $k => $v ) {
				if ( $v == '' || $v == [] ) continue;
				$qa[] = strtoupper( $k );
				if ( is_array( $v ) )
					$v = implode( $k == 'where' ? ' and ' : ',', array_filter( $v ) );
				$qa[] = $v;
			}
			$q = implode( ' ', $qa );
		}
		$this->sql = $q;
		return $this;
	}

	//.. where
	function where( $wh ) {
		$this->wh = is_array( $wh ) ? implode( ' and ', array_filter( $wh ) ) : $wh;
		return $this;
	}

	//.. cnt
	function cnt( $wh = '' ) {
		if ( $wh != '' )
			$this->where( $wh );
		return $this->q([ 'where' => $this->wh ])->fetchColumn();
	}

	//.. q クエリ実行メイン
	function q( $ar ) {
		$this->setsql( $ar );

		//... mngシステム版
		if ( FLG_MNG ) {
			$res = $this->pdo->query( $this->sql );
			$er = $this->pdo->errorInfo();
			if ( $er[0] == '00000' ) {
				return $res;
			} else {
				//- エラー
				_kvtable([
					'DB file' => $this->fn_db ,
					'query'   => $this->sql ,
					'error message' => print_r( $er, 1 )
				], 'DB error');
			}
		} else {

		//... WEB版
			//- エラーが出なくなるまで繰り返す
			foreach ( range( 1, 5 ) as $cnt ) {
				$res = $this->pdo->query( $this->sql );
				$er = $this->pdo->errorInfo();
				if ( $er[0] == '00000' ) return $res;
				usleep( 500000 ); //- 0.5秒
			}

			die( TEST 
				? _p( "DB error\n" ) . _t( 'pre', ''
					. "\nDB file: {$this->fn_db}"
					. "\nquery: {$this->sql}"
					. "\nerror message\n"
					. print_r( $er, 1 )
				)
				: 'Database process is busy. Please, retry later.'
			);
		}
	}
	
	//.. qcol, qar, qobj
	function qcol( $ar ) {
		return $this->q( $ar )->fetchAll( PDO::FETCH_COLUMN, 0 );
	}
	function qar( $ar ) {
		return $this->q( $ar )->fetchAll( PDO::FETCH_ASSOC );
	}
	function qobj( $ar ) {
		return $this->q( $ar )->fetchAll( PDO::FETCH_OBJ );
	}

	//.. log
	function log( $a, $b, $c ) {
		global $_simple;
		if ( ! $_COOKIE['sqlite_log'] ) return;
		$_simple->sqlite_log[] = [ $a, $b, $c ];
	}
}
