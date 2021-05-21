/*
ysearchjs
*/

//. 開始時
$( function() { 
	_form.init();
	_ystab.init();

	//- 戻る・進む動作
	if ( history && history.pushState ){
		$(window).on('popstate', function(e){
			_form.reloadform();
		});
	}
});

//. [func] _emn_search
function _emn_search(){
	$( '#form1' ).attr( 'action', 'esearch.php' ).submit();
}

//. [obj] _ystab
var _ystab = {
	page_num: [],
	$cnt_ent: $('.cnt_ent'),

	//.. init
	init: function() {
		this.get('page', 0); //- 初期画面の結果取得
		if ( phpvar.get_cnt )
			this.get_cnt();
		this.get('hit', 0);
	},

	//.. set タブ切り替え ボタンから
	set: function( name ){
		_form.$act_tab.val( name );
		_form.$page_num.val( this.page_num[ name ] || 0 );
		_form.changed('act_tab'); //- なぜかコールバックされないので
	},

	//.. change タブ切り替え フォームから
	change: function(){
		name = _form.$act_tab.val() || 'emdb';
		_tab.s( 'res', name );

		//- 中身カラなら読み込み
		if ( !$( '#tabdiv_res_' + name ).text() ) {
			this.get('page');
		}
		//- フォームの行内容を操作
		_form.disp_row();
	},

	//.. page ページ ボタンから
	page: function( num, tab ) {
		_form.$page_num.val( num );
		_form.$act_tab.val( tab );
		_form.changed('pagen'); //- なぜかコールバックされないので
	},

	//.. disp_mode: モード切替
	disp_mode: function() {
		_localstr.set( 'ys_mode', $('input[name=mode]:checked').val() );
		['emdb', 'pdb', 'sas', 'chem'].forEach( function(c) {
			$( '#tabdiv_res_' + c ).text('');
		});
		this.get('page');
	},

	//.. get_all 全部再検索
	get_all: function( sp ) {
		this.page_num = {};
		_form.$page_num.val(0);
		$( '#ent_info' ).hide('medium');
/*
		$( '#oc_div_result' )._loadex({
			u: '?ajax=res',
			v: _form.$form.serialize() ,
			speed: sp ,
			func: function(){
				this.get('page');
			}.bind(this)
		});
*/
		this.get( 'page');
		this.get_cnt();
		phpvar.tabs.forEach( function(c) {
			var tab = _form.$act_tab.val();
			if ( c != tab )
				$( '#tabdiv_res_' + c ).text('');
		});
	} ,

	//.. get ( hit / page)
	get: function( type, sp ) {
		$( type == 'page'
			? '#tabdiv_res_' + _form.$act_tab.val()
			: '#hit_item'
		)._loadex({
			u: '?ajax=' + type ,
			v: _form.$form.serialize() ,
			speed: sp || 'fast'
		});
	} ,

	//.. get_cnt
	get_cnt: function() {
		this.$cnt_ent.html( phpvar.loading_anim );
		$.ajax({
			url      : '?ajax=cnt' ,
			data     : _form.$form.serialize() ,
			cache    : false ,
			dataType : 'json' ,
			success: function( json ) {
				for( key in json ) {
					$('#cnt_ent_' + key).text( json[ key ] );
				}
			}
		});
	}
}

//. [obj] _form
var _form = {
	prev_val: {},
	$act_tab	: null,
	$page_num	: null,
	$form		: $('#form1') ,

	//.. init:
	init: function() {
		//- フォーム初期化 (再読み込み時、毎回実行)
		this.$act_tab	= $('#act_tab');
		this.$page_num	= $('#pagen');
		this.$act_tab.val( this.$act_tab.val() || phpvar.actab );

		//- モードをローカルストレージの値に
		var m = _localstr.get('ys_mode');
		m && $( 'input[name=mode]' ).val([ m ]);

		this.$form.change(
			function(e){
				this.changed( $( e.target ).attr('name') );
			}.bind(this)
		).attr( 'onsubmit', 'return false' ); //- submitボタン無効に

		this.disp_row( 0 );
	},

	//.. changed: 検索条件に変更
	changed: function( name, keep_histry ) {
		//- アドレスバー書き換え
		if ( ! keep_histry )
			history.pushState( null, null, '?' + this.$form.serialize() );

		if ( name == 'mode' ) {
			_ystab.disp_mode();
		} else if ( name == 'act_tab' ) {
			_ystab.change();
		} else if ( name == 'pagen' ) {
			_ystab.get('page');
		} else {
			_ystab.get_all();
		}
	},

	//.. disp_row: 表示行をタブ内容にあわせる
	disp_row: function( speed ){
		var tabs_to_show = phpvar.opt_tr[ this.$act_tab.val() || 'emdb' ];
		speed = speed == undefined ? 'medium' : speed ;
		$('.opt_tr').each( function(i, e){
			var $o = $(e);
			if (
				tabs_to_show && 
				tabs_to_show.indexOf( $o.attr('id').replace('tr_', '') ) != -1
			) {
				$o.is(':visible') || $o.stop(1,1).show(speed);
			} else {
				$o.is(':visible') && $o.stop(1,1).hide(speed);
			}
		});
	} ,

	//.. reloadform
	//- 戻る・進むボタン対応、フォームを書き換えて、検索実行
	reloadform: function() {
		this.$form._loadex({
			u: ( location.search || '?' ) + '&ajax=form',
			speed: 0,
			func:function(){
				this.init();
				this.changed('all', true);
			}.bind(this)
		});
	}
}
