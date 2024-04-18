<?php
    // get the details from the table..

    // are we being posted to?
    $harvest_uri = $source['harvest_uri'];
    $harvest_frequency = $source['harvest_frequency'] ? $source['harvest_frequency'] : 'never';
    $harvest_overwrites = $source['harvest_overwrites'];
    $harvest_last = $source['harvest_last'];

    // if we are being posted to then we save the settings.
    $render_harvest_progress = false;
    if(@$_POST && isset($_POST['harvest_button']) && Authorisation::canEditSourceData($source_id)){

        $harvest_uri = $_POST['harvest_uri'];
        $harvest_frequency = $_POST['harvest_frequency'];
        $harvest_overwrites = isset($_POST['harvest_overwrites']) ? 1 : 0;
       
        $harvest_uri_safe = $mysqli->real_escape_string($harvest_uri);
        $mysqli->query("UPDATE `sources` SET `harvest_uri` = '$harvest_uri_safe', `harvest_frequency` = '$harvest_frequency', `harvest_overwrites` = $harvest_overwrites WHERE id = $source_id");
        echo $mysqli->error;

        // if they requested a harvest we kick that off now
        if($_POST['harvest_button'] == 'harvest' && $harvest_uri){

            // download the file to a local folder
            $now = time();
            $input_file_path = "../data/session_data/user_{$user['id']}/source_{$source_id}";
            @mkdir($input_file_path, 0777,true);
            $input_file_path .= "/$now.csv";
            file_put_contents($input_file_path, file_get_contents($harvest_uri));

            $importer = new Importer($input_file_path, $harvest_overwrites ? true : false, $source_id, $facet_value);
            $_SESSION['importer'] = serialize($importer);

            $render_harvest_progress = true;

        }else{
            // we don't want any importers lying around if we haven't 
            // requested a harvest
            unset($_SESSION['importer']);
            $importer = false;
        }
    }


?>
<p class="lead">
    You can use the settings here to automatically harvest lists of names from a CSV file hosted anywhere on the
    internet. The way this works is the same as for the Upload option except the file is pulled from a URL.
</p>
<?php

    if(Authorisation::canEditSourceData($source_id)){
        if($render_harvest_progress){
?>
<div id="harvest_progress_bar">
    <div class="alert alert-warning" role="alert"><strong>Downloading ... </strong></div>
</div>
<div>
    <a href="source.php?tab=harvest-tab&source_id=<?php echo $source_id ?>">Cancel</a>
</div>
<script>
// call the progress bar every second till it is complete
const harvest_div = document.getElementById('harvest_progress_bar');
callProgressBar(harvest_div);
</script>

<?php
        }else{
?>

<form method="POST" action="source.php">

    <input type="hidden" name="tab" value="harvest-tab" />
    <input type="hidden" name="source_id" value="<?php echo $source_id ?>" />

    <div class="mb-3">
        <label for="harvest_uri" class="form-label">Harvest URL</label>
        <input type="url" class="form-control" id="harvest_uri" name="harvest_uri" aria-describedby="harvest_uri_help"
            value="<?php echo $harvest_uri ?>" />
        <div id="harvest_uri_help" class="form-text">The full HTTP or HTTPS URL of the file to be imported.</div>
    </div>

    <div class="mb-3">
        <label for="harvest_frequency" class="form-label">Harvest frequency</label>
        <select class="form-select" id="harvest_frequency" name="harvest_frequency" aria-describedby="frequency_help">
            <option <?php echo  $harvest_frequency == 'never' ? 'selected' : ''; ?> value="never">Never (Only harvest on
                demand)</option>
            <option <?php echo  $harvest_frequency == 'daily' ? 'selected' : ''; ?> value="daily">Daily</option>
            <option <?php echo  $harvest_frequency == 'weekly' ? 'selected' : ''; ?> value="weekly">Weekly</option>
            <option <?php echo  $harvest_frequency == 'monthly' ? 'selected' : ''; ?> value="monthly">Monthly</option>
        </select>
        <div id="frequency_help" class="form-text">For scheduled harvesting the importer checks the modification date of
            the file and only imports new files. This check isn't made for manually triggered harvests.</div>

    </div>

    <div class="mb-3">
        <input class="form-check-input" <?php echo  $harvest_overwrites ? 'checked' : ''; ?> type="checkbox" value=""
            id="overwrite_checkbox" name="harvest_overwrites" value="overwrite"
            onchange="let el = document.getElementById('overwrite_warning'); if(this.checked){el.style.display = 'block' }else{ el.style.display = 'none' };">
        <label class="form-check-label" for="overwrite_checkbox">
            Overwrite existing list.
        </label>
        <p>
        <div class="alert alert-warning" id="overwrite_warning" role="alert"
            style="display: <?php echo  $harvest_overwrites ? 'block': 'none'; ?>;">Harvesting will
            overwrite the current list
            with the contents of
            the remote
            file.</div>
        </p>
    </div>
    <div class="mb-3">
        <button type="submit" name="harvest_button" value="save" class="btn btn-primary">Save settings</button>
        &nbsp;
        <button type="submit" name="harvest_button" value="harvest" class="btn btn-primary">Harvest now</button>
    </div>

</form>

<?php
        }// rendering form
    }else{ // can use form
?>
<div class="alert alert-danger" role="alert">Sorry, you don't have rights to harvest to this list.</div>
<?php
    } // can't edit form
?>