<?php 
//. init
define( 'EM_MODE'		, $_GET[ 'em' ] || $_POST[ 'em' ] );
define( 'COLOR_MODE'	, EM_MODE ? 'emn' : 'ym' );
define( 'IMG_MODE'		, 'em' );
require( __DIR__. '/common-web.php' );

define( 'GET_ID', _getpost_safe( 'id' ) );
define( 'GET_STR', _getpost_safe( 'str' ) );
define( 'NUM_IN_PAGE', 20 );

define( 'KW'   , _getpost( 'kw' ) );
define( 'IMPF' , _getpost_safe( 'if' ) ?: 0 );
define( 'JN'   , _getpost_safe( 'jn' ) );
define( 'AUTH' , _getpost( 'author' ) );
define( 'MET'  , _getpost_safe( 'method' ) ?: 'all' );

_add_lang( 'pap' );
_define_term( <<<EOD
TERM_INC_NONEM
	Include non EM papers
	EMデータ以外の文献含める
TERM_NO_DATA
	No data found
	データが見つかりません
TERM_PAP_LIST
	List of structure papers
	構造論文のリスト
TERM_DATE_DEP
	structure data deposition date
	構造データの登録日
TERM_DATE_DATACOL
	data collection date
	データ計測日
TERM_SEARCH_PUBMED
	Search PubMed
	PubMedで検索

EOD
);
//. ajax reply
if ( _getpost_safe( 'ajax' ) == 'list' ) {
	die( _getlist() );
}

//. ID決定
$id = GET_ID;
if ( GET_STR ) {
	$id = _ezsqlite([
		'dbname' => 'pmid' ,
		'select' => 'pmid' ,
		'where' => [ 'strid', strtr( GET_STR, [ 'pdb-' => '', 'emdb-' => 'e' ] ) ] ,
	]);
}
define( 'ID', $id );

//. form
//.. 検索条件
$met_names = _subdata( 'trep', 'met2name' );
$met_list = [ 'all' => 'All' ];
foreach ( [
	'x-ray' ,
	'nmr' ,
	'em' ,
	'sas' ,
	'neutron' ,
	'fiber' ,
//	'scattering' ,
	'powder' ,
	'infrared' ,
	'epr' ,
	'fluorescence' ,
	'theoretical',
] as $s )
	$met_list[ $s ] = $met_names[ $s ];
$met_list[ 'hybrid' ] = 'Hybrid';

$_simple->hdiv( 'Search query',
	_t( 'form | #form1', _table_2col([
		'Keywords' => _inpbox( 'kw', KW, [ 'acomp' => 'kw' ] ) ,
		'Structure methods' => EM_MODE
			? ''
			: _radiobtns(
				[ 'name' => 'method', 'on' => MET ], 
				$met_list
			)
		,
		'Author' => _inpbox( 'author', AUTH, [ 'acomp' => 'an' ] ) ,
		'Journal' => _inpbox( 'jn', JN ) ,
		'IF' => _radiobtns(
			[ 'name' => 'if', 'on' => IMPF ],
			[
				'30'	=> '>30' ,
				'20'	=> '>20' ,
				'10'	=> '>10' ,
				'5'		=> '>5' ,
				'0'		=> 'all' ,
			]
		)
	])
		. _e( "input | type:hidden | #input_emmode | name:em | value:" . ( EM_MODE ? 1 : 0 ) )
		. _e( 'input | type:submit | st: width:100% | .submitbtn' ) 
		. ' '
		. _e( 'input | type:reset | .submitbtn' ) 
		. ( EM_MODE ? BR. _btn(
			"!$('#input_emmode').val(0);$('#form1').submit();" ,
			TERM_INC_NONEM
		) : '' ) 
	) 
	,
	[ 'hide' => ID != '' ]
);

//. データ表示
if ( ID == '' ) {
	//- 検索結果
	$_simple->hdiv( TERM_PAP_LIST, _getlist(), [ 'id' => 'list' ] );
} else {
	//- 単独データ取得
	$res = ( new cls_sqlite( 'pap' ) )->qar([
		'select'	=> 'pmid, journal, date, data, if' ,
		'where'		=> 'pmid='. _quote( ID )
	]);
	$_simple->hdiv(
		"Structure paper" ,
		count( $res )
			? _simple_table( _indivi( $res[0] ) )
			: _p( TERM_NO_DATA )
	);
}

//. output
$_simple->page_conf([
	'title' => EM_MODE ? _ej( 'EMN Papers', 'EMN文献' ): _ej( 'Yorodumi Papers', '万見文献' ) ,
	'icon'	=> 'lk-article.gif' ,
	'js'	=> '' ,
	'docid' => EM_MODE ? 'about_empap' : 'about_pap' ,
	'newstag' => EM_MODE ? 'emn' : 'ym' ,
	'auth_autocomp' => true ,
])
->css( <<<EOD
table { width: 100% }
strong { color: #b00; font-weight: bold }
EOD
)->out();

//. fucntion _indivi(): 個別データ
function _indivi( $res ) {
	extract( (array)$res ); //- $pmid $journal, $date, $data, $if
	$data = json_decode( $data );
	$pmjson = _json_load2( _fn( 'pubmed', ID ) ) ;

	//.. issue
	$is = _imp([
		_ifnn( $journal, '<i>\1</i>' ),
		$data->issue 
	]);
	if ( $data->doi != '' ) {
		$is = _ab([ 'doi', $data->doi ], $is );
	}
	if ( TEST ) {
		$is .= _test( BR. _span( '.red', $if ) );
	}
	

	//.. date
	$date = _datestr( $date ). _kakko([
		'str' => TERM_DATE_DEP ,
		'exp' => TERM_DATE_DATACOL
	][ $data->date_type ] );

	//.. links
	$links = [];
	if ( $data->doi != ''  ) 
		$links[] =  _ab([ 'doi', $data->doi ], IC_L. ( $journal ?: "Publisher's page" ) );

	if ( $pmid != '' && substr( $pmid, 0, 1 ) != '_' ) 
		$links[] =  _ab([ 'pubmed', $pmid ], IC_L. "PubMed:$pmid" );
	else {
		$links[] = _ab( 'http://www.ncbi.nlm.nih.gov/pubmed/?term=' . 
			urlencode( $data->title . ' ' . implode( ' ', (array)$data->author ) )
			,
			TERM_SEARCH_PUBMED
		);
	}
	if ( $pmjson->id->pmc ) {
		$links[] = _ab([ 'pmc', $pmjson->id->pmc ], IC_L. 'PubMed Central' );
	}

	$links2[] = [];
	if ( TEST ) {
		$links2[] = _ab([ 'jsonview', 'pubmed.'    . ID ], IC_L. 'PubMed-json' );
		$links2[] = _ab([ 'txtdisp' , 'pubmed_xml.'. ID ], IC_L. 'PubMed XML' );
		$links2[] = _ab([ 'jsonview', 'pap_info.'  . ID ], IC_L. 'pap_info' );
	}

	//.. abst
	$abst = [];
	foreach ( (object)$pmjson->abst as $k => $v ) {
		if ( $k == 'Copyright' ) continue;
		$abst[] = is_numeric( $k ) ? $v : _kv([ $k => $v ]);
	}

	//.. taxo
	$t = [];
	foreach( (array)$data->src as $s )
		$t[ strtolower( $s ) ] = $s;

	$taxo =[];
	foreach ( $t as $s )
		$taxo[] = _obj('taxo')->item( $s );

	//.. str tile強調
	$ent_set = $title_set = [];
	foreach ( $data->ids as $did ) {
		$ent_set[ $did ] = new cls_entid( $did );
		$title_set[ $did ]= $ent_set[ $did ]->title();
	}

	//... 頻出文節分析
	if ( 1 < count( $data->ids ) ) {
		//- 比較用タイトル
		$title_4comp = [];
		foreach ( $title_set as $did => $title ) {
			$title_4comp[ $did ] = '^'. strtolower( strtr(
				$title_set[ $did ], 
				[ ', ' => ' ' ,]
			) ). '$';
		}
		//- 文節収集
		$title_hash = [];
		foreach ( array_unique( $title_4comp ) as $t ) {
			$t = explode( ' ', $t );
			foreach ( range( 1, count( $t ) - 1 ) as $n ) {
				++ $title_hash[ implode( ' ', array_slice( $t, 0, $n ) )  ];
				++ $title_hash[ implode( ' ', array_slice( $t, -1 * $n ) ) ];
			}
		}
		//- 不一致・冗長を消す
		foreach ( $title_hash as $t1 => $cnt1 ) {
			if ( $cnt1 == 1 ) {
				unset( $title_hash[ $t1 ] );
				continue;
			}
			foreach ( $title_hash as $t2 => $cnt2 ) {
				if ( $cnt1 != $cnt2 || $t1 == $t2 ) continue;
				if ( _instr( $t1, $t2 ) )
					unset( $title_hash[ $t1 ] );
			}
		}
		//... 強調
		if ( $title_hash ) foreach ( $title_set as $did => $title ) {
			$exp_title = explode( ' ', $title );
			$cnt_title = count( $exp_title );

			foreach ( range( 2, max( $title_hash ) ) as $lim ) {
				$num_list = [ 0 ];
				foreach( $title_hash as $hash => $num ) {
					if ( $num < $lim ) continue;
					if ( ! _instr( $hash, $title_4comp[ $did ] ) ) continue;
					$num_list[] = count( explode( ' ', $hash ) )
						* ( substr( $hash, -1 ) == '$' ? -1 : 1 )
					;
				}
				$head = max( $num_list );
				$tail = min( $num_list ) * -1;
				if ( $cnt_title <= $head + $tail || $head + $tail == 0 ) continue;
				$exp_title[ $head ] = '<strong>'. $exp_title[ $head ];
				$exp_title[ $cnt_title - $tail -1 ] .= '</strong>';
				$title_set[ $did ] = implode( ' ', $exp_title )
					// . _kakko("s$head-$tail / $cnt_title")
				;
				break;
			}
		}
	}
	$test_title = '';
//	_table_2col( $title_test ) . _table_2col( $title_hash );

	//.. str data main
	$str_data = $id_done = [];
	$flg_unp_emdb = false;
	foreach ( $ent_set as $did => $ent ) {
		$fit_pdb = $ent->db == 'emdb' ? _emn_json( 'fit', $did ) : [];

		//- 別の論文のデータは除く
		foreach ( (array)$fit_pdb as $k => $v ) {
			if ( in_array( $v, $data->ids ) ) continue;
			unset( $fit_pdb[ $k ] );
		}
		if ( $ent->db == 'sasbdb' ) {
			$add = [
				'Method' => 'SAXS/SANS'
			];
		 } else if ( $ent->db == 'pdb' ) {
			$q = _ezsqlite([
				'dbname' => 'pdb' ,
				'select' => [ 'reso', 'method' ] ,
				'where'  => [ 'id', $ent->id ] ,
			]);
			$add = [
				'Method' => _methodlist( explode( ',', $q['method'] ) ) ,
				'Resolution' => _ifnn( $q['reso'] , '\1 &Aring;' )
			];
		} else if ( $ent->db == 'emdb' ) {
			$add = [
				'Method' => _methodlist( $ent->add()->met ) ,
				'Resolution' => _ifnn( $ent->add()->reso, '\1 &Aring;' ) ,
			];
			//- unp annot
			if ( TEST && _flg_emdb_unp( $ent->id ) ) {
				$add['F&H_annot'] = _input_emdb_unp( $ent->id, $pmid );
				$flg_unp_emdb = true;
			}
		}
		
		//... 単独エントリ
		if ( ! $fit_pdb ) {
			$str_data[ $did ] = $ent->ent_item_list([
				'data' => $add ,
				'title' => $title_set[ $did ] ,
			]);
			continue;
		}

		//... fitグループ
		$icons = $ent->ent_item_img();
		$e_title = $title_set[ $did ];
		$titles = $same_title = [];
		foreach ( $fit_pdb as $pdb_id ) {
			$ent2 = new cls_entid( $pdb_id );
			$icons .= $ent2->ent_item_img();
			$t = $title_set[ $pdb_id ]; 
			if ( $t == $e_title ) {
				$same_title[] = $ent2->DID;
			} else {
				$titles[ $ent2->DID ] = $t;
			}
			$id_done[] = $pdb_id;
		}
		//- 同じタイトルのやつをまとめる
		$titles_out = $same_title ? [
			_ent_kv( array_merge( [ $ent->DID ], $same_title ) ) ,
			$e_title
		]: [
			_ent_kv( $ent->DID, $e_title )
		];
		foreach ( $titles as $i => $t )
			$titles_out[] = _ent_kv( $i, $t );
		$titles_out[] = _kv( $add );

		$str_data[ $did ] = _div( '.clearfix topline', ''
			. _div( '.left clearfix', $icons )
			. _p( implode( BR, $titles_out ) )
		);
	}
	foreach ( $id_done as $i )
		unset( $str_data[ $i ] );
	ksort( $str_data );


	//.. retrun
	return [
		//- journal
		'Title'					=> $data->title ,
		'Journal, issue, pages' => $is ,
		'Publish date'			=> $date  ,
		_ic( 'auth' ). _l( 'Authors' ) => $pmjson
			? _imp2( _pubmed_auth( $pmjson ) ) : _authlist( $data->author )
		,
		'PubMed ' . _l( 'Abstract' ) => _long( implode( BR, $abst ), 200 ) ,
		'External links'		=> _imp2( $links ) ,
		_span( '.red', 'Test links' ) => _imp2( $links2 ) ,
		//- exp
		'Methods' 				=> _methodlist( $data->method2 ) ,
		'Resolution'			=> _ifnn( $data->reso, '\1 &Aring;' ) ,
	
		//- str data
//		'Structure data' => _ent_catalog( $data->ids, [ 'mode' => 'list' ] ) ,

		_span( '.red', 'UNP_annot' ) => $flg_unp_emdb ? _input_emdb_unp( $pmid ) : '' ,
		_span( '.red', 'categ all' ) => _set_categ( "pmid$pmid" ) ,


		'Structure data' 		=> implode( '', $str_data ) ,

		'title' => $test_title,
		
		'Chemicals' => _ent_catalog( (array)$data->chemid, [ 'mode' => 'list' ] ) ,

		//- taxo
		'Source' => _ul( _uniqfilt( $taxo ) ) ,

		'Keywords'				=> _keywords( (array)$data->kw ) ,

	];
}
//.. function _ent_kv
function _ent_kv( $key, $val = '' ) {
	$ids = [];
	foreach ( is_array( $key ) ? $key : [ $key ] as $i )
		$ids[] = _ab( 'quick.php?id='. $i, $i );
	return _span( '.bld', _imp( $ids ). ':' ). ( $val ? " $val" : '' );
}

//. function _getlist: リスト作成
function _getlist() {
	$page = _getpost_safe( 'page' ) ?: 0 ;

	//.. クエリ作成
	$where = [];
	$term = [];

	//- キーワード
	foreach ( (array)_kwprep( KW ) as $w ) {
		$where[] = _like( 'search_kw', $w );
		$term[ 'keywords' ][] = $w;
	}

	//- author
	foreach ( (array)_kwprep( AUTH ) as $w ) {
		$where[] = _like( 'search_auth', $w );
		$term[ 'author' ][] = $w;
	}

	//- method
	if ( MET != '' && MET != 'all' ) {
		$where[] = _like( 'method', MET == 'hybrid' ? '|' : MET, true );
		$term[ 'method' ] = MET;
	}

	//- IF
	if ( IMPF > 0 ) {
		$where[] = 'if >= '. IMPF;
		$term[ 'IF' ][] = IMPF;
	}

	//- journal name
	if ( JN != '' ) {
		$where[] = _like( 'journal', JN );
		$term[ 'journal' ][] = JN;
	}
	
	//- EM mode?
	if ( EM_MODE ) {
		$where[] = 'emflg = 1';
	}

	//.. 検索
	$sq = new cls_sqlite( 'pap' );
	$num_hit = $sq->cnt( $where );
	$ans = $sq->qar([
		'select'	=> [ 'pmid', 'journal', 'data', 'if' ] ,
		'order by'	=> 'score DESC' ,
		'limit'		=> NUM_IN_PAGE ,
		'offset'	=> NUM_IN_PAGE * $page
	]);
	unset( $sq );

	//.. リストループ
	$out = '';
	foreach ( $ans as $a ) {
		$out .= _pap_item( $a );
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
