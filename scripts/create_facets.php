<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');

echo "Create new facets from a csv file\n";

if(count($argv) < 2){
    echo "You must pass the name of a file in the data folder.\n";
    exit;
}

$file_path = '../data/'. $argv[1];

if(!file_exists($file_path)){
    echo "Can't find file '$file_path'. Try again...\n";
    exit;
}

$in = fopen($file_path, 'r');

// ignore the header
fgetcsv($in);

// read each line assuming it goes facet/value/description

while($row = fgetcsv($in)){

    // get the associated facet.

    // if it doesn't exist then get it
    $facet = Facet::getFacetByNameValue($row[0], $row[1], $row[2], true);
    echo "{$facet->getId()}\n";

}

fclose($in);
