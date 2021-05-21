/*
pop_mov �|�b�v�A�b�v�E�B���h�E�pJmol
*/
var
backbone_only = '*.CA,*.P,'  ,
//- �^�C�}�[
timer = {},

//- �A�v���b�g�p �O���[�o���ϐ�
A = {} , 

//�I�����[�h
selmodef = { 'halo':true, 'center':true }
;

//. �E�C���h�E�A���T�C�Y�����Ƃ��A�����Ƃ�
$(window)
	//- ���T�C�Y�ŁAJmol�T�C�Y�ύX
	.resize( function() {
		_timer.do_after_busy( function() {
			var wh = _winsize2jmolsize();
			Jmol.resizeApplet( jmol0, wh );
	    }, 200 );
	})
;

//. �J�n��
$( function(){
	var a = _winsize2jmolsize();
	phpvar.jmolconf.width = a[0];
	phpvar.jmolconf.height = a[1];
	Jmol._alertNoBinary = false; 
	$( '#jmolinner' ).html( Jmol.getAppletHtml( 'jmol0', phpvar.jmolconf ) );
});

//.. _winsize2jmolsize �E�C���h�E�T�C�Y����Jmol�̃T�C�Y��Ԃ����T�C�Y
function _winsize2jmolsize() {
	return [
		$(window).width() - 4,
		$(window).height() - 54
	];
}

//. �R�}���h���s
//.. _send_cmd
// 

//function _send_cmd( que ) {
_cmd.init();
var _cmd = {
	other: function(){
		if ( phpvar.jmolcmd[ qobj ] ) {
			_jmols( phpvar.jmolcmd[ qobj ] );
			return true;
		} else {
			return false;
		}
	},
	asb: function( param, trg_obj ) {
		//- param = 1 or [abid:1, backbone_only: true, ]
		if ( typeof param === 'string' )
			param = {abid: param};

		_jmols( 'load "" FILTER "'
			+ ( param.bbonly ? backbone_only : '' ) //- �卽����
			+ 'biomolecule ' + param.abid + '";'
			+ phpvar.jmolcmd.init 
		, 0 );
		_ofunc( '_datareloaded' );
		_ofunc( '_btn_deco', 'asb', trg_obj );
	} ,
	reload: function() {
		_jmols( phpvar.jmolcmd.reload + ';' + phpvar.jmolcmd.init );
		_ofunc( '_datareloaded' );
		_ofunc( '_btn_deco', 'asb' );
	} ,
	select: function( param, trg_obj ) {
		_select( param, trg_obj );
	}
}



//.. _jmols: jmol�R�}���h
//- f �ǂݍ��ݒ��E�v�Z���t���O => 0: �\�� 1: �\��
function _jmols( s, f ){
	s = s.replace( /_dq_/g, '"' );
	_popvw.cmdhist( '$ ' + s );
	if ( f != undefined )
		_loadstart( f );
	try {
		Jmol.script( jmol0, s );
	} catch(e) {
		return false;
	}
	return true;
}

//.. _select �I��
function _select( cmd, trg_obj ) {
	//- �O��̑I���Ɠ����H�g�O���X�C�b�`
	if ( trg_obj != undefined && trg_obj == A.last_trg_obj ) {
		//- �I�����I�t
		cmd = 'selectionHalos OFF; select all; label off;'
			+ ( selmodef.center ? 'zoomto 0.7 0;' : '' );
		_ofunc( '_btn_deco', 'select' );
		A.last_trg_obj = '';
	} else {
		cmd = 'select ' + cmd
			+ ( selmodef.halo   ? ';selectionHalos ON;' 		: '' )
			+ ( selmodef.center ? ';if ({selected}.size > 0) zoomto 0.7 {selected} 0;'	: '' )
		;
		_ofunc( '_btn_deco', 'select', trg_obj );
		A.last_trg_obj = trg_obj;
	}
	return _jmols( cmd );
}

//. �ʐM�E���b�Z�[�W�n�֐�
//.. _jmolmsg

function _jmolmsg( s1, s2, s3 ) {
//	if ( /^echo/i.test( s2 ) ) return;
	//- �����Ȃ�
	if ( ! $.isFunction( Jmol.script ) ) {
		_popvw.cmdhist( 'Jmol is busy now.', 'green' );
		return;
	}

	$( '#loading' ).hide( 'slow' ); //- ���[�h���o�[�����܂�
	if ( A.pg_calc == 1 )
		$( '#calcsurf' ).show( 'slow' ); //- �\�ʌv�Z���o�[���o��
	if ( A.pg_calc == 2 )
		$( '#calcstr' ).show( 'slow' ); //- �v�Z���o�[���o��

	//- �\�ʌv�Z�����
	if ( A.pg_calc ==  1 && / created with /.test( s2 ) ) {
		$( '#calcsurf' ).hide( 'slow' );
		A.pg_calc = 0;
	}
	//- �\���v�Z�����
	if ( A.pg_calc == 2 && / atoms selected/.test( s2 ) ) {
		$( '#calcstr' ).hide( 'slow' );
		A.pg_calc = 0;
	}

	//- �Ӗ��Ȃ��n
	if ( s2 == '' ) return;
	if ( /Callback = |languageTranslation = /.test(s2) ) return;
	if ( /^script [0-9]+ started$/.test(s2) ) return;


	//- �R�}���h�p�l���o��
	_popvw.cmdhist( s2, ( /ERROR/.test( s2 ) ) ? 'red' : 'blue' );

	//- �I�𐔃��b�Z�[�W
	if ( /atoms selected/.test( s2 ) )
		_ofunc( '_selected_count', s2.replace( 'atoms selected', '' ) )
}

//.. _mousepick �}�E�X�N���b�N
function _mousepick( p1, p2 ) {
	w_o._vw.mousepick( window.name, p2 );
}

//.. ���f���ǂݍ��݁E�\��

//... _asb: �W���̍\���ǂݍ���
// i: biomol��ID
// f: �卽�����t���O
// limit: ���̂ق��̏��� (������
function _asb( i, f ) {
	_jmols( 'load "" FILTER "'
		+ ( f ? backbone_only : '' ) //- �卽����
		+ 'biomolecule ' + i + '";' + phpvar.jmolcmd.init 
	, 0 );
//	_datareloaded(); //- �ǂݍ��񂾃f�[�^�̏����N���A
//	_asb_btn( c );
}

//... _loadend / _loadstart
//- f => 1: �\�ʃ��f���v�Z���o�[���o�� 0: �\���f�[�^�������o�[���o��
function _loadstart( f ) {
	A.pg_calc = ( f == 1 ) ? 1 : 2;
	clearTimeout( timer.loaderror );
	$( '.loadingbar' ).html( phpvar.loadingbar );
	$( '#loading' ).show( 'fast' );
	timer.loaderror = setTimeout( function(){
		//�S�́i�y�[�W�J�����Ƃ��ɏo�Ă���z�j
		$( '.loadingbar' ).html( phpvar.loadingerror );
		//- �A�v���b�g�̓z��5�b�ŏ���
		timer.loaderror2 = setTimeout( "$('#loading').hide('slow')", 5000 );
	}, 20000 );
}

/*
/
//... reloadstr �ēǂݍ��� 
// m: ���[�h ud:original 1:���j�b�g�Z�� 2x2x2 etc
// c: �{�^���̃I�u�W�F�N�g
function _reloadstr( c, m1, m2, m3 ) {
	var s1, s2;
	var ld = ( fileid == 2 ) // �ʂ̃��f����ǂ�ł��Ȃ��H
		? 'load ""'
		: 'load "' + _u_pdb( id ) + '"'
	;
	if ( m1 != undefined ) { // ���j�b�g�Z��
		s1 = ' {' + m1 + ' ' + m2 + ' ' + m3 + '} ';
		s2 = 'unitcell {'
			+ ( m1 > 2 ? '1' : '0' ) + ' '
			+ ( m2 > 2 ? '1' : '0' ) + ' '
			+ ( m3 > 2 ? '1' : '0' ) + '};';
		jmols( ld + s1 + ';' + init_style + s2, 0 );
	} else {
		jmols( ld + ';' + init_style, 0 );
	}
	_datareloaded() //- �t�B�b�g�}�b�v�N���A
	_asb_btn( c );
}

//... _datareloaded: �f�[�^�������[�h���ꂽ�Ƃ��̑Ώ�
function _datareloaded() {
	//- �t�B�b�g�}�b�v�̃`�F�b�N�{�b�N�X�͂���
	$( '.cb_fit' ).prop('checked', false);
	loadedmap = undefined;

	//-split �p ���[�h�{�^���𕜊�
	$( '.b_apnd' ).prop('disabled', false);
	fileid = 2;

	//- ef-site�̃`�F�b�N�{�b�N�X�͂���
	$( '.cb_ef' ).prop('checked', false);
	loadedef = {};
	
	//- jmol�\��
	$( '#surflist' ).text('');
	_chsurf();
	mscnt = 0;
}
*/

