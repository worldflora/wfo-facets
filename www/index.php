<?php
require_once('../config.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

require_once('header.php');
?>

<h1>WFO Facet Service</h1>
<p>This is a service that stores facts about plant taxa. It isn't meant to have a human interface but to provide data to
    indexes and portals.</p>

<h2>Facets By Name</h2>
<p>Here is a list of the facets we have. Click a facet to see its values.</p>

<?php
    $response = $mysqli->query("SELECT facet_id, count(*) as n FROM wfo_facets.facets group by facet_id;");
    $rows = $response->fetch_all(MYSQLI_ASSOC);

    echo "<ul>";
    foreach($rows as $row){
        $facet = WikiItem::getWikiItem($row['facet_id']);
        $count = $row['n'];
        echo "<li><a href=\"facet.php?id={$facet->getId()}\"/>{$facet->getLabel()}</a> 
            | Values: {$count}
            | <a target=\"wikidata\" href=\"{$facet->getWikidataLink()}\">Wikidata</a>
            </li>";
    }
    echo "</ul>";

?>

<h2>Unplaced Values</h2>
<p>Here we will have a list of values that haven't been assigned to a facet.</p>
<?php

    $response = $mysqli->query("SELECT s.value_id, count(*) as n 
        FROM wfo_scores as s 
        left join facets as f on s.value_id = f.value_id
        where f.value_id is null
        group by s.value_id
        order by n desc;");
    $rows = $response->fetch_all(MYSQLI_ASSOC);

    echo "<ul>";
    foreach($rows as $row){
        $value = WikiItem::getWikiItem($row['value_id']);
        $count = $row['n'];
        echo "
            <form method=\"GET\" action=\"place_value.php\">
            <li><a href=\"value.php?id={$value->getId()}\"/>{$value->getLabel()}</a> 
            | Scores: {$count}
            | <a target=\"wikidata\" href=\"{$value->getWikidataLink()}\">Wikidata</a>
            | <input type=\"hidden\" name=\"value_id\" value=\"{$value->getId()}\" />
              <input type=\"text\" name=\"facet_id\" placeholder=\"Facet Q number\" />
              <input type=\"submit\" value=\"Set\" />
            </li>
            </form>";
    }
    echo "</ul>";

    require_once('footer.php');
?>