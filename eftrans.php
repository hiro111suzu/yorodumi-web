<?php
echo file_get_contents( "http://ef-site.hgc.jp/eF-site/servlet/Download?type=efvet&entry_id="
	. $_GET[ 'id' ] );
