<?php
/*

*/
//. misc define
require_once( 'cls_omoid.php' );

define( 'DN_OMO', TESTSV
	? realpath( __DIR__ . '/../omokage' )
	: '/mounts/filesv3/yorodumi/omokage'
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
