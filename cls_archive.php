<?php
class cls_archive {

//. param
protected
	$id, $id_dir, $type, $name, $fn, $url, $path, $doc,
	$rep	= []
;

//. constract
function __construct( $id ) {
	$this->id = $id;
	$this->id_dir = substr( $id, 1, 2 );
}

//. set
protected function set( $type ) {
	if ( ! $type ) return;
	$name = $name_j = $name_e = $fn = $url = $path_fs3 = $path_mainsv = null;
	extract( _subdata( 'archive', $type ) ?: [] );
	$this->type = $type;
	$this->doc	= $doc;
	$this->name = $name ?: _ej( $name_e, $name_j );
	$this->fn	= $this->rep( $fn );
	$this->url	= $this->rep( $url ). $this->fn;
	$this->path	= $this->rep( TESTSV ? $path_fs3 : $path_mainsv ). $this->fn;
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

//. kv
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
	return $this->url
		? _a( $this->url, IC_DL. $this->fn )
		: null
	;
}

//. disp
function disp() {
	return $this->path
		? _ab([ 'disp', 'arch.'. $this->type. '.'. $this->id ],
			_fa('file-text'). _l('Display') 
		)
		: null
	;
}

//. size
function size() {
	return file_exists( $this->path )
		? _format_bytes( filesize( $this->path ) )
		: '?'
	;
}

}
