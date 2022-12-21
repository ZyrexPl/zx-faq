<?php
if( !defined( 'WP_UNINSTALL_PLUGIN' ))
    exit();
//usuwam tabelę
global $wpdb;
$table_name = $wpdb->prefix . 'zxfaq';
$query ='DROP TABLE '.$table_name;
$wpdb->query($query);
