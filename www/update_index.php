<?php

require_once('../config.php');
//require_once('header.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

$wfo_id = $_GET['wfo_id'];

WfoFacets::indexTaxon($wfo_id);

header("Location: taxon.php?id=$wfo_id");