<?php
//. init
ini_set( "memory_limit", "512M" );
define( 'IMG_MODE', 'ym' );
require( __DIR__. '/common-web.php' );
require( __DIR__. '/omo-web-common.php' );
define( 'LISTMODE', _getpost( 'list' ) );

//.. subdata
_define_term( <<<EOD
TERM_TITLE
	Omokage search
	Omokage検索
TERM_SUB
	Shape similarity search of macromolecules
	生体超分子の形状類似性検索
TERM_SHOW_DET
	Show details
	解説を表示
TERM_ID
	ID of EMDB, PDB or SASBDB
	EMDB/PDB/SASBDBのID
TERM_ID_EMDB
	EMDB map| 4 or 5 digit ID | or string starts with "e"
	EMDB マップ| 4文字か5文字の数字のIDコード | または、"e"で始まる文字列
TERM_ID_PDB
	PDB deposited unit (asymetric unit, AU) | 4 letter ID code | or ID code + hyphen + "d"
	PDBの登録構造(非対称単位, AU)| 4文字のIDコード | または、IDに"-d"を付加
TERM_ID_PDB_ASB
	PDB assembly (biological unit, BU)| ID + hyphen + assembly-ID | Or click the image of AU/BU, which will be shown below when you input an PDB-ID
	PDBの集合体(生物学的単位, BU)| ID + ハイフン + assembly-ID | PDB-IDを入力した後に、この下に表示される画像をクリックして選択することもできます
TERM_ID_SASBDB
	SASBDB model| SASBDB-ID| or SASBDB-model-ID
	SASBDBのモデル| SASBDB-ID| または、SASBDB-model-ID
TERM_EG
	<i>e.g.</i>
	例:
TERM_SYMBOLIC
	Structure data giving symbolic results
	象徴的な結果が得られるサンプル
TERM_2190
	This is an EM map data of the RNA polymerase II at 25 A resolution. You will find atomic models and EM maps of RNA polymerase structures from the EMDB & PDB.
	RNAポリメラーゼIIの電子顕微鏡による解像度25Åの3Dマップデータです。この検索で、EMDBとPDBの両データベースから原子モデル・3Dマップ両方のRNAポリメラーゼの構造データが見つかります。
TERM_3IFV
	The DNA clamp is a ring shaped oligomer essential for DNA metabolism. All the DNA clamps have similar shapes, while bacterial clamps are dimer ring, and archaeal/eukaryal ones are trimer ring. By search using this trimer clamp, you will find dimer clamp structures as well.
	DNAクランプはDNA代謝に必須のリング状オリゴマー分子です。どのDNAクランプも形状はよく似ていますが、細菌では2量体、真核生物や古細菌では3量体のタンパク質がこれを構成します。この3量体クランプの検索から、両方のタイプのクランプの構造データが見つかります。
TERM_1OB2
	EF-Tu/tRNA complex (RNA + protein) and EF-G (monomeric protein) have similar shapes despite their different compositions ("molecular mimicry"). You will find both EFs by this search.
	翻訳伸長因子EF-Tu・tRNA複合体とEF-Gの形状はよく似ています。前者はタンパク質・RNA複合体、後者は単量体タンパク質なのに、です。このような類似性は「分子擬態」と呼ばれています。このEF-Tu・tRNAの検索から、EF-Gの構造データも見つかります。
TERM_S135
	This is a dummy-atom model of lumazine synthase obtained by SAS. You will find many crystal structures and EM maps of same molecules.
	このデータは小角散乱法で得られたルマジン合成酵素のダミー原子モデルです。この検索で、同じ分子の結晶構造やEMマップが見つかります。
TERM_1003
	Many 70S ribosome structure data are stored in EMDB and PDB. Omokage search is useful to find out such the huge molecule structures.
	EMDBとPDBにはたくさんの70Sリボソームの構造データが登録されています。Omokage検索はこういった巨大複合体の構造データを「総ざらい」するのにも便利です。
TERM_1UF2
	For huger molecule, such as this rice dwarf virus, Omokage search can be used.
	さらに巨大な分子、例えばこのイネ萎縮ウイルスのような構造データ検索にも、Omokage検索は役に立ちます。
TERM_1DL4
	Not only for the huge structures but also for small molecules, such as this DNA fragment, this tool might be useful. By this search, some proteins having similar shape to this DNA fragment are found (for good or for bad).
	巨大な分子だけでなく、例えばこのDNA断片のような、比較的小さな分子の構造データにも使えます。この結果からはDNA断片だけでなく、(幸か不幸か)それに似た形状のタンパク質の構造も見つかります。
TERM_4ES0
	This is another example for small molecules. Which proteins have simialar shapes to this beta-propeller structure?
	これは比較的小さな分子のもう一つの例です。このようなベータプロペラ構造と形状が似ているタンパク質には、どのようなものがあるでしょうか？
TERM_PERF_SEARCH
	_Perform search.
	検索を実行

TERM_UPLOAD
	You can use your original data or a modified data for the search. Select type of the file and upload the file by this form.
	オリジナルの構造や、既存のものを改変した構造を使った検索が可能です。下のフォームでデータ形式を選択し、ファイルをアップロードしてください。

TERM_UPLOAD_MODEL
	This is a search using PDB format coordinate file (atomic model, SAXS bead model, etc) | Uncompressed or gzip-compressed file can be used
	PDB形式の座標ファイル (原子モデル、SAXSビーズモデルなど) を利用した検索を実行します | 非圧縮または、gzip圧縮したファイルを利用できます

TERM_UPLOAD_MAP
	This is a search using 3D density map file in CCP4/MRC format. | Only for map with cubic voxels. | Max voxel number is ~200. Large map data should be trimmed and shrined before uploading. | Uncompressed or gzip-compressed file can be used
	CCP4/MRC 形式の3次元密度マップファイルを利用した検索を実行します | ボクセルが立方体のマップのみです | 最大ボクセル数は200程度です。事前に切り出しや縮小処理をすることを推奨します | 非圧縮または、gzip圧縮したファイルを利用できます

TERM_UPLOAD_VQ
	This is a search using "Codebook" vectors made by <i>qvol</i> or <i>qpdb</i> tool in <a href="http://situs.biomachina.org" target="_blank"><i>Situs</i> package</a>. Two models, model with 30 and 50 points are required for Omokage comparison. " ,
	<a href="http://situs.biomachina.org" target="_blank"><i>Situs</i> パッケージ</a>のqvolかqpdbで作成したコードブックベクトルを利用した検索を実行します。|Omokage比較では、30点と50点の2つのモデルを利用します。


TERM_TAB_DBSTR
	<b>Registered data</b><br>structure in databanks
	<b>登録データ</b><br>データベースに登録されている構造
TERM_TAB_UPLOAD
	<b>Upload</b><br>your original/modified data
	<b>アップロード</b><br>オリジナルの構造で検索
TERM_IDENT_ASB
	Assembly #_2_ of PDB-_1_ is identical to the deposited model. Search is performed using deposited model.
	PDB-_1_の集合体#_2_は登録構造と同一です。登録構造を利用して検索を行います。


TERM_ERR_GET
	Couldn't get data file
	データを受信できませんでした。
TERM_ERR_SIZE
	Too large file uploaded. The file must be smaller than 10MB.
	ファイルが大きすぎます。上限は10MBです。
TERM_ERR_END
	Uploading process is terminated
	アップロードが完了しませんでした
TERM_RECEIVED
	Data successfully received
	データを受信しました

TERM_NO_QUERY_DATA
	Query data not found
	検索条件データが見つかりません
EOD
);

//.. sample IDs
define( 'SAMPLE_IDS_REC', [
	'e2190'  => TERM_2190 ,
	'3ifv-d' => TERM_3IFV ,
	'1ob2-d' => TERM_1OB2 ,
	's135'   => TERM_S135 ,
	'e1003'  => TERM_1003 ,
	'1uf2-1' => TERM_1UF2 ,
	'1dl4-d' => TERM_1DL4 ,
	'4es0-d' => TERM_4ES0 ,
]);

define( 'SAMPLE_IDS_EMDB', [
	'e1022', 'e1531', 'e1603', 'e1714', /*'e2572',*/ 'e2651', 'e5001', 'e5130', 'e5225',
	'e5438', 'e5646', 'e6106'
]);

define( 'SAMPLE_IDS_PDB', [
	'1a00-d', '1cg6-1', '1g8w-d', '1hen-d', '1opf-1', '1ryp-d', '1xi5-1', '2qc9-d', '2r4p-1',
	'2rcs-d', '2xct-1', '3ajp-1', '3bri-d', '3cre-d', '3lz0-d', '3p4g-3', '3vmy-1', '4bse-1',
	'4gxl-d', '4hfh-d', '4mya-1', '6r1r-4', '6tna-d', '1k4c-1', '4mna-d'	
]);

define( 'SAMPLE_IDS_SASBDB', [
	's8', 's39', 's100', 's36', 's68', 's116', 's239'
]);

//.. ID 決定
//$id = UPLOAD ? '' : strtolower( _getpost( 'id' ) );
$o_omoid = new cls_omoid();
extract( $o_omoid->get() ); //- $id, $db, $asb

//- upload?
define( 'UPLOAD', count( $_FILES ) > 0 );
define( 'USERDATA', 
	UPLOAD || ( strlen( $id ) == 33 && substr( $id, 0, 1 ) == '_' )
);

//- 登録構造と同じアセンブリ
$o_identasb = '';
if ( ! UPLOAD && $db == 'pdb' && ! $o_omoid->ex_vq() 
	&& in_array(
		$asb, 
		(array)json_decode( _ezsqlite([
			'dbname' => 'pdb' ,
			'where'  => [ 'id', $o_omoid->o_id->id ] ,
			'select' => 'json' ,
		]))->identasb
	) 
) {
	$o_omoid2 = new cls_omoid( $o_omoid->id );
	if ( $o_omoid2->ex_vq() ) {
		$o_identasb = clone $o_omoid;
		$o_omoid = clone $o_omoid2;
		extract( $o_omoid->get() ); //- $id, $db, $asb
	}
}

//define( 'NO_ID', !UPLOAD && !USERDATA && !$o_omoid->ex_vq() );
define( 'NO_ID', UPLOAD || USERDATA || !$o_omoid->ex_vq() );
if ( NO_ID ) 
	$id = '';

//. contents

//.. クエリ DBの中のデータ
$help_id = [];
foreach ([
	[ TERM_ID_EMDB    , '1003, EMD-1003, e1003' ] ,
	[ TERM_ID_PDB     , '100d, 100d-d' ] ,
	[ TERM_ID_PDB_ASB ,	'1oel-1' ] ,
	[ TERM_ID_SASBDB  , 'SASDAA5, 100, sas-100' ],
] as $c ) {
	list( $c, $eg ) = $c;
	list( $title, $details ) = explode( '|', $c, 2 );
	$help_id[] = "<b>$title</b>"
		. _term_ul( $details. '|'. _span( 'green', TERM_EG. " $eg" ) )
	;
}

//... input box
$subj_db = ''
. TERM_ID
. ': '
. _idinput(
	$id ?: _getpost( 'id' ) ,
	[
		'btnlabel'	=> _l( 'Search' ) ,
		'size' 		=> 15 ,
		'acomp'		=> 'omoid' ,
		'posttext'	=> _more(
			_ul( $help_id ) . _doc_pop( '3databanks' ) ,
			[ 'btn' => '?', 'btn2' => _l( 'hide' ) ]
		)
	]
)

//... sample box
. _div( '.clboth' , '')
. _simple_tabs(
	'#sampleids' ,
	'Samples' ,
	[
		'tab' => 'Recommended',
		'div' => TERM_SYMBOLIC. ' '
			. _btn( '!$(\'.sampledesc\').toggle(200);', TERM_SHOW_DET ). BR
			. _sample_img( SAMPLE_IDS_REC ) 
		,
		'active' => true
	], [
		'tab' => 'EMDB' ,
		'div' => _p( _doc_pop( 'emdb' ) ). _sample_img( SAMPLE_IDS_EMDB ) ,
	], [
		'tab' => 'PDB' ,
		'div' => _p( _doc_pop( 'pdb' ) ) . _sample_img( SAMPLE_IDS_PDB ) ,
	], [
		'tab' => 'SASBDB' ,
		'div' => _p( _doc_pop( 'sasbdb' ) ). _sample_img( SAMPLE_IDS_SASBDB ) ,
	]
);

//.. クエリ ユーザーオリジナルデータ
$hidden = '';
$action = '';
/*
if ( HOST == 'nbdc' ) {
	$hidden = _e( 'input | type:hidden | name:uihost | value:' . $_SERVER[ 'HTTP_HOST' ] );
	$action = ' |action="http://ipr.pdbj.org/emnavi/omo-search.php"' ;
define( 'BTN_SUBMIT', $hidden . BR . _e( 'input | type:submit | .submitbtn' ) );
}
*/

$file = 'input| type:file| required| name';

$subj_orig = ''
. TERM_UPLOAD
. _simple_tabs(
	'#data_type' ,
	'Data type' ,
	[
		'tab' => 'Model' ,
		'active' => true ,
		'div' => _upload_form( TERM_UPLOAD_MODEL, [
			'File' => _e( "$file:pdb" )
		]) ,
	], [
		'tab' => 'Density map' ,
		'div' => _upload_form( TERM_UPLOAD_MAP, [
			'File' => _e( "$file:map" ) ,
			'Surface level'
				=> _input( 'number', 'name:thr| size: 10| step:any', 1 )
		]) ,
	], [
		'tab' => 'Codebook vectors' ,
		'div' => _upload_form( TERM_UPLOAD_VQ, [
			'30-dot model' => _e( "$file:vq30" ) ,
			'50-dot model' => _e( "$file:vq50" )
		]) ,
	]
);

function _upload_form( $txt, $in ) {
	global $action; //- 計算サーバーが外にある場合用
	return _term_ul( $txt )
	. _t(
		"form | method:post | enctype:multipart/form-data $action",
		_table_2col( $in ). _input( 'submit', 'st: width:20em' )
	);
}

//.. 検索フィルタ (中止)
/*
$filters = TEST ? ''
	. _p( 'Filter by composition similarity: '
		. _radiobtns( [
			'name' => 'mode_compos',
			'on' => _getpost('mode_compos') ?: 'no' ,
			'otheropt'=>'form:id_form' 
		], [
			'no'	=> _l( 'none' ) ,
			'sim'	=> _l( 'similar only' ) ,
			'dis'	=> _l( 'different only' ) ,
		])
	)
	. _p( 'Filter by Keyword: '
		. _e( 'input'
			. '|.acomp| type:search| name:kw| list:acomp_kw| size:40| form:id_form'
			. '|value:' . _getpost( 'kw' )
		)
	)
	. _e( 'input | type:submit | .submitbtn| form:id_form| value:' . _l( 'Search' ) )
: '';
$filters = '';
*/

//.. search query
$_simple->hdiv( 'Search query' ,
	_simple_tabs(
		'Query structure data' ,
		[ 
			'tab' => [ 'database', TERM_TAB_DBSTR ] ,
			'active' => true ,
			'div' => $subj_db ,
		], [
			'tab' => [ 'upload', TERM_TAB_UPLOAD ] ,
			'div' => $subj_orig
		]
	)
	. $filters
	. _t( 'datalist | #acomp_omoid', '' )
	,
	[ 'id' => 'query', 'hide' => ! NO_ID || UPLOAD, 'only' => true ]
);

//.. uploaded data
if ( UPLOAD ) {
//	_die([ 'upload' => $_FILES ]);
	_testinfo( $_FILES, '$_FILES' );

	$uniqid = '_' . md5( uniqid( rand(), 1 ) );
	if ( is_array( $_FILES[ 'map' ] ) ) {
		//- map data
		$type = 'map';
		$size = number_format( $_FILES[ 'map' ][ 'size' ] );
		$sfn = $_FILES[ 'map' ][ 'tmp_name' ];
		if ( file_exists( $sfn ) )
			$ok = move_uploaded_file( $sfn, _fn( 'user_map', $uniqid, ADD_PRE ) );
	} else if ( is_array( $_FILES[ 'pdb' ] ) ) {
		//- pdb data
		$type = 'PDB';
		$size = number_format( $_FILES[ 'pdb' ][ 'size' ] );
		$sfn = $_FILES[ 'pdb' ][ 'tmp_name' ];
		if ( file_exists( $sfn ) )
			$ok = move_uploaded_file( $sfn, _fn( 'user_pdb', $uniqid, ADD_PRE ) );
	} else {
		//- vq
		$type = 'codebook vectors';
		$size = number_format( $_FILES[ 'vq30' ][ 'size' ] )
			. ' + ' . number_format( $_FILES[ 'vq50' ][ 'size' ] );
		$sfn1 = $_FILES[ 'vq30' ][ 'tmp_name' ];
		$sfn2 = $_FILES[ 'vq50' ][ 'tmp_name' ];
		if ( file_exists( $sfn1 ) && file_exists( $sfn2 ) ) {
			$ok =
				move_uploaded_file( $sfn1, _fn( 'user_vq30', $uniqid, ADD_PRE ) )
				&&
				move_uploaded_file( $sfn2, _fn( 'user_vq50', $uniqid, ADD_PRE ) )
			;
		}
	}
	if ( ! $ok )  {
		$e = $_FILES[ 'map' ][ 'error' ] . $_FILES[ 'pdb' ][ 'error' ];
		$ermsg = TERM_ERR_GET . _kakko( "error code: $e" );
		if ( $e == UPLOAD_ERR_INI_SIZE || $e == UPLOAD_ERR_FORM_SIZE )
			$ermsg = TERM_ERR_SIZE;
		if (  $e == UPLOAD_ERR_PARTIAL )
			$ermsg = TERM_ERR_END;
	}

	$_simple->hdiv( 'Received data' ,
		$ok ? ''
			. _p( TERM_RECEIVED )
			. _table_2col([
				'Type'				=> $type ,
				'File Size (byte)'	=> $size ,
				'Search ID'			=> $uniqid
			])
		: _p( _l( 'Uploding error' ) . ": $ermsg" )
	);
	if ( $ok ) {
		$id = $uniqid;
	}
}

//.. identasb
if ( $o_identasb != '' ) {
	$i = $o_identasb->id;
	$a = $o_identasb->asb;
	$_simple->hdiv( 'Identical assembly', _term_rep( TERM_IDENT_ASB, $i, $a ) );
}

//.. 隠しボックス
$_simple->add_contents( ''
	//- subject 
	. _div( '#subj_box', '' )

	//- result
	. _div( '#result_box | .clboth', $id != '' ? LOADINGT : '' )
	. _test( _div( '#test_box|.hide', '' ) )
);

//.. notid
if ( NO_ID && ! UPLOAD && _getpost( 'id' ) != '' ) {
	$s = _getpost( 'id' );
	$_simple->hdiv(
		TERM_NO_QUERY_DATA ,
		_p( _ab([ 'ysearch', 'kw' => $s ], IC_SEARCH. _term_rep( TERM_KW_SEARCH, $s )))
	);
}

/*
//. 札幌から大阪に繋ぎ変え （現在無効）
//_testinfo( [ 'userdata' => ( substr( $id, 0, 1 ) == '_' )], 'data type' );

$uihost = _getpost( 'uihost' );
if ( $uihost != '' ) {
	$_simple->meta([
		'http-equiv' => 'refresh,
		'content' => "5;url=http://$uihost/emnavi/omo-search.php?id=$id"
	]);
}
*/


//. output
$_simple->page_conf([
	'title' 	=> TERM_TITLE ,
	'sub'		=> TERM_SUB ,
	'icon'		=> 'omokage' ,
	'openabout'	=> ( NO_ID && ! UPLOAD ) ,
	'js'		=> [ 'omos' ] ,
	'newstag'	=> 'omo' ,
	'docid'		=> 'about_omosearch'
])

//.. css
->css( <<<EOD

//- サンプル
.sampledesc { display: none; }

//- スコア
.rank { font-weight:bold; fonst-size: larger }

//- スコアのグラフ
.sbar { border: 1px solid #009; padding: 1px; margin: 1px; display: inline-block;
	background: white;
	height: 0.5em; width: 10em;
	box-shadow: 0 0.1em 0.1em 0.1em rgba(0,0,0,0.2) inset;
}
.sbari { height: 100%; background: #009;
	box-shadow: 0 0.1em 0.2em 0.2em rgba(255,255,255,0.4) inset;
}

//- 結果ボックス
.topline { border-top: 1px solid $col_medium;}
.rdsc { display: none; }

.iimg { width: 100px; height: 100px; border: 2px solid white; margin: 1px; }

#test_box {
	border: 2px solid red; font-size: small; padding: 0.5em; display: none;
}
//- dbid
.filt_inc { background: #bbf; border: 1px solid #00a; }
.filt_exc { background: #fbb; border: 1px solid #a00; }
}

EOD
)

//.. jsvar
->jsvar([
	'id' 		=> $id ,
	'userdata'	=> UPLOAD ,
	'ajaxurl' 	=> URL_OMOAJAX ,
	'postvar' => [
		'id'			=> $id ,
		'thr'			=> _getpost( 'thr' ) ,
		'list'			=> _getpost( 'list' ) ,
		'pg'			=> _getpost( 'pg' ) ,
		'gmref' 		=> _getpost( 'gmref' ) ,
		'gmrefn'		=> _getpost( 'gmrefn' ) ,

		'mode_compos'	=> _getpost( 'mode_compos' ) ,
		'lev_compos'	=> _getpost( 'lev_compos' ) ,
		'kw'			=> _getpost( 'kw' ) ,
		'dbid_inc'		=> _getpost( 'dbid_inc' ) ,
		'dbid_exc'		=> _getpost( 'dbid_exc' ) ,
		'mode_db'		=> _getpost( 'mode_db' ),
		'actab'			=> _getpost( 'actab' ),
		'lang'			=> _ej( 'en', 'ja' ) ,
//		'goid'	=> _	getpost( 'goid' ) ,
	] ,
])

//.. output
->out();

//. function
//.. _term_ul
function _term_ul( $term ) {
	$ret = [];
	foreach ( explode( '|', $term ) as $s )
		$ret[] = trim( $s );
	return _ul( $ret, 0 );
}

//.. _sample_img
function _sample_img( $a ) {
	$ret = '';
	foreach ( $a[0] ? array_fill_keys( $a, null ) : $a as $i => $str ) {
		$f = substr( $i, 0, 1 );
		if ( $f == 's' ) {
			$n = ( new cls_entid() )->set_sasmodel( $i )->imgfile();
		} else if ( $f == 'e' ) {
			$n = ( new cls_entid() )->set_emdb( $i )->imgfile();
		} else {
			list( $i4, $a ) = explode( '-', $i );
			$n = $a == 'd' ? _url( 'pdb_img_dep', $i4 ) : _url( 'pdb_img_asb', $i4, $a );
		}
		$ret .= _img( ".enticon sampleicon| !_sample('$i',this);", $n )
			. ( $str 
				? _t( "p| .clearfix sampledesc", $str. _a( "?id=$i", TERM_PERF_SEARCH ) )
				: '' 
			)
		;
	}
	return $ret;
}

