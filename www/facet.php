<?php
require_once('../config.php');
require_once('header.php');

$fname_id = $_GET['id'];
$response = $mysqli->query("SELECT * FROM facet_names WHERE id = $fname_id");
$facets = $response->fetch_all(MYSQLI_ASSOC);
$fname = $facets[0];

echo "<h2>Facet: {$fname['title']}</h2>";
echo "<p>{$fname['description']}</p>";

?>



<h2>Facet Values</h2>
<?php
    $response = $mysqli->query("SELECT * FROM facet_values WHERE name_id = $fname_id ORDER BY title");
    $facet_values = $response->fetch_all(MYSQLI_ASSOC);

    echo "<ul>";
    foreach($facet_values as $fvalue){
        echo "<li><strong>{$fvalue['title']}</strong>: {$fvalue['description']}</li>";
    }
    echo "</ul>";

?>



<?php

require_once('footer.php');
?>