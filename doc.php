<?php
require( __DIR__. '/common-web.php' );
define( AJAX, (boolean)_getpost( 'ajax' ) );

//. 表示する文書を選ぶ
define( 'ID',	_getpost_safe( 'id' ) );
define( 'TAG',	_getpost_safe( 'tag' ) );
define( 'TYPE',	_getpost_safe( 'type' ) );
define( 'KW',	_getpost( 'kw') );

_add_lang( 'doc' );
_define_term( <<<EOD
TERM_SEARH_FILT
	Search & filter documents
	表示する文書の検索と絞り込み
TERM_NOT_FOUND
	Not found
	文書が見つかりませんでした
TERM_NO_DOC
	No document found for keyword, "_1_"
	検索語「_1_」に該当する文書が見つかりませんでした
TERM_SUB_EMN
	EM Navigator related documents
	EM Navigatorに関連する文書
TERM_SUB_YM
	Yorodumi related documents
	万見に関連する文書
TERM_SUB_OMO
	Yorodumi related documents
	Omokage検索に関連する文書
TERM_SUB_FAQ
	FAQ (Frequently Asked Questions)
	FAQ (ありがちな質問と回答)
TERM_SUB_NEWS
	News & blogs
	お知らせとブログ
TERM_SUB_INFO
	misc. information
	情報
EOD
);
$sqlite = new cls_sqlite( 'doc' );

//.. フィルタ
$ids = $rel = [];
if ( ID ) {
	//- ID指定
	$ids = [ ID ];
	$rel = json_decode( $sqlite->qcol([
		'select' => 'json',
		'where'  => 'id='. _quote( ID )
	])[0] )->rel;
} else if ( TAG . TYPE . KW == '' ) {
	//- 全部表示
	$ids = $sqlite->qcol([
		'select'   => 'id',
		'order by' => 'num' ,
	]);
} else {
	//- キーワード
	$where = _kw2sql( KW, 'doc' );

	//- タイプ
	if ( TYPE )
		$where[] = 'type = '. _quote( TYPE );
	
	//- tag
	if ( TAG )
		$where[] = _like( 'tag',  '|'. TAG. '|' );

	//- 取得
	$ids = $sqlite->qcol([
		'select'   => 'id',
		'where'    => $where,
		'order by' => 'num' ,
	]);
}

//.. 読み込み
define( 'MANY', count( $ids ) > 10 );
$count = [];
//_testinfo( $ids, 'ids' );

$out = [];
foreach ( $rel as $id ) {
	$out['rel'] .= _doc_hdiv( $id, [ 'hide' => MANY ] );
}

foreach ( $ids as $id ) {
	$t = $sqlite->qcol([
		'select' => 'type',
		'where'  => "id='$id'"
	])[0];
	$out[ $t ] .= _doc_hdiv( $id, [ 'hide' => MANY ] );
	++ $count[ $t ];
}
//_testinfo( $out, 'out' );

//. 作成

//.. フォーム
$get = $_GET;
unset( $get['lang'] );
$get = '?'. http_build_query( $get );
$samples = [];
foreach ([
	'?type=faq'	 => 'FAQ' ,
	'?type=news' => 'News' ,
	'?type=info' => 'Information' ,

	'?tag=emn'	 => 'EM Navigator' ,
	'?tag=ym'	 => 'Yorodumi' ,
	'?tag=omo'	 => 'Omokage search' ,
	'?tag=about' => 'Services' ,
	'?'			 => 'All docs' ,
] as $url => $name ) {
	$samples[] = $get== $url
		? _span( '.bld', IC_HELP. _l( $name ) )
		: _a( $url, IC_HELP. _l( $name ) )
	;
}

$_simple->hdiv(
	TERM_SEARH_FILT ,
	_t( 'form | method:get | action:' ,
		_l( 'Keywords' ) . ': '
		. _input( 'search', 'name:kw | size: 30', KW )
		. _input( 'submit' )
	)
	. _p( _imp( $samples ) )
//	. _test(
//		_p( _span( 'st:background: pink', _a( '_mng-docs.php', 'データベース更新' ) ) )
//	)
	,
	[ 'hide' => ID != '' ]
);

//.. ドキュメント
$btns = MANY
	? BTN_HDIV2_ALL
	: ''
;

foreach([
	'news' => 'News' ,
	'faq'  => 'FAQ' ,
	'info' => 'Information' ,
	'rel'  => 'Related docs' ,
] as $type => $name ) {
	if ( ! $out[ $type ] ) continue;
	$_simple->hdiv(
		_l( $name ). _kakko( $count[ $type ] ),
		( MANY ? BTN_HDIV2_ALL : '' ). $out[ $type ]
	);
}

if ( ! $ids ) {
	$_simple->hdiv(
		TERM_NOT_FOUND ,
		_term_rep( TERM_NO_DOC, KW )
	);
}

//. output
$_simple->out([
	'title' => _ej( 'Yorodumi Docs', '万見文書' ) ,
	'icon'	=> 'help' ,
	'sub'	=> [
		'emn'	=> TERM_SUB_EMN ,
		'ym'	=> TERM_SUB_YM ,
		'omo'	=> TERM_SUB_OMO ,
		'faq'	=> TERM_SUB_FAQ ,
		'news'	=> TERM_SUB_NEWS , 
		'info'	=> TERM_SUB_INFO ,
	][ ID . TAG . TYPE . KW ] ,
	'openabout' => $get == '' ,
	'docid' => 'about_doc' ,
]);

