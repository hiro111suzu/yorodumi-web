<?php
//. 
require( __DIR__. '/common-web.php' );

define( 'WWW', is_dir( '/var/www/html/emnavi' ) );


$linkarray = [
	'[EMN]'		=> _ab( './', '[EMN]' ) ,
	'[YM]'		=> _ab( 'view.php', '[YM]' ) ,
];
// prev. values
foreach ( $_COOKIE as $name=>$value )
	$ck[ $name ] = $value;

// new values
foreach ( $_GET as $name=>$value ) {
	if ( $name == 't' ) continue;
	if ( $value == 'clear' ) {
		setcookie( $name, false );
		unset( $ck[ $name ] );
	} else {
		setcookie( $name, $value, time()+60*60*24*30 );
		$ck[ $name ] = $value;
	}
}

//
$lk = '';
if ( ! WWW ) foreach ( glob( 'mng-*' ) as $fn )
	$lk .= _ab( $fn, IC_L . strtr( $fn, [ '.php' => '' ] )  ). ' ';


$lk .= _ab( 'prime.php?prime_test=on', 'Prime testmode' );

echo ''
, _ab( './', IC_EMN . 'EMN top' )
, ' '
, _ab( 'view.php', IC_YM . 'YM' )
, ' '
, $lk
, BR

//- reload
, _a( '?t=' . substr( microtime(), -5, 5 ), '[ reload ]' ) . BR
;

//. table
echo ''
, '<table>'
, _t( 'tr', ''
	. _t( 'th', 'name' )
	. _t( 'th', 'set' )
	. _t( 'th', 'current value' )
)
, _tr( 'testhoge'		, 'test'					, 1 )
//, _tr( 'show_trep'		, 'show_trep'					, 1 )

//, _tr( 'novem'			, 'mediadata-novem'			, 1 )
//, _tr( 'flash'			, 'flash'					, 1 )
//, _tr( 'servername'		, 'show servername'			, 1 )
, _tr( 'lang'			, 'language'				, [ 'ja', 'en' ] )
//, _tr( 'svdbg'			, '[seqview] debug mode' 	, [ 1	, 2 ] )
//, _tr( 'app'			, '[YM] applet'				, [ 'jv', 'jmol' ] )
//, _tr( 'emlocal'		, '[YM] emlocal mode'		, 1 )
//, _tr( 'abst'			, '[EMN] citation: show abstract' , 1 )
//, _tr( 'searchparam'	, '[EMN] search: show params' , 1 )
;

if ( count( $ck ) > 0 ) foreach ( $ck as $name=>$value ) {
	if ( $flg[ $name ] ) continue;
	echo _tr( $name, "[?] " );
}
echo '</table>';


//. function
function _tr( $name, $desc, $sets = '' ) {
	global $ck, $flg, $link, $linkarray;
	
	$link = '';
//	if ( $desc == '' ) $desc = $name;
	$flg[ $name ] = true;
	if ( $sets == 1 )
		_link( $name, 'On' );
	else if ( $sets != '' ) {
		foreach ( $sets as $s )
			_link( $name, $s );
	}
	_link( $name, 'Clear' );
	$v = $ck[ $name ];
	$desc = strtr( $desc, $linkarray );
	return "<tr><td>$desc ($name)</td><td>$link</td><td>$v</td></tr>";
}

function _link( $name, $s = '' ) {
	global $link;
	$v = $s;
	if ( $s == 'Clear' ) $v = 'clear';
	if ( $s == 'On' ) $v = '1';
	$t = substr( microtime(), -5, 5 );
	$link .= "[<a href=\"?t=$t&$name=$v\">$s</a>] ";
}
