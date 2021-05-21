<?php
if ( count( array_keys( $_GET ) ) == 0 ) {
	require( __DIR__ . '/_mng.php' );
	$lk = [];
	foreach ( glob( 'data/*.sqlite' ) + glob( 'doc/*.sqlite' ) as $pn ) {
		$fn = basename( $pn, '.sqlite' );
		$lk[] = _ab( '?sqlite=&username=s&db=' . $pn, $fn );
	}
	$_simple->hdiv( 'DBs', _ul( $lk, 1000 ) );
} else {
	//. adminer
	function adminer_object() {
		include_once "../misc/plugin.php";
		include_once "../misc/login-password-less.php";
		return new AdminerPlugin(array(
			new AdminerLoginPasswordLess(password_hash("-", PASSWORD_DEFAULT)),
		));
	}
	require( realpath( '../misc/adminer-4.7.6.php' ) );
}

