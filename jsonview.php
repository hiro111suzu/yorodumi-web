<?php
require( __DIR__. '/common-web.php' );

ini_set( "memory_limit", "2048M" );
define( 'MAXLINES', 5000 );

_add_url( 'jsonview' );
_add_fn(  'jsonview' );

//. get解釈
$adr  = _getpost( 'a' ) ?: _getpost( 'path' );
$id   = _getpost( 'id' );

define( 'DISP_MODE', _getpost( 'mode' ) );

//- 'id'がアドレス？
if ( _instr( '.', $id ) || _instr( '/', $id ) )
	$adr = $id;

//- 'id'で受け取った
if ( ! $adr && $id ) {
	$o_id = new cls_entid( 'get' );
	extract( $o_id->get() ); //- $db, $DB, $id, $DID, $did
	$adr = implode( '.', array_filter([ $db, $id ]) );
}

//- pubmed-id?
if ( preg_match( '/^[0-9]{7,8}$/', $id ) === 1 )
	$adr = "pubmed.$id";


//. ファイルの選択
$a = explode( '.', $adr );
list( $json_type, $json_name ) = $a;
$tag_tree = $tag_tree_orig = array_slice( $a, 2 );
$fn_json = '';

//.. _fn 関数対応のファイル
if ( $json_type != 'file' ) {

	$json_type = [
		'emdb'   => 'emdb_json' ,
		'json3'  => 'emdb_json3' ,
		'pdb'    => 'pdb_json' ,
		'pdb_pre' => 'pdb_json_pre' ,
		'epdb'   => 'epdb_json' ,
		'plus'   => 'pdb_plus' ,
		'chem'   => 'chem_json' ,
		'sasbdb' => 'sas_json',
		'bird'	 => 'bird_json' ,
		'prd'	 => 'bird_json' ,
	][ $json_type ] ?: $json_type ;

	if ( $json_type == 'add' )
		$json_type = _inlist( $json_name, 'epdb' ) ? 'pdb_add' : 'emdb_add';

	$fn_json = _fn( $json_type, $json_name );
	if ( $json_type == 'maindb' )
		$fn_json = _fn( 'maindbjson', ( new cls_entid( $json_name ) )->did );
}

//.. 特定のjsonファイル?
if ( ! $fn_json ) {
	//- 'file.'とか書いていない (ファイル名直書きなどを想定)
	if ( $json_type != 'file' && $json_type != 'dir' ) {
		$tag_tree = array_merge( [ $json_name ], $tag_tree );
		$json_name = $json_type;
		$json_type = 'file';
	}

	//- タグツリー配列に、拡張子が入り込んでいるなら消す
	if ( $tag_tree[0] == 'json' ) {
		$tag_tree = array_slice( $tag_tree, 1 );
		if ( $tag_tree[0] == 'gz' )
			$tag_tree = array_slice( $tag_tree, 1 );
	}

	//- それらしいファイルを探す
	foreach ([
		'', DN_DATA .'/', DN_PREP . '/'
	] as $dn ) foreach ([
		'', ".json" , ".json.gz" ,
	] as $ex ) {
		$f = $dn . $json_name . $ex;
		if ( ! file_exists( $f ) ) continue;
		if ( is_dir( $f ) )
			$json_type = 'dir';
		$fn_json = $f;
		break;
	}
}

//.. ディレクトリ
$file_list = [];

//... ディレクトリスト
if ( $json_type == 'file' || $json_type == 'dir' ) {
	if ( $json_name == '' ) {
		$pp = realpath( '..' );
		$file_list[ 'd' ] = [
			_p( _a( '_mng-dir.php?path=data', 'data' ) ) ,
			_p( _a( "_mng-dir.php?path=$pp/prepdata", 'prepdata' ) ) ,
			_p( _a( '?a=file.$pp/fdata', 'fdata' ) ) 
		];
	}
}

//... ファイル一覧作成
if ( $json_type == 'dir' ) {
	$rp = realpath( $json_name );
	$file_list[ 'dn' ] = $rp;
	$up = realpath( "$rp/.." );
	$file_list[ 'd' ][] = _p( $up == '/'
		? '(root)'
		: _dir( '..', '.. (親ディレクトリ)' ) )
	;

	foreach ( glob( "$dn/*" ) as $pn ) {
		$bn = basename( $pn );
		//- ディレクトリ
		if ( is_dir( $pn ) )
			$file_list[ 'd' ][] = _p( _dir( $pn ) );
		//- jsonファイル
		if ( _instr( '.json', $bn ) ) {
			$f = strtr( $bn, [ '.json' => '', '.gz' => '' ] );
			$file_list[ 'f' ][] = _p( _a( "?path=$rp/$bn", $bn ) );
		}
		//- 多すぎ？
		if ( count( (array)$file_list['d'] ) + count( (array)$file_list[ 'f' ] ) > 50 ) {
			$file_list[ 'f' ][] = _p( '.....' );
			break;
		}
	}
}

function _dir( $path, $name = '' ) {
	return _ab(
		'_mng-dir.php?path=' . realpath( $path ),
		_fa( 'folder' ) . ( $name ?: basename( $path ) )
	);
}

//. json 取得
$json = [];
if ( $fn_json )
	$json = _json_load( $fn_json );


//.. emdbv3の場合は変換
if ( $json_type == 'emdb_json3' && !$_GET[ 'orig' ] ) {
	$j = _json_load2( $fn_json );
	_emdb_json3_rep( $j );
	$json = (array)$j;
}


//.. json 子孫をたどる
if ( $json && count( $tag_tree ) ) foreach ( $tag_tree as $t ) {
	$json_sis = array_keys( (array)$json ); //- 姉妹
	$json = $json[ $t ];
	$curtag = $t;
	if ( !$json )
		break;
}

//. コンテンツ作成
//.. プレーンデータ出力モード、おしまい
if ( DISP_MODE != '' ) {
	if ( $json == '' )
		die( 'error: data is null' );
	if ( DISP_MODE == 'p' ) {
		header( 'application/json' );
		die( _to_json( $json ) );
	}
	if ( DISP_MODE == 'pp' )
		die( _json_pretty( $json ) );
}

//.. クエリとか
$out = 'ID: ' . _idinput( $id ?: $json_name );

if ( is_array( $idarray ) ) {
	$out .= _p( _kv( $idarray ) ); 
}

if ( TEST ) {
	$out .= _p( _imp([
		_a( '?a=file', 'JSON file' ) ,
		_a( '?a=pubmed.9761674', 'pubmed.9761674' ) ,
		_a( '?a=unp_json.P01308', 'uniprot.P01308' ) ,
		_a( '?a=pdb.100d', 'PDB-100d' ),
		_a( '?a=emdb.1001', 'EMDB-1001' ),
		_a( '?a=sasbdb.SASDA82', 'SASDA82' ),
	]));
}
_simple()->hdiv( 'Data', $out );

//.. エントリ情報
$out = '';
if ( $json_name != '' ) {
	//... Pubmed
	if ( $json_type == 'pubmed' ) {
		$a = [];
		$l = glob( _fn( 'pubmed', '*' ) );
		shuffle( $l );
		$l = array_slice( $l, 0, 30 );
		sort( $l );
		foreach ( $l as $pn ) {
			$i = basename( $pn, '.json' );
			$a[] = _a( "?a=pubmed.$i", $i );
		}
		$out = _ab( "paper.php?id=$json_name", "PubMed-ID: $json_name" )
			. BR
			. "Other data: " . _imp( $a )
		;

	//... file
	} else if ( $json_type == 'file' ) {
		$a = [];
		foreach ( glob( DN_DATA . '/*.json*' ) as $pn ) {
			$f = strtr( basename( $pn, '.gz' ), [ '.json' => '' ] );
			$a[] = _a( "?a=file.$f", $f );
		}
		$out = _imp( $a );

		if ( $json_name == 'pdb/prerel' ) {
			if ( $tag_tree[0] ) {
				$out .= BR . _ab( 'quick.php?id=' . $tag_tree[0], 'Quick' );
			}
		}
	
	//... PDB/EMDB/others
	} else {
		if ( in_array( $json_type, [ 'pdb', 'emdb', 'chem' ] ) )
			$i = "$json_type-$json_name";
		else {
			if ( $json_type == 'qinfo' )
				$i = "pdb-$json_name";
			else
				$i = $json_name;
		}
		$o_id = new cls_entid( $i );
		extract( $o_id->get() );
		$out .= $o_id->ex() ? $o_id->ent_item_list() : '';

		$a = [];

		//... pdb
		if ( $db == 'pdb' ) {
			$a = [
				_a( "?a=pdb.$id"	, "json" ) ,
				_a( "?a=pdb_pre.$id"	, "json-pre" ) ,
				_a( "?a=qinfo.$id"	, "qinfo" ),
				_a( "?a=plus.$id"	, "plus" ) ,
			];
			if ( _inlist( $id, 'epdb' ) ) {
				$a = array_merge( $a, [
					_a( "?a=add.$id", "add" ) ,
					_a( "?a=maindb.$id",  "maindb" ) ,
					_a( "?a=epdb%2Fassembly.$id", "assembly" )
				]);
			}

		//... emdb
		} else if ( $db == 'emdb' ) {
			$a = [
				_a( "?a=emdb.$id"	, "json" ) ,
				_a( "?a=json3.$id"	, "json3" ) ,
				_a( "?a=add.$id"	, "add" ) ,
				_a( "?a=movinfo.$id", "movinfo" ) ,
				_a( "?a=mapinfo.$id", "mapinfo" ) ,
				_a( "?a=maindb.$id"	, "maindb" ) 
			];
		}

		//... others
		if ( count( $a ) > 0 ) {
			$out .= _p( "Other JSONs: " . _imp( $a ) );
		}
	}
}
if ( $json_type == 'emdb_json3' ) {
	$get = $_GET;
	unset( $get[ 'orig' ] );
	$u = '?'. http_build_query( $get );
	$out .= _p( $_GET[ 'orig' ]
		? _span( '.red', 'オリジナルを表示しています' )
			.SEP. _a( $u, '変換後を表示' )
		: _span( '.red', '変換後を表示しています' )
			.SEP. _a( "$u&orig=1", 'オリジナルを表示' )
	);
}

if ( $out != '' ) {
	_simple()->hdiv( 'Entry info', $out );
}

//.. json 親、姉妹、子供
if ( $json != [] ) {

	//... 親
	$pr = [];
	$pr_link = [];
	if ( count( $tag_tree ) ) {
		$tr = "$json_type.$json_name";
		$pr[] = _a( "?a=$tr", $tr );
		foreach ( $tag_tree as $t ) {
			$tr .= ".$t";
			$pr[] = $t == $curtag
				? "<b>$t</b>"
				: _a( "?a=$tr", $t )
			;
		}
		if ( $json_type == 'pdb' ) {
			if ( count( $tag_tree ) ) {
				$pr_link[] = _ab(
					[ 'cifdic', implode( '.', [ $tag_tree[0], $tag_tree[2] ] ) ] ,
					'Cif dic viewer' 
				);
				$pr_link[] = _ab(
					[ 'dic_pdb_cat', $tag_tree[0] ] ,
					'PDBx/mmCIF Dictionary: "' .  $tag_tree[0] . '"'
				);
			}
		} else if ( $json_type == 'sasbdb' ) {
			$pr_link[] = _ab(
				[ 'cifdic_sas', implode( '.', [ $tag_tree[0], $tag_tree[2] ] ) ],
				'Cif dic viewer'
			);
			
		}
	}

	//... 姉妹
	if ( is_array( $json_sis ) ) {
		$sis = '';
		$siscnt = count( $json_sis ) - 1;
		$u = preg_replace( '/[^\.]+$/', '', $tr );
		sort( $json_sis );

		if ( $json_type == 'pdb' && count( $tag_tree ) == 1 ) {
			//- PDB カテゴリ
			$categ = [];
			foreach ( $json_sis as $s ) {
				list( $s1, $s2 ) = explode( '_', $s );
				$c = $a[0] == 'pdbx' ? $a[1] : $a[0];
				$categ[ $c ][] = $s;
			}

			ksort( $categ );
			foreach ( $categ as $c => $v ) {
				$o = [];
				foreach ( $v as $t ) {
					$o[] = _atag( $t, $u );
				}
				$o2[ $c ] = _imp( $o );
			}
			$sis = _simple_table( $o2 );
		} else {
			//- その他一般
			$o = [];
			foreach ( $json_sis as $t ) {
				$o[] = _atag( $t, $u );
			}
			$sis = _imp_lim( $o );
		}
	} else  {
		$siscnt = 0;
	}

	//... 子供
	$ch = [];
	if ( is_array( $json ) ) {
		$chlcnt = count( $json );
		$a = array_keys( $json );
		sort( $a );
		foreach ( $a as $n ) {
			$ch[] = _a( "?a=$adr.$n", $n );
		}
	} else {
		$chlcnt = 0;
	}

	_simple()
	->hdiv( _ic( 'parent' ) . 'Parents',
		_p( $fn_json ) .
		_p( implode( ' / ', $pr ) . ( count( $pr_link ) > 0 ? _p( _imp( $pr_link ) ) : '' ) )
	)
	->hdiv( _ic( 'sister' ) . "$siscnt sisters", $sis )
	->hdiv( _ic( 'child'  ) . "$chlcnt children", _imp_lim( $ch ) )
	;
}

function _atag( $t, $u ) {
	global $curtag;
	return $t == $curtag
		? "<b>$t</b>"
		: _a( "?a=$u$t", $t )
	;
}

function _imp_lim( $o ) {
	$lim = 300;
	return count( $o ) > $lim
		? _imp( array_slice( $o, 0, $lim ) ) . BR . _span( '.red', '-- snip --' )
		: _imp( $o )
	;
}

//.. json display
if ( $json != [] ) {
	//... データあり
	$cnt = 0;
	$out = '';
	$repin = [];
	$repout = [];

	//- 要素が一個のデータは、ダブルクオートだけ色付け
	$repin[]  = '/^"|"$/';
	$repout[] = _span( '.mark', '\0' );

	if ( $chlcnt > 0 ) {
		$repin = [ 
			'/^"(.+?)"/' ,
			'/^"|": ["{\[]?|[",}\]]+$|^[{\[]$/' ,
		];
		$repout = [
			'"' . _span( '.key', '\1' ) . '"',
			_span( '.mark', '\0' ) ,
		];
	}
	//- url色付け
	$repin[] = '/>(http:.+?)</';
	$repout[] = '>' . _ab( '$1', '$1' ) . '<';

	$omitflag = false;
	foreach ( explode( "\n", _json_pretty( $json ) ) as $linep ) {
		$line = ltrim( $linep );
		$lv = ( strlen( $linep ) - strlen( $line ) ) / 4;
		$out .= _t( "p | .l$lv",
			preg_replace( $repin, $repout, $line )
		);

		//- 200行以上続いたら終わり
		++ $cnt;
		if ( $cnt > MAXLINES ) {
			$omitflag = 1;
			break;
		}
	}
	_simple()->hdiv( 'JSON', 
		_div( '.json simple_border', $out )
		. ( $cnt > 2
			? _p( '.green bold', $omitflag ? 'The rest omitted' : 'End of data' ) 
			: ''
		)
	);
} else {
	//... データなし
	if ( $adr != '' and $file_list == [] ) {
		 if ( $adr == 'file' ){
			$a = [];
			foreach ( glob( DN_DATA . '/*.json*' ) as $pn ) {
				$f = strtr( basename( $pn, '.gz' ), [ '.json' => '' ] );
				$a[] = _a( "?a=file.$f", $f );
			}
			_simple()->hdiv( 'JSON files', _imp( $a ) );	
		} else {
			_simple()->hdiv( 'JSON', file_exists( $fn_json )
				? "empty JSON data: $fn_json"
				: "no JSON file: $fn_json"
			);
		}
	}
}

//.. file list
//- jsonファイルがないなら、適当なファイルリストを表示
if ( count( $file_list ) > 0 ) {
	_simple()->hdiv( "File list: {$file_list['dn']}", 
		_simple()->hdiv( 'Dir',
			implode( '', (array)$file_list['d'] ) ,
			[ 'type' => 'h2' ] 
		)
		.
		_simple()->hdiv( 'Json files',
			implode( '', (array)$file_list['f'] ) ,
			[ 'type' => 'h2' ]
		)
	);
}

//.. testinfo
$o = '';
foreach ( compact( 
	'adr', 'fn_json', 'json_type', 'json_name', 'tag_tree', 'tag_tree_orig'
) as $k => $v )
	$o .= TR.TH.$k.TD. ( is_array( $v ) ? _imp( $v ) : $v );

_testinfo( _t( 'table', $o ), 'values' );


//. page
_simple()
//.. conf
->page_conf([
	'title' => 'JSON view' ,
	'sub'	=> 'viewer for data used in EM Navigator/Yorodumi' ,
	'icon'	=> 'json' ,
])

//.. css
->css( <<<EOD

.js { margin: 10px; padding: 5px; border: 1px solid gray;}
.l1 { padding-left: 2em; }
.l2 { padding-left: 4em; }
.l3 { padding-left: 6em; }
.l4 { padding-left: 8em; }
.l5 { padding-left: 10em; }
.l6 { padding-left: 12em; }
.l7 { padding-left: 14em; }
.l8 { padding-left: 16em; }
.l9 { padding-left: 16em; }
.l10 { padding-left: 16em; }
.mark { color: #f66 }
.key { color: #088; font-style: oblique; }
EOD
)

//.. output
->out();

