<?php
require_once('../config.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

require_once('header.php');

$facet = WikiItem::getWikiItem($_GET['id']);

echo "<h2>Facet:{$facet->getLabel()}</h2>";

?>

<h2>Facet Values</h2>
<?php
    $response = $mysqli->query("SELECT label_en FROM facets as fv JOIN wiki_cache as wc on fv.value_id = wc.q_number and fv.facet_id = {$_GET['id']} order by wc.label_en;");
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    echo "<ul>";
    foreach($rows as $row){
        echo "<li><strong>{$row['label_en']}</strong> </li>";
     }
    echo "</ul>";


require_once('footer.php');
?>