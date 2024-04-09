<?php
    require_once('header.php');

    $source_id = (int)@$_REQUEST['source_id'];

        
    $response = $mysqli->query("SELECT * FROM `sources` WHERE id = $source_id");
    $source = $response->fetch_assoc();
    $response->close();

    // load up the facet value we are looking at
    $response = $mysqli->query("SELECT f.id as facet_id, fv.id as facet_value_id, f.name as facet_name, fv.name as facet_value_name FROM facet_values as fv JOIN facets as f on fv.facet_id = f.id WHERE fv.id = {$source['facet_value_id']};");
    $facet_value = $response->fetch_assoc();
    $response->close();

    // get the source

    echo '<div style="float: right;">';
    if($user && $user['role'] == 'god'){
        echo '<a class="btn btn-sm btn-outline-secondary" href="source_edit.php?source_id='. $source_id .'" role="button">Edit source</a>';
        echo '&nbsp;<a class="btn btn-sm btn-outline-danger" href="source_delete.php?source_id='. $source_id .'" role="button">Delete source</a>';
    }
    echo '</div>';

    echo "<h1>{$source['name']}</h1>";
    echo "<p><a href=\"facet_values.php?facet_id={$facet_value['facet_id']}\">{$facet_value['facet_name']}: {$facet_value['facet_value_name']}</a>.</p>";
    echo "<p class=\"lead\">{$source['description']}</p>";

    // write out the select tab 
    /*
    searchvar triggerEl = document.querySelector('#myTab a[href="#profile"]')
    bootstrap.Tab.getInstance(triggerEl).show() // Select tab by name
    */

?>

<ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-bottom: 1em;">

    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
            role="tab">List</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button"
            role="tab">Add</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button"
            role="tab">Upload</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="download-tab" data-bs-toggle="tab" data-bs-target="#download" type="button"
            role="tab">Download</button>
    </li>
</ul>


<div class="tab-content" id="myTabContent">

    <!-- LIST DISPLAY -->
    <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
        <?php require_once('source_list.php'); ?>
    </div>

    <!-- ADD SINGLE -->
    <div class=" tab-pane fade" id="add" role="tabpanel" aria-labelledby="profile-tab">
        <?php require_once('source_add.php'); ?>
    </div>

    <!-- UPLOAD -->
    <div class="tab-pane fade" id="upload" role="tabpanel" aria-labelledby="contact-tab">
        <?php require_once('source_upload.php'); ?>
    </div>

    <!-- DOWNLOAD -->
    <div class="tab-pane fade" id="download" role="tabpanel" aria-labelledby="contact-tab">
        <?php require_once('source_download.php'); ?>
    </div>

</div>



<?php
    require_once('footer.php');
?>

<script>
<?php
    // we need to be able to display a particular tab.
    if(@$_REQUEST['tab']){
        echo "var someTabTriggerEl = document.querySelector('#{$_REQUEST['tab']}');\n";
        echo "var tab = new bootstrap.Tab(someTabTriggerEl);\n";
        echo "tab.show();\n";
    }
?>
</script>