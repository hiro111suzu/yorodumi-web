<?php
//. init
define( 'EM_MODE'    , $_GET[ 'em' ] || $_POST[ 'em' ] );
define( 'COLOR_MODE' , EM_MODE ? 'emn' : 'ym' );
define( 'IMG_MODE'   , EM_MODE ? 'em' : 'y' );
require( __DIR__. '/common-web.php' );

define( 'ID', _getpost( 'id' ) );
define( 'NUM_IN_PAGE', 20 );
define( 'KW'	, _getpost( 'kw' ) );
define( 'ANS'	, _getpost( 'mode' ) == 'ans' );

$o_sqlite = new cls_sqlite( 'authori' );

//. ajax reply
if ( _getpost( 'ajax' ) == 'list' ) {
	die( _getlist() );
}

//. form
//.. 検索条件
$_simple->hdiv( _ej( 'Search query', '検索条件' ) ,
	_t( 'form | #form1', _t( 'table', ''
		//- ID
		.TR.TH. 'ID'
		.TD. _inpbox( 'id', '' )

		//- keywords
		.TR.TH. _l( 'Keywords' )
		.TD. _inpbox( 'kw', KW, [ 'acomp' => 'kw' ] )
	)
		. _e( 'input | type:submit | st: width:100% | .submitbtn' ) 
	) 
	,
	[ ]
);

//. データ表示
if ( ID == '' ) {
	//- 検索結果
	$_simple->hdiv(
		_ej( 'Entry list', 'エントリリスト' ) ,
		_getlist() ,
		[ 'id' => 'list' ]
	);
} else {
	//- 単独データ取得
	$_simple->hdiv( ( ANS ? 'Ancestors' : 'Descendants' ) 
		. ' of PDB-' . ID
		,
		_dig( ID )
	);
}

//. output

$_simple
->css( <<<EOD
table { width: 100% }
.branch { margin-left: 1em; border-left: 5px solid $col_medium }
EOD
)->out([
	'title' => 'UseCount',
//	'icon'	=> 'lk-black.gif' ,
//	'docid' => EM_MODE ? 'about_empap' : 'about_pap' ,
//	'newstag' => EM_MODE ? 'emn' : 'ym' ,
//	'auth_autocomp' => true ,

]);

//. function _getlist: リスト作成
function _getlist() {
	global $o_sqlite;
	$page = _getpost( 'page' ) ?: 0;

	//.. クエリ作成
	$where = [];
	$term = [];

	//- キーワード
	foreach ( (array)_kwprep( KW ) as $w ) {
		$where[] = _like( 'kw', $w );
		$term[ 'keywords' ][] = $w;
	}

	//.. 検索
	$num_hit = $o_sqlite->cnt( $where );
	$ans = $o_sqlite->qar([
		'select'	=> [ 'id', 'count', 'parents', 'children' ] ,
		'order by'	=> 'count DESC' ,
		'limit'		=> NUM_IN_PAGE ,
		'offset'	=> NUM_IN_PAGE * $page
	]);

	//.. リストループ
	$out = '';
	foreach ( $ans as $cnt => $a ) {
		$out .= _ent( $a['id' ], [
			'Rank'		=> $cnt + 1 ,
			'Count used' => $a['count'],
			'Parents'	=> $a['parents'],
			'Children'	=> $a['children'] 
		]);
	}

	//.. page button
	$opg = new cls_pager([
		'str'	=> $term ,
		'total'	=> $num_hit ,
		'page'	=> $page ,
		'range'	=> NUM_IN_PAGE ,
		'div'	=> '#oc_div_list' ,
		'pvar'	=> $_GET + [ 'ajax' => 'list' ]
	]);
	return $opg . $out . $opg->btn();
}
//. function _ent
function _ent( $id, $data = [] ) {
//	$data[ 'links' ] = _imp2(
//		_ab( "?mode=anc&id=$id", 'Ancestors' ) ,
//		_ab( "?mode=des&id=$id", 'Descendants' )
//	);
	$data[ 'Children' ] = _ids2link( $id, $data[ 'Children' ], 'des' );
	$data[ 'Parents' ]  = _ids2link( $id, $data[ 'Parents'  ], 'ans' );
	return ( new cls_entid )
		->set( $id )
		->ent_item_list([ 'data' => $data ])
	;
}
function _ids2link( $id, $str, $mode ) {
	if ( $str == '' ) return;
	$ids = explode( ',', $str );
	if ( count( $ids ) > 10 ) {
		$ids = _imp( array_slice( $ids, 0, 9 ) ) . ', etc.';
	} else {
		$ids = _imp( $ids );
	}
	return _ab( "?mode=$mode&id=$id", $ids ); 
}


//. function _dig

function _dig( $id, $lev = 0 ) {
	global $o_sqlite;
	$ans = $o_sqlite->qar([
		'select' => [ 'parents', 'children', 'count' ] ,
		'where'  => "id=\"$id\""
	]);
	$ans = $ans[0];
	$ids_str = $ans[ ANS ? 'parents' : 'children' ];
	if ( $ids_str != '' ) {
		$ids = explode( ',', $ids_str );
		$out = '';
		if ( $lev > 20 ) {
			$out = _imp( $ids );
		} else {
			foreach ( $ids as $i ) {
				$out .= _dig( $i, $lev + 1 );
			}
			$out = _div( '.branch', $out );
		}
	}
	return _ent( $id, [
		'Used count' => $ans['count'] ,
		'Parents'  => $ans[ 'parents' ] ,
		'Children' => $ans[ 'children' ]
	]) . $out ;
}
