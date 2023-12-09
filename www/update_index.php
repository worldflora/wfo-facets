<?php

require_once('../config.php');
//require_once('header.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/WfoFacets.php');

$wfo_id = $_GET['wfo_id'];

WfoFacets::indexTaxon($wfo_id);

header("Location: taxon.php?id=$wfo_id");