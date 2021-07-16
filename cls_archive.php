<?php
class cls_archive {

//. param
protected
	$id, $id_dir, $type, $name, $fn, $url, $path, $doc, $url_disp,
	$rep	= []
;

//. constract
function __construct( $id ) {
	$this->id = $id;
	$this->id_dir = substr( $id, 1, 2 ); //- PDBの分割ディレクトリ名
}

//. set
protected function set( $type ) {
	if ( ! $type ) return;
	$name = $name_j = $name_e = $fn = $url = $path_fs3 = $path_mainsv = $url_disp = null;
	extract( _subdata( 'archive', $type ) ?: [] );
	$this->type = $type;
	$this->doc	= $doc;
	$this->name = $name ?: _ej( $name_e, $name_j );
	$this->fn	= $this->rep( $fn );
	$this->url	= $this->rep( $url ). $this->fn;
	$this->path	= $this->rep( TESTSV ? $path_fs3 : $path_mainsv ). $this->fn;
	$this->url_disp = $this->rep( $url_disp );
}

//. rep
protected function rep( $in ) {
	if ( ! $this->rep  )
		$this->rep = _subdata( 'archive', 'rep' );
	return strtr( $in, $this->rep + [
		'<id>'		=> $this->id,
		'<id_dir>'	=> $this->id_dir
	]);
}

//. path
function path( $type = null ){
	$this->set( $type );
	return $this->path;
}

//. get
function get( $type = null ) {
	$this->set( $type );
	return [
		'name'	=> $this->name ,
		'dl'	=> $this->dl() ,
		'size'	=> $this->size() ,
		'disp'	=> $this->disp() ,
		'doc'	=> $this->doc ? _doc_pop( $this->doc ) : null
	];
}

//. link
function td( $type = null ) {
	$this->set( $type );
	return implode( TD, [
		$this->dl() ,
		$this->size() ,
		$this->disp() ,
		$this->doc ? _doc_pop( $this->doc ) : null
	]);
}

//. dl
function dl( $type = null ){
	$this->set( $type );
	$url = $this->url;
	if ( ! $url ) return;
	if ( ! $this->fn ) {
		//- アーカイブディレクトリ
		$ftp = strtr( $url, [ 'https:' => 'ftp:' ] );
		return _ab( $url, IC_L. $url ). BR. _ab( $ftp, IC_L. $ftp );
	} else {
		//- ファイル
		return _a( $this->url, IC_DL. $this->fn );
	}
}

//. disp
function disp() {
	return ( $this->url_disp
		? _ab( $this->url_disp, _fa('file-text-o'). _l('Tree view') )
		: null
	) ?: ( $this->path
		? _ab([ 'disp', 'arch.'. $this->type. '.'. $this->id ],
			_fa('file-text-o'). _l('Display') )
		: null
	);
}

//. size
function size() {
	return file_exists( $this->path )
		? _format_bytes( filesize( $this->path ) )
		: ( $this->path ? null : 'HTTPS'. BR. 'FTP' )
	;
}

}
