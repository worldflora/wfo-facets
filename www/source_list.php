<?php

// the list on the source page

// we need a list of all the names in this source - sorted

$response = $mysqli->query("SELECT ws.wfo_id FROM wfo_scores as ws LEFT JOIN name_cache as nc ON ws.wfo_id = nc.wfo_id WHERE ws.source_id = $source_id ORDER BY nc.`name` LIMIT 100");
$rows = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

echo '<p>Filter box?</p>';


echo '<ul class="list-group" id="list_results">';

$editable = true; // fixme - should be calculated

if($rows){
    foreach ($rows as $row) {
        $wfo_id = $row['wfo_id'];
        echo "<li class=\"list-group-item\" id=\"$wfo_id\" >$wfo_id</li>";
        echo "<script>\n";
        echo "replaceNameListItem('$wfo_id', {$source_id}, {$facet_value['facet_value_id']}, $editable);";
        echo "\n</script>";

    }
    

}else{
    echo '<li class="list-group-item">Nothing to show</li>';
}


echo '</ul>';


?>