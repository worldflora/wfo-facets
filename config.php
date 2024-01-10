<?php

/*

    configuration that is safe to include in 
    github

*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
//error_reporting(E_ALL);
session_start();

include('../../wfo_facet_secrets.php'); // things we don't put in github

// Location of the solr server
define('SOLR_QUERY_URI', $solr_query_uri); // from wfo_secrets.php
define('SOLR_USER', $solr_user); // from wfo_secrets.php
define('SOLR_PASSWORD', $solr_password); // from wfo_secrets.php


define('PLANT_LIST_GRAPHQL_URI', $plant_list_graphql_uri);


// used for lookups and other services that don't want to 
// trouble themselves with many versions of backbone
// will normally be set to the most recent.
define('WFO_DEFAULT_VERSION','2023-12');

// create and initialise the database connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);  

// connect to the database
if ($mysqli->connect_error) {
  echo $mysqli->connect_error;
}

if (!$mysqli->set_charset("utf8")) {
  echo printf("Error loading character set utf8: %s\n", $mysqli->error);
}