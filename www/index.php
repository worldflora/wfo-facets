<?php
require_once('../config.php');

require_once('header.php');
?>

<h1>WFO Facet Service</h1>
<p>This is a service that stores facts about plant taxa. It isn't meant to have a human interface but to provide data to
    indexes and portals.</p>

<h2>Facets By Name</h2>
<p>Here is a list of the facets we have. Click a facet to see its values.</p>

<?php
    $response = $mysqli->query("SELECT * FROM facet_names ORDER BY title");
    $facet_names = $response->fetch_all(MYSQLI_ASSOC);

    echo "<ul>";
    foreach($facet_names as $fname){
        echo "<li><a href=\"facet.php?id={$fname['id']}\"/>{$fname['title']}</a>: {$fname['description']}</li>";
    }
    echo "</ul>";

?>



<?php

require_once('footer.php');
?>