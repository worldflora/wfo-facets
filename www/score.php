<?php
require_once('../config.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');
require_once('../include/SolrIndex.php');

// get the name we are scoring
$wfo_id = @$_REQUEST['wfo_id'];
$index = new SolrIndex();
$solr_doc = $index->getDoc($wfo_id);

// get the facet we are updating
$facet_id = $_REQUEST['facet_id'];
$facet = WikiItem::getWikiItem($facet_id);

$source_id = 47146; // 'Q47146' user interface

// are they doing an update?
if(@$_POST){

    echo "<pre>";
    print_r($_POST);

    // and the facet values / scores
    $sql = "SELECT f.value_id, wc.label_en FROM facets as f join wiki_cache as wc on f.value_id = wc.q_number WHERE facet_id = $facet_id;";
    $response = $mysqli->query($sql);
    $facet_values = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();


    // get a list of current scores and work through them
    foreach($facet_values as $fval){

        // what is the current state
        $response = $mysqli->query("SELECT * FROM wfo_scores WHERE value_id = {$fval['value_id']} AND source_id = {$source_id} AND wfo_id = '{$wfo_id}'");
        $current_values = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();
        if($current_values){
            $currently_present = true;
            $currently_negated = $current_values[0]['negated'] == 1;
        }else{
            $currently_present = false;
            $currently_negated = false;
        }
        
        // what is the state in the form?
        $should_be_present = @$_POST['scored'] && in_array($fval['value_id'], $_POST['scored']);
        $should_be_negated = @$_POST['negated'] && in_array($fval['value_id'], $_POST['negated']);


        // do the stuff to update the value in the db
        $negated_int = $should_be_negated ? 1:0;

        if($should_be_present && !$currently_present){
            $mysqli->query("INSERT INTO wfo_scores (wfo_id, value_id, source_id, negated) VALUES ('$wfo_id', {$fval['value_id']}, $source_id, $negated_int)");
        }elseif(!$should_be_present && $currently_present){
            $mysqli->query("DELETE FROM wfo_scores WHERE id = {$current_values[0]['id']};");
        }elseif($should_be_present && $currently_present){
            // it should be there and it is there
            // does it have the correct negation state?
            if($current_values[0]['negated'] != $negated_int){
                $mysqli->query("UPDATE wfo_scores SET negated = $negated_int WHERE id = {$current_values[0]['id']};");
            }
        }

        if($mysqli->error){
            echo $mysqli->error;
            exit;
        }
    
    }

    // redirect to ourselves so everything refreshes
    header("Location: taxon.php?id={$wfo_id}#{$fname_id}");
    exit;

}

// Get the facet values for display
$response = $mysqli->query(
    "SELECT
        f.value_id, wc.label_en, s.id, s.negated
    FROM 
        facets as f
    LEFT JOIN 
        wfo_scores as s on s.value_id = f.value_id AND s.wfo_id = \"$wfo_id\"
    JOIN 
        wiki_cache as wc on wc.q_number = f.value_id
    WHERE 
        f.facet_id = $facet_id
    ORDER BY wc.label_en");
$values = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

require_once('header.php');

echo "<h2>{$solr_doc->full_name_string_html_s}</h2>";
echo "<p><strong>{$solr_doc->role_s}</strong> | <a href=\"https://list.worldfloraonline.org/{$solr_doc->id}\">{$wfo_id}</a></p>";

echo "<hr/>";
echo "<h2>Facet: {$facet->getLabel()}</h2>";
echo "<hr/>";

    echo '<form method="POST" action="score.php" >';
    echo "<input type=\"hidden\" name=\"wfo_id\" value=\"{$wfo_id}\" />";
    echo "<input type=\"hidden\" name=\"facet_id\" value=\"{$facet->getId()}\" />";
    echo "<table style=\"width: auto;\">";
    echo "<tr><th>Title</th><th>Scored</th><th>Negated</th>";
    foreach($values as $value){
        echo "<tr>";

        echo "<td>{$value['label_en']}</td>";

        $negate_id = 'negate_' . $value['value_id'];        
        $val_checked = $value['id'] ? 'checked' : '';
        echo "<td style=\"text-align: center;\"><input {$val_checked} name=\"scored[]\" value=\"{$value['value_id']}\" type=\"checkbox\" onchange=\"scoreChanged({$value['value_id']}, this.checked)\"/></td>";

        $neg_checked = $value['negated'] ? 'checked' : '';
        $neg_disabled = $value['id'] ? '' : 'disabled';
        echo "<td style=\"text-align: center;\"><input {$neg_checked} {$neg_disabled} id=\"$negate_id\" name=\"negated[]\" value=\"{$value['value_id']}\" type=\"checkbox\"/></td>";
        
        echo "</tr>";
    }

    echo "<tr>";
    echo "<td>&nbsp;</td>";
    echo "<td><a href=\"taxon.php?id={$wfo_id}\">Cancel</a></td>";
    echo "<td><input type=\"submit\" value=\"Save\"/></td>";
    echo "</tr>";

    echo "</table>";
    echo "</form>";

?>
<hr />
<h2>Score</h2>



<script>
function scoreChanged(fvalueId, checked) {

    var negateBox = document.getElementById('negate_' + fvalueId);

    if (checked) {
        negateBox.disabled = false;
    } else {
        negateBox.disabled = true;
        negateBox.checked = false;
    }

}
</script>


<?php
require_once('footer.php');
?>