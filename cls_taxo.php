<?php

_add_lang( 'taxo' );
_add_url(  'taxo' );
class cls_taxo {

//. vals
protected $json, $info = [], $key, $name, $cache;

//. from_db
function from_db( $name, $cn_given = '' ) {
	$this->key = _ezsqlite([
		'dbname' => 'taxoid' ,
		'where'	 =>	[ 'name', $name ] ,
		'select' => 'id' ,
	]) ?: $name ;
	list( $this->name, $this->json ) = array_values( _ezsqlite([
		'dbname' => 'taxo' ,
		'where'	=>	[ 'key', $this->key ],
		'select' => [ 'name', 'json1' ] ,
	]));
	$this->parse( $name, $cn_given );
	return $this;
}

//. from_key
function from_key( $key ) {
	$this->key = $key;
	list( $this->name, $this->json ) = array_values( _ezsqlite([
		'dbname' => 'taxo' ,
		'where'	=>	[ 'key', $key ],
		'select' => [ 'name', 'json1' ] ,
	]) );
	$this->parse( $this->name );
	return $this;
}

//. from_json
function from_json( $key, $name, $json ) {
	$this->json = $json;
	$this->key = $key ;
	$this->parse( $name );
	return $this;
}

//. parse
function parse( $name, $cn_given = '' ) {
	extract( (array)json_decode( $this->json ) );
	$type = _l( $ty );
	$name = $name ?: $this->key;

	//- wikipe name
	$wikipe_name = _obj('wikipe')->taxo( $this->key )->e2j();
	if ( _same_str( $wikipe_name, $name ) )
		$wikipe_name = '';

	//- short name
	$cn =
		_ej( $en, $ja ) ?:
		$wikipe_name ?:
		_ej( $e2, $j2 ) ?:
		$cn_given ?:
		$type 
	;
	if ( $cn == 'virus' && _instr( 'virus', $name ) )
		$cn = '';

	$this->info = [
		'id'		=> $id ,
		'name'		=> $name ,
		'name_plus' => ( $ty == 'virus' || $ty == 'others' || $ty == 'unknown' 
			? $name : "<i>$name</i>"
		) . _kakko( $cn ) ,
		'oname' 	=> L_EN ? $en : ( $ja ?: $en ) ,
		'type' 		=> $type ,
		'type_icon' => ( $fn = _url_file_ex( 'txtype_icon', $ty ) )
						? _img( '.txtype_icon', $fn ) : '',
		'thermo'	=> $th ,
		'icon'		=> $ic ,
		'emdb_icon'	=> $ei ,
		'wikipe_img' => $wi ,
		'host'		=> $ho ,
	];
}

//. item
function item( $name = '', $cname = '' ) {
	if ( $name )
		$this->from_db( $name, $cname );
	extract( $this->info );
	return _pop_ajax( ''
		. $type_icon
		. ( $icon ?	_img([ 'taxo_icon_s', $icon ]) : '' )
		. ( $emdb_icon ? _img( '.txtype_icon', [ 'virus_emdb_img', $emdb_icon ] ) : '' )
		. ( $wikipe_img ? _img( '.txwikipe_icon', $this->wikipe_img( $wikipe_img ) ): '' )
		. ( $thermo ? _img( '.txtype_icon', [ 'txtype_icon', 'thermo' ] ) : '' )
		. _obj('wikipe')->taxo( $this->key )->icon()
		. $name_plus
		. ( TEST && !$id ? _span( '.red bld', ' ?' ) :'' )
	,
		[ 'mode' => 'taxo', 'k' => $this->key ]
	);
}

//. pop_cont ポップアップ中身
function pop_cont() {
	extract( $this->info );
	//- アイコンキャプション
	$icon_cap = ''
		. ( $icon
			? _img( '.right', [ 'taxo_icon', $icon ] )
			: ''
		)
		. ( $emdb_icon
			? _div( '.right', ( new cls_entid( $emdb_icon ) )->ent_item_img() )
			: ''
		)
		. ( $wikipe_img
			? _div( '.right', _ab(
				$this->wikipe_img_page( $wikipe_img ) ,
				_img( $this->wikipe_img( $wikipe_img, true ) ) 
			))
			: ''
		)
	;

	//- virus host
	if ( $host ) {
		foreach ( (array)$host as $i => $v ) {
			$host[$i] = ( new cls_taxo )->item( $v );
		}
	}

	return $icon_cap . _ul([
		_ab([ 'taxo', 'k'=>$this->key ], IC_L. $name_plus ) ,
		_imp2(
			$type_icon . $type ,
			$oname ,
			( $thermo ? _img([ 'txtype_icon', 'thermo' ]). _l( 'thermophilic' ): '' )
		) ,
		( $host ? _l( 'Host' ). ':'. _imp2( $host ) : '' ) ,
		_obj('wikipe')->taxo( $this->key )->show()
	]);
}

//. wikiep_img
function wikipe_img( $img, $flg_large = false ) {
	if ( ! $img ) return;
	return _url( 'wikipe_img', preg_replace( '/^.+\//', '', $img ) );
	/*
	return _url(
		$flg_large ? 'wikipe_img_l' : 'wikipe_img_s' ,
		$img ,
		preg_replace( '/^.+\//', '', $img )
	);
	*/
}

//. wikiep_img_page
function wikipe_img_page( $img ) {
	if ( ! $img ) return;
	return _url(
		_ej( 'wikipe_img_en', 'wikipe_img_ja' ) ,
		preg_replace( '/^.+\//', '', $img )
	);
}

//. end
}
