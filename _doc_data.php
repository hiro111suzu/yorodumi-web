<?php
/*
@ data更新
{http://marem/emnavi/_mng-docs.php

ネタ
how to cite


*/




//. よく使うリンクとか
//.. ソフト

$link_chimera = [
	'https://www.cgl.ucsf.edu/chimera/', 'UCSF Chimera Home Page' ];
$link_jmol = [
	'http://jmol.sourceforge.net/',
	'Jmol: an open-source Java viewer for chemical structures in 3D'
];
$link_libav = [ 'https://libav.org/', 'Libav' ];

$db_lab =	[
//	'http://www.protein.osaka-u.ac.jp/rcsfp/pi/index_en.html' ,
	'http://www.protein.osaka-u.ac.jp/en/laboratories/database-en' ,
	'Laboratory of Protein Databases., Institute of Protein Research,Osaka University' ,
	'http://www.protein.osaka-u.ac.jp/laboratories/database' ,
	'蛋白質研究所 データベース開発研究室' 
];


//.. omokage paper
$omop_text =  "Omokage search: shape similarity search service for biomolecular structures in both the PDB and EMDB. Suzuki Hirofumi, Kawabata Takeshi, and Nakamura Haruki, <i>Bioinformatics.</i> (2015) btv614";

$omop_url = 'http://bioinformatics.oxfordjournals.org/content/early/2015/11/09/bioinformatics.btv614';

$omop_supurl = 'http://bioinformatics.oxfordjournals.org/content/suppl/2015/10/24/btv614.DC1/supplementray-pub-suzuki.pdf';

$omop_e = $omop_text . BR
	. _ab( $omop_url, "main text (HTML, Open Access)" ) . ', '
	. _ab( $omop_supurl, "supplementray data (PDF, Open Access)" )
;
$omop_j = $omop_text . BR
	. _ab( $omop_url, "本文 (HTML、オープンアクセス)" ) . ', '
	. _ab( $omop_supurl, "supplementray data (PDF, オープンアクセス)" )
;


//. news
$_type = 'news';
//.. 2020-08-12 New page for Covid-19 info
_d([
	'2020-08-12', '',
	'New: Covid-19 info' ,
	'New: 新型コロナ情報' ,
	[
		'New page: Covid-19 featured information page in EM Navigator' ,
		_ab( 'covid19.php', _img( 'img/about_covid19.jpg' ) )
	], [
		'新ページ: EM Navigatorに新型コロナウイルスの特設ページを開設しました。' ,
		_ab( 'covid19.php', _img( 'img/about_covid19.jpg' ) )
	],
],[
	'id' => 'newpage_covid19' ,
	'rel' => [ 'about_covid19', 'sars-cov-2' ] ,
	'tag' => 'omo emn ym' ,
	'link' => []
]);

//.. 2020-03-05 Novel coronavirus structure data
_d([
	'2020-03-05', '',
	'Novel coronavirus structure data' ,
	'新型コロナウイルスの構造データ' ,
	[
		'International Committee on Taxonomy of Viruses (ICTV) defined the short name of the 2019 coronavirus as "SARS-CoV-2".'
		. BR
		. _ab( 'https://www.nature.com/articles/s41564-020-0695-z', 'The species Severe acute respiratory syndrome-related coronavirus: classifying 2019-nCoV and naming it SARS-CoV-2 - nature microbiology' )
		,
		'In the structure databanks used in Yorodumi, some data are registered as the other names, "COVID-19 virus" and "2019-nCoV". Here are the details of the virus and the list of structure data.'
		. _ul([
				_ab(
					'taxo.php?k=2697049' ,
					'Severe acute respiratory syndrome coronavirus 2'
				),
				_ab(
					'ysearch.php?kw=SARS-CoV-2' ,
					'\'SARS-CoV-2\' - Yorodumi Search'
				),

//				_ab(
//					'taxo.php?k=1508227' ,
//					'Bat SARS-like coronavirus'
//				)
			])
	], [
		'国際ウイルス分類委員会(ICTV)は、新型コロナウイルス感染症(COVID-19)の病原ウイルスの略称を「SARS-CoV-2」と決定しました。'
		. BR
		. _ab( 'https://www.nature.com/articles/s41564-020-0695-z', 'The species Severe acute respiratory syndrome-related coronavirus: classifying 2019-nCoV and naming it SARS-CoV-2 - nature microbiology' )
		,
		'万見で扱っている構造データベースでは、"COVID-19 virus"、"2019-nCoV"といった別称・仮称でも登録されています。こちらでウイルスの詳細と構造データの一覧を見られます。'
		. _ul([
				_ab(
					'taxo.php?k=2697049' ,
					'Severe acute respiratory syndrome coronavirus 2'
				),
				_ab(
					'ysearch.php?kw=SARS-CoV-2' ,
					'\'SARS-CoV-2\' - 万見検索'
				),
//				_ab(
//					'taxo.php?k=1508227' ,
//					'Bat SARS-like coronavirus'
//				)
			])
	]], [
	'id' => 'sars-cov-2' ,
//	'img' => 'stat_plot.png' ,
	'rel' => [ 'about_taxo' ] ,
	'tag' => 'omo emn ym' ,
	'link' => [
		[
			'https://pdbj.org/featured/covid-19',
			'COVID-19 featured content - PDBj',
			'COVID-19特集ページ - PDBj'
		] , [
			'https://numon.pdbj.org/mom/242' ,
			'Molecule of the Month (242)：Coronavirus Proteases',
			'今月の分子2020年2月：コロナウイルスプロテーアーゼ'
		]
		
	] ,
	'img' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/78/SARS-CoV-2_49534865371.jpg/150px-SARS-CoV-2_49534865371.jpg' ,

]);


//.. 2019-07-04 Download text
_d([
	'2019-07-05', '',
	'Downlodablable text data' ,
	'テキストデータのダウンロード' ,
	[
		'Some data of EM Navigator services can be downloaded as text file. Software such as Excel can load the data files.' .
		_table_toph( [ 'Page', 'Data', 'Format' ], [
			[ _ab( 'esearch.php', 'EMN Search' ), 'search result', 'CSV, TSV, or JSON' ] ,
			[ _ab( 'stat.php', 'EMN statistics' ) ,  'data table', 'CSV or TSV'  ],
		])
	],	[
		'EM Navigatorの以下のサービスのデータが、テキスト形式でダウンロードできるようになりました。Excelなどでダウンロードしたデータを読み込むことができます。' .
		_table_toph( [ 'ページ', 'データ', 'フォーマット'], [
			[ _ab( 'esearch.php', 'EM Navigator 検索' ), '検索結果', ' CSV, TSV, JSON' ],
			[ _ab( 'stat.php', 'EM Navigator 統計情報' ),'表データ', 'CSV, TSV' ],
		])
	]
],[
	'id' => 'text-download' ,
	'img' => 'stat_plot.png' ,
	'rel' => [ 'about_esearch', 'about_stat' ] ,
	'tag' => 'emn' , 
]);


//.. 2019-01-31 EMDB-ID
_d([
	'2019-01-31', '',
	'EMDB accession codes are about to change! (news from PDBe EMDB page)' ,
	'EMDBのIDの桁数の変更' ,
	[
		'The allocation of 4 digits for EMDB accession codes will soon come to an end. Whilst these codes will remain in use, new EMDB accession codes will include an additional digit and will expand incrementally as the available range of codes is exhausted. The current 4-digit format prefixed with “EMD-” (i.e. EMD-XXXX) will advance to a 5-digit format (i.e. EMD-XXXXX), and so on. It is currently estimated that the 4-digit codes will be depleted around Spring 2019, at which point the 5-digit format will come into force. (see '
		. _ab( 'https://www.ebi.ac.uk/pdbe/emdb', 'PDBe EMDB page')
		. ')' ,
		'The EM Navigator/Yorodumi systems omit the EMD- prefix.'
	],
	[
		'EMDBエントリに付与されているアクセスコード(EMDB-ID)は4桁の数字(例、EMD-1234)でしたが、間もなく枯渇します。これまでの4桁のID番号は4桁のまま変更されませんが、4桁の数字を使い切った後に発行されるIDは5桁以上の数字(例、EMD-12345)になります。5桁のIDは2019年の春頃から発行される見通しです。' ,
		'EM Navigator/万見では、接頭語"EMD-"は省略されています。'
	],
],[
	'id' => 'emdb-id' ,
	'rel' => [ 'what_emd', 'id_notation' ] ,
	'tag' => 'emn ym' , 
	'link' => [
		[
			'https://www.ebi.ac.uk/pdbe/emdb',
			'EMDB at PDBe', 'PDBeのEMDBサイト'
		] , [
			'https://pdbj.org/contact' ,
			'Contact to PDBj', 'PDBjへお問い合わせ'
		]
		
	]
		
]);


//.. 2018-02-20 PDBj workshop
_d([
	'2018-02-20', '',
	'PDBj/BINDS workshop in Osaka University',
	'2018年2月20日のPDBj & BINDS 合同講習会の資料',
	[],
	[
		_ab(
			'https://pdbj.org/workshop/20180220/suzuki_lecture_20180220.pdf',
			'講義資料' 
		),
		_ab(
			'https://pdbj.org/workshop/20180220/suzuki_exercise_20180220.pdf',
			'演習資料' 
		), 
		_ab( 'https://pdbj.org/news/20180105', '講習会のページ' )
	]
],[
	'id' => 'pdbj_workshop2018-02' ,
	'tag' => 'emn'
]);

//.. 2017-10-04 nobel prize
_d([ '2017-10-04', '',
	'Three pioneers of this field were awarded Nobel Prize in Chemistry 2017' ,
	'この分野の3人の先駆者が、ノーベル化学賞を受賞しました' ,
	[
		'Jacques Dubochet (University of Lausanne, Switzerland) is a pioneer of ice-embedding method of EM specimen (as known as cryo-EM), Most of 3DEM structures in EMDB and PDB are obtained using his method.' ,
		'Joachim Frank (Columbia University, New York, USA) is a pioneer of single particle reconstruction, which is the most used reconstruction method for 3DEM structures in EMDB and EM entries in PDB. And also, he is a develper of Spider, which is one of the most famous software in this field, and is used for some EM Navigor data (<i>e.g.</i> map projection/slice images).' ,
		'Richard Henderson (MRC Laboratory of Molecular Biology, Cambridge, UK) was determined the first biomolecule structure by EM. The first EM entry in PDB, PDB-1brd is determinedby him.'	],[
		'Jacques Dubochet (University of Lausanne, Switzerland)は、電子顕微鏡試料の氷包埋法（いわゆるクライオ電顕法）を開発しました。EMDBやPDBにある電子顕微鏡のエントリの大多数がこの方法を利用しています。' ,
		'Joachim Frank (Columbia University, New York, USA)は、単粒子解析法を開拓しました。EMDBとPDBの電子顕微鏡エントリの大多数がこの解析法によるものです。また、広く利用されている画像解析ソフトェア「Spider」の開発者でもあります。EM Navigatorでもマップデータの画像作成などにSpiderを利用しています。' ,
		'Richard Henderson (MRC Laboratory of Molecular Biology, Cambridge, UK)は電子顕微鏡による生体分子の立体構造解析の始祖です。電子顕微鏡による最初のPDBエントリであるPDB-1brdは、彼によるものです。'
	]
],[
	'id' => 'nobelprize' ,
	'tag' => 'emn' ,
	'link' => [
		[
			'https://www.nobelprize.org/nobel_prizes/chemistry/laureates/2017/press.html' ,
			'The 2017 Nobel Prize in Chemistry - Press Release'
		]
	]
]);


//.. 2017-07-12 omokage filter
_d([
	'2017-07-12', '',
	'Major update of PDB' ,
	'PDB大規模アップデート',
	[ 
		'wwPDB released updated PDB data conforming to the new PDBx/mmCIF dictionary. This is a major update changing the version number from 4 to 5, and with <i>Remediation</i>, in which all the entries are updated. See below links for details.' ,
		'In this update, many items about electron microscopy experimental information are reorganized (e.g. em_software). Now, EM Navigator and Yorodumi are based on the updated data.'
	],[
		'新バージョンのPDBx/mmCIF辞書形式に基づくデータがリリースされました。今回の更新はバージョン番号が4から5になる大規模なもので、全エントリデータの書き換えが行われる「Remediation」というアップデートに該当します。詳細は下記のリンクをご覧ください。',
		'このバージョンアップで、電子顕微鏡の実験手法に関する多くの項目の書式が改定されました(例：em_softwareなど)。EM NavigatorとYorodumiでも、この改定に基づいた表示内容になります。'
	]
],[
	'id' => 'pdb_v5' ,
	'tag' => 'omo emn ym' ,
	'link' => [
		[
			'https://www.wwpdb.org/documentation/remediation' ,
			'wwPDB Remediation'
		], 
		[
			'https://www.wwpdb.org/news/news?year=2017#5963997661fd3d50915a4af7',
			'Enriched Model Files Conforming to OneDep Data Standards Now Available in the PDB FTP Archive',

			'https://pdbj.org/news/20170712' ,
			'OneDepデータ基準に準拠した、より強化された内容のモデル構造ファイルが、PDBアーカイブで公開されました。'
			
		]
	]

]);

//.. 2017-06-16 omokage filter
_d([
	'2017-06-16', '',
	'Omokage search with filter' ,
	'Omokage検索で絞り込み',
	[ 
		'Result of Omokage search can be filtered by keywords and the database types' ,
	],[
		'Omokage検索の結果をキーワードとデータベースの種類で絞り込むことができるようになりました。'
	]
],[
	'id' => 'omo_filter' ,
	'rel' => [ 'about_omosearch' ],
	'tag' => 'omo emn ym'
]);
//.. 2016-09-15 new em navigator & yorodumi
_d([
	'2016-09-15', '',
	'EM Navigator & Yorodumi renewed' ,
	'新しくなったEM Navigatorと万見' ,
	[
		'New versions of EM Navigator and Yorodumi started'
	],[
		'EM Navigatorと万見を刷新しました' 
	]
],[
	'id' => 'emn_repl2',
	'rel' => [ 'new_emn_changes' ],
	'tag' => 'emn ym' 
]);

//.. 2016-08-31 new em navigator & yorodumi
_d([
	'2016-08-31', '',
	'New EM Navigator & Yorodumi' ,
	'新しいEM Navigatorと万見' ,
	[
		'In 15th Sep 2016, the development versions of EM Navigator and Yorodumi will replace the official versions.' ,
		'Current version will continue as \'legacy version\' for some time.' ,
	],[
		'これまで開発版として公開していたEM Navigatorと万見が、9月15日から正式版となります。' ,
		'現行版も「旧版」としてしばらく公開を継続します。'
	]
],[
	'id' => 'emn_repl',
	'rel' => [ 'new_emn_changes', 'about_emn', 'about_ym'] ,
	'tag' => 'emn ym' 
]);
/*
_d([
	'2016-05-27', '',
	'Release of New EM Navigator' ,
	'新EM Navigatorの公開' ,
/*
	[
		'Changes:', [
			'New user interface unified with <i>Yorodumi</i> & <i>Omokage search</i>, supporting mobile devices as well as PC.',
			'New <i>Yorodumi</i> play a part in the page for the individual data entry (<i>"Detail page"</i> in the legacy system). It is unified browser for EMDB, PDB, and SASBDB entries integrated with <i>EM Navigator</i> and <i>Omokage search</i>.' ,
			'The viewers (structure/movie viewers) appear in pop-up windows. On a PC, multiple viewer windows can be opened. On mobile devices, they support touch operation.',

		] ,
		'New features:', [
			'<b><i>Molmil</i></b>, molecular structure viewer, and <b><i>SurfView</i></b>, surface model viewer for EMDB map data, are available to view the 3D structures. Both viewers support mobile device use.',
			'<b><i>EMN Papers</i></b>, citation database of EM data entries', 
		] ,
		'The legacy pages will also continue for some time.',
	], [
		'変更点', [
			'ページ外観や操作性を刷新しました。新しい「万見」と「Omokage検索」と共通化し、PCだけでなくモバイル機器にも対応しました。',
			'個別のデータエントリのページ（旧版の「詳細ページ」）は、新しい「万見」が受け持ちます。「Omokage検索」のフロントエンド・詳細情報表示も兼ねた、EMDB、PDB、SASBDB用の共通の詳細ページです。',
			'構造ビューア、ムービービューアは個別のポップアップウインドウに表示されます。PCでは一度に複数のビューアウインドウの表示が可能です。モバイル機器ではタッチ操作にも対応しています。'
		] ,
		'新しい機能', [
			'3次元構造の閲覧に、分子構造ビューア「<b>Molmil</b>」と、EMDBマップ表面モデルビューア「<b>SurfView</b>」が利用できるようになりました。',
			'EMデータエントリの引用文献のデータベース「<b>EMN文献</b>」を開始しました。',
		],
		'旧版のページも、今後しばらく継続します。'
	]

],[
	'rel' => [ 'about_emn_leg', 'about_emn', 'surfview', 'molmil', 'movie', 'about_empap' ] ,
	'tag' => 'omo emn ym' 
]);

*/

//.. 2016-04-13 omokage got faster
_d([
	'2016-04-13', '',
	'Omokage search got faster' ,
	'Omokage検索が速くなりました' ,
	[
		'The computation time became ~1/2 compared to the previous version by re-optimization of data accession' ,
		'Enjoy "shape similarity" of biomolecules, more!'
	], [
		'データアクセスプロセスを見直し、計算時間をこれまでの半分程度に短縮しました。' ,
		'これまで以上に、生体分子の「カタチの類似性」をお楽しみください!'
	]
],[
	'rel' => [ 'about_omosearch' ] ,
	'tag' => 'omo emn ym' ,
]);

//.. 2016-03-03
//- セミナーURL: http://www.protein.osaka-u.ac.jp/seminar/iprseminar_20160219/
$u = 'http://www.protein.osaka-u.ac.jp/en/seminar-en/iprseminar_20160219/';
$f = 'doc/2016-02-IPR-seminar.pdf';
_d([
	'2016-03-03', '',
	_ab( $f, 'Presentation (PDF format)' )
		. ' at ' . _ab( $u, 'IPR seminar on Feb 19' ) . '.'
	,
	_ab( $u, '2月19日の蛋白研セミナー' )
		. 'での' . _a( $f, 'プレゼンテーション(PDFファイル)' )

],[
	'tag' => 'omo emn ym'
]);


//.. 2015-12-04 omokage paper
$p = _ab(
	'http://bioinformatics.oxfordjournals.org/content/early/2015/11/09/bioinformatics.btv614' ,
	 "Omokage search: shape similarity search service for biomolecular structures in both the PDB and EMDB. Suzuki Hirofumi, Kawabata Takeshi, and Nakamura Haruki, <i>Bioinformatics.</i> (2015) btv614"
);
$sup = _ab(
	'http://bioinformatics.oxfordjournals.org/content/suppl/2015/10/24/btv614.DC1/supplementray-pub-suzuki.pdf' ,
	'Supplementary Data - PDF file'
);
_d([
//	'2015-12-04 Omokage search paper', '2015-12-04 Omokage検索の論文',
	'2015-12-04', '' ,
	'The article about Omokage search is published online',
	'Omokage検索の論文がオンライン出版されました',
	[
		$omop_e

	],[
		$omop_j

	],
],[
	'tag' => 'omo emn' ,
	'rel' => [ 'about_omosearch', 'gmfit' ]
]);

//.. 2015-11-28 sasbdb
_d([
	'2015-11-28', '' ,
	"Omokage search starts to support SASBDB models" ,
	'SASBDBの登録モデルもOmokage検索で探せるようになりました' ,
	[ 
		'Models data in SASBDB are added to the Omokage search database.' ,
		'SASBDB is a databank for small angle scattering data' ,
		'A SASBDB model can be used as a search query.' ,
		'The search result may include SASBDB models.' ,
	],[
		'Omokage検索のデータベースにSASBDBの登録モデルを追加しました。' ,
		'SASBDBは、小角散乱の実験データを扱うデータバンクです。' , 
		'SASBDBモデルを検索クエリとして利用できます。' ,
		'検索結果の中にSASBDBモデルが含まれるようになります。' ,
	]
], [
	'tag' => 'omo emn' ,
	'rel' => [ 'about_omosearch', 'sasbdb', '3databanks' ]	
]);

//.. 2014-12-10 large
_d([
	'2014-12-10', '2014-12-10',
	'PDB "SPLIT" entries are replaced with "LARGE" entries' ,
	'PDBの分割登録エントリが、単独のエントリに置き換えられました' ,
	[
		'Structure data deposited as multiple PDB entries are replace with single combined entries,  which were previously stored as "large structure". '
		.  _ab( 'http://wwpdb.org/news/news_2014.html#10-December-2014', 'See here for details.' )
		, 
		' In EM Navigator, many ribosome and several virus entries are replaced.'
	],[
		'原子数や鎖数の都合で単一の構造を複数に分割し登録されていたデータは廃止になり、それらをひとつに統合したエントリ（これまでは「巨大構造」として別途公開されていた）が公式のデータになります。'. _ab( 'http://pdbj.org/news/20141210', '詳細はこちらをご覧ください。' )
		,
		'EM Navigatorでは、多数のリボソームと数件のウイルスのデータが置き換わりました。'
	]
],[
//	'id' => '2014-12-10' ,
	'tag' => 'pdb emn' ,
]);

//.. 2014-09-22 omokage search
_d([
	'2014-09-22', '2014-09-22',
	'New service: Omokage search', '新規サービス: Omokage検索',
	'Shape similarity search service, <i>Omokage search</i> has started.' ,
	'形状類似検索「Omokage検索」を開始しました。'
],[
//	'id' => '2014-09-22' ,
	'tag' => 'omo emn' ,
	'rel' => [ 'about_omosearch' ]
]);

//.. stat page
_d([
	'2013-03-30', '',
	'New page of in EM Navigator, ' . _a( 'stat.php', _ic( 'statistics' ) . 'statistics' ) ,
	'EM Navigatorの新ページ' . _a( 'stat.php', _ic( 'statistics' ) . '統計情報ページ' ) . 'を公開を開始しました'
],[
	'tag' => 'emn old' ,
	'rel' => [ 'about_stat' ]
]);

//.. xml 1.9
_d([
	'2013-01-16', '',
	'New EMDB header format', '新しいEMDBヘッダ形式への対応' ,
	[
		"Format of the EMDB header file (XML based meta data file, not the map data themselves) are updated to version 1.9 ("
		. _ab( 'http://emdatabank.org/emdb_hdr_update.html', 'see here for details' ) . ')' ,
		'Now, the contents of EM Navigator are based on the new data. Colors of some parts (e.g. bars in top area of the Detail pages) indicate 3D-reconstruction method, instead of \'aggregation states\'.'
	],[
		'EMDBエントリのヘッダファイル（XML形式の付随情報データ、マップデータそのものではない）の形式が、バージョン1.9に更新されました（'
		. _ab( 'http://emdatabank.org/emdb_hdr_update.html', '詳細はこちら' ) . '）。' ,
		'EM Navigatorのコンテンツもそれに対応したものに変更しました。詳細ページの上部のバーなど、これまで\'aggregation state\'（集合状態）による色分けをされていた部分が、\'method\'（3次元再構成の手法）による色分けとなります。'
	]
],[
//	'id' => '2013-01-16' ,
	'tag' => 'emdb emn old' ,

//	'rel' => [ 'about_stat' ]
]);


//. faq
$_type = 'faq';

//.. 全部
//... 色付け
_d([
	'How do you make the images for the structure data? What do their colors mean?' ,
	'構造データの画像はどうやって作っているのですか？色はどういう意味があるのですか？' ,

	'Images of EMDB entries are made for EM Navigator, and ones of PDB entries are for Yorodumi. There are several patterns of coloring.' ,
	'EMDBの画像はEM Navigatorの画像、PDBデータの画像は万見の画像です。色付けは複数のパターンがあります。' ,
	
	'See following items for the details.' ,
	'詳細はについては、それぞれの項目をご覧ください。' ,
], [
	'id' => 'faq_image' ,
	'tag' => 'ym omo emn' ,
	'rel' => [ 'how_to_make_movie', 'how_to_make_pdbimg' ]
]);

//... アップデート
_d([
	'When the data are updated?', 'データの更新はいつですか？',
	'EMDB and PDB entries are released/updated every Wednesday at  0:00GMT/9:00JST' ,
	'EMDBとPDBのデータは、毎週水曜の日本時間午前9:00に更新・公開されます'  ,
	[ 
		'Data in EM Navigator, Yorodumi, and Omokage are updated at the same time.' ,
		'SASBDB seems to update irregularly.' ,
	], [
		'EM Navigatorと万見、Omokage検索のデータも同時に更新されます。' ,
		'SASBDBは不定期に更新されているようです。' ,
	]
], [
	'id' => 'when_update' ,
	'tag' => 'emn databank'
]);


//.. em
//... 3DEM and cryoEM
_d([
	'Is 3DEM same as electron cryo microscopy (cryoEM)?' ,
	'3DEMとは、低温電子顕微鏡法（クライオ電顕、cryoEM）のことですか？' ,
	'No, "3DEM" and "cryoEM" are distinct to be exact.' ,
	'「3DEM」と「クライオ電顕」は厳密には別の用語です。' ,
	[
		'However, they are closely related. Some people call 3DEM "cryoEM' ,
		'Many but not all the 3DEM analyses are perfomed with cryoEM. In EMDB and PDB, there are some entries, whose structure data were obtained in non-cryo contition.'
	] ,
	[
		'しかしながら、「3DEM」と「クライオ電顕」は深い関係にあり、しばしば同じ意味の用語として使用されます。' ,
		'多くの3DEMデータはクライオ電顕によるものですが、すべてがそうではありません。EMDBやPDBにも低温環境下ではない実験で得られた構造データがいくつか登録されています。'
	]
], [
	'id' => 'faq_3dem_cryoem' ,
	'tag' => 'emn' ,
	'rel' => [ '3dem', 'cryoem' ]
]);

//... FTP site
$j = _ab_url( 'ftp://ftp.pdbj.org/pub/emdb' );
$e = _ab_url( 'ftp://ftp.ebi.ac.uk/pub/databases/emdb' );
$w = _ab_url( 'ftp://ftp.wwpdb.org/pub/emdb' );
_d([
	'Where is the official data of EMDB?', 'EMDBの公式データはどこにありますか？' ,
	'Following three are the official, have the same contents, and update at same time' ,
	'以下3つです。すべて同じ内容で、更新時刻も同じです。',
	[ _table_toph(
		[ 'Country'	, 'Organization', 'URL' ], [
		[ 'Japan'	, 'PDBj' , $j ] ,
		[ 'UK'		, 'EBI'  , $e ] ,
		[ 'USA'		, 'wwPDB', $w ] ,
	]) ],
	[ _table_toph(
		[ '国'		, '組織' , 'URL' ], [
		[ '日本'	, 'PDBj' , $j ] ,
		[ 'イギリス', 'EBI'  , $e ] ,
		[ 'アメリカ', 'wwPDB', $w ] ,
	]) ]
],[
	'id' => 'ftpurl' ,
	'tag' => 'emn emmap' ,
	'rel' => 'when_update' ,
]);

//... ED map と同じ？
_d([
	'Is an EM map electron-density map?' ,
	'マップデータとは電子密度マップのことですか？',
	'No. But, they are very similar.' ,
	'よく似ていますが、違います。' ,
	[
		'To be exact, 3D map derived by 3D-EM is related to <b>Coulomb potential (or electron potential)</b>, rather than electron density.' ,
		'The difference seems to be ignored in the most studies of atomic model building and fitting.' ,
		'Thers are some reports that protonation state affects the density. (see the ext. link)'
	], [
		'厳密には、3D-EMで得られるマップは、電子密度ではなく<b>クーロンポテンシャル(ポテンシャル密度)</b>に関係しています。'  ,
		'現在、原子モデルの当てはめや原子モデルの構築の過程では、両者の違いはあまり考慮されていないようです' ,
		'プロトン化の有無によって密度に差が出るという報告もあります。'
	]
], [
	'id' => 'same_edmap' ,
	'tag' => 'emn emmap' ,
	'link' => [
		[
			'http://www.nature.com/nature/journal/v389/n6647/full/389206a0.html' ,
			'Kimura et al. Surface of bacteriorhodopsin revealed by high-resolution electron crystallography. Nature 389, 206, 1997 doi:10.1038/38323'
		]
	]
]);

//... フォーマットは？
$pdf = _ab(
	'ftp://ftp.pdbj.org/pub/emdb/doc/Map-format/current/EMDB_map_format.pdf' ,
	'EMDB_map_Format.pdf'
);

_d([
	'What is the format for the maps?' ,
	'マップデータのフォーマットは何ですか？' ,
	'It is CCP4 map format', 'CCP4マップ型式です。' ,
	[
		'It\'s binary data with header for map geometry and body for density values.' ,
		'Here is description of EMDB map data. ' . $pdf,
	], [
		'バイナリ形式のデータで、マップのサイズなどを記述するためのヘッダと、密度値を記述するための本体からなります。',
		'詳細はこちら: ' . $pdf 
	]
],[
	'id' => 'map_format' ,
	'tag' => 'emn emmap' ,
	'rel' => [ 'same_edmap' ] ,
	'link' => [
		['http://www.ccp4.ac.uk/html/maplib.html', 'CCP4 map format' ],
	]
]);

//... どのソフト
$softlist = '<br>' . _ab(
	'http://en.wikibooks.org/wiki/Software_Tools_For_Molecular_Microscopy/Visualization_and_modeling_tools',
	IC_L . 'Software Tools For Molecular Microscopy/Visualization and modeling tools - Wikibooks'
);

_d([
	'Which software is suitable to view EMDB maps?' ,
	'どのソフトを使えば、EMDBのマップデータを見られますか？' ,
	'Many software packages can display CCP4 map data.' ,
	'多くのソフトウェアが、マップデータの表示に対応しています。' ,
	[
		'Some softwares introduced in this page shoud be suitable.' . $softlist ,
		'Movies for EMDB map data in the EM Navigator are made by UCSF-Chimera.' ,
		'UCSF-Chimera seems to be the major software in the community.'
	], [
		'このページで紹介されているソフトウェアが相応しいでしょう。' . $softlist ,
		'EM Navigatorでは、EMDBエントリのムービー作成にUCSF-Chimeraを使用しています。' ,
		'この分野の研究者には、UCSF-Chimeraのユーザーが多いようです。'
	]
],[
	'id' => 'soft4map' ,
	'link' => [ $link_chimera ] ,
	'tag' => 'emmap' ,
	'rel' => [ 'map_format' ]
	
]);

//.. emdb
//... EMD-未整理
_d([
	'What is "EMD"?',
	'「EMD」とは何ですか？',
	'EMD (or emd) is "prefix" for ID number of EMDB',
	'EMD (またはemd)は、EMDBのID番号の「接頭語」です。',
	[
		'At the beginning, the EMDB was also called EMD.',
		'Since the EM Navigator uses PDB and EMDB data, ID codes are indicated as [database name]-[ID], without the prefix. e.g. EMDB-1001, PDB-1brd',
	],[
		'設立当初は、EMDBはEMDとも呼ばれていました。' ,
		'EM NavigaotrではPDBとEMDBのデータを利用しているため、IDの表記は、[データベースの名称-ID]とし、接頭語は省略しています。例: EMDB-1001, PDB-1brd',
	]
],[
	'id' => 'what_emd' ,
	'rel' => [ 'emdb', 'about_emn' ] ,
	'tag' => 'emn'
]);


//.. em navi
//... EM Navigatorで使っているデータは
_d([
	'What is the data source of EM Navigator?', 'EM Navigatorの情報源は？',
	'EMDB and PDB electron microscopy data' ,
	'EMDBとPDBの電子顕微鏡データです' ,
	[
		'Main source:' ,
		[
			'all EMDB entries' ,
			'PDB entries with method information (exptl.details) including "electron microscopy" or "electron diffraction"' ,
		] ,
		"Some contents and infromation are original of EM Navigator:" ,
		[
			'Movies and their snapshot images' ,
			'Projection and slice images' ,
			'Some information about related data and similar structures.' ,
		]
	],[
		'主要な情報源:' ,
		[
			'EMDB全エントリ' ,
			'PDBエントリのうち、手法(exptl.details)として"electron microscopy"か、"electron diffraction"が記述されているエントリ' ,
		] ,
		"以下の情報やコンテンツはEM Navigator独自のものです:" ,
		[
			'ムービーやそのスナップショット' ,
			'投影像と断面図' ,
			'関連データの一部と、類似構造の情報.'
		]
	] ,
],[
	'id' => 'emn_source' ,
	'tag' => 'emn' ,
	'rel' => [ 'about_emn', 'emdb', 'pdb' ] ,
]);

//... ムービーの利用

_d([
	'Can I use the movies and their snapshots in the EM Navigator for papers or presentations?' ,
	'EM Navigator上のムービーやムービーのスナップショットを、プレゼンテーションや論文などに利用できますか？' ,
	'Yes, plese. The EM Navigator movies and their snapshots are open to public. It would be appreciated if you would cite it as "EM Navigator, PDBj".' ,
	'どうぞ、ご利用ください。引用元は”EM Navigator, PDBj”としていただければ幸いです。',
],[
	'id' => 'emn_term_of_use' ,

//	'rel' => [] ,
	'link' => [
		[ 'http://pdbj.org/info/terms-conditions',
		'Terms and conditions - PDBj', 'PDBjの利用規約とプライバシーポリシー' ]
	]
]);

//... ムービーどうやって作っているか
$chimera_script = '<pre>
system mkdir img1
movie reset
movie record fformat png directory ./img1/ pattern img*
roll y 2 180; wait
wait 15
roll x 2 180; wait
reset pos1; wait
reset pos2 180; wait
reset pos1 180; wait
reset
movie stop
</pre>';

_d([
	'How do you make the movies in EM Navigator?' ,
	'EM Navigatorのムービーはどうやって作っているのですか？' ,
	"Series of images are recorded by UCSF-Chimera or Jmol. Then, they are encoded into movie files by avconv command in the libav package." ,
	"UCSF-ChimeraかJmolで連続画像を作成し、libavパッケージのavconvコマンドでムービーにエンコードしています。" ,
	[
		'This is an example of Chimera script.' . $chimera_script
		. '("pos1" and "pos2" are saved position, which are the start and end of sectioning motion)',
		'Chimera session files distributed with the movie files may be helpfull.'
	], [
		'Chimeraのスクリプトの例です。' . $chimera_script
		. '(「pos1」と「pos2」は、断面を表示するモーションの開始点と終了点で保存したpositionです)'
		,
		'ムービーと併せてChimeraのセッションファイルを公開しています。参考になるかもしれません。'
		
	]
],[
	'id' => 'how_to_make_movie' ,
	'tag' => 'emn' ,
	'rel' => [] ,
	'link' => [ $link_chimera, $link_jmol, $link_libav ]

]);


/*
. _faq(
	'How do you choose surface levels of the map data for the movies?' ,
	'ムービー'
)


*/

//... 画像 どうやって
$q = _ej( 
	'Q: How do you make the images for the structure data? What do their colors mean?' ,
	'Q: 構造データの画像はどうやって作っているのですか？色はどういう意味があるのですか？'
);

$a0 = _ej( 
	'A: Images of EMDB entries are made for EM Navigator, and ones of PDB entries are for Yorodumi. There are several patterns of coloring.' ,
	'EMDBの画像はEM Navigatorの画像、PDBデータの画像は万見の画像です。色付けは複数のパターンがあります。'
);

$d = _ej([
	'EMDB entries' ,
	[
		'Method: They are the snapshots of the EM Navigator movies, which are made using UCSF Chimera in semi-automatic process.' ,
		'Orientation: The structures are manually rotated to be seen in "good" orientation (usually, figures of the paper is refererd), or in similar orientation with similar structure data. For the case of icosahedral symmetry structure data, they are viewed normal to their five-fold axis, while typical view of such the structure data are along the two-fold or three-fold axes. This is to unify the orientation with structure data with pseudo-icosahedral symmetry, such as bacteriophage with tail structures.' ,
		'Color: In EM Navigator, multiple images are made for a single entry. In Omokage search, image of "colored surface view" are shown. The surface are colored by height, distance, or cylindrical radius. See the detail page for the coloring of particular images.'
	] ,

], [
	'EMDBエントリの画像' ,
	[
		'手法: 画像はEM Navigatoの動画のスナップショットです。動画はUSCF Chimeraを利用して半自動的に作成しています。' ,
		'方向: 手作業で、そのデータに相応しい方向（論文が出版・発行されている場合はその図を参考にします）、あるいは同様の構造がある場合は極力それに似た方向になるようにしています。正20面体対称の構造体の場合は、一般的な描画では2回対称軸に沿った方向にしますが、EM Navigatorでは5回対称軸を縦に向けています。これは尾構造を持つバクテリオファージなどに見られる擬似的な正20面体対称構造と方向を統一するためです。' ,
		'色: EM Navigaotrでは、ほとんどのデータに対して、着色したものと単色の画像を用意していますが、Omokage検索では着色したものが表示されます。色は中心からの距離か、円筒半径か、あるいはある軸に沿った「高さ」によって決まります。個々の動画の着色法ついては、データの詳細ページに表示されています。'
	] ,
]);
//... PDBエントリの画像はどうやって
_d([
	'How do you make the images of PDB entries in Yorodumi, Omokage, etc.?' ,
	'万見やOmokageのPDBの画像は、どうやって作っていますか？' ,
	'We are making them by full-automatic process using Jmol. Their styles are depend on the data type' ,
	'Jmolを利用して全自動で作成しています。スタイルはデータの種類によります。' ,
	[
		'Orientation:' ,
		[
			'Icosahedral assembly: Original orientation.' ,
			'Helical assembly: 6 orientation (+/- of X, Y, Z direction) images are generated in jpeg format. Then, the largest file is chosen. (large JPEG => many dots/colors)' ,
			'Others: by "rotate best" command of Jmol.',
			'Ribosomes: by "rotate best" command of Jmol. Only RNA coordinates are used for the orientation.'
		] ,
		'Color:' ,
		[
			'monomer AUs: by N->C (blue->red) rainbow' ,
			'BUs of monomer AUs: by "color molecule" (Jmol\'s coloring)' ,
			'multimer AUs and thier BUs (including monomer BUs): by "color chain". For this coloring, Jmol uses not a rainbow color but the colors determined by the chain-ID.',
		] ,
		'Problems:' ,
		[
			'BU models are generated by Jmol\'s system. It works well for the most data, but there are some exception.' ,
			'Checking and fixing processes of the many (>200,000) images are in progress. There are still some wrong images.'
		]
	],[
		'方向:',
		[
			'正20面体対称構造: そのままの方向' ,
			'らせん対称構造: まず6方向(+/- X,Y,Z 方向)からの画像をJPEG形式で作成し、ファイルのサイズが一番大きかったものを採用しています。(同じサイズの画像のJPEG形式のファイルのサイズは、圧縮の難しさ、つまり画像の複雑さによるからです)' ,
			'その他: Jmolの"roate best"コマンドを利用' ,
			'リボソーム: Jmolの"roate best"コマンドを利用(ただしRNAの座標のみ考慮)'
		] ,
		'色:' ,
		[
			'モノマーのAU: 配列順による虹色 (N->C, 青->赤)' ,
			'モノマーのAUのBU: Jmolの"color molecule"' ,
			'マルチマーのAUと、そのBU (BUがモノマーの場合も含む): "color chain" (Jmolの "color chain"は虹色着色ではなく、鎖IDごとに定められた色による着色です)' ,
			'(ここでいうモノマーとは、DNAかRNAかポリペプチドの鎖が一つしかない構造です)'
		],
		'問題点:' ,
		[
			'集合体のモデル作成はJmolのシステムを利用しています。ほとんどのデータについてはうまく動作していますが、一部うまく作成できていないものがあります。' ,
			'現在、画像のチェックと再作成を進めています。正しく描画されていない画像が残っています。'
		]
	]
],[
	'id' => 'how_to_make_pdbimg' ,
	'link' => [ $link_jmol ] ,
	'tag' => 'ym omo emn' ,
	'rel' => [  ]
,
]);

//... 誰？
$dot = _span( '.red', '.' );
$mail = 'hirofumi ' . _img( 'img/am.jpg' ) . " protein{$dot}osaka-u{$dot}ac{$dot}jp";
$fb = _ab( "https://www.facebook.com/hirofumi.suzuki.104", 'Facebook - ' . _img( 'img/face.jpg' ) );

_d([
	'Who make these documents? Who develop EM Navigator, Yorodumi, Omokage search, etc?',
	'この文書を書いているのは誰ですか？EM Navigator、万見、Omokage検索などの開発者は？' ,
	'Hirofumi Suzuki (PDBj / IPR, Osaka University)' ,
	'大阪大学蛋白質研究所・PDBjの鈴木博文です。',
	[
		$mail ,
		'Institute for Protein Research, Osaka University, 3-2 Yamadaoka, Suita, Osaka, 565-0871, Japan.' ,
		$fb ,
	],[
		$mail ,
		'大阪大学蛋白質研究所 蛋白質データベース開発研究室' ,
 		$fb ,
	]
],[
	'id' => 'developer' ,

	'rel' => [
		'pdbj' 
	] ,
	'link' => [
		$db_lab 
	] ,

]);

//... ネタ
/*
名前由来
コンタクト



*/

//... ネタ
/*
ムービーのアドレス
図のアドレス

どうやってポリゴンデータを作っているのか？

1. comvert ccp4 map to 'obj' format data with UCSF Chimera.
Chimera command:
export format OBJ foo.obj

2. modify the obj file for Jmol.
Jmol seems be able to read only 'v' and 'f' lines.

2-1. remove the header and comment lines, such as '#', 'vn', 'g', 'usemtl', and 'mtllib' lines.
regexp:
'^(#|vn|g|usemtl|mtllib) .+\n' => ''

2-2 replace '1234//1234' with '1234' in 'f' lines.
regexp:
'\/\/[0-9\.]+' => ''

3. export to JVXL format by Jmol.

Jmol command:
isosurface obj "foo.obj";
write isosurface "foo.jvxl";

*/

//. misc doc
$_type = 'info';

//.. about
//... emn
$emn = _ic( 'emn' ) . _a( 'index2.php', 'EM Navigator' );
_d([
	'EM Navigator', 'EM Navigator' ,
	'3D electron microscopy data browser', '3次元電子顕微鏡データブラウザ',
	[
		'Browser for 3D electron microscopy (3D-EM) data of biological molecules and assemblies.'
	], [
		'生体分子や生体組織の3次元電子顕微鏡データを、<b>気軽にわかりやすく</b>眺めるためのウェブサイト' 
	]
],[
	'id'  => 'about_emn' ,
	'tag' => 'emn emdb pdb about' ,
	'rel' => [ 'emdb', 'pdb', 'pdbj' ] ,
	'url' => '.' ,
]);

//... emn search *
_d([
	'EMN Search', 'EMN検索' ,
	'3DEM data search', '3次元電子顕微鏡データ検索' ,
	[
		'Advanced data search for EMDB and EM data in PDB widh various search and display options'
	],[
		'EMDBとPDBの電子顕微鏡エントリを検索するページ',
		'豊富な検索条件と表示条件' ,
	]
],[
	'id' => 'about_esearch' ,
	'tag' => 'emn emdb pdb about' ,
	'rel' => [ 'emdb', 'pdb', 'about_emn','emn_source', 'about_ysearch' ] ,
	'url' => 'esearch.php' ,
]);


//... 3DEM papers
_d([
	'EMN Papers', 'EMN文献',
	'Database of articles cited by 3DEM data entries' ,
	'3DEM構造データから引用されている文献のデータベース' ,
	[
		'Database of articles cited by 3DEM data entries in EMDB and PDB' ,
		'Using PubMed data', 

	], [
		'EMDDBとPDBの3次元電子顕微鏡エントリから引用されている文献のデータベースです' ,
		'Pubmedのデータを利用しています' ,
	],
],[
	'id' => 'about_empap' ,
	'url' => 'pap.php?em=1' ,
	'tag' => 'emn about' ,
	'rel' => [ 'emdb', 'pdb', 'emn_source', 'about_emn', 'about_pap' ]
]);

//... gallery
_d([
	'EMN Gallery', 'EMNギャラリー' ,
	'Image gallery of 3DEM data' ,
	'3DEMデータを画像で一覧' ,
	[
		'Categorization is done by EM Navigator manager manually. It is not strict.'
	], [
		'分類はEM Navigator独自のものです。厳密な分類ではありません。'
	],
],[
	'id' => 'about_gallery' ,
	'url' => 'gallery.php' ,
//	'rel' => '3databanks' ,
	'tag' => 'emn about' ,
	'rel' => [ 'emdb', 'pdb', 'about_emn']
]);

//... statistics
_d([
	'EMN Statistics', 'EMN統計情報' ,
	'Statistics of 3DEM data in table and graph styles' ,
	'3DEMデータの統計情報を表やグラフで閲覧' ,
	[
		'The table can be sorted. Click the column header to be sorted. (second click to reverse, [Shift]+click for multi-column sort) ' ,
		'To show the bar graph in table mode, point the column/row header by mouse courser.' ,
		'To search the correspoinding data, click the cell of value.',
		'Examples:', 
		[
			_a([ 'stat', 'key' => 'reso_seg', 'k2' => 'method' ] ,
				'"Resolution" vs. "Method"' ) ,
			_a([ 'stat', 'key' => 'temp_seg', 'k2' => 'reso_seg' ] ,
				'"Specimen Temperature" vs. "Resolution"' )
		]
	, 

	], [
		'表はソートできます。列の先頭をクリックすると、その列基準のソートになります。（再度クリックすると逆順、[Shift]+クリックで複数列基準のソート）' ,
		'行や列の先頭にマウスカーソルを置くと、棒グラフが現れます。' ,
		'数値のセルをクリックすると該当する検索結果のページが開きます。' ,
		'例：' ,
		[
			_a([ 'stat', 'key' => 'reso_seg', 'k2' => 'method' ] ,
				'解像度 vs. 手法' ) ,
			_a([ 'stat', 'key' => 'temp_seg', 'k2' => 'reso_seg' ] ,
				'試料温度 vs. 解像度' )
		]
	],
],[
	'id'  => 'about_stat' ,
	'url' => 'stat.php' ,
	'tag' => 'emn about' ,
	'rel' => [ 'emdb', 'pdb', 'emn_source', 'about_emn' ]
]);

//... Yorodumi
_d([
	'Yorodumi', '万見 (Yorodumi)',
	'Thousand views of thousand structures' ,
	'幾万の構造データを、幾万の視点から' ,
	[
		'Yorodumi is a browser for structure data from EMDB, PDB, SASBDB, etc.' ,
//		'All the functionalities will be ported from the levgacy version.' ,
		'This page is also the successor to <b>EM Navigator detail page</b>, and also detail information page/front-end page for <i>Omokage search</i>.' ,
	], [
		'万見(Yorodumi)は、EMDB/PDB/SASBDBなどの構造データを閲覧するためのページです。' ,
//		'旧バージョンのすべての機能をこちらに移植する予定です' ,
		'EM Navigatorの詳細ページの後継、Omokage検索のフロントエンドも兼ねています。' ,
	]
],[
	'id' => 'about_ym' ,
	'url' => 'quick.php' ,
	'tag' => 'ym about' ,
	'rel' => [ 'emdb', 'pdb', 'sasbdb', '3databanks', 'about_ysearch' ]
]);


//... ym search *
_d([
	'Yorodumi Search', '万見検索' ,
	'Cross-search of EMDB, PDB, SASBDB, etc.', 'EMDB/PDB/SASBDBなどの横断検索' ,
	[
	],[
	]
],[
	'id' => 'about_ysearch' ,
	'tag' => 'ym emdb pdb about' ,
	'rel' => [ 'emdb', 'pdb', 'sasbdb', '3databanks', 'about_esearch' ] ,
	'url' => 'ysearch.php' ,
]);


//... structure papers
_d([
	'Yorodumi Papers', '万見文献',
	'Database of articles cited by EMDB/PDB/SASBDB data' ,
	'EMDB/PDB/SASBDBから引用されている文献のデータベース' ,
	[
		'Database of articles cited by EMDB, PDB, and SASBDB entries', 
		'Using PubMed data', 

	], [
		'EMDB/PDB/SASBDBのエントリから引用されている文献のデータベースです' ,
		'Pubmedのデータを利用しています' ,
	],
],[
	'id' => 'about_pap' ,
	'url' => 'pap.php' ,
	'tag' => 'ym about' ,
	'rel' => [ 'emdb', 'pdb', 'sasbdb', 'about_ym', 'about_empap' ]
]);

//... taxonomy
_d([
	'Yorodumi Speices', '万見生物種',
	'Taxonomy data in EMDB/PDB/SASBDB' ,
	'EMDB/PDB/SASBDBの生物種情報' ,
	[
		'Taxonomy database of sample sources of data in EMDB/PDB/SASBDB'
	], [
		'EMDB/PDB/SASBDBの構造データの試料情報、由来する生物種に関するデータベース' 
	],
],[
	'id' => 'about_taxo' ,
	'url' => 'taxo.php' ,
	'tag' => 'ym about' ,
	'rel' => [ 'emdb', 'pdb', 'sasbdb', '3databanks' ]
]);

//... omokage search
_d([
	'Omokage search', 'Omokage検索' ,
	'Search structure by SHAPE', '「カタチ」で構造検索' ,
	[
		'"Omokage search" is a shape similarity search service for 3D structures of macromolecules. By comparing <b>global shapes</b>, and ignoring details, similar-shaped structures are searched.',
		'The search is performed ageinst >200,000 structure data, which consists of EMDB map data, PDB coordinates (deposited units (asymmetric units, usually), PDB biological units, and SASBDB mdoels).',
		'For the search query, you can use either a data in the PDB/EMDB/SASBDB or your original model.',
		'Supported formats are PDB (atomic model, SAXS bead model, etc.) and CCP4/MRC map (3D density map).',
		'Shape comparison is performed by iDR profile method, which uses 1D-distance profiles of super-simplified models generated by vector quantizaion in <i>Situs</i> package.',
	], [
		'Omokage検索は、生体超分子の形状類似性検索サービスです。細部を無視した<b>全体の形状のみの比較</b>により、類似データを検索します。' ,
		'検索の対象となるデータセットは、EMDBのマップデータ、PDBの登録構造（通常は非対称単位、AU）、PDBの生物学的単位(BU)、SASBDBの登録モデルで、合計約20万の構造データからの検索となります。' ,
		'EMDB・PDB・SASBDBの登録データ、あるいは利用者所有のオリジナルの構造を使った検索が可能です。' ,
		'対応する形式は、PDB形式(原子モデル、SAXSビーズモデルなど)か、CCP4/MRC形式(3次元密度分布マップ)です。' ,
		'比較はiDRプロファイル法によって行います。' ,
	],
],[
	'id' => 'about_omosearch' ,
	'url' => 'omo-search.php' ,
	'rel' => '3databanks' ,
	'tag' => 'omo about' ,
]);

//... doc
_d([
	'Yorodumi Docs', '万見文書' ,
	'Documentation for Yorodumi, EM Navigator, Omokage, etc.' ,
	'万見・EM Navigator・Omokageなどのヘルプや情報を検索・閲覧' ,
	[
	], [
	],
],[
	'id' => 'about_doc' ,
	'url' => 'doc.php' ,
//	'rel' => '3databanks' ,
	'tag' => 'omo ym emn about' ,
]);

//... covid-19
_d([
	'Covid-19 info', 'Covid-19情報' ,
	'Page for Covid-19 featured contents.' ,
	'新型コロナウイルスの情報ページ' ,
	[
	], [
	],
],[
	'id' => 'about_covid19' ,
	'url' => 'covid19.php' ,
	'rel' => '3databanks' ,
	'tag' => 'omo ym emn about' ,

]);

//... fh search

_d([
	'F&H Search', 'F&H 検索' ,
	'Function & Homology similarity search' ,
	'機能・相同性の類似性検索' ,
	[
		'Based on the similarity of Function & Homolog items related to the structure data, similar data are searched from structure databases.',
	], [
		'構造データに関連付けられた機能・相同性アイテムの類似性に基づき、類似するデータを構造データベースから検索します。'
	]
],[
	'id' => 'about_fh_search' ,
	'url' => 'fh-search.php' ,
	'rel' => 'func_homology' ,
	'tag' => 'omo ym emn about' ,
]);


//.. viewers
//... movie
_d([
	'Movie viewer', 'ムービービューア' ,
	'Visualization of 3DEM structure data by movies.' ,
	'動画による3DEM構造データビューア' ,
	[
		'Horizontal- and vertical- rotation motions and "slicing" motion are recorded in the movies.' ,
		'As the movie frame can be controlled by mouse/touch position, it can be operated interactively as a molecular viewer. According to the mouse/finger position and orientation of the motion on the movie panel, the model rotates on horizontal or vertical direction. By horizontal mouse/finger movement on the top part of the movie, the slicing motion is shown.', 
	],[
		'ムービーには横回転、縦回転、切断面の移動のモーションが録画されています。',
		'ムービーのフレームは、マウスやタッチの位置で切り替わるので、分子構造ビューアのようなインタラクティブな操作が可能です。ムービー画面上のマウスや指の位置と動く方向に合わせてフレームが切り替わり、モデルが縦方向・横方向に回転します。画面上部でマウスや指を横に移動させると、断面のモーションが表示されます。',
	]
],[
	'id' => 'movie' ,
	'tag' => 'viewer emn',
	'rel' => [ 'how_to_make_movie', 'emn_term_of_use', 'surfview' ]
]);

//... Jmol
_d([
	'Jmol/JSmol', '' ,
	'An open-source viewer for chemical structures in 3D' ,
	'オープンソースの3次元化学構造ビューア',
	_mouse(
		[ 'left drag', '[Ctrl] + right drag', 'center drag (vertical) / wheel' ],
		[ 'single finger', 'double finger', 'pinch' ] ,
		true
	)
	.  'Jmol menu: [Ctrl] + click / right click'
	,
	_mouse(
		[ '左ボタンドラッグ', '[Ctrl] + 右ボタンドラッグ', '中ボタンドラッグ(縦方向) / ホイール回転' ],
		[ '1本指', '2本指', 'ピンチ（つまむ動作）' ]
	)
	. 'Jmolメニュー: [Ctrl] + クリック / 右クリック' ,
],[
	'id' => 'jmol' ,
	'tag' => 'viewer emn ym' ,
	'rel' => [ 'molmil', 'about_ym' ],
	'link' => [
		['http://jmol.sourceforge.net/', 'Jmol: an open-source Java viewer for chemical structures in 3D' ]
	]
]);

//... Molmil
_d([
	'Molmil', '',
	'WebGL based molecular viewer' ,
	'WebGLを利用した分子構造ビューア' ,
	[
		'Molmil runs on Web browser on a smartphone or tablet device as well as PC. (Molmil is based on WebGL and Javascript).',
		_mouse(
			[ 'left drag', 'center drag / shift + left drag', 'right drag / wheel', 'Function menu: top left buttons on viewer screen ' ],
			[ 'single finger', '', 'pinch', '' ] ,
		true )
	],[
		'PCだけでなく、スマートフォンやタブレットでも動作します。(WebGLと呼ばれる仕組みを利用しています)',
		_mouse(
			[ '左ボタンドラッグ', '中ボタンドラッグ / [Shift] + left drag', 'right drag / wheel' ],
			[ 'single finger', '', 'pinch' ] ,
		true ) ,
	],
], [
	'id' => 'molmil',
	'tag' => 'viewer ym' ,
	'link' => [
		[ 'https://pdbj.org/help/molmil',
			'Documentation of Molmil in PDBj site', 'PDBjサイト内のMolmilの解説' ] ,
		[ 'https://github.com/gjbekker/molmil',
			'Molmil\'s page in GitHub', 'GitHubのMolmilのページ'] ,
	]
]);

//... surfview
_d([
	'SurfView', '',
	'EMDB map surface models viewer on Web browsers' ,
	'ブラウザ上で動作するEMDBマップの表面モデルビューア' ,
	[
		'SurfView run on Web browser on a smartphone or tablet device as well as PC. (SurfView is based on a WebGL and <i>three.js</i>, Javascript 3D library)' ,
		'The surface models shwon in this viewer are modified for simplification and data size reduction. To get views of full-resolution data, see movies instead.' ,
		_mouse(
			[ 'left drag', 'right drag', 'center drag / wheel' ],
			[ 'single finger', 'double finger', 'pinch' ] ,
		true )
	],[
		'PCだけでなく、スマートフォンやタブレットでも動作します。(WebGLとJavaScript3次元ライブラリを利用しています)',
		'単純化とデータサイズ削減のために、表面モデルが改変されている場合があります。完全な解像度の構造はムービーでご覧ください。' ,
		_mouse(
			[ '左ボタンドラッグ', '右ドラッグ', '中ボタンドラッグ / ホイール回転' ],
			[ '一本指', '2本指', 'ピンチ（つまむ動作）' ]
		)
	]

], [
	'id' => 'surfview' ,
	'img' => 'surfview.jpg' ,
	'rel' => [ 'movie', 'molmil' ] ,
]);
//... func: mouse
function _mouse( $mouse, $touch, $english = false ){
	return $english
		? 'Mouse/touch operation'. _table_2col([
			'[th]'  => [ 'X-Y rotation', 'X-Y move', 'Zoom in/out' ] ,
			'Mouse' => $mouse ,
			'Touch' => $touch ,
		])
		: 'マウス・タッチ操作'. _table_2col([
			'[th]'		 => [ 'X-Y回転', 'X-Y移動', '拡大・縮小' ] ,
			'マウス操作' => $mouse ,
			'タッチ操作' => $touch ,
		])
	;
}	

//.. DB info
//... 3 databanks
$topr = TR_TOP.TH.TH. 'PDB' .TH. 'EMDB' .TH. 'SASBDB';
_d([
	'Comparison of 3 databanks', '3つのデータバンクの比較',
	'', '',
	'Yorodumi and Omokage can cross-search these DBs.'
	. _table_2col([
		'[th]'   => [ 'method', 'main data', 'str. data format', 'meta data format' ] ,
		'PDB'    => [ 'various', 'atomic model', 'PDBx/mmCIF, etc.', 'PDBx/mmCIF, etc.' ],
        'EMDB'   => [ '3DEM', '3D map',  'CCP4 map', 'EMDB XML' ],
        'SASBDB' => [ 'Small Angle Scattering (SAS)','SAS profile <b>(+/- 3D models)' ,
			'PDB + sasCIF',  'ASCII + sasCIF' ] ,
	]) ,
	'万見とOmokageではこれらのデータベスの横断検索が可能です'
	. _table_2col([
		'[th]'   => [ '解析手法', '主データ', '構造データのフォーマット',
			'付随データのフォーマット'
		],
		'PDB'    => [ '多様', '原子モデル',  'PDBx/mmCIF, etc.',  'PDBx/mmCIF, etc.'  ] ,
		'EMDB'   => [ '3DEM', '3次元マップ', 'CCP4マップ', 'EMDB XML' ],
		'SASBDB' => ['SAS (小角散乱)', 'SASプロファイル<br> (+/- 3次元構造)', 'PDB + sasCIF', 
			'テキスト + sasCIF' ] ,
	])
],[
	'id'  => '3databanks' ,
	'tag' => 'db emdb pdb sasbdb' ,
	'rel' => [ 'emdb','pdb','sasbdb' ] ,
]);


//... ID 表記
_d([
	'ID/Accession-code notation in Yorodumi/EM Navigator' ,
	'万見/EM NavigatorにおけるID/アクセスコードの表記' ,
	_table_2col([
		'[th]Database'	=> [ 'pattern'  , 'e.g.'     , 'note' ] ,
		'EMDB'		    => [ 'EMDB-XXXX', 'EMDB-1001', 'prefix EMD- omitted' ] ,
		'PDB'		    => [ 'PDB-XXXX' , 'PDB-1a00' , '' ] ,
		'SASBDB'	    => [ '-'        , 'SASDA24'  , 'as it is' ] ,
	]),
	_table_2col([
		'[th]データベース' => [ 'パターン' , '例'       , '備考' ],
		'EMDB'	           => [ 'EMDB-XXXX', 'EMDB-1001', '接頭語EMD-は省略' ] ,
		'PDB'	           => [ 'PDB-XXXX' , 'PDB-1a00' , '' ] ,
		'SASBDB'	       => [ '-'        , 'SASDA24'  , 'IDコードはそのまま' ],
	])
],[
	'id'  => 'id_notation' ,
	'rel' => [ '3databanks', 'what_emd' ] ,
]);

//... EMDB
_d([
	'EMDB', '',
	'Electron Microscopy Data Bank, Databank for 3DEM', 'Electron Microscopy Data Bank、 3DEMのためのデータバンク'
], [
	'id' => 'emdb' ,
	'link' => [
		[ 'https://www.ebi.ac.uk/pdbe/emdb/', 'The Electron Microscopy Data Bank (PDBe)' ] ,
		[ 'https://www.emdataresource.org/', 'EMDataResource' ] ,
	] ,
	'wikipe' => 'EM_Data_Bank' ,
	'rel' => [ '3dem' ] ,
	'tag' => 'db' ,
	'url' => 'https://www.ebi.ac.uk/pdbe/emdb/' ,
]);

//... PDB
_d([
	'PDB', '',
	'Protein Data Bank - The single repository of information about the 3D structures of proteins, nucleic acids, and complex assemblies.', '蛋白質構造データバンク(PDB) - タンパク質や核酸、それらの複合体の3次元構造のための唯一のデータベース'
], [
	'id' => 'pdb' ,
	'link' => [
		[ 'http://wwPDB.org/', 'World Wide PDB' ] ,
//		[ 'https://www.ebi.ac.uk/pdbe/emdb/', 'The Electron Microscopy Data Bank (PDBe site)' ] ,
//		[ 'https://en.wikipedia.org/wiki/EM_Data_Bank', 'EM Data Bank - Wikipedia' ] ,
	] ,
//	'rel' => [ '3dem' ] ,
	'tag' => 'db' ,
	'rel' => [ 'pdbj' ] ,
	'url' => 'https://wwPDB.org/' ,
]);

//... PDBj
_d([
	'PDBj', '',
	'Protein Data Bank Japan.' ,
	'日本蛋白質構造データバンク (Protein Data Bank Japan)' ,
	'A member of wwPDB.',
	'wwPDBのメンバー',
], [
	'id' => 'pdbj' ,
	'link' => [
//		[ 'http://pdbj.org/', 'World Wide PDB' ] ,
//		[ 'https://www.ebi.ac.uk/pdbe/emdb/', 'The Electron Microscopy Data Bank (PDBe site)' ] ,
//		[ 'https://en.wikipedia.org/wiki/EM_Data_Bank', 'EM Data Bank - Wikipedia' ] ,
	] ,
//	'rel' => [ '3dem' ] ,
	'url' => 'http://pdbj.org/' ,
	'tag' => 'db' ,
	'rel' => [ 'pdb' ] ,
	'link' => [
		[ 'https://www.facebook.com/PDBjapan', 'PDBj@Facebook' ],
		[ 'https://twitter.com/PDBj_en', 'PDBj@Twitter' ],
		$db_lab
	]
]);

//... SASBDB
//$u = 

_d([
	'SASBDB', '',
	'Small Angle Scattering Biological Data Bank - Curated repository for small angle scattering data and models' ,
	'生体試料小角散乱データバンク - 小角散乱のデータと立体構造モデルのデータバンク' ,
	[
		'Develop & manage: BioSAXS group in EMBL' ,
		'Since 2014'
	],[
		'EMBLのBioSAXSにより運営' ,
		'2014年に設立'
	]
	
], [
	'id' => 'sasbdb' ,
	'link' => [
		[
			'http://nar.oxfordjournals.org/content/early/2014/10/28/nar.gku1047.long' ,
			'Valentini E, Kikhney AG, Previtali G, Jeffries CM, Svergun DI. SASBDB, a repository for biological small-angle scattering data. Nucleic Acids Res. 2015 Jan 28;43:D357-63.'
		]
	] ,
//	'rel' => [ '3dem' ] ,
	'tag' => 'db',
	'url' => 'http://www.sasbdb.org/' ,
//	'rel' => [ 'pdbj' ]
]);

//... gmfit
_d([
	'gmfit', '',
	'a program for fitting subunits into density map of complex using GMM (Gaussian Mixture Model) ' ,
	'ガウス混合モデル(Gaussian Mixture Model, GMM)を使った3DEMマップのフィッティングプログラム',
	[
		'Developed by Takeshi Kawabata in IPR, Osaka-univ.' ,
		'Used from Omokage search.' ,
	],[
		'阪大・蛋白研の川端猛 博士により開発' ,
		'Omokage検索から利用' ,
	]
	
], [
	'id' => 'gmfit' ,
	'link' => [
		[
			'https://pdbj.org/gmfit/pairgmfit.html' ,
			'Pairwise gmfit'
		]
	] ,
	'rel' => [ 'about_omosearch' ] ,
	'tag' => 'emdb db omo',
	'url' => 'https://pdbj.org/gmfit/' ,
//	'rel' => [ 'pdbj' ]
]);


//... wwPDB ネタ

//.. aboutn EM
//... 3dem
_d([
	'3D electron microscopy (3DEM)', '3次元電子顕微鏡(3DEM)', 
	'A generic term of electron microscopic analyses to obtain 3D structures' ,
	'3次元構造を得るための電子顕微鏡解析の総称' ,
	[ 
		'Analyses such as electron tomography, single particle analysis, and electron diffraction are included.'
		. _table_2col([
			'[th]Aggregation states' => 'Methods generally used' ,
			'"individual structure"' => 'electron tomography' ,
			'"single particle"'		 => 'single particle analysis' ,
			'"icosahedral"'			 => 'single particle analysis' ,
			'"helical"' => 'helical reconstruction / single particle analysis' ,
			'"2D/3D-crystal"' => 'electron diffraction / Fourier filtering'  ,
		])
		,
			'Characteristics compared to X-ray crystallography and NMR:' ,
			[
				'<b>Advantage</b>: Wider applicability of sample (not require high-purity or high-concentration smaple, and useful for more huge, complex, flexible, and less uniform sample)' ,
				'<b>Disadvantage (previous)</b>: Lower resolution (hard to get atomic-level resolution data)' ,
				'<b>Disadvantage (new)</b>: High-performance electron microscopes and their maintenance costs are quite expensive.'
			]
		
	], [
		'たとえば、単粒子解析、電子線トモグラフィー、電子線結晶学など'
		. _table_2col([
			'[th]試料の集合状態' => '主に使われる手法' ,
			'単独の構造 (individual structure)' =>
				'電子線トモグラフィー (electron tomography)' ,
			'単粒子 (single particle)' => '単粒子解析 (single particle analysis)' ,
			'正20面体対称 (icosahedral)' => '単粒子解析' ,
			'らせん対称 (helical)' =>
				'らせん対称再構成 (helical reconstruction) / 単粒子解析' ,
			'2次元結晶 (2D-crystal)' >
				'電子線結晶学 (electron diffraction) / フーリエフィルタ (Fourier filtering)' ,
		])
		,
		'NMRやX線結晶学と比較して、一般的には次のような特徴がある' ,
		[
			'<b>長所</b>: 試料調製のハードルが低い - 低純度・希少な試料、巨大・柔軟・脆弱・不均一な対象にも利用可能' ,
			'<b>短所(以前)</b>: 解像度が低い - 原子レベルの解像度の解析は難しい' ,
			'<b>短所</b>: 高性能な電子顕微鏡の設置と維持は高額を要し、普及の途上である'
		]
	]
	
], [
	'id' => '3dem' ,
	'tag' => 'emn emdb' 
]);

//... cryo EM
_d([
	'electron cryo microscopy (cryoEM)' ,'低温電子顕微鏡（クライオ電顕、cryoEM）' ,

	'Electron microscopy where the sample is studied at cryogenic temperatures' ,
	'クライオ（低温）条件下の試料が観察可能な電子顕微鏡法、あるはその装置' ,

	'The specimen is cooled at 4～100K (-269～-170℃) to keep it in hydrated state in the highly vaqumed environment, and to reduce radiation dammage by electron beam.' ,
	'試料は、4～100K (-269～-170℃)に冷却される。これにより高真空中でも水和状態を保ち、電子線照射によるダメージも軽減される。' ,
], [
	'id' => 'cryoem' ,
	'tag' => 'emn' ,
	'link' => [
		[
			'http://en.wikipedia.org/wiki/Cryo-electron_microscopy' ,
			'Cryo-electron microscopy - Wikipedia'
		]
	] ,
	'rel' => [ '3dem' ] ,
	'wikipe' => 'Transmission_electron_cryomicroscopy' ,
]);

//.. old movie
_d([
	'"Movies out of date"', '「古いムービー」',
	'Movies with this annotation may not be up-to-date.', 
	'この注釈の付いたムービーは、最新のマップデータに対応していない可能性があります。' ,
	'EMDB map data are sometimes remediated. Unfortunately, the process of making movies in the EM Navigaotr are not fully automated, and it is very hard to remake the all to catch up. So, some movies and movie parameters are based on the older data, and may be improper for new map data.' ,
	'EMDBのマップデータは、修正されることがあります。EM Navigatorのムービー作成作業は完全自動ではなく、全ての修正への追従はできていません。したがっていくつかのデータエントリについては、ムービーやそれに関するパラメータは古いマップデータを元にしており、新しいマップデータには対応していない場合があります。' 
], [
	'id' =>	'oldmov' ,
	'tag' => 'emn' ,
	'rel' => [ 'how_to_make_movie' ]
]);


//.. misc
//... polysac
$u_ref  = 'https://www.sciencedirect.com/science/article/pii/S0008621516305316';
$link_soft = _ab( 'http://www.rings.t.soka.ac.jp/downloads.html', '<i>GlycanBuilder2</i>' );

_d([
	'Carbohydrate representation', '糖鎖の表現' ,
	'Polysaccaride/carbohydrate data representation in PDB' ,
	'PDBにおける糖鎖・炭水化物の情報の表現について' ,
	[
		'In July 2020, representation for polysaccaride/carbohydrate information in PDB is improved.' ,
		'The polysaccaride figures are generated by '. $link_soft. '. ('
			. _ab( $u_ref, 'Article' ). ')'
		,
		'See following external links for other details.' ,
	], [
		'2020年7月にPDBでの糖鎖データの表現について、大規模な更新が実施されました。' ,
		'糖鎖の画像は'. $link_soft. 'で作成されています。 ('
		. _ab( $u_ref, '文献' ). ')'
		,
		'その他、詳細は下記のリンクを参照してください。' 
	]
], [
	'id' => 'polysac' ,
	'tag' => 'ym' ,
	'link' => [
		[ 
			'https://www.wwpdb.org/documentation/carbohydrate-remediation' ,
			'Carbohydrate Remediation - wwPDB documentation'
		] ,
		[
			'https://www.wwpdb.org/news/news?year=2020#5f0495919902836395e11ce8' ,
			'Coming July 29: Improved Carbohydrate Data at the PDB - wwPDB news' ,

			'https://pdbj.org/news/20200708' ,
			'[wwPDB] 7月29日に糖鎖分子の表現を改善したPDBデータを公開します - PDBjお知らせ' ,
		]
	]
]);

//... met
_d([
	'Experimental methods, equipments, and software data' , 
	'実験手法・装置(施設・設備・機器)・ソフトウェアのデータ' ,
	'Database of experimental methods of EMDB, PDB and SABDB.' ,
	'EMDB・PDB・SABDBから収集した実験情報のデータベース' ,
], [
	'id' => 'met_data' ,
	'tag' => 'ym' ,
	'rel' => [ '3databanks', 'about_ysearch' ]
]);


//... function & homology
$func	= TD. 'Gene ontology, Enzyme Commission number, Reactome, etc.';
$domain	= TD. 'CATH, InterPro, SMART, etc.';
$compo	= TD. 'UniProt, GenBank, PDB chemical component, etc.';

_d([
	'Function and homology information' , 
	'機能・相同性情報' ,
	'Molecular function, domain, and homology information from related databases.' ,
	'関連データベースから収集した分子機能・ドメイン・相同性などの情報' ,
	'To help to understand the molecular function and to find the related structure data, Yorodumi and Yorodumi Search display and utilize the related database information about function and homology. In addition to the information of EC, EMBL, GeneBank, GO, InterPro, UniProt, etc. stored in the EMDB header XML, PDBx/mmCIF and sasCIF original data, information of Pfam, PROSITE, Reactome, UniProkKB, etc. are collected via PDBMLplus, EMDB-PDB fitting data, and/or UniProt.' 
	. BR
	. _t( 'table', ''
		.TR_TOP. TH. 'Category'. TH. 'Name of database or definition'
		.TR.TH. 'Function'. $func
		.TR.TH. 'Domain/homology'. $domain
		.TR.TH. 'Component'. $compo
	)
	, 
	'万見と万見検索では、構造データの機能の理解や類似構造データの検索に役立つように、分子機能やホモロジーに関するデータベースの情報を表示・活用しています。EMDBのヘッダXML、PDBx/mmcif、sasCIFの公式のデータに含まれるEC、EMBL、GeneBank、GO、InterPro、UniProtなどの情報に加えて、PDBMLplus経由、EMDB-PDBフィッティングデータ経由、UniProt経由で収集された、Pfam、PROSITE、Reactome、UniProtKBなどの情報が整理されています。'
	. BR
	. _t( 'table', ''
		.TR_TOP. TH. 'カテゴリ'. TH. 'データベース名・定義名'
		.TR.TH. '分子機能'. $func
		.TR.TH. 'ドメイン・ホモロジー'. $domain
		.TR.TH. '構成要素'. $compo
	)
], [
	'id' => 'func_homology' ,
	'tag' => 'ym' ,
	'rel' => [ 'mlplus', 'about_ysearch', 'about_ym' ]
]);


//... gmfit re-rank

_d([
	'Re-ranking by gmfit', 'gmfitで並べなおし',
	'The similarity ranking by Omokage search can be re-ordered according to correlation coefficient by gmfit, expecting incorporation of the (potential) benefits of two methods with following properties.',
	'Omokage検索による類似度順位をgmfitによる相関係数に従って再順位付けすることができます。以下の様な特徴を持つ2つの手法の「いいとこ取り」を目指した機能です。' ,
	[ 
		_t( 'table', ''
			.TR_TOP.TH. 'Method' .TH.  'Speed' .TH. 'Potential accuracy'
			.TR.TH. 'Omokage search' .BR. ' (iDR profile comparison)'
			.TD. _span( '.bld blue', '++' ) .BR. ' (sub-msec / 1 comparison)'
			.TD. _span( '.bld red', '-' ) .BR. ' (1D profile comparison)'
			.TR.TH. 'gmfit' .BR. ' (Gaussian mixture model fitting)'
			.TD. _span( '.bld red', '+' ) .BR.  '(sub-sec / 1 comparison)'
			.TD. _span( '.bld blue', '+' ) .BR. '(comparison in 3D space)'
		)
	], [
		_t( 'table', ''
			.TR_TOP.TH. '手法' .TH. '速度' .TH. '想定される信頼性'
			.TR.TH. 'Omokage検索' .BR. '(iDRプロファイル比較)'
			.TD. _span( '.bld blue', '++' ) .BR. '(ミリ秒以下/1比較)'
			.TD. _span( '.bld red', '-' ) .BR. '(1次元のプロファイルを利用)'
			.TR.TH. 'gmfit'.BR.' (ガウス混合モデルのフィッティング)'
			.TD. _span( '.bld red', '+' ) .BR.  '(1秒以下/1比較)'
			.TD. _span( '.bld blue', '+' ) .BR. '(3次元空間での比較)'
		)
	]
],[
	'id' => 'gmfit_rerank' ,
	'tag' => 'omo gmfit' ,
	'rel' => [ 'gmfit', 'about_omosearch' ] 
]);

//.. new EMN
_d([
	'Changes in new EM Navigator and Yorodumi', '新しいEM Navigatorと万見の変更点',
	'Changes in new versions of EM Navigator and Yorodumi. (Sep. 2016)' ,
	'新しいEM Navigatorと万見の変更点(2016年9月)' ,
	[
		'Changes:', [
			'New user interface unified in <i>EM Navigator</i>, <i>Yorodumi</i> & <i>Omokage search</i>, supporting mobile devices as well as PCs.',
			'New <i>Yorodumi</i> replace the individual data page (<i>"Detail page"</i> of EM Navigator in the legacy system). It is unified browser for EMDB, PDB, and SASBDB entries integrated with <i>EM Navigator</i> and <i>Omokage search</i>.' ,
			'The viewers (structure/movie viewers) appear in pop-up windows. On a PC, multiple viewer windows can be opened. On mobile devices, the viewers support touch operation.',

		] ,
		'New features:', [
			'<b><i>Molmil</i></b>, molecular structure viewer, and <b><i>SurfView</i></b>, surface model viewer for EMDB map data, are available to view the 3D structures. Both viewers support mobile device use.',
			'Yorodumi support SASBDB entries.',
			'Some new pages such as <b><i>Yorodumi Papers</i></b>, citation database of structure data entries and <b><i>Yorodumi Search</i></b>, cross search by keywords', 
		] ,
	], [
		'変更点', [
			'ページ外観や操作性を刷新しました。新しい「万見」と「Omokage検索」と共通化し、PCだけでなくモバイル機器にも対応しました。',
			'EM Navigatorの個別のデータエントリのページ（旧版の「詳細ページ」）は、新しい「万見」が受け持ちます。「Omokage検索」のフロントエンド・詳細情報表示も兼ねた、EMDB、PDB、SASBDB用の共通の詳細ページです。',
			'構造ビューア、ムービービューアは個別のポップアップウインドウに表示されます。PCでは一度に複数のビューアウインドウの表示が可能です。モバイル機器ではタッチ操作にも対応しています。'
		] ,
		'新しい機能', [
			'3次元構造の閲覧に、分子構造ビューア「<b>Molmil</b>」と、EMDBマップ表面モデルビューア「<b>SurfView</b>」が利用できるようになりました。' ,
			'万見はSASBDBのデータエントリの表示に対応しました。' ,
			'その他新しいページ （引用文献のデータベース「<b>万見文献</b>」「<b>EMN文献</b>」、キーワードによる横断検索「万見検索」など)',
		],
		'旧版のページも、当面は継続します。'
	]
],[
	'id' => 'new_emn_changes',
	'rel' => [ 'about_emn', 'about_ym',  'surfview', 'molmil', 'movie', 'about_empap', 'about_pap', 'about_ysearch' ] ,
	'tag' => 'omo emn ym' 
]);

//.. 情報源
//... PDBMLplus
_d([
	'Information from PDBMLplus', 'PDBML-plusからの情報' ,
	'PDBMLplus is the XML format file including additional information relating to individual proteins. Currently, PDB files are lacking a detailed description of function, experimental conditions, and the like. And then such information was included to extended XML database, named PDBMLplus.' ,
	'PDBMLplusはXML形式のPDBデータフォーマットである「PDBML」のデータに対し、個々の分子に関する情報をPDBjで独自に追加したものです。'
],[
	'id' => 'mlplus',
	'rel' => [ 'pdbj' ] ,
	'tag' => 'omo emn ym' ,
	'link' => [
		[ URL_PDBJ . '/help/pdbmlplus', 'PDBMLplus - PDBj helip' ],
		[ URL_PDBJ . '/help?PID=783', 'About Functional Details page', '機能情報のページについて' ]
	]
]);

//... YM annot
_d([
	'Yorodumi annotation', '万見注釈' ,
	'Annotation by Yorodumi/EM Navigator manager' ,
	'万見・EM Navigatorの管理者による注釈'
],[
	'id' => 'ym_annot',
	'rel' => [ 'developer' ] ,
	'tag' => 'omo emn ym' ,
]);


/*
//.. new Yorodumi old
_d([
	'Changes in new Yorodumi', '新しい万見の変更点',
	'In Sep. 2016, Yorodumi changes as follows.' ,
	'2016年9月から万見が新しくなります。変更点は以下のとおりです。' ,
	[
		'Changes:', [
			'New user interface unified with <i>Yorodumi</i> & <i>Omokage search</i>, supporting mobile devices as well as PC.',
			'New <i>Yorodumi</i> play a part in the page for the individual data entry (<i>"Detail page"</i> in the legacy system). It is unified browser for EMDB, PDB, and SASBDB entries integrated with <i>EM Navigator</i> and <i>Omokage search</i>.' ,
			'The viewers (structure/movie viewers) appear in pop-up windows. On a PC, multiple viewer windows can be opened. On mobile devices, they support touch operation.',

		] ,
		'New features:', [
			'<b><i>Molmil</i></b>, molecular structure viewer, and <b><i>SurfView</i></b>, surface model viewer for EMDB map data, are available to view the 3D structures. Both viewers support mobile device use.',
			'<b><i>EMN Papers</i></b>, citation database of EM data entries', 
		] ,
		'The legacy pages will also continue for some time.',
	], [
		'変更点', [
			'ページ外観や操作性を刷新しました。新しい「万見」と「Omokage検索」と共通化し、PCだけでなくモバイル機器にも対応しました。',
			'個別のデータエントリのページ（旧版の「詳細ページ」）は、新しい「万見」が受け持ちます。「Omokage検索」のフロントエンド・詳細情報表示も兼ねた、EMDB、PDB、SASBDB用の共通の詳細ページです。',
			'構造ビューア、ムービービューアは個別のポップアップウインドウに表示されます。PCでは一度に複数のビューアウインドウの表示が可能です。モバイル機器ではタッチ操作にも対応しています。'
		] ,
		'新しい機能', [
			'3次元構造の閲覧に、分子構造ビューア「<b>Molmil</b>」と、EMDBマップ表面モデルビューア「<b>SurfView</b>」が利用できるようになりました。',
			'EMデータエントリの引用文献のデータベース「<b>EMN文献</b>」を開始しました。',
		],
		'旧版のページも、当面は継続します。'
	]
],[
	'id' => 'new_ym_changes',
	'rel' => [ 'about_emn_leg', 'about_emn', 'surfview', 'molmil', 'movie', 'about_empap' ] ,
	'tag' => 'omo emn ymn' 
]);
//. legacy コメントアウト

//... Yorodumi legacy
_d([
	'Yorodumi (legacy version)', '万見 (旧版)',
	'Touch the mechanism of life' ,
	'生命のカラクリにさわろう' ,
	[
	], [
	],
],[
	'id' => 'about_ym_leg' ,
	'url' => 'viewtop.php?lgc=1' ,
	'tag' => 'ym about' ,
	'rel' => [ 'emdb', 'pdb', 'about_ym' ]
]);

//... emn legacy *
_d([
	'EM Navigator (legacy version)', 'EM Navigator (旧版)' ,
	 'Legacy version of ' . $emn, $emn . '旧版',
	[
		'EM Navigator is the web site to browse 3D electron microscopy (3D-EM) data of biological molecules and assemblies.'
		,
		"The data are based on $e and $p data." 
		. _ab( 'stat.php', '(statistics)' )
		, 
		'This is for <b>non-specialists</b>, <b>beginners</b>, and <b>experts</b> in 3D-EM or structural/molecular biology.'
		,
		"run by $j" ,
	], [
		'生体分子や生体組織の3次元電子顕微鏡データを、<b>気軽にわかりやすく</b>眺めるためのウェブサイトです。' 
		,
		"{$e}と{$p}のデータを利用しています。"
		. _ab( 'stat.php', '(統計情報)' )
		,
		'分子・構造生物学の専門家にも、初心者や専門外のかたにも利用していただけるサイトを目指しています。'
		,
		"{$j}が運営しています。"
	]
],[
	'id' => 'about_emn_leg' ,
	'tag' => 'emn emdb pdb about' ,
	'rel' => [ 'emdb', 'pdb', 'pdbj' ] ,

]);

//. テンプレ
*/
/*
_d([
	'',
	''
],[
	'id' => 'id' ,
	'tag' => 'tag' ,
	'rel' => [] ,
	'link' => ''
]);
*/

//. function
//.. _ab_url: urlだけ
function _ab_url( $s ) {
	return _ab( $s, $s );
}


