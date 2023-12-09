<?php
require_once('../config.php');
require_once('../includes/SolrIndex.php');

// get the name we are scoring
$wfo_id = @$_REQUEST['wfo_id'];
$index = new SolrIndex();
$solr_doc = $index->getDoc($wfo_id);

// get the facet we are updating
$fname_id = $_REQUEST['facet_name_id'];
$response = $mysqli->query("SELECT * FROM facet_names WHERE id = $fname_id");
$facets = $response->fetch_all(MYSQLI_ASSOC);
$response->close();
$fname = $facets[0];

// what's the source we are working with?
if(@$_REQUEST['source_id']){
    $source_id = $_REQUEST['source_id'];
    $_SESSION['source_id'] = $source_id;
}elseif(@$_SESSION['source_id']){
    $source_id = $_SESSION['source_id'];
}else{
    // not got it from elsewhere so get the first one
    $response = $mysqli->query("SELECT id FROM sources ORDER BY id LIMIT 1");
    $sources = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();
    $source_id = $sources[0]['id'];
    $_SESSION['source_id'] = $source_id;
}

// and the facet values / scores
$sql = "SELECT fv.id as facet_value_id, fv.title, fv.`description`,  s.id as score_id, s.negated 
        FROM facet_values as fv 
        LEFT JOIN wfo_scores as s on fv.id = s.facet_value_id AND s.source_id = $source_id AND s.wfo_id = '$wfo_id'
        WHERE fv.name_id = $fname_id 
        ORDER BY title";
$response = $mysqli->query($sql);
$facet_values = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

// are they doing an update?
if(@$_POST){

    // get a list of current scores and work through them
    foreach($facet_values as $fval){

        // what is the current state
        $response = $mysqli->query("SELECT * FROM wfo_scores WHERE facet_value_id = {$fval['facet_value_id']} AND source_id = {$source_id} AND wfo_id = '{$wfo_id}'");
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
        $should_be_present = @$_POST['scored'] && in_array($fval['facet_value_id'], $_POST['scored']);
        $should_be_negated = @$_POST['negated'] && in_array($fval['facet_value_id'], $_POST['negated']);


        // do the stuff to update the value in the db
        $negated_int = $should_be_negated ? 1:0;

        if($should_be_present && !$currently_present){
            $mysqli->query("INSERT INTO wfo_scores (wfo_id, facet_value_id, source_id, negated) VALUES ('$wfo_id', {$fval['facet_value_id']}, $source_id, $negated_int)");
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
//$response = $mysqli->query("SELECT * FROM facet_values as fv JOIN wfo_scores as s on fv.id = s.facet_value_id AND s.wfo_id = '$wfo_id' WHERE name_id = $fname_id ORDER BY title");
//$facet_values = $response->fetch_all(MYSQLI_ASSOC);
//$response->close();

require_once('header.php');

echo "<h2>{$solr_doc->full_name_string_html_s}</h2>";
echo "<p><strong>{$solr_doc->role_s}</strong> | <a href=\"https://list.worldfloraonline.org/{$solr_doc->id}\">{$wfo_id}</a></p>";

echo "<hr/>";

echo "<h2>Facet: {$fname['title']}</h2>";
echo "<p>{$fname['description']}</p>";

echo "<hr/>";

echo '<form method="GET" action="score.php" >';
echo "<input type=\"hidden\" name=\"wfo_id\" value=\"{$wfo_id}\" />";
echo "<input type=\"hidden\" name=\"facet_name_id\" value=\"{$fname_id}\" />";
echo "<strong>Source: &nbsp;</strong>";
echo "<select name=\"source_id\" onchange=\"this.form.submit();\">";

$response = $mysqli->query("SELECT id, title FROM sources ORDER BY title");
$sources = $response->fetch_all(MYSQLI_ASSOC);
$response->close();
foreach($sources as $source){
    $selected = $source['id'] == $source_id ? 'selected' : '';
    echo "<option $selected value=\"{$source['id']}\">{$source['title']}</option>";
}
echo "</select>";
echo '</form>';

echo "<hr/>";

    echo '<form method="POST" action="score.php" >';
    echo "<input type=\"hidden\" name=\"wfo_id\" value=\"{$wfo_id}\" />";
    echo "<input type=\"hidden\" name=\"facet_name_id\" value=\"{$fname_id}\" />";
    echo "<input type=\"hidden\" name=\"source_id\" value=\"{$source_id}\" />";
    echo "<table style=\"width: auto;\">";
    echo "<tr><th>Title</th><th>Description</th><th>Scored</th><th>Negated</th>";
    foreach($facet_values as $fvalue){
        echo "<tr>";

        echo "<td>{$fvalue['title']}</td>";
        echo "<td>{$fvalue['description']}</td>";   

        $negate_id = 'negate_' . $fvalue['facet_value_id'];        
        $val_checked = $fvalue['score_id'] ? 'checked' : '';
        echo "<td style=\"text-align: center;\"><input {$val_checked} name=\"scored[]\" value=\"{$fvalue['facet_value_id']}\" type=\"checkbox\" onchange=\"scoreChanged({$fvalue['facet_value_id']}, this.checked)\"/></td>";

        $neg_checked = $fvalue['negated'] ? 'checked' : '';
        $neg_disabled = $fvalue['score_id'] ? '' : 'disabled';
        echo "<td style=\"text-align: center;\"><input {$neg_checked} {$neg_disabled} id=\"$negate_id\" name=\"negated[]\" value=\"{$fvalue['facet_value_id']}\" type=\"checkbox\"/></td>";
        
        echo "</tr>";
    }

    echo "<tr>";
    echo "<th>Source</th>";
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