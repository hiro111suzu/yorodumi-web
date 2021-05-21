<?php
$id = substr( trim( $_GET[ 'i' ] . $_GET[ 'id' ] ), -4 );
echo ( implode( "\n", gzfile( "_pdb/dep/pdb$id.ent.gz" ) ) ); 
