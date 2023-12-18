<?php

require_once('../config.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

echo "Importing facets\n";

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

while($line = fgetcsv($in)){

    $facet = WikiItem::getWikiItem($line[0]);
    $value = WikiItem::getWikiItem($line[1]);

    $mysqli->query("INSERT IGNORE INTO `facets` (`facet_id`, `value_id`) VALUES ({$facet->getId()},{$value->getId()})" );
    if($mysqli->error) throw new ErrorException($mysqli->error);

    echo $value->getLabel() . "\n";

    
}