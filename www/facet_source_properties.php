<?php

// Edit the properties of the source
if(@$_POST && $_POST['properties_button'] &&  Authorisation::canEditSourceData($source_id)){

    $name_safe = $mysqli->real_escape_string($_POST['name']);
    $description_safe = $mysqli->real_escape_string($_POST['description']);
    $uri_safe = $mysqli->real_escape_string($_POST['link_uri']);

    $mysqli->query("UPDATE `sources` SET `name` = '$name_safe', `description` = '$description_safe', `link_uri` = '$uri_safe' WHERE id = $source_id;");

    echo "<script>document.location = 'facet_source.php?source_id={$source_id}&tab=properties-tab'</script>";
}

$name = $source['name'];
$description = $source['description'];
$link_uri = $source['link_uri'];

?>


<p class="lead">
    Edit the metadata fields for this data source.
</p>

<form method="POST" action="facet_source.php">

    <input type="hidden" name="source_id" value="<?php echo $source_id ?>" />
    <input type="hidden" name="tab" value="properties-tab" />

    <div class="mb-3">
        <label for="name" class="form-label">Source name</label>
        <input type="txt" class="form-control" id="name" name="name" aria-describedby="name_help"
            value="<?php echo $name ?>" />
        <div id="name_help" class="form-text">Keep it short and meaningful but at least 8 characters long.</div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Source description</label>
        <textarea class="form-control" id="description" name="description"
            aria-describedby="description_help"><?php echo $description ?></textarea>
        <div id="description_help" class="form-text">A concise description of the source.</div>
    </div>

    <div class="mb-3">
        <label for="link_uri" class="form-label">Link URL</label>
        <input type="url" class="form-control" id="link_uri" name="link_uri" aria-describedby="link_uri_help"
            value="<?php echo $link_uri ?>" />
        <div id="link_uri_help" class="form-text">A link to more information about the source.</div>
    </div>

    <button type="submit" name="properties_button" value="save" class="btn btn-primary">Save</button>
</form>