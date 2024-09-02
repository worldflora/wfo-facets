<?php

require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');
require_once('../include/Importer.php');
require_once('../include/NameCache.php');

// this will run the harvester on sources that need it.

$response = $mysqli->query("SELECT * FROM sources
    WHERE (harvest_frequency != 'never' AND length(harvest_uri) > 0)
    AND
    (
        (harvest_frequency = 'monthly' AND harvest_last <= CURRENT_DATE() - INTERVAL 1 MONTH)
        OR
        (harvest_frequency = 'weekly' AND harvest_last <= CURRENT_DATE() - INTERVAL 1 WEEK)
        OR
        (harvest_frequency = 'daily' AND harvest_last <= CURRENT_DATE() - INTERVAL 1 DAY)
        OR
        harvest_last is null
    )
    ORDER BY harvest_last;");

$sources = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

foreach($sources as $source){

    echo "Processing\t{$source['id']}\t{$source['name']}\n";
    $now = time();
    $input_file_path = "../data/session_data/harvester/source_{$source['id']}";
    @mkdir($input_file_path, 0777,true);
    $input_file_path .= "/$now.csv";
    if(file_put_contents($input_file_path, file_get_contents($source['harvest_uri']))){
        $importer = new Importer($input_file_path, $source['harvest_overwrites'] == 1 ? true : false, $source['id'], $source['facet_value_id']);

        $page_size = 100;
        $count = 0;
        while($imported = $importer->import($page_size)){
            $count += $imported;
            echo "\tImported: " . number_format($count, 0) . "\n";
            if($imported < $page_size) break;
        }

        echo "\tFinished\n";

    }else{
        echo "Something went wrong writing the file\n";
    }

}

echo "\nFinished all\n";