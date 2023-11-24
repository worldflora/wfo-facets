<?php

require_once('../config.php');

echo "Extract facets from WCVP and put them in a CSV file\n";

echo "Loading Level mappings\n";

$in = fopen('../data/tdwg_level_3_2_1_mapping.csv', 'r');
$header = fgetcsv($in);
$tdwg_levels = array();
while($line = fgetcsv($in)){
    $tdwg_levels[$line[0]] = array('level3' => $line[1], 'level2' => $line[2], 'level1' => $line[3]);
}
fclose($in);

echo "\t loaded ". count($tdwg_levels) . " mappings\n";

echo "Loading ISO mappings\n";

$in = fopen('../data/tdwg_level3_to_iso_alpha2.csv', 'r');
$header = fgetcsv($in);
$tdwg_iso = array();
while($line = fgetcsv($in)){
    $tdwg_iso[$line[0]] = $line[1];
}
fclose($in);

echo "\t loaded ". count($tdwg_iso) . " mappings\n";

echo "Working through WCVP\n";

$out = fopen('../data/wcvp_country_scores.csv', 'w');

$response = $mysqli->query("SELECT ")

fclose($out);