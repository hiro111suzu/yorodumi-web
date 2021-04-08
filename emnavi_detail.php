<?php
header( "HTTP/1.1 303 See Other" ); 
header( 'Location: quick.php?' . http_build_query( $_GET ) );
die();
