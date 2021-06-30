<?php
/*
- DB EMDBのみか、PDBも含めるか
- 最新のみか、全部
- コロナ全部、ベータコロナ、sars関連、sars-cov-2、コロナレセプター、コロナ治療薬
*/

//. init
define( 'COLOR_MODE', 'emn' );
define( 'IMG_MODE', 'em' );
require( __DIR__. '/common-web.php' );

//_add_lang( 'esearch' );
//_add_fn(   'esearch' );
_define_term( <<<EOD
EOD
);

define( 'NAME_SARS_COV_2', 'Severe acute respiratory syndrome coronavirus 2' );
define( 'NAME_LINE',[
	'sars' => 'Severe acute respiratory syndrome-related coronavirus' ,
	'corona' => 'Cornidovirineae'
]);
define( 'TYPE_KW', [
	'spike' => 'in:ipr027400' ,
	'rnap'  => 'in:ipr018995' ,
	'ace2'	=> 'un:q9byf1' ,
]);

//.. term
_define_term( <<<EOD
TERM_COVID19_STR
	Covid-19 related structure data
	Covid-19関連構造データ

TERM_COV2_REGEND
	Coronavirus, 2020. Modified from the original illustration by David S. Goodsell@RCSB PDB
	コロナウイルス, 2020. David S. Goodsell@RCSB PDB による原画を修正

TERM_ADV_SEARCH
	Advanced search by "EMN Search"
	「EM Navigator検索」で詳細な検索

TERM_REL_INFO
	Related info
	関連情報

TERM_YM_PAGES
	Yorodumi pages
	万見のページ

TERM_YM_SEARCH
	Search for "SARS-CoV-2" - Yorodumi search
	「SARS-CoV-2」を検索 - 万見検索

TERM_YM_PROTEIN
	Proteins of SARS-CoV-2 - Yorodumi search
	SARS-CoV-2由来のタンパク質 - 万見検索

TERM_PDBJ_PAGES
	PDBj & wwPDB member pages
	PDBjのページ

TERM_PDBJ_PAGETITLE
	COVID-19 featured content - PDBj
	新型コロナウイルスの構造情報 - PDBj

TERM_BMRB_PAGETITLE
	COVID-19/SARS-CoV-2 featured content - PDBj-BMRB
	新型コロナウイルスの特集コンテンツ - PDBj-BMRB

TERM_MOM
	Molecule of the Month
	今月の分子

TERM_SPIKE
	Spike glycoprotein
	スパイク糖タンパク質

TERM_POLYPRO
	RNA polymerase/Protease, etc
	RNAポリメラーゼ・プロテアーゼなど

TERM_RECEPTOR
	Virus receptor (human)
	ウイルス受容体（ヒト）
EOD
);
/*
TERM_COV_PROTEASE
	Coronavirus Proteases
	コロナウイルスプロテアーゼ

URL_MOM_PROTEASE
	http://pdb101.rcsb.org/motm/242
	https://numon.pdbj.org/mom/242

TERM_COV_SPIKE
	SARS-CoV-2 Spike
	SARSコロナウイルス2 スパイク

URL_MOM_SPIKE
	http://pdb101.rcsb.org/motm/246
	https://numon.pdbj.org/mom/246

TERM_COV_POLYM
	SARS-CoV-2 RNA-dependent RNA Polymerase
	SARSコロナウイルス2 RNA依存性RNAポリメラーゼ

URL_MOM_POLYM
	http://pdb101.rcsb.org/motm/249
	https://numon.pdbj.org/mom/249


*/

//. getpost
//- G_HOGE: get/postから来た値
define( 'G_RANGE'	, _getpost( 'range' )	?: 50 );
define( 'G_PAGE'	, (integer)_getpost( 'page' )	?: 0 );
define( 'G_DB'		, _getpost( 'db' )		?: 'both' );
define( 'G_REL'		, _getpost( 'rel' )		?: 'all' );
define( 'G_SRC'		, _getpost( 'src' )		?: 'scov2' );
define( 'G_TYPE'	, _getpost( 'type' )	?: 'all' );

//define( 'G_DISPMODE' , $m == 10 ? 'list' : $m ); //- 表示モード
//define( 'G_KW'		, _getpost( 'kw' ) );

//. where作成
$where = [];

//.. db
if ( G_DB != 'both' )
	$where[] = _sql_eq( 'database', strtoupper( G_DB ) );

//.. rel
if ( G_REL == 'new' )
	$where[] = _sql_eq( 'release', _release_date() ); 

//.. spec
if ( G_TYPE != 'ace2' ) {
	if ( G_SRC == 'corona' || G_SRC == 'sars' ) {
		$spec_list = ( new cls_sqlite( 'taxo' ) )->qcol([
			'select' => [ 'name' ] ,
			'where' => [
				_sql_like( 'line', NAME_LINE[ G_SRC ], '|' ) ,
				'emdb + pdb != 0'
			]
		]);
		$where[] = _sql_like( 'spec', $spec_list, '|' );
	} else if ( G_SRC == 'scov2' ) {
		$where[] = _sql_like( 'spec', NAME_SARS_COV_2, '|' );
	}
}

//.. type
if ( G_TYPE != 'all' ) {
	$where[] = _sql_like( 'search_words', TYPE_KW[ G_TYPE ] );
}

//. emn search へのリンク
$esearch = [];
$esearch = [
	0 => 'esearch' ,
	'kw'	=> G_TYPE != 'all' ? '"'. TYPE_KW[ G_TYPE ]. '"' : '',
	'db'	=> G_DB == 'both' ? '' : strtolower( G_DB ) ,
	'new'	=> G_REL == 'new' ? 'new' : '',
]; 

if ( G_TYPE != 'ace2' ) {
	$esearch['kw'] .= ' "spec:Severe acute respiratory syndrome coronavirus 2"';
	if ( in_array( G_SRC, [ 'sars', 'corona' ] ) )
		$esearch = '';
}

//. カタログ作成
$o_sql = new cls_sqlite( 'main' );
// _testinfo( $where );
$o_pager = new cls_pager([
	'total'		=> $o_sql->where( $where )->cnt() ,
	'page'		=> G_PAGE ,
	'range'		=> G_RANGE ,
	'func_name' => '_catalog' ,
	'objname'	=> 'catalog' ,
]);
define( 'CATALOG', _div( '#catalog', ''
	. $o_pager
	. _ent_catalog( 
		(array)$o_sql->qcol([
			'select'	=> [ 'db_id' ] ,
			'where'		=> $where ,
			'order by'	=> 'release DESC, sort_sub DESC, db_id' ,
			'limit'		=> G_RANGE ,
			'offset'	=> G_PAGE * G_RANGE ,
		]) ,
		[ 'mode' => 'icon' ]
	)
	. $o_pager->btn()
	. ( $esearch
		? _p( _a( _local_link( $esearch ), _ic( 'search' ). TERM_ADV_SEARCH ) )
		: ''
	)
));
//_testinfo( $o_sql->getsql() );

//. ajax
if ( _getpost( 'ajax' ) )
	die( CATALOG );

//. ページ作成
$_simple
->page_conf([
	'title' 	=> _ej( 'EMN Covid-19 info', 'EMN Covid-19情報' ) ,
	'icon'		=> 'emn' ,
	'openabout'	=> false ,
//	'js'		=> [ 'esearch' ] ,
	'docid' 	=> 'about_covid19' ,
	'newstag'	=> 'emn' ,
//	'auth_autocomp' => true ,
])

//.. 構造データエリア
->hdiv( TERM_COVID19_STR, _div( '.clearfix', ''
	. _pop(
//		'https://pdbj.org/images/coronavirus_feature-page.png' ,
		'https://pdbj.org/cms-data/images/coronavirus_feature-page.png' ,
		TERM_COV2_REGEND ,
		[
			'type' => 'img',
			'trgopt' => '.poptrg cov2_icon'
		]
	)
	. _t( 'form | autocomplete:off | #form1', _table_2col([
		'Database'	=> _radiobtns( [ 'name' => 'db', 'on' => G_DB ], [
			'both'	=> _l( 'EMDB & PDB' ),
			'EMDB'	=> 'EMDB' ,
			'PDB'	=> 'PDB' ,
		]). _div( '.right small', _doc_pop( 'emn_source' ) ) ,

		'Release'	=> _radiobtns( [ 'name' => 'rel', 'on' => G_REL ], [
			'all'	=> _l( 'ALL' ),
			'new'	=> _l( 'latest only' ) ,
		]),
		'Source'		=> _radiobtns( [ 'name' => 'src', 'on' => G_SRC ], [
			'scov2'	=> 'SARS-CoV-2' ,
			'sars'	=> 'SARS related coronavirus' ,
			'corona' => 'All coronavirus' ,
		]) ,
		'Type'		=> _radiobtns( [ 'name' => 'type', 'on' => G_TYPE ], [
			'all'	=> 'All' ,
			'spike'	=> TERM_SPIKE , 
			'rnap'	=> TERM_POLYPRO ,
			'ace2'	=> TERM_RECEPTOR ,
		]) ,
	]). _input( 'submit', 'st: width:20em' ) ))
	. CATALOG
)
//.. 関連情報エリア
->hdiv( TERM_REL_INFO, ''
	. $_simple->hdiv( TERM_YM_PAGES, _ul([
		_obj('taxo')->item( NAME_SARS_COV_2 ) ,
		_ab(
			_local_link([ 'ysearch', 'kw' => '"'. NAME_SARS_COV_2. '"' ]),
			TERM_YM_SEARCH
		),
		_ab( 
			_local_link([ 'ysearch', 'kw' => '"'. NAME_SARS_COV_2. '"', 'act_tab' => 'dbid' ]) ,
			TERM_YM_PROTEIN
		)

	]), [ 'type' => 'h2' ] )
	. $_simple->hdiv( TERM_PDBJ_PAGES, _ul([
		_ab( 'https://pdbj.org/featured/covid-19', TERM_PDBJ_PAGETITLE ) ,
		_ab( 'https://bmrbj.pdbj.org/top_search/covid-19', TERM_BMRB_PAGETITLE ) ,
		TERM_MOM. _ul([
			_mom_link( 242 ) ,
			_mom_link( 246 ) ,
			_mom_link( 249 ) ,
			_mom_link( 256 ) ,
		])
	]), [ 'type' => 'h2' ] )
)

//.. css
->css( <<<EOD
.cov2_icon {
	float: right;
	width: 200px; height: 200px;
}
EOD
)

//.. js
->js( <<<EOD
$( function() { 
	$('#form1').change( function(){ _catalog(); } );
});
function _catalog( page ) {
	$('#catalog')._loadex({
		u:'?ajax=1&page=' + page,
		v: $('#form1').serialize(),
		speed: 'medium'
	})
	.fadeTo( 'fast', 1 );
}
EOD
)

//.. output
->out();

