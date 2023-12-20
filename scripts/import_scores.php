<?php

require_once('../config.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');


echo "Import scores\n";


if(count($argv) < 2){
    echo "You must pass id of the SOURCE and the path of the csv file to import.\n";
    exit;
}

$file_path = $argv[1];


echo "Importing from: $file_path\n";

if(!file_exists($file_path)){
    echo "That path looks wrong. Can't find the file.\n";
    exit;
}
$in = fopen($file_path, 'r');
$header = fgetcsv($in);
echo "Header\n";
print_r($header);

// source id is at the end of the file name after and underscore.
$matches = array();
if(!preg_match('/_(Q[0-9]+)\.csv$/', $file_path, $matches) || count($matches) < 2){
    print_r($matches);
    echo "Could not extract source id from path.\n";
    exit;
}
$source_id = $matches[1];

$count = 0;
while($line = fgetcsv($in)){

    $wfo = $line[0];
    $q = $line[1];
    // negation is optional
    if(count($line) > 2){
        $negated = $line[2];
    }else{
        $negated = 0;
    }

    // check the wikidata is good
    $item = WikiItem::getWikiItem($q);

    if(!$item){
        echo "No value for $q\n";
        exit;
    }

    $q_number = substr($q, 1);
    $source_q_number = substr($source_id, 1);

    $mysqli->query("INSERT 
        INTO wfo_scores (wfo_id, value_id, source_id, negated) 
        VALUES ('$wfo',$q_number, $source_q_number, $negated) 
        ON DUPLICATE KEY UPDATE negated = $negated;");
    
    if($mysqli->error){
        echo $mysqli->error;
        break;
    }
    $count++;

    echo $count . "\t" . $wfo . "\t" . $item->getLabel() . "\n";

    // debug
//    if($count > 100) break;
}

fclose($in);