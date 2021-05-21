<?php
define( 'VIEWER_ID', 'movie' );
require( __DIR__ . '/pop_common.php' );

//. init
//- ムービー指定なしなら、代表ムービー
$num = _movnum();
$movinfo = $o_id->movinfo();
$movurl = $movinfo[ $num ][ 'files' ];

$o = [];
foreach ( $movinfo as $n => $a ) {
	$o[ $n ] = $a[ 'files' ];
}

//. output
$_simple->page_conf([
	'icon' => 'lk-movie.gif' ,
	'jslib' => [ 'jplayer' ] ,
	'js'	=> 'pop_mov'
])

//.. contents
->add_contents(
	_div( "#moviebox", ''
		. _div( "#m0| .player",
			_img( $movurl[ 'l' ][ 'poster'] ) 
		)

		//- ムービーが表示できないメッセージ
		. _div( '.jp-no-solution', '' )

		//- メッセージウインドウ
		. _div( "#movmsgbox", ''
			. _img( '.mvicon right', 'img/play.gif' )
			. _span( '.mvmsg', '' )
		)

		//- プログレスバー
		. _div( '.jp-progress', ''
			. _div( '.jp-seek-bar br4', ''
				. _div( '.jp-play-bar br2', '' )
			)
		)
	)
	. _div( '#movrot_info', '' )
)

//.. jsvar
->jsvar([ 
	'app'		=> 'mov' ,
	'movs_url'	=> $o ,
	'mov_url'	=> $movurl ,
	'mv_str'	=> _subdata( 'trep', 'movstr' )
])

//.. css
->css( <<< EOD
#moviebox {
	position: fixed; cursor: pointer; top: 1em;
	margin: 0px; overflow: hidden;
}

#movmsgbox { position: absolute; top: 5px; width: 100%; }
.mvicon { margin: 5px 10px; float: right; display: none; }
.mvmsg { position: relative; top: 5px; padding: 2px 5px;
	background: white; color: #800; font-weight: bold; display: none; }

.jp-progress {
	left:0px;
	top:0px;
	margin: 5px;
	height:16px;
}

.jp-seek-bar {
	border: 1px solid $col_dark;
	background: white;
	margin: 0;
	width: 0;
	padding: 1px;
	height: 12px;
	cursor: pointer;
	box-shadow: inset 0px 2px 6px rgba(0,0,0,0.3);
	user-select: none;
}
.jp-seek-bar:hover { border: 1px solid #e77; }

.jp-play-bar {
	background: $col_bright;
	border: 0 solid $col_dark;
	border-width: 0 5px 0 0;
	height:100%;
	margin: 1px;
	user-select: none;
	box-shadow: inset 0px -2px 6px rgba(0,0,0,0.3);
}
.playbar_active {
	background: #e77;
}
EOD
//#prgbar { position: fixed; width: 100%; bottom: 0 }
)

//.. 出力
->popvw_output();

//. func: _movnum: 
function _movnum(){
	global $o_id;
	$n = _getpost_safe( 'num' );
	if ( $n != '' )
		return $n; 
	if ( $o_id->db == 'emdb' ) {
		return $o_id->status()->img;
	} else {
		$num = 1;
		foreach ( $o_id->movinfo() as $n => $b ) {
			if ( $b->type != $o_id->status()->snap ) continue;
			$num = $n;
		}
		return $num;
	}
}

//. func: _gmenu_items
function _gmenu_items() {
	global $o_id;
	$movinfo = $o_id->movinfo();
	$num = _getpost_safe( 'num' ) ?: _movnum();

	//- その他のムービー
	$a = [];
	foreach ( $movinfo as $n => $m ) {
		$a[] = ( $n == $num ? _span( '.red bld', "#$n" ): _btn( "!_movnum('$n')", "#$n" ) )
			. ': ' . _imp( array_slice( $m['cap'], 0, 2 ) )
		; 
	}
	_tab_item( 'data', TERM_ENT_MOVS. _ul( $a, 0 ) );

	//- view
	foreach ( _mov_remocon(true) as $li ) {
		_tab_item( 'view', $li );
	}
	return;
}

