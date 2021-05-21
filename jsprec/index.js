/*
index.js
*/

//. 開始時
$(function(){
	$('#reldate').change( function() {
		_get_outer( $(this).val() );
	});
});

//. ajax func
//.. _get_outer
function _get_outer( reldate ) {
	phpvar.postdata.reldate = reldate
	$('#catalog_outer')._loadex({
		u:'?ajax=outer',
		v: phpvar.postdata,
		func: function(){ _tab.reset(); }
	});
	$('#num_emdb').text( phpvar.ent_num[reldate][0] );
	$('#num_pdb').text( phpvar.ent_num[reldate][1] );
}

//.. _tabsel
function _tabsel( tabname ) {
	var obj_ct = $('#ct_'+ tabname );
	phpvar.postdata.tab = tabname;
	if ( obj_ct.html() ) return;
	obj_ct._loadex({
		u:'?ajax=tab',
		v: phpvar.postdata
	});
}

//.. _page
function _page( num ) {
	$('#ct_' + phpvar.postdata.tab )._loadex({
		u:'?ajax=tab&page=' + num,
		v: phpvar.postdata
	});
}
