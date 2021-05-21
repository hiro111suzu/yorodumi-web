<?php
//. init
require( __DIR__ . '/_mng.php' );
$message = '';
define( 'RANGE', 200 );
_add_url( 'mng_dir' );
_add_fn( 'mng_dir' );

list( $type, $id ) = preg_split( '/[\.\| ]/', _getpost( 'fn' ), 2 );

define( 'PATH', _getpost( 'path' ) ?: _fn( $type, $id ) ?: '' );
define( 'FILTER', _getpost( 'filter' ) );
define( 'CUR_DIR', realpath( '.' ) );
define( 'EXT_IMG', [ 'jpg', 'jpeg', 'png', 'gif', 'svg' ] );
define( 'EXT_ICON', [
	'file-text-o'    => [ 'txt', 'tsv' ] ,
	'sitemap'        => [ 'json' ] ,
	'book'           => [ 'dic' ] ,
	'window-close-o' => [ 'xml' ] ,
	'gears'          => [ 'ini' ] ,
	'file-image-o'   => [ 'jpg', 'jpeg', 'png', 'gif', 'svg' ] ,
	'file-video-o'   => [ 'mp4', 'webm' ],
	'file-code-o'    => [ 'php', 'js', 'css' ],
	'star'           => [ 'cif', 'pdb', 'pdb1', 'pdb2', 'pdb3', 'pdb4' ] ,
	'database'       => [ 'sqlite' ] ,
]);

//. 対象ディレクトリ決定
if ( is_dir( PATH ) ) {
	$tree = [ _dirlink( '/', 'root' ) ];
	$path_sum = '';
	foreach ( explode( '/', realpath( PATH ) ) as $p ) {
		if ( $p == '' ) continue;
		$path_sum .= '/'. $p;
		$tree[] = _dirlink( $path_sum, $p );
	}
	$tree = LI. 'Current: '. _imp2( $tree );
} else {
	$tree = LI. 'Search (locate) for '. PATH;
	if ( $type )
		$message = "ファイルタイプ不明: ". _table_2col([
			'get' => _getpost( 'fn' ), 'type' => $type, 'id' => $id, 'path' => PATH
		]);
}

//. ajax reply
if ( _getpost( 'ajax' ) ) {
	die( _item_table(). _t( 'pre', json_encode( _getpost(''), JSON_PRETTY_PRINT ) ) );
}

//. ディレクトリ表示
$_simple->hdiv( 'Directory path', _ul([
	'Favorite: ' . _imp2([
		_dirlink( DN_DATA , 'data' ),
		_dirlink( DN_PREP , 'prep' ),
		_dirlink( DN_FDATA, 'fdata' ),
		_dirlink( '/dev/shm/yorodumi', 'tmp' ) ,
		_dirlink( DN_PREP . '/mail', 'mail' )  ,
		_dirlink( DN_PREP . '/marem', 'marem' )  ,
		_dirlink( '/home/archive', 'archive' ) ,
	]) . $tree
	,
	_t( 'form', ''
		. _inpbox( 'path', realpath( PATH ), [ '' ] )
		. _inpbox( 'filter', FILTER, [ '' ] )
		. _e( 'input | type:submit | .submitbtn' ) 
	)
]));

if ( $message )
	$_simple->hdiv( 'Message', $message );

$_simple->hdiv( 'File items', _item_table(), [ 'id' => 'main' ] );

//. output

//.. css
$_simple->css( <<<EOD
#item_table, #item_table tr, #item_table td {
	border: none;
	padding: 0 0.2em;
	margin: 0;
}
.imgicon { max-height: 50px; max-width: 50px; border: 1px solid gray; }

EOD
);

//. function
//.. item_table
function _item_table() {
	global $base_name, $ext, $path_name;
	if ( is_dir( PATH ) )
		$items = glob( PATH . '/'. ( FILTER ? '*'. FILTER. '*' : '*' ) );
	else {
		exec( 'locate ' . PATH, $items );
		$items = array_filter( $items );
	}

	//... main loop
	$files = $dirs = '';
	foreach ( array_slice( $items, PAGE * RANGE, RANGE ) as $path_name ) {
		$path_name = realpath( $path_name );
		$ext = pathinfo( $path_name, PATHINFO_EXTENSION );
		if ( strtolower( $ext ) == 'gz' )
			$ext = pathinfo( basename( $path_name, ".gz" ), PATHINFO_EXTENSION ) . ".$ext";
		$ext_lc = strtr( strtolower( $ext ), [ '.gz' => '' ] );
		$base_name = basename( $path_name, ".$ext" );

		if ( is_dir( $path_name ) ) {
			//- dir
			$ext = '-';
			$dirs .= _tr( 'dir', _fa( 'folder' ) );
		} else {
			//- file
			if ( in_array( $ext_lc, EXT_IMG ) && _instr( CUR_DIR, $path_name ) ) {
				$icon = _img( '.imgicon', strtr( $path_name, [ CUR_DIR. '/' => '' ] ) );
			} else {
				$icon = 'question';
				foreach ( EXT_ICON as $i => $ext_set ) {
					if ( ! in_array( $ext_lc, $ext_set ) ) continue;
					$icon = $i;
					break;
				}
				$icon = _fa( $icon );
			}
			$files .= _tr(
				[
					'json'   => 'jsonview' ,
					'sqlite' => 'sqlite' ,
					'tsv'	 => 'tsvview' ,
				][ $ext_lc ] ?? 'txt'
				,
				$icon
			);
		}
	}

	//... return
	$o_pager = new cls_pager([
		'total'		=> count( $items ) ,
		'page'		=> PAGE ,
		'range'		=> RANGE ,
		'pvar'		=> [ 'ajax' => true, 'path' => PATH ] ,
		'div'		=> '#oc_div_main'
	]);
	return ''
		. $o_pager->msg()
		. $o_pager->btn()
		. _t( 'table| #item_table', $dirs . $files )
		. $o_pager->btn()
	;
}

//.. _dirlink
function _dirlink( $u, $str ) {
	$u = realpath( $u );
	return _a_flg( PATH == $u, _url( 'dir', realpath( $u ) ), $str ); 
}

//.. _tr
function _tr( $type, $icon ) {
	global $ext, $path_name, $base_name;
	$pn = '';
	if ( ! is_dir( PATH ) ) {
		$pn = pathinfo( $path_name, PATHINFO_DIRNAME );
		$pn = _e('td|.smaller'). _a(
			_url( 'dir', $pn ),
			'.../'. preg_replace( '/^.+\//', '', $pn ) ,
			'?'. $pn
		);
	}
	return TR
		.TD. _a( 
			_url( $type, $path_name ),
			$icon. $base_name ,
			"?$path_name". ( $type == 'dir' ? '' : '| target:_blank' ) 
		)
		.TD. $ext
		. $pn
		. _e( 'td|.green smaller right' ). ( $type == 'dir'
			? '-'
			: number_format( filesize( $path_name ) ) 
		)
		. _e( 'td|.green samller' ).  date( 'Y-m-d\ H:i', filemtime( $path_name ) )
	;
}
