<?php
require_once('../config.php');
require_once('header.php');

$response = $mysqli->query("SELECT * FROM sources order by title");
$sources = $response->fetch_all(MYSQLI_ASSOC);

echo "<h2>Facet Values</h2>";

    echo "<ul>";
    foreach($sources as $source){
        echo "<li><a href=\"{$source['uri']}\">{$source['title']}</a>: {$source['description']}</li>";
    }
    echo "</ul>";

?>

<?php

require_once('footer.php');
?>