<?php

// the list on the source page

// we need a list of all the names in this source - sorted
if(@$_REQUEST['filter']){
    $filter = $_REQUEST['filter'];
    $filter_safe = $mysqli->real_escape_string($filter);
    $filter_clause = "AND nc.`name` LIKE '$filter_safe%' ";
}else{
    $filter = '';
    $filter_clause = '';
}

$sql = "SELECT ws.wfo_id FROM wfo_scores as ws LEFT JOIN name_cache as nc ON ws.wfo_id = nc.wfo_id WHERE ws.source_id = $source_id $filter_clause ORDER BY nc.`name` LIMIT 100";
$response = $mysqli->query($sql);
$rows = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

$sql = "SELECT count(*) as n FROM wfo_scores as ws WHERE ws.source_id = $source_id;";
$response = $mysqli->query($sql);
$row = $response->fetch_assoc();
$total_rows = number_format($row['n']);
$response->close();

?>
<script>
const filter_on_load = '<?php echo $filter ?>';
</script>
<form method="GET" action="source.php">
    <input type="hidden" name="source_id" value="<?php echo $source_id; ?>" />
    <input type="hidden" name="tab" value="list-tab" />
    <div class="mb-3">
        <input autofocus type="txt" class="form-control" id="filter" name="filter" value="<?php echo $filter ?>"
            onkeyup="if(this.value != filter_on_load) this.form.submit();"
            onfocus="this.setSelectionRange(this.value.length, this.value.length);"
            placeholder="Type the first few letters of the name to filter the list." />
    </div>
</form>

<?php

$showing = number_format(count($rows), 0);
echo "<p>Showing $showing of $total_rows names.<p>";

echo '<ul class="list-group" id="list_results">';

$editable = true; // fixme - should be calculated

if($rows){
    foreach ($rows as $row) {
        $wfo_id = $row['wfo_id'];
        echo "<li class=\"list-group-item\" id=\"$wfo_id\" >Loading $wfo_id ...</li>";
        echo "<script>\n";
        echo "replaceNameListItem('$wfo_id', {$source_id}, {$facet_value['facet_value_id']}, $editable);";
        echo "\n</script>";

    }
    

}else{
    echo '<li class="list-group-item">Nothing to show</li>';
}


echo '</ul>';


?>