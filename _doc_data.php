<?php
/*
ネタ
* how to cite


How do you choose surface levels of the map data for the movies?
ムービー

*/

//. よく使うリンクとか
_rep(<<<EOD
link_chimera
	u	https://www.cgl.ucsf.edu/chimera/
	t	UCSF Chimera Home Page
	-
link_jmol
	u	https://jmol.sourceforge.net/
	t	Jmol: an open-source Java viewer for chemical structures in 3D
	-
link_pdbj_lab
	u	http://www.protein.osaka-u.ac.jp/rcsfp/databases/index.html.en
	uj	http://www.protein.osaka-u.ac.jp/rcsfp/databases/index.html.ja
	t	Protein Data Bank Japan at IPR, Osaka-univ.
	tj	プロテインデータバンク研究室
	-
link_pdbj_contact
	u	https://pdbj.org/contact
	t	Contact to PDBj
	tj	PDBjへお問い合わせ
	-
operation_head_e
	X-Y rotation
	X-Y move
	Zoom in/out
operation_head_j
	X-Y回転
	X-Y移動
	拡大・縮小

EOD
, true );

//. news
$_type = 'news';
//.. 2020-08-12 New page for Covid-19 info
_d(<<<EOD
title
	2020-08-12
abst
	Covid-19 info
	新型コロナ情報
main_e
	New page: Covid-19 featured information page in EM Navigator.
main_j
	新ページ: EM Navigatorに新型コロナウイルスの特設ページを開設しました。
img
	about_covid19.jpg
url
	covid19.php
id
	newpage_covid19
rel
	about_covid19
	sars-cov-2
tag
	omo emn ym
EOD
);

//.. 2020-03-05 Novel coronavirus structure data
_rep(<<<EOD
taxo
	taxo.php?k=2697049 | Severe acute respiratory syndrome coronavirus 2
ysearch
	ysearch.php?kw=SARS-CoV-2 | SARS-CoV-2
pap_u
	https://www.nature.com/articles/s41564-020-0695-z
pap_t
	The species Severe acute respiratory syndrome-related coronavirus: classifying 2019-nCoV and naming it SARS-CoV-2 - nature microbiology
EOD
);

_d(<<<EOD
title
	2020-03-05
abst
	Novel coronavirus structure data
	新型コロナウイルスの構造データ
main_e
	International Committee on Taxonomy of Viruses (ICTV) defined the short name of the 2019 coronavirus as "SARS-CoV-2".
		< %pap_u% | %pap_t% >
	In the structure databanks used in Yorodumi, some data are registered as the other names, "COVID-19 virus" and "2019-nCoV". Here are the details of the virus and the list of structure data.
		< %taxo% | Severe acute respiratory syndrome coronavirus 2>
		< %ysearch% - Yorodumi Search>
main_j
	国際ウイルス分類委員会(ICTV)は、新型コロナウイルス感染症(COVID-19)の病原ウイルスの略称を「SARS-CoV-2」と決定しました。
		< %pap_u% | %pap_t% >
	万見で扱っている構造データベースでは、"COVID-19 virus"、"2019-nCoV"といった別称・仮称でも登録されています。こちらでウイルスの詳細と構造データの一覧を見られます。 $link_j
		< %taxo% >
		< %ysearch% - 万見検索>
id
	sars-cov-2
rel
	about_taxo
tag
	omo emn ym
link
	u	https://pdbj.org/featured/covid-19
	t	COVID-19 featured content - PDBj
	tj	COVID-19特集ページ - PDBj
	-
	u	https://numon.pdbj.org/mom/242
	t	Molecule of the Month (242)：Coronavirus Proteases
	tj	今月の分子2020年2月：コロナウイルスプロテーアーゼ
EOD
);

//.. 2019-07-04 Download text
_table_prep(<<<EOD
Page
	Data
	Format
<esearch.php| EMN Search>
	search result
	CSV, TSV, or JSON
<stat.php| EMN statistics>
	data table
	CSV or TSV

-----
ページ
	データ
	フォーマット
<esearch.php| EM Navigator 検索>
	検索結果
	CSV, TSV, JSON
<stat.php| EM Navigator 統計情報>
	表データ
	CSV, TSV
EOD
);

_d(<<<EOD
title
	2019-07-05
abst
	Downlodablable text data
	テキストデータのダウンロード
main_e
	Some data of EM Navigator services can be downloaded as text file. Software such as Excel can load the data files. %table-1%
main_j
	EM Navigatorの以下のサービスのデータが、テキスト形式でダウンロードできるようになりました。ダウンロードしたデータをExcelなどで読み込むことができます。%table-2%
id
	text-download
img
	stat_plot.png
rel
	about_esearch about_stat
tag
	emn
EOD
);


//.. 2019-01-31 EMDB-ID
_d(<<<EOD
title
	2019-01-31
abst
	EMDB accession codes are about to change! (news from PDBe EMDB page)
	EMDBのIDの桁数の変更
main_e
	The allocation of 4 digits for EMDB accession codes will soon come to an end. Whilst these codes will remain in use, new EMDB accession codes will include an additional digit and will expand incrementally as the available range of codes is exhausted. The current 4-digit format prefixed with “EMD-” (i.e. EMD-XXXX) will advance to a 5-digit format (i.e. EMD-XXXXX), and so on. It is currently estimated that the 4-digit codes will be depleted around Spring 2019, at which point the 5-digit format will come into force.
	The EM Navigator/Yorodumi systems omit the EMD- prefix.
main_j
	EMDBエントリに付与されているアクセスコード(EMDB-ID)は4桁の数字(例、EMD-1234)でしたが、間もなく枯渇します。これまでの4桁のID番号は4桁のまま変更されませんが、4桁の数字を使い切った後に発行されるIDは5桁以上の数字(例、EMD-12345)になります。5桁のIDは2019年の春頃から発行される見通しです。
	EM Navigator/万見では、接頭語「EMD-」は省略されています。
id
	emdb-id
rel
	what_emd id_notation
tag
	emn ym
link
	u	https://www.emdataresource.org/news/emdb_id_expansion_soon.html
	t	EMDB Accession Codes are Changing Soon!
	-
	%link_pdbj_contact%
EOD
);


//.. 2018-02-20 PDBj workshop
_d(<<<EOD
title
	2018-02-20
abst
	PDBj/BINDS workshop in Osaka University
	2018年2月20日のPDBj & BINDS 合同講習会の資料
id
	pdbj_workshop2018-02
link
	uj	https://pdbj.org/workshop/20180220/suzuki_lecture_20180220.pdf
	tj	講義資料 (PDF)
	-
	uj	https://pdbj.org/workshop/20180220/suzuki_exercise_20180220.pdf
	tj	演習資料 (PDF)
	-
	uj	https://https://pdbj.org/news/20180105
	tj	講習会のページ
tag
	emn
EOD
);

//.. 2017-10-04 nobel prize
_d(<<<EOD
title
	2017-10-04
abst
	Three pioneers of this field were awarded Nobel Prize in Chemistry 2017
	この分野の3人の先駆者が、ノーベル化学賞を受賞しました
main_e
	Jacques Dubochet (University of Lausanne, Switzerland) is a pioneer of ice-embedding method of EM specimen (as known as cryo-EM), Most of 3DEM structures in EMDB and PDB are obtained using his method.
	Joachim Frank (Columbia University, New York, USA) is a pioneer of single particle reconstruction, which is the most used reconstruction method for 3DEM structures in EMDB and EM entries in PDB. And also, he is a develper of Spider, which is one of the most famous software in this field, and is used for some EM Navigor data (<i>e.g.</i> map projection/slice images).
	Richard Henderson (MRC Laboratory of Molecular Biology, Cambridge, UK) was determined the first biomolecule structure by EM. The first EM entry in PDB, PDB-1brd is determinedby him.

main_j
	Jacques Dubochet (University of Lausanne, Switzerland)は、電子顕微鏡試料の氷包埋法（いわゆるクライオ電顕法）を開発しました。EMDBやPDBにある電子顕微鏡のエントリの大多数がこの方法を利用しています。
	Joachim Frank (Columbia University, New York, USA)は、単粒子解析法を開拓しました。EMDBとPDBの電子顕微鏡エントリの大多数がこの解析法によるものです。また、広く利用されている画像解析ソフトェア「Spider」の開発者でもあります。EM Navigatorでもマップデータの画像作成などにSpiderを利用しています。
	Richard Henderson (MRC Laboratory of Molecular Biology, Cambridge, UK)は電子顕微鏡による生体分子の立体構造解析の始祖です。電子顕微鏡による最初のPDBエントリであるPDB-1brdは、彼によるものです。
id
	nobelprize
tag
	emn
link
	u	https://www.nobelprize.org/nobel_prizes/chemistry/laureates/2017/press.html
	t	The 2017 Nobel Prize in Chemistry - Press Release
EOD
);


//.. 2017-07-12 PDB大規模アップデート
_d(<<<EOD
title
	2017-07-12
abst
	Major update of PDB
	PDB大規模アップデート
main_e
	wwPDB released updated PDB data conforming to the new PDBx/mmCIF dictionary. 
	This is a major update changing the version number from 4 to 5, and with <i>Remediation</i>, in which all the entries are updated.
	In this update, many items about electron microscopy experimental information are reorganized (e.g. em_software).
	Now, EM Navigator and Yorodumi are based on the updated data.
main_j
	新バージョンのPDBx/mmCIF辞書形式に基づくデータがリリースされました。
	今回の更新はバージョン番号が4から5になる大規模なもので、全エントリデータの書き換えが行われる「Remediation」というアップデートに該当します。
	このバージョンアップで、電子顕微鏡の実験手法に関する多くの項目の書式が改定されました(例：em_softwareなど)。
	EM NavigatorとYorodumiでも、この改定に基づいた表示内容になります。
id
	pdb_v5
tag
	omo emn ym
link
	u	https://www.wwpdb.org/documentation/remediation
	t	wwPDB Remediation
	-
	u	https://www.wwpdb.org/news/news?year=2017#5963997661fd3d50915a4af7
	t	Enriched Model Files Conforming to OneDep Data Standards Now Available in the PDB FTP Archive
	uj	https://pdbj.org/news/20170712
	tj	OneDepデータ基準に準拠した、より強化された内容のモデル構造ファイルが、PDBアーカイブで公開されました。
EOD
);

//.. 2017-06-16 omokage filter
_d(<<<EOD
title
	2017-06-16
abst
	Omokage search with filter
	Omokage検索で絞り込み
main_e
	Result of Omokage search can be filtered by keywords and the database types
main_j
	Omokage検索の結果をキーワードとデータベースの種類で絞り込むことができるようになりました。
id
	omo_filter
rel
	about_omosearch
tag
	omo emn ym
EOD
);

//.. 2016-09-15 new em navigator & yorodumi
_d( <<<EOD
title
	2016-09-15
abst
	EM Navigator & Yorodumi renewed
	新しくなったEM Navigatorと万見
main_e
	New versions of EM Navigator and Yorodumi started
main_j
	EM Navigatorと万見を刷新しました
id
	emn_repl2
rel
	new_emn_changes
tag
	emn ym
EOD
);

//.. 2016-08-31 new em navigator & yorodumi
_d( <<<EOD
title
	2016-08-31
abst
	New EM Navigator & Yorodumi
	新しいEM Navigatorと万見
main_e
	In 15th Sep 2016, the development versions of EM Navigator and Yorodumi will replace the official versions.
	Current version will continue as 'legacy version' for some time.
main_j
	これまで開発版として公開していたEM Navigatorと万見が、9月15日から正式版となります。
	現行版も「旧版」としてしばらく公開を継続します。
id
	emn_repl
rel
	new_emn_changes about_emn about_ym
tag
	emn ym
EOD
);

//.. 2016-04-13 omokage got faster
_d(<<<EOD
title
	2016-04-13
abst
	Omokage search got faster
	Omokage検索が速くなりました
main_e
	The computation time became ~1/2 compared to the previous version by re-optimization of data accession.
	Enjoy "shape similarity" of biomolecules, more!
main_j
	データアクセスプロセスを見直し、計算時間をこれまでの半分程度に短縮しました。
	これまで以上に、生体分子の「カタチの類似性」をお楽しみください!
rel
	about_omosearch
tag
	omo emn ym
EOD
);

//.. 2016-03-03 IPR seminar
_d(<<<EOD
title
	2016-03-03
abst
	Presentation PDF file for IPR seminar on Feb 19.
	2月19日の蛋白研セミナーのプレゼンテーション(PDFファイル)
link
	u	http://www.protein.osaka-u.ac.jp/en/seminar-en/iprseminar_20160219/
	t	IPR seminar Feb 19th, 2016
	tj	蛋白研セミナー・2016年2月19日
	-
	u	doc/2016-02-IPR-seminar.pdf
	t	Presentation PDF
	tj	プレゼンテーション(PDF)
tag
	omo emn ym
EOD
);


//.. 2015-12-04 omokage paper
_d( <<<EOD
title
	2015-12-04
abst
	The article about Omokage search is published online
	Omokage検索の論文がオンライン出版されました
main
	Omokage search: shape similarity search service for biomolecular structures in both the PDB and EMDB. Suzuki Hirofumi, Kawabata Takeshi, and Nakamura Haruki, <i>Bioinformatics.</i> (2015) btv614
link
	u	http://bioinformatics.oxfordjournals.org/content/early/2015/11/09/bioinformatics.btv614
	t	Main text (HTML, Open Access)
	tj	本文 (HTML、オープンアクセス)
	-
	u	http://bioinformatics.oxfordjournals.org/content/suppl/2015/10/24/btv614.DC1/supplementray-pub-suzuki.pdf
	t	Supplementray data (PDF, Open Access)
	tj	Supplementray data (PDF, オープンアクセス)
tag
	omo emn
rel
	about_omosearch gmfit
EOD
);

//.. 2015-11-28 sasbdb
_d(<<<EOD
title
	2015-11-28
abst
	Omokage search starts to support SASBDB models
	SASBDBの登録モデルもOmokage検索で探せるようになりました
main_e
	Models data in SASBDB are included in the Omokage search database.
		SASBDB is a databank for small angle scattering data.
	A SASBDB model can be used as a search query.
	The search result may include SASBDB models.
main_j
	Omokage検索のデータベースにSASBDBの登録モデルを追加しました。
		SASBDBは、小角散乱の実験データを扱うデータバンクです。
	SASBDBモデルを検索クエリとして利用できます。
	検索結果の中にSASBDBモデルが含まれるようになります。
tag
	omo emn
rel
	about_omosearch
	sasbdb
	3databanks
EOD
);

//.. 2014-12-10 large
_d(<<<EOD
title
	2014-12-10
abst
	Multiple "PDB "SPLIT" entries are replaced with single "LARGE" entries
	PDBの分割登録エントリが、単独のエントリに置き換えられました
main_e
	Structure data deposited as multiple PDB entries are replace with single combined entries,  which were previously stored as "large structure". 
	In EM Navigator, many ribosome and several virus entries are replaced.
main_j
	原子数や鎖数の都合で単一の構造を複数に分割し登録されていたデータは廃止になり、それらをひとつに統合したエントリ（これまでは「巨大構造」として別途公開されていた）が公式のデータになります。
	EM Navigatorでは、多数のリボソームと数件のウイルスのデータが置き換わりました。
tag
	pdb emn
link
	u	https://www.wwpdb.org/news/news?year=2014#10-December-2014
	t	Integration of Large Structures with the Main PDB Archive
	uj	https://pdbj.org/news/20141210
	tj	巨大構造がメインのPDB FTPアーカイブ内に統合されました
EOD
);

//.. 2014-09-22 omokage search
_d(<<<EOD
title
	2014-09-22
abst
	New service: Omokage search
	新規サービス: Omokage検索
main_e
	Shape similarity search service, <i>Omokage search</i> has started.
	形状類似検索「Omokage検索」を開始しました。
tag
	omo emn
rel
	about_omosearch
EOD
);

//.. 2013-03-30 stat page
_d(<<<EOD
title
	2013-03-30
abst
	New page of EM Navigator, EMN Statistics
	EM Navigatorの新ページ「統計情報ページ」の公開を開始しました
id
	new_stat_page
main_e
	<stat.php| EMN Statistics>
main_j
	<stat.php| EMN統計情報>
tag
	emn old
rel
	about_stat
EOD
);

//.. xml 1.9
_d(<<<EOD
title
	2013-01-16
abst
	New EMDB header format
	新しいEMDBヘッダ形式への対応
main_e
	Format of the EMDB header file (XML based meta data file, not the map data themselves) are updated to version 1.9. 
	Now, the contents of EM Navigator are based on the new data. Colors of some parts (e.g. bars in top area of the Detail pages) indicate 3D-reconstruction method, instead of "aggregation states".
main_j
	EMDBエントリのヘッダファイル（XML形式の付随情報データ、マップデータそのものではない）の形式が、バージョン1.9に更新されました。
	EM Navigatorのコンテンツもそれに対応したものに変更しました。詳細ページの上部のバーなど、これまで"aggregation state"（集合状態）による色分けをされていた部分が、"method"（3次元再構成の手法）による色分けとなります。
tag
	emdb emn old
link
	u	https://www.emdataresource.org/news/emdb_hdr_update.html
	t	Announcement: EMDB header format update
EOD
);


//. faq
$_type = 'faq';

//.. 全部
//... 色付け
_d( <<<EOD
title
	How do you make the images for the structure data? What do their colors mean?
	構造データの画像はどうやって作っているのですか？色はどういう意味があるのですか？
abst
	Images of EMDB entries are made for EM Navigator, and ones of PDB entries are for Yorodumi. There are several patterns of coloring.
	EMDBの画像はEM Navigatorの画像、PDBデータの画像は万見の画像です。色付けは複数のパターンがあります。
main_e
	See following items for the details.
main_j
	詳細はについては、それぞれの項目をご覧ください。
id
	faq_image
tag
	ym omo emn
rel
	how_to_make_movie how_to_make_emdbimg how_to_make_pdbimg
EOD
);

//... アップデート
_d( <<<EOD
title
	When the data are updated?
	データの更新はいつですか？
abst
	EMDB and PDB entries are released/updated every Wednesday at  0:00GMT/9:00JST
	EMDBとPDBのデータは、毎週水曜の日本時間午前9:00に更新・公開されます
main_e
	Data in EM Navigator, Yorodumi, and Omokage are updated at the same time.
	SASBDB seems to update irregularly.
main_j
	EM Navigatorと万見、Omokage検索のデータも同時に更新されます。
	SASBDBは不定期に更新されているようです。
id
	when_update
tag
	emn databank
link
	u	https://pdbj.org/help/faq_data01
	t	The release schedule of PDB data, the available data at each wwPDB site
	tj	PDBデータ公開のタイミング、wwPDB各拠点での公開データについて
EOD
);


//.. em
//... 3DEM and cryoEM
_d(<<<EOD
title
	Is 3DEM same as electron cryo microscopy (cryoEM)?
	3DEMとは、低温電子顕微鏡法（クライオ電顕、cryoEM）のことですか？
abst
	No, "3DEM" and "cryoEM" are distinct to be exact.
	「3DEM」と「クライオ電顕」は厳密には別の用語です。
main_e
	However, they are closely related. Some people call 3DEM "cryoEM".
	Many but not all the 3DEM analyses are perfomed with cryoEM. In EMDB and PDB, there are some entries, whose structure data were obtained in non-cryo contition.
main_j
	しかしながら、「3DEM」と「クライオ電顕」は深い関係にあり、しばしば同じ意味の用語として使用されます。
	多くの3DEMデータはクライオ電顕によるものですが、すべてがそうではありません。EMDBやPDBにも低温環境下ではない実験で得られた構造データがいくつか登録されています
id
	faq_3dem_cryoem
tag
	emn
rel
	3dem cryoem
EOD
);

//... FTP site
_rep( <<<EOD
ftp_j
	< https://ftp.pdbj.org/pub/emdb |>
	< ftp://ftp.pdbj.org/pub/emdb |>
ftp_e
	< https://ftp.ebi.ac.uk/pub/databases/emdb |>
	< ftp://ftp.ebi.ac.uk/pub/databases/emdb |>
ftp_w
	< https://ftp.wwpdb.org/pub/emdb |>
	< ftp://ftp.wwpdb.org/pub/emdb |>
EOD
);

_table_prep(<<<EOD
Country
	Organization
	HTTP URL
	FTP URL
Japan
	PDBj
	%ftp_j%
UK
	EBI PDBe
	%ftp_e%
USA
	wwPDB & RCSB PDB
	%ftp_w%
-----
国
	組織
	HTTP URL
	FTP URL
日本
	PDBj
	%ftp_j%
イギリス
	EBI
	%ftp_e%
アメリカ
	wwPDB & RCSB PDB
	%ftp_w%
EOD
);

_d(<<<EOD
title
	Where is the official data of EMDB?
	EMDBの公式データはどこにありますか？
abst
	Following three are the official, have the same contents, and update at same time
	以下3つです。すべて同じ内容で、更新時刻も同じです
main_e
	%table-1%
main_j
	%table-2%
id
	ftp_url
tag
	emn emmap
rel
	when_update
link
	u	https://pdbj.org/help/data_download
	t	About the contents of the PDBj data site - PDBj
	tj	PDBj FTPサイトの詳細 - 日本蛋白質構造データバンク
EOD
);

//... ED map と同じ？
_d(<<<EOD
title
	Is an EM map electron-density map?
	マップデータとは電子密度マップのことですか？
abst
	No. But, they are very similar.
	よく似ていますが、違います。
main_e
	To be exact, 3D map derived by 3D-EM is related to <b>Coulomb potential (or electron potential)</b>, rather than electron density.
	The difference seems to be ignored in the most studies of atomic model building and fitting.
	Thers are some reports that protonation state affects the density. (see the ext. link)
main_j
	厳密には、3D-EMで得られるマップは、電子密度ではなく<b>クーロンポテンシャル(ポテンシャル密度)</b>に関係しています。
	現在、原子モデルの当てはめや原子モデルの構築の過程では、両者の違いはあまり考慮されていないようです
	プロトン化の有無によって密度に差が出るという報告もあります。
id
	same_edmap
tag
	emn emmap
link
	u	http://www.nature.com/nature/journal/v389/n6647/full/389206a0.html
	t	Kimura et al. Surface of bacteriorhodopsin revealed by high-resolution electron crystallography. Nature 389, 206, 1997 doi:10.1038/38323
EOD
);

//... フォーマットは？
_d(<<<EOD
title
	What is the format for the maps?
	マップデータのフォーマットは何ですか？
abst
	It is CCP4 map format
	CCP4マップ型式です
main_e
	It's binary data with header for map geometry and body for density values.
main_j
	バイナリ形式のデータで、マップのサイズなどを記述するためのヘッダと、密度値を記述するための本体からなります。
id
	map_format
tag
	emn emmap
rel
	same_edmap
link
	u	https://ftp.pdbj.org/pub/emdb/doc/Map-format/current/EMDB_map_format.pdf
	t	EMDB Map Distribution Format Description (PDF document)
	tj	EMDB Map Distribution Format Description (PDF文書)
	-
	u	https://www.ccp4.ac.uk/html/maplib.html
	t	CCP4 map format
EOD
);

//... どのソフト
_rep(<<<EOD
soft_link
	<http://en.wikibooks.org/wiki/Software_Tools_For_Molecular_Microscopy/Visualization_and_modeling_tools | Software Tools For Molecular Microscopy/Visualization and modeling tools - Wikibooks>

EOD
);

_d(<<<EOD
title
	Which software is suitable to view EMDB maps?
	どのソフトを使えば、EMDBのマップデータを見られますか？
abst
	Many software packages can display CCP4 map data
	多くのソフトウェアが、マップデータの表示に対応しています
main_e
	Some softwares introduced in this page shoud be suitable.
		%soft_link%
	Movies for EMDB map data in the EM Navigator are made by UCSF-Chimera.
	UCSF-Chimera seems to be the major software in the community.
main_j
	このページで紹介されているソフトウェアが相応しいでしょう。
		%soft_link%
	EM Navigatorでは、EMDBエントリのムービー作成にUCSF-Chimeraを使用しています。
	この分野の研究者には、UCSF-Chimeraのユーザーが多いようです。
id
	soft4map
tag
	emmap
rel
	map_format
link
	%link_chimera%
	%t_chimera%

EOD
);

//.. emdb
//... EMD
_d(<<<EOD
title
	What is EMD?
	「EMD」とは何ですか？
abst
	EMD (or emd) is "prefix" for ID number of EMDB
	EMD (またはemd)は、EMDBのID番号の「接頭語」です
main_e
	At the beginning, the EMDB was also called EMD.
	Since the EM Navigator uses PDB and EMDB data, ID codes are indicated as [database name]-[ID], without the prefix. e.g. EMDB-1001, PDB-1brd.
main_j
	設立当初は、EMDBはEMDとも呼ばれていました。
	EM NavigaotrではPDBとEMDBのデータを利用しているため、IDの表記は、[データベースの名称-ID]とし、接頭語は省略しています。例: EMDB-1001, PDB-1brd
id
	what_emd
rel
	emdb about_emn
tag
	emn
EOD
);

//.. em navi
//... EM Navigatorで使っているデータは
_d( <<<EOD
title
	What are the data sources of EM Navigator?
	EM Navigatorの情報源は？
abst
	EMDB and PDB electron microscopy dat
	EMDBとPDBの電子顕微鏡データです
main_e
	Main source:
		all EMDB entries
		PDB entries with method information (exptl.details) including "electron microscopy" or "electron diffraction"
	Some contents and infromation are original of EM Navigator:
		Movies and their snapshot images
		Projection and slice images
		Some information about related data and similar structures
main_j
	主要な情報源
		EMDB全エントリ
		PDBエントリのうち、手法(exptl.details)として"electron microscopy"か、"electron diffraction"が記述されているエントリ
	以下の情報やコンテンツはEM Navigator独自のものです:
		ムービーやそのスナップショット
		投影像と断面図
		関連データの一部と、類似構造の情報
id
	emn_source
tag
	emn
rel
	about_emn emdb pdb
EOD
);

//... ムービーの利用
_d( <<<EOD
title
	Can I use the movies and their snapshots in the EM Navigator for papers or presentations?
	EM Navigator上のムービーやムービーのスナップショットを、プレゼンテーションや論文などに利用できますか？
abst
	Yes, plese
	どうぞ、ご利用ください
main_e
	The EM Navigator movies and their snapshots are open to public. It would be appreciated if you would cite it as "EM Navigator, PDBj"
main_j
	引用元は”EM Navigator, PDBj”としていただければ幸いです。
id
	emn_term_of_use
link
	u	http://pdbj.org/info/terms-conditions
	t	Terms and conditions - PDBj
	tj	PDBjの利用規約とプライバシーポリシー
EOD
);

//... ムービーどうやって作っているか
$chimera_script = _pre( <<<EOD
<pre>
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
</pre>
EOD
);


_d(<<<EOD
title
	How do you make the movies in EM Navigator?
	EM Navigatorのムービーはどうやって作っているのですか？
abst
	Series of images are recorded by UCSF-Chimera or Jmol. Then, they are encoded into movie files by FFmpeg
	UCSF-ChimeraかJmolで連続画像を作成し、FFmpegでムービーにエンコードしています
main_e
	This is an example of Chimera script. $chimera_script
	NR	"pos1" and "pos2" are saved position, which are the start and end of sectioning motion.
	Chimera session files distributed with the movie files may be helpfull
main_j
	Chimeraのスクリプトの例です。$chimera_script
	NR	「pos1」と「pos2」は、断面を表示するモーションの開始点と終了点で保存したpositionです
	ムービーと併せてChimeraのセッションファイルを公開しています。参考になるかもしれません。
id
	how_to_make_movie
tag
	emn
rel
	soft4map
link
	%link_chimera%
	-
	%link_jmol%
	-
	u	https://www.ffmpeg.org/
	t	FFmpeg
EOD
);

//... EMDB エントリの画像はどうやって

_d(<<<EOD
title
	How do you make the images of EMDB entries in Yorodumi, Omokage, etc.?
	万見やOmokageのEMDBの画像は、どうやって作っていますか？
abst
	in semi-automatic process with UCSF Chimera
	Chimeraを利用して半自動的に作成しています
main_e
	Method:
		We make been creating the images and movies for structure entries in EM Navigator using UCSF Chimera in semi-automatic process.
	Orientation:
		The structures are manually rotated to be seen in "good" orientation (usually, figures of the paper is refererd), or in similar orientation with similar structure data.
		For the case of icosahedral symmetry structure data, they are viewed normal to their five-fold axis, while typical view of such the structure data are along the two-fold or three-fold axes. This is to unify the orientation with structure data with pseudo-icosahedral symmetry, such as bacteriophage with tail structures.
	Color:
		In EM Navigator, multiple images are made for a single entry. In Omokage search, image of "colored surface view" are shown.
		The surface are colored by height, distance, or cylindrical radius. See the detail page for the coloring of particular images.
main_j
	方法:
		EM Navigatoの構造エントリの動画・画像は、動画はUSCF Chimeraを利用して半自動的に作成しています。
	方向:
		手作業で、そのデータに相応しい方向（論文が出版・発行されている場合はその図を参考にします）、あるいは同様の構造がある場合は極力それに似た方向になるようにしています。
		正20面体対称の構造体の場合は、一般的な描画では2回対称軸に沿った方向にしますが、EM Navigatorでは5回対称軸を縦に向けています。これは尾構造を持つバクテリオファージなどに見られる擬似的な正20面体対称構造と方向を統一するためです。
	色:
		EM Navigaotrでは、ほとんどのデータに対して、着色したものと単色の画像を用意していますが、Omokage検索では着色したものが表示されます。
		色は中心からの距離か、円筒半径か、あるいはある軸に沿った「高さ」によって決まります。個々の動画の着色法ついては、データの詳細ページに表示されています。
id
	how_to_make_emdbimg
link
	%link_chimera%
rel
	how_to_make_pdbimg
	how_to_make_movie
tag
	ym omo emn
EOD
);

//... PDBエントリの画像はどうやって
_d(<<<EOD
title
	How do you make the images of PDB entries in Yorodumi, Omokage, etc.?
	万見やOmokageのPDBの画像は、どうやって作っていますか？
abst
	We are making them by full-automatic process using Jmol. Their styles are depend on the data type
	Jmolを利用して全自動で作成しています。スタイルはデータの種類によります。
main_e
	Orientation:
		Icosahedral assembly: Original orientation.
		Helical assembly: 6 orientation (+/- of X, Y, Z direction) images are generated in jpeg format. Then, the largest file is chosen. (large JPEG => many dots/colors)
		Others: by "rotate best" command of Jmol.
		Ribosomes: by "rotate best" command of Jmol. Only RNA coordinates are used for the orientation.
	Color:
		monomer AUs: by N->C (blue->red) rainbow
		BUs of monomer AUs: by "color molecule" (Jmol's coloring)
		multimer AUs and thier BUs (including monomer BUs): by "color chain". For this coloring, Jmol uses not a rainbow color but the colors determined by the chain-ID.
	Problems:
		BU models are generated by Jmol's system. It works well for the most data, but there are some exception.
		Checking and fixing processes of the many (>200,000) images are in progress. There are still some wrong images.
main_j
	方向:
		正20面体対称構造: そのままの方向
		らせん対称構造: まず6方向(+/- X,Y,Z 方向)からの画像をJPEG形式で作成し、ファイルのサイズが一番大きかったものを採用しています。(同じサイズの画像のJPEG形式のファイルのサイズは、圧縮の難しさ、つまり画像の複雑さによるからです)
		その他: Jmolの"roate best"コマンドを利用
		リボソーム: Jmolの"roate best"コマンドを利用(ただしRNAの座標のみ考慮)
	色:
		モノマーのAU: 配列順による虹色 (N->C, 青->赤)
		モノマーのAUのBU: Jmolの"color molecule"
		マルチマーのAUと、そのBU (BUがモノマーの場合も含む): "color chain" (Jmolの "color chain"は虹色着色ではなく、鎖IDごとに定められた色による着色です)
		(ここでいうモノマーとは、DNAかRNAかポリペプチドの鎖が一つしかない構造です)
	問題点:
		集合体のモデル作成はJmolのシステムを利用しています。ほとんどのデータについてはうまく動作していますが、一部うまく作成できていないものがあります。
		現在、画像のチェックと再作成を進めています。正しく描画されていない画像が残っています。
id
	how_to_make_pdbimg
link
	%link_jmol%
tag
	ym omo emn
EOD
);

//... 誰？
//$dot = _span( '.red', '.' );
//$mail = 'hirofumi ' . _img( 'img/am.jpg' ) . " protein{$dot}osaka-u{$dot}ac{$dot}jp";

_d(<<<EOD
title
	Who make these documents? Who develop EM Navigator, Yorodumi, Omokage search, etc?
	この文書を書いているのは誰ですか？EM Navigator、万見、Omokage検索などの開発者は？
abst
	Hirofumi Suzuki (PDBj / IPR, Osaka University)
	大阪大学蛋白質研究所・PDBjの鈴木博文です。
main_e
//	$mail
	Institute for Protein Research, Osaka University, 3-2 Yamadaoka, Suita, Osaka, 565-0871, Japan.
main_j
//	$mail
	大阪大学蛋白質研究所 蛋白質データベース開発研究室
id
	developer
rel
	pdbj
link
	%link_pdbj_contact%
	-
	%link_pdbj_lab%
EOD
);

//.. PDB
//... PDB coordinate formats
_table_prep(<<<EOD
Format
	mmCIF compatible
	Format based on
	Provider
	Comment
PDBx/mmCIF
	-
	CIF > STAR > plain text
	wwPDB
	Officail data format
PDB
	no
	plain text
	wwPDB
	Legacy officail
PDBML
	yes
	XML > plain text
	wwPDB
	For programers & develpers
PDBx/mmJSON
	yes
	JSON > plain text
	PDBj
	For programers & develpers
-----
形式
	mmCIF互換情報
	ファイル形式のベース
	提供組織
	コメント
PDBx/mmCIF
	-
	CIF > STAR > プレーンテキスト
	wwPDB
	公式フォーマット
PDB
	no
	プレーンテキスト
	wwPDB
	過去の公式フォーマット
PDBML
	yes
	XML > プレーンテキスト
	wwPDB
	プログラマー・開発者向け
PDBx/mmJSON
	yes
	JSON > プレーンテキスト
	PDBj
	プログラマー・開発者向け
EOD
);

_d(<<<EOD
id
	format_pdb
title
	What is difference in PDB coordinate formats?
	PDBの座標データの形式は、どのような違いがあるのですか？
abst
	As follows
	以下の通りです
main_e
	%table-1%
main_j
	%table-2%
tag
	pdb
rel
	file_mmcif file_pdb file_mmjson

EOD
);


//.. ネタ
/*
コンタクト
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

//.. file
//... emdb map
_d(<<<EOD
id
	file_emdb_map
title
	EMDB map data format
	EMDBマップデータ形式
abst
	3D density map data in CCP4 format
	CCP4形式の3次元密度分布マップデータ
tag
	emn emdb emmap
rel
	emdb

link
	u	https://ftp.pdbj.org/pub/emdb/doc/Map-format/current/EMDB_map_format.pdf
	t	EMDB Map Distribution Format Description
	-
	u	https://www.ccp4.ac.uk/html/maplib.html
	t	Format technical details
	-
	u	https://en.wikipedia.org/wiki/CCP4_(file_format)
	t	CCP4 - Wikipedia
rel
	map_format soft4map same_edmap
EOD
);
//... emdb header
_d(<<<EOD
id
	file_emdb_header
title
	EMDB header
	EMDBヘッダ
abst
	Meta information in XML format
	XML形式のメタ情報(付随情報)データ
tag
	emn emdb
link
	u	https://ftp.pdbj.org/pub/emdb/doc/XML-schemas/emdb-schemas/v3/current_v3/doc/Untitled.html
	t	EMDB Schema v3
	-
	u	https://ftp.pdbj.org/pub/emdb/doc/XML-schemas/emdb-schemas/v1/v1_9/doc_v1_9_6/Untitled.html
	t	EMDB Schema v1.9
	-
	t	Documentation directory
	tj	文書ディレクトリ
	u	https://ftp.pdbj.org/pub/emdb/doc/XML-schemas/emdb-schemas/
rel
	emdb

EOD
);

//... EMDB mask
_d(<<<EOD
id
	file_emdb_masks
title
	Mask map
	マスクマップ
abst
	Map data of mask pattern, sub-region, segmentation, etc
	マスクパターン、部分マップ、セグメンテーションマップなど
tag
	emn emdb
rel
	emdb
EOD
);

//... FSC
_d(<<<EOD
id
	file_emdb_fsc
title
	FSC data file
	FSCデータファイル
abst
	Fourier shell correlation data in XML format for resolution estimation
	XML形式のフーリエシェル相関データ、解像度の算出に利用
tag
	emn emdb
link
	t	Fourier shell correlation - Wikipedia
	u	https://en.wikipedia.org/wiki/Fourier_shell_correlation
	-
	t	FSC-schema
	u	https://ftp.pdbj.org/pub/emdb/doc/XML-schemas/FSC-schema/current/
rel
	emdb
EOD
);

//... EMDB validaton report
_d(<<<EOD
id
	file_emdb_valrep
title
	EMDB validaton report
	EMDB検証レポート
abst
	Validaton report of EMDB entry by wwPDB and EMDB
	wwPDBとEMDBによるEMDBエントリの検証レポート
tag
	emn emdb
link
	u	https://www.wwpdb.org/validation/2017/EMMapValidationReportHelp
	t	User guide to the EmDataBank map validation reports
rel
	emdb

EOD
);

//... mmcif
_d(<<<EOD
id
	file_mmcif
title
	PDBx/mmCIF format
	PDBx/mmCIF形式
abst
	Officail data format for PDB entries, in CIF text format, having atomic coordinates and meta information
	PDBの公式データフォマット。原子座標と付随情報を含む。STAR形式に基づくテキストデータ。
tag
	pdb
link
	t	PDBx/mmCIF Dictionary Resources
	tj	PDBx/mmCIF辞書関連情報
	u	https://mmcif.wwpdb.org/
	uj	https://mmcif.pdbj.org/
	-
	u	https://en.wikipedia.org/wiki/Crystallographic_Information_File
	t	Crystallographic Information File
rel
	pdb

EOD
);

//... PDB format
_d(<<<EOD
id
	file_pdb
title
	PDB format
	PDB形式
abst
	Legacy officail data format for PDB entries, contains atomic coordinates
	以前のPDBの公式データフォマット、原子座標データを含む
tag
	pdb
link
	t	Atomic Coordinate Entry Format Version 3.3
	u	https://www.wwpdb.org/documentation/file-format-content/format33/v3.3.html
rel
	pdb

EOD
);

//... mmcif
_d(<<<EOD
id
	file_mmjson
title
	PDBx/mmJSON format
	PDBx/mmJSON形式
abst
	JSON representation of the PDBx/mmCIF data developed by PDBj
	PDBx/mmCIF"をJSON形式で表現した、PDBjが開発したファイルフォーマット
tag
	pdb
link
	t	mmJSON - PDBj help
	tj	mmJSON - PDBjヘルプ
	u	https://pdbj.org/help/mmjson

rel
	pdb

EOD
);


//... PDB validation report
_d(<<<EOD
id
	file_pdb_valrep
title
	wwPDB validaton report
	wwPDB検証レポート
abst
	Validaton report of PDB entry by wwPDB
	wwPDBによるPDBエントリの検証レポート
tag
	pdb
link
	u	https://www.wwpdb.org/validation/validation-reports
	t	wwPDB: Validation Reports
	-
	tj	検証レポートには、どんな種類がありますか？ - validation FAQ
	uj	https://pdbj.org/help/dafaq_type01
	t	wwPDB: validation report FAQs
	u	https://www.wwpdb.org/validation/2016/FAQs#different_types
rel
	pdb file_emdb_valrep

EOD
);


//.. about
//... emn
_d(<<<EOD
title
	EM Navigator
abst
	3D electron microscopy data browser
	3次元電子顕微鏡データブラウザ
main_e
	EM Navigator is a browser for 3D electron microscopy (3D-EM) data of biological molecules and assemblies.
	It provides EMDB-PDB cross-search, statistical information, and links to similar structures.
	You can easily check the latest EM structural data and structural papers.
main_j
	生体分子や生体組織の3次元電子顕微鏡データを、気軽にわかりやすく眺めるためのウェブサイト 
	EMDB-PDBの横断検索や統計情報のサービス、類似構造へのリンクなどをを提供しています
	最新のEM構造データや構造論文を手早くチェックできます
id
	about_emn
tag
	emn emdb pdb about
rel
	emdb pdb pdbj
url
	.
EOD
);

//... emn search
_d(<<<EOD
title
	EMN Search
	EMN検索
abst
	3DEM data search
	3次元電子顕微鏡データ検索
main_e
	Advanced data search for EMDB and EM data in PDB widh various search and display options
main_j
	豊富な検索条件と表示条件で、EMDBとPDBの電子顕微鏡エントリを検索できます。
id
	about_esearch
tag
	emn emdb pdb about
rel
	emdb pdb about_emn emn_source about_ysearch
url
	esearch.php
EOD
);

//... 3DEM papers
_d(<<<EOD
title
	EMN Papers
	EMN文献
abst
	Database of articles cited by 3DEM data entries
	3DEM構造データから引用されている文献のデータベース
main_e
	Database of articles cited by 3DEM data entries in EMDB and PDB
	Using PubMed data
main_j
	EMDDBとPDBの3次元電子顕微鏡エントリから引用されている文献のデータベースです。
	PubMedのデータを利用しています。
id
	about_empap
url
	pap.php?em=1
tag
	emn about
rel
	emdb pdb emn_source about_emn about_pap
EOD
);

//... gallery
_d(<<<EOD
title
	EMN Gallery
	EMNギャラリー
abst
	Image gallery of 3DEM data
	3DEMデータを画像で一覧
main_e
	Categorization is done by EM Navigator manager manually. It is not strict.
main_j
	分類はEM Navigator独自のものです。厳密な分類ではありません。
id
	about_gallery
url
	gallery.php
tag
	emn about
rel
	about_emn
EOD
);

//... statistics
_rep( <<<EOD
res_vs_met
	stat.php?key=reso_seg&k2=method
tmp_vs_res
	stat.php?key=temp_seg&k2=reso_seg
EOD
);

_d(<<<EOD
title
	EMN Statistics
	EMN統計情報
abst
	Statistics of 3DEM data in table and graph styles
	3DEMデータの統計情報を表やグラフで閲覧
main_e
	The table can be sorted. Click the column header to be sorted. (second click to reverse, [Shift]+click for multi-column sort) 
	To show the bar graph in table mode, point the column/row header by mouse courser.
	To search the correspoinding data, click the cell of value.
	Examples:
		< %res_vs_met% | "Resolution" vs. "Method">
		< %tmp_vs_res% | "Specimen temperature" vs. "Resolution">
main_j
	表はソートできます。列の先頭をクリックすると、その列基準のソートになります。（再度クリックすると逆順、[Shift]+クリックで複数列基準のソート）
	行や列の先頭にマウスカーソルを置くと、棒グラフが現れます。
	数値のセルをクリックすると該当する検索結果のページが開きます。
	例：
		< %res_vs_met% | 「解像度」 vs.「手法」>
		< %tmp_vs_res% | 「試料温度」 vs.「 解像度」>
id
	about_stat
url
	stat.php
tag
	emn about
rel
	emn_source about_emn
EOD
);

//... Yorodumi
_d(<<<EOD
title
	Yorodumi
	万見 (Yorodumi)
abst
	Thousand views of thousand structures
	幾万の構造データを、幾万の視点から
main_e
	Yorodumi is a browser for structure data from EMDB, PDB, SASBDB, etc.
	This page is also the successor to <b>EM Navigator detail page</b>, and also detail information page/front-end page for <i>Omokage search</i>.
	The word "yorodu" (or yorozu) is an old Japanese word meaning "ten thousand". "mi" (miru) is to see.
main_j
	万見(Yorodumi)は、EMDB/PDB/SASBDBなどの構造データを閲覧するためのページです。
	EM Navigatorの詳細ページの後継、Omokage検索のフロントエンドも兼ねています。
id
	about_ym
url
	quick.php
tag
	ym about
rel
	emdb pdb sasbdb 3databanks about_ysearch
EOD
);


//... ym search *
_d(<<<EOD
title
	Yorodumi Search
	万見検索
abst
	Cross-search of EMDB, PDB, SASBDB, etc.
	EMDB/PDB/SASBDBなどの横断検索
id
	about_ysearch
tag
	ym emdb pdb about
rel
	emdb pdb sasbdb 3databanks about_esearch
url
	ysearch.php
EOD
);


//... structure papers
_d(<<<EOD
title
	Yorodumi Papers
	万見文献
abst
	Database of articles cited by EMDB/PDB/SASBDB data
	EMDB/PDB/SASBDBから引用されている文献のデータベース
main_e
	Database of articles cited by EMDB, PDB, and SASBDB entries
	Using PubMed data
main_j
	EMDB/PDB/SASBDBのエントリから引用されている文献のデータベースです
	Pubmedのデータを利用しています
id
	about_pap
url
	pap.php
tag
	ym about
rel
	emdb pdb sasbdb about_ym about_empap
EOD
);

//... taxonomy
_d(<<<EOD
title
	Yorodumi Speices
	万見生物種
abst
	Taxonomy data in EMDB/PDB/SASBDB
	EMDB/PDB/SASBDBの生物種情報
main_e
	Taxonomy database of sample sources of data in EMDB/PDB/SASBDB
main_j
	EMDB/PDB/SASBDBの構造データの試料情報、由来する生物種に関するデータベース
id
	about_taxo
url
	taxo.php
tag
	ym about
rel
	emdb pdb sasbdb 3databanks
EOD
);

//... omokage search
_d(<<<EOD
title
	Omokage search
	Omokage検索
abst
	Search structure by SHAPE
	「カタチ」で構造検索
main_e
	"Omokage search" is a shape similarity search service for 3D structures of macromolecules. By comparing <b>global shapes</b>, and ignoring details, similar-shaped structures are searched.
	The search is performed ageinst >200,000 structure data, which consists of EMDB map data, PDB coordinates (deposited units (asymmetric units, usually), PDB biological units, and SASBDB mdoels).
	For the search query, you can use either a data in the PDB/EMDB/SASBDB or your original model.
	Supported formats are PDB (atomic model, SAXS bead model, etc.) and CCP4/MRC map (3D density map).
	Shape comparison is performed by iDR profile method, which uses 1D-distance profiles of super-simplified models generated by vector quantizaion in <i>Situs</i> package.
main_j
	Omokage検索は、生体超分子の形状類似性検索サービスです。細部を無視した<b>全体の形状のみの比較</b>により、類似データを検索します。
	検索の対象となるデータセットは、EMDBのマップデータ、PDBの登録構造（通常は非対称単位、AU）、PDBの生物学的単位(BU)、SASBDBの登録モデルで、合計約20万の構造データからの検索となります。
	EMDB・PDB・SASBDBの登録データ、あるいは利用者所有のオリジナルの構造を使った検索が可能です。
	対応する形式は、PDB形式(原子モデル、SAXSビーズモデルなど)か、CCP4/MRC形式(3次元密度分布マップ)です。
	比較はiDRプロファイル法によって行います。
id
	about_omosearch
url
	omo-search.php
rel
	3databanks
tag
	omo about
EOD
);

//... doc
_d(<<<EOD
title
	Yorodumi Docs
	万見文書
abst
	Documentation for Yorodumi, EM Navigator, Omokage, etc.
	万見・EM Navigator・Omokageなどのヘルプや情報を検索・閲覧
id
	about_doc
url
	doc.php
tag
	omo ym emn about
EOD
);

//... covid-19
_d( <<<EOD
title
	Covid-19 info
	Covid-19情報
abst
	Page for Covid-19 featured contents
	新型コロナウイルスの情報ページ
id
	about_covid19
url
	covid19.php
rel
	3databanks
tag
	omo ym emn about
EOD
);

//... fh search
_d(<<<EOD
title
	F&H Search
	F&H 検索
abst
	Function & Homology similarity search
	機能・相同性の類似性検索
main_e
	Based on the similarity of Function & Homolog items related to the structure data, similar data are searched from structure databases.
main_j
	構造データに関連付けられた機能・相同性アイテムの類似性に基づき、類似するデータを構造データベースから検索します。
id
	about_fh_search
url
	fh-search.php
rel
	func_homology
tag
	omo ym emn about
img
	about_fh_search.jpg
EOD
);


//.. viewers
define( 'TABLE_MOUSE_TOUCH', <<<EOD
_
	X-Y rotation
	X-Y move
	Zoom in/out
Mouse
	%mouse_e%
Touch
	%touch_e%
-----
_
	X-Y回転
	X-Y移動
	拡大・縮小
マウス操作
	%mouse_j%
タッチ操作
	%touch_j%
EOD
);

//... movie

_d(<<<EOD
title
	Movie viewer
	ムービービューア
abst
	Visualization of 3DEM structure data by movies
	動画による3DEM構造データビューア
main_e	
	Horizontal- and vertical- rotation motions and "slicing" motion are recorded in the movies.
	As the movie frame can be controlled by mouse/touch position, it can be operated interactively as a molecular viewer.
		According to the mouse/finger position and orientation of the motion on the movie panel, the model rotates on horizontal or vertical direction.
		By horizontal mouse/finger movement on the top part of the movie, the slicing motion is shown.
main_j
	ムービーには横回転、縦回転、切断面の移動のモーションが録画されています。
	ムービーのフレームは、マウスやタッチの位置で切り替わるので、分子構造ビューアのようなインタラクティブな操作が可能です。
		ムービー画面上のマウスや指の位置と動く方向に合わせてフレームが切り替わり、モデルが縦方向・横方向に回転します。
		画面上部でマウスや指を横に移動させると、断面のモーションが表示されます。
id
	movie
tag
	viewer emn
rel
	how_to_make_movie emn_term_of_use surfview
EOD
);

//... Jmol
_rep(<<<EOD
mouse_e
	left drag
	[Ctrl] + right drag
	center drag (vertical) / wheel
touch_e
	single finger
	double finger
	pinch
mouse_j
	左ボタンドラッグ
	[Ctrl] + 右ボタンドラッグ
	中ボタンドラッグ(縦方向) ・ ホイール回転
touch_j
	1本指
	2本指
	ピンチ（つまむ動作）
EOD
);
_table_prep( TABLE_MOUSE_TOUCH );

_d(<<<EOD
title
	Jmol/JSmol
abst
	An open-source viewer for chemical structures in 3D
	オープンソースの3次元化学構造ビューア
main_e
	Mouse/touch operation
	NR	%table-1%
	Jmol menu: [Ctrl] + click / right click
main_j
	マウス・タッチ操作	
	NR	%table-2%
	Jmolメニュー: [Ctrl] + クリック / 右クリック
id
	jmol
tag
	viewer emn ym
rel
	molmil about_ym
link
	%link_jmol%
EOD
);

//... Molmil
_rep(<<<EOD
mouse_e
	left drag
	center drag / shift + left drag
	right drag / wheel
touch_e
	single finger
	double finger
	pinch
mouse_j
	左ボタンドラッグ
	中ボタンドラッグ / [Shift] + 左ボタンドラッグ
	右ボタンドラッグ(縦方向) ・ ホイール回転
touch_j
	1本指
	_
	ピンチ（つまむ動作）
EOD
);
_table_prep( TABLE_MOUSE_TOUCH );

_d(<<<EOD
title
	Molmil
abst
	WebGL based molecular viewer
	WebGLを利用した分子構造ビューア
main_e
	Molmil runs on Web browser on a smartphone or tablet device as well as PC. (Molmil is based on WebGL and Javascript).
	Mouse/touch operation
	NR	%table-1%
	Function menu: top left buttons on viewer screen
main_j
	PCだけでなく、スマートフォンやタブレットでも動作します。(WebGLと呼ばれる仕組みを利用しています)
	マウス・タッチ操作
	NR	%table-2%
	機能メニュー: ビューアースクリーンの左上部ボタン
id
	molmil
tag
	viewer ym
link
	u	https://pdbj.org/help/molmil
	t	Documentation of Molmil in PDBj site
	t_j	PDBjサイト内のMolmilの解説
	-
	u	https://github.com/gjbekker/molmil
	t	Molmil page in GitHub
	t_jGitHubのMolmilのページ
EOD
);

//... surfview
_rep(<<<EOD
mouse_e
	left drag
	right drag
	center drag / wheel
touch_e
	single finger
	double finger
	pinch
mouse_j
	左ボタンドラッグ
	右ボタンドラッグ
	中ボタンドラッグ / ホイール回転
touch_j
	1本指
	2本指
	ピンチ（つまむ動作）
EOD
);

_d(<<<EOD
title
	SurfView
abst
	EMDB map surface models viewer on Web browsers
	ブラウザ上で動作するEMDBマップの表面モデルビューア
main_e
	SurfView run on Web browser on a smartphone or tablet device as well as PC.
	SurfView is based on a WebGL and <i>three.js</i>, Javascript 3D library.
	The surface models shwon in this viewer are modified for simplification and data size reduction. To get views of full-resolution data, see movies instead.
	Mouse/touch operation
	NR	%table-1%

main_j
	PCだけでなく、スマートフォンやタブレットでも動作します。
	WebGLとJavaScript3次元ライブラリを利用しています。
	単純化とデータサイズ削減のために、表面モデルが改変されている場合があります。完全な解像度の構造はムービーでご覧ください。
	マウス・タッチ操作
	NR	%table-2%

id
	surfview
img
	surfview.jpg
rel
	movie molmil
EOD
);

//.. DB info
//... 3 databanks

_table_prep(<<<EOD
_
	Method
	Main data
	Str. data format
	meta data format
PDB
	various
	atomic model
	PDBx/mmCIF, etc.
	PDBx/mmCIF, etc.
EMDB
	3DEM
	3D map
	CCP4 map
	EMDB XML
SASBDB
	Small Angle Scattering (SAS)
	SAS profile
	BR	(+/- 3D models)
	PDB + sasCIF
	ASCII + sasCIF
-----
_
	解析手法
	主データ
	構造データのフォーマット
	付随データのフォーマット
PDB
	多種
	原子モデル
	PDBx/mmCIF, etc.
	PDBx/mmCIF, etc.
EMDB
	3DEM
	3次元マップ
	CCP4マップ
	EMDB XML
SASBDB
	SAS (小角散乱)
	SASプロファイル
	BR	+/- 3次元構造
	PDB + sasCIF
	テキスト + sasCIF
EOD
);

_d(<<<EOD
title
	Comparison of 3 databanks
	3つのデータバンクの比較
abst
	Yorodumi and Omokage can cross-search these DBs
	万見とOmokageではこれらのデータベスの横断検索が可能です
main_e
	%table-1%
main_j
	%table-2%
id
	3databanks
tag
	db emdb pdb sasbdb
rel
	emdb pdb sasbdb
EOD
);


//... ID 表記
_table_prep(<<<EOD
Database
	pattern
	<i>e.g.</i>
	note
EMDB
	EMDB-****
	EMDB-1001
	prefix EMD- omitted
PDB
	PDB-****
	PDB-1a00
SASBDB
	_
	SASDA24
	as it is
-----
データベース
	パターン
	例
	備考
EMDB
	EMDB-****
	EMDB-1001
	接頭語EMD-は省略
PDB
	PDB-****
	PDB-1a00
SASBDB
	_
	SASDA24
	IDコードはそのまま
EOD
);

_d(<<<EOD
title
	ID/Accession-code notation in Yorodumi/EM Navigator
	万見/EM NavigatorにおけるID/アクセスコードの表記
main_e
	%table-1%
main_j
	%table-2%
id
	id_notation
rel
	3databanks what_emd
EOD
);

//... EMDB
_d(<<<EOD
title
	EMDB
abst
	Electron Microscopy Data Bank, Databank for 3DEM
	Electron Microscopy Data Bank、 3DEMのためのデータバンク
id
	emdb
link
	u	https://www.ebi.ac.uk/emdb/
	t	The Electron Microscopy Data Bank - EBI
	-
	u	https://www.emdataresource.org/
	t	EMDataResource
wikipe
	EM Data Bank
rel
	3dem pdb
tag
	db
url
	https://www.ebi.ac.uk/emdb/
EOD
);

//... PDB
_d(<<<EOD
title
	PDB
abst
	Protein Data Bank - The single repository of information about the 3D structures of proteins, nucleic acids, and complex assemblies
	蛋白質構造データバンク(PDB) - タンパク質や核酸、それらの複合体の3次元構造のための唯一のデータベース
id
	pdb
url
	https://wwPDB.org/
tag
	db
rel
	pdbj
wikipe
	Protein Data Bank

EOD
);

//... PDBj
_d(<<<EOD
title
	PDBj
abst
	Protein Data Bank Japan
	日本蛋白質構造データバンク (Protein Data Bank Japan)
main_e
	A member of wwPDB
main_j
	wwPDBのメンバー
id
	pdbj
url
	http://pdbj.org/
link
	%link_pdbj_lab%
	-
	u	https://www.facebook.com/PDBjapan
	t	PDBj@Facebook
	-
	u	https://twitter.com/PDBj_en
	uj	https://twitter.com/PDBj_ja
	t	PDBj@Twitter
tag
	db
rel
	pdb
EOD
);

//... SASBDB
_d(<<<EOD
title
	SASBDB
abst
	Small Angle Scattering Biological Data Bank - Curated repository for small angle scattering data and models
	生体試料小角散乱データバンク - 小角散乱のデータと立体構造モデルのデータバンク
main_e
	Develop & manage: BioSAXS group in EMBL
	Since 2014
main_j
	EMBLのBioSAXSにより運営
	2014年に設立
id
	sasbdb
link
	u	http://nar.oxfordjournals.org/content/early/2014/10/28/nar.gku1047.long
	t	Valentini E, Kikhney AG, Previtali G, Jeffries CM, Svergun DI. SASBDB, a repository for biological small-angle scattering data. Nucleic Acids Res. 2015 Jan 28;43:D357-63.
tag
	db
url
	http://www.sasbdb.org/

EOD
);

//... gmfit
_d(<<<EOD
title
	gmfit
abst
	A program for fitting subunits into density map of complex using GMM (Gaussian Mixture Model) 
	ガウス混合モデル(Gaussian Mixture Model, GMM)を使った3DEMマップのフィッティングプログラム
main_e
	Developed by Takeshi Kawabata in IPR, Osaka-univ.
	Used from Omokage search.
main_j
	阪大・蛋白研の川端猛 博士により開発
	Omokage検索から利用
id
	gmfit
url
	https://pdbj.org/gmfit/
link
	u	https://pdbj.org/gmfit/pairgmfit.html
	t	Pairwise gmfit
rel
	about_omosearch
tag
	emdb db omo
EOD
);


//... wwPDB ネタ

//.. aboutn EM
//... 3dem
_table_prep( <<<EOD
Aggregation states
	Methods generally used
"individual structure"
	electron tomography
"single particle"
	single particle analysis
"icosahedral"
	single particle analysis
"helical"
	helical reconstruction / single particle analysis
"2D/3D-crystal"
	electron diffraction / Fourier filtering

-----
試料の「集合状態」
	主に使われる手法
単独の構造 (individual structure)
	電子線トモグラフィー (electron tomography)
単粒子 (single particle)
	単粒子解析 (single particle analysis)
正20面体対称 (icosahedral)
	単粒子解析
らせん対称 (helical)
	らせん対称再構成 (helical reconstruction) / 単粒子解析
2次元結晶 (2D-crystal)
	電子線結晶学 (electron diffraction) / フーリエフィルタ (Fourier filtering)

-----
_
	_
Advantage
	Wider applicability of sample (not require high-purity or high-concentration smaple, and useful for more huge, complex, flexible, and less uniform sample)
Disadvantage (previous)
	Lower resolution (hard to get atomic-level resolution data)
Disadvantage (new)
	High-performance electron microscopes and their maintenance costs are quite expensive

-----
_
	_
長所
	試料調製のハードルが低い - 低純度・希少な試料、巨大・柔軟・脆弱・不均一な対象にも利用可能
短所(以前)
	解像度が低い - 原子レベルの解像度の解析は難しい
短所(近年)
	高性能な電子顕微鏡の設置と維持は高額を要し、普及の途上である

EOD
);

_d(<<<EOD
title
	3D electron microscopy (3DEM)
	3次元電子顕微鏡(3DEM)
abst
	A generic term of electron microscopic analyses to obtain 3D structures
	3次元構造を得るための電子顕微鏡解析の総称
main_e
	Analyses such as electron tomography, single particle analysis, and electron diffraction are included. %table-1%
	Characteristics compared to X-ray crystallography and NMR: %table-3%
main_j
	たとえば、単粒子解析、電子線トモグラフィー、電子線結晶学など %table-2%
	NMRやX線結晶学と比較して、一般的には次のような特徴がある %table-4%
id
	3dem
tag
	emn emdb
EOD
);

//... cryo EM
_d(<<<EOD
title
	electron cryo microscopy (cryoEM)
	低温電子顕微鏡（クライオ電顕、cryoEM）
abst
	Electron microscopy where the sample is studied at cryogenic temperatures
	クライオ（低温）条件下の試料が観察可能な電子顕微鏡法、あるはその装置
main_e
	The specimen is cooled at 4～100K (-269～-170℃) to keep it in hydrated state in the highly vaqumed environment, and to reduce radiation dammage by electron beam.
main_j
	試料は、4～100K (-269～-170℃)に冷却される。これにより高真空中でも水和状態を保ち、電子線照射によるダメージも軽減される。
id
	cryoem
tag
	emn
wikipe
	Cryogenic electron microscopy
	Transmission electron cryomicroscopy
rel
	3dem
EOD
);

//.. old movie
_d(<<<EOD
title
	"Movies out of date"
	「古いムービー」
abst
	Movies with this annotation may not be up-to-date.
	この注釈の付いたムービーは、最新のマップデータに対応していない可能性があります。
main_e
	EMDB map data are sometimes remediated. The process of making movies in the EM Navigaotr are not fully automated, and it is very hard to remake the all to catch up. So, some movies and movie parameters are based on the older data, and may be improper for new map data.
main_j
	EMDBのマップデータは、修正されることがあります。EM Navigatorのムービー作成作業は完全自動ではなく、全ての修正への追従はできていません。したがっていくつかのデータエントリについては、ムービーやそれに関するパラメータは古いマップデータを元にしており、新しいマップデータには対応していない場合があります。
id
	oldmov
tag
	emn
rel
	how_to_make_movie
EOD
);


//.. misc
//... polysac
_d(<<<EOD
title
	Carbohydrate representation
	糖鎖の表現
abst
	Polysaccaride/carbohydrate data representation in PDB
	PDBにおける糖鎖・炭水化物の情報の表現について
main_e
	In July 2020, representation for polysaccaride/carbohydrate information in PDB is improved.
	The polysaccaride figures are generated by GlycanBuilder2.
	See following external links for details.
main_j
	2020年7月にPDBでの糖鎖データの表現について、大規模な更新が実施されました。
	糖鎖の画像はGlycanBuilder2で作成されています。
	その他、詳細は下記のリンクを参照してください。
id
	polysac
tag
	ym
link
	u	https://www.sciencedirect.com/science/article/pii/S0008621516305316
	t	Implementation of GlycanBuilder to draw a wide variety of ambiguous glycans - ScienceDirect
	-
	u	http://www.rings.t.soka.ac.jp/downloads.html
	t	GlycanBuilder2 - RINGS (Resource For INformatics Of Glycomes at Soka)
	-
	u	https://www.wwpdb.org/documentation/carbohydrate-remediation
	t	Carbohydrate Remediation - wwPDB documentation
	-
	u	https://www.wwpdb.org/news/news?year=2020#5f0495919902836395e11ce8
	t	Coming July 29: Improved Carbohydrate Data at the PDB - wwPDB news
	uj	https://pdbj.org/news/20200708
	tj	7月29日に糖鎖分子の表現を改善したPDBデータを公開します - PDBjお知らせ
EOD
);

//... met
_d(<<<EOD
title
	Experimental methods, equipments, and software data
	実験手法・装置(施設・設備・機器)・ソフトウェアのデータ
abst
	Database of experimental methods of EMDB, PDB and SABDB.
	EMDB・PDB・SABDBから収集した実験情報のデータベース
id
	met_data
url
	ysearch.php?&act_tab=met
tag
	ym
rel
	3databanks about_ysearch
EOD
);


//... function & homology
_rep( <<<EOD
item_func
	Gene ontology, Enzyme Commission number, Reactome, etc.
item_domain
	CATH, InterPro, SMART, etc.
item_compo
	UniProt, GenBank, PDB chemical component, etc.
EOD
);
_table_prep(<<<EOD
Category
	Name of database or definition
Function%
	%item_func%
Domain/homology
	%item_domain%
Component
	%item_compo%
-----
カテゴリ
	データベース名・定義名
分子機能
	%item_func%
ドメイン・相同性
	%item_domain%
構成要素
	%item_compo%
EOD
);

_d( <<<EOD
title
	Function and homology information
	機能・相同性情報
abst
	Molecular function, domain, and homology information from related databases.
	関連データベースから収集した分子機能・ドメイン・相同性などの情報
main_e
	To help to understand the molecular function and to find the related structure data, Yorodumi and Yorodumi Search display and utilize the related database information about function and homology.
	In addition to the information of EC, EMBL, GeneBank, GO, InterPro, UniProt, etc. stored in the EMDB header XML, PDBx/mmCIF and sasCIF original data, information of Pfam, PROSITE, Reactome, UniProkKB, etc. are collected via PDBMLplus, EMDB-PDB fitting data, and/or UniProt.
	NR	%table-1%
main_j
	万見と万見検索では、構造データの機能の理解や類似構造データの検索に役立つように、分子機能や相同性に関するデータベースの情報を表示・活用しています。
	EMDBのヘッダXML、PDBx/mmcif、sasCIFの公式のデータに含まれるEC、EMBL、GeneBank、GO、InterPro、UniProtなどの情報に加えて、PDBMLplus経由、EMDB-PDBフィッティングデータ経由、UniProt経由で収集された、Pfam、PROSITE、Reactome、UniProtKBなどの情報が整理されています。
	NR	%table-2%
id
	func_homology
tag
	ym
rel
	about_fh_search mlplus about_ysearch about_ym
EOD
);

//... gmfit re-rank
$plus2 = _span( '.bld blue', '++' );
$plus  = _span( '.bld blue', '+' );
$minus = _span( '.bld red', '-' );

_table_prep(<<<EOD
Name
	Method
	Speed
	Potential accuracy
Omokage search
	iDR profile comparison
	$plus2 <br> sub-msec / 1 comparison
	$minus <br>	1D profile comparison
gmfit
	Gaussian mixture model fitting
	$plus <br> sub-sec / 1 comparison
	$plus <br> comparison in 3D space
-----
名称
	手法
	速度
	想定される信頼性
Omokage検索
	iDRプロファイル比較
	$plus2 <br> ミリ秒以下/1比較
	$minus <br> 1次元のプロファイルを利用
gmfit
	ガウス混合モデルのフィッティング
	$plus <br> 1秒以下/1比較
	$plus <br> 3次元空間での比較

EOD
);

_d(<<<EOD
title
	Re-ranking by gmfit
	gmfitで並べなおし
abst
	The similarity ranking by Omokage search can be re-ordered according to correlation coefficient by gmfit, expecting incorporation of the (potential) benefits of two methods with following properties.
	Omokage検索による類似度順位をgmfitによる相関係数に従って再順位付けすることができます。以下の様な特徴を持つ2つの手法の「いいとこ取り」を目指した機能です。
main_e
	%table-1%
main_j
	%table-2%
id
	gmfit_rerank
tag
	omo gmfit
rel
	gmfit about_omosearch
EOD
);

//.. new EMN
_d(<<<EOD
title
	Changes in new EM Navigator and Yorodumi
	新しいEM Navigatorと万見の変更点
abst
	Changes in new versions of EM Navigator and Yorodumi. (Sep. 2016)
	新しいEM Navigatorと万見の変更点(2016年9月)
main_e
	Changes:
		New user interface unified in <i>EM Navigator</i>, <i>Yorodumi</i> & <i>Omokage search</i>, supporting mobile devices as well as PCs.
		New <i>Yorodumi</i> replace the individual data page (<i>"Detail page"</i> of EM Navigator in the legacy system). It is unified browser for EMDB, PDB, and SASBDB entries integrated with <i>EM Navigator</i> and <i>Omokage search</i>.
		The viewers (structure/movie viewers) appear in pop-up windows. On a PC, multiple viewer windows can be opened. On mobile devices, the viewers support touch operation.
	New features:
		<b><i>Molmil</i></b>, molecular structure viewer, and <b><i>SurfView</i></b>, surface model viewer for EMDB map data, are available to view the 3D structures. Both viewers support mobile device use.
		Yorodumi support SASBDB entries.
		Some new pages such as <b><i>Yorodumi Papers</i></b>, citation database of structure data entries and <b><i>Yorodumi Search</i></b>, cross search by keywords
main_j
	変更点
		ページ外観や操作性を刷新しました。新しい「万見」と「Omokage検索」と共通化し、PCだけでなくモバイル機器にも対応しました。
		EM Navigatorの個別のデータエントリのページ（旧版の「詳細ページ」）は、新しい「万見」が受け持ちます。「Omokage検索」のフロントエンド・詳細情報表示も兼ねた、EMDB、PDB、SASBDB用の共通の詳細ページです。
		構造ビューア、ムービービューアは個別のポップアップウインドウに表示されます。PCでは一度に複数のビューアウインドウの表示が可能です。モバイル機器ではタッチ操作にも対応しています。
	新しい機能
		3次元構造の閲覧に、分子構造ビューア「<b>Molmil</b>」と、EMDBマップ表面モデルビューア「<b>SurfView</b>」が利用できるようになりました。
		万見はSASBDBのデータエントリの表示に対応しました。
		その他新しいページ （引用文献のデータベース「<b>万見文献</b>」「<b>EMN文献</b>」、キーワードによる横断検索「万見検索」など)
	旧版のページも、当面は継続します。
id
	new_emn_changes
rel
	about_emn about_ym surfview molmil movie about_empap about_pap about_ysearch
tag
	omo emn ym
EOD
);

//.. 情報源
//... PDBMLplus
_d(<<<EOD
title
	Information from PDBMLplus
	PDBML-plusからの情報
abst
	PDBMLplus is the XML format file including additional information relating to individual proteins. Currently, PDB files are lacking a detailed description of function, experimental conditions, and the like. And then such information was included to extended XML database, named PDBMLplus.
	PDBMLplusはXML形式のPDBデータフォーマットである「PDBML」のデータに対し、個々の分子に関する情報をPDBjで独自に追加したものです。
id
	mlplus
rel
	pdbj
tag
	omo emn ym
link
	u	https://pdbj.org/help/pdbmlplus
	t	PDBMLplus - PDBj helip
	tj	PDBMLplus - PDBjヘルプ
	-
	u	https://pdbj.org/help?PID=783
	t	About Functional Details page - PDBJ help
	tj	機能情報のページについて - PDBjヘルプ
EOD
);

//... YM annot
_d(<<<EOD
title
	Yorodumi annotation
	万見注釈
abst
	Annotation by Yorodumi/EM Navigator manager
	万見・EM Navigatorの管理者による注釈
id
	ym_annot
rel
	developer
tag
	omo emn ym
EOD
);

//. legacy 廃止
//... Yorodumi legacy
/*
_d_obso(<<<EOD
title
	Yorodumi (legacy version)
	万見 (旧版)
abst
	Touch the mechanism of life
	生命のカラクリにさわろう
id
	about_ym_leg
url
	viewtop.php?lgc=1
tag
	ym about
rel
	emdb pdb about_ym
EOD
);
*/
//... emn legacy *
/*
_d(<<<EOD
title
	EM Navigator (legacy version)
	EM Navigator (旧版)
abst
	Legacy version of EM Navigator
	EM Navigator 旧版
main_e
	EM Navigator is the web site to browse 3D electron microscopy (3D-EM) data of biological molecules and assemblies.'
	The data are based on EMDB and PDB data.
	This is for <b>non-specialists</b>, <b>beginners</b>, and <b>experts</b> in 3D-EM or structural/molecular biology.
	run by PDBj
main_j
	生体分子や生体組織の3次元電子顕微鏡データを、<b>気軽にわかりやすく</b>眺めるためのウェブサイトです。
	EMDBとPDBのデータを利用しています。
	分子・構造生物学の専門家にも、初心者や専門外のかたにも利用していただけるサイトを目指しています。
	PDBjが運営しています。
id
	about_emn_leg
tag
	emn emdb pdb about
rel
	emdb pdb pdbj

EOD
);
*/

//. テンプレ
/*
_d(<<<EOD
title
abst
main_e
main_j
id
tag
rel
link
EOD
);
*/

