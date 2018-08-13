<?php


//Delete the table when plugin is deleted
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

$sql = "DROP TABLE IF EXISTS `".$wpdb->prefix . "magix_dropdown`;";
$wpdb->query($sql);


delete_option("md_db_version");