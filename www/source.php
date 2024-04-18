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
        echo '&nbsp;<a class="btn btn-sm btn-outline-danger" href="#" onclick="alert(\'Deleting is serious business and is currently done at the database level only.\')" role="button">Delete source</a>';
    }
    echo '</div>';

    echo "<h1>{$source['name']}</h1>";
    echo "<p><a href=\"facet_values.php?facet_id={$facet_value['facet_id']}\">{$facet_value['facet_name']}: {$facet_value['facet_value_name']}</a>.</p>";
    echo "<p class=\"lead\">{$source['description']}</p>";


?>

<ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-bottom: 1em;">

    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
            role="tab">List</button>
    </li>
    <?php
     if(Authorisation::canEditSourceData($source_id)){
?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button"
            role="tab">Add</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button"
            role="tab">Upload</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="harvest-tab" data-bs-toggle="tab" data-bs-target="#harvest" type="button"
            role="tab">Harvest</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties" type="button"
            role="tab">Properties</button>
    </li>
    <?php
     } // can edit
?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="download-tab" data-bs-toggle="tab" data-bs-target="#download" type="button"
            role="tab">Download</button>
    </li>
    <?php
     if(Authorisation::isGod()){
?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button"
            role="tab">Users</button>
    </li>
    <?php
     } // is god
?>
</ul>


<div class="tab-content" id="myTabContent">

    <!-- LIST DISPLAY -->
    <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
        <?php require_once('source_list.php'); ?>
    </div>
    <?php
     if(Authorisation::canEditSourceData($source_id)){
?>
    <!-- ADD SINGLE -->
    <div class=" tab-pane fade" id="add" role="tabpanel">
        <?php require_once('source_add.php'); ?>
    </div>

    <!-- UPLOAD -->
    <div class="tab-pane fade" id="upload" role="tabpanel">
        <?php require_once('source_upload.php'); ?>
    </div>

    <!-- HARVEST -->
    <div class="tab-pane fade" id="harvest" role="tabpanel">
        <?php require_once('source_harvest.php'); ?>
    </div>

    <!-- PROPERTIES -->
    <div class="tab-pane fade" id="properties" role="tabpanel">
        <?php require_once('source_properties.php'); ?>
    </div>
    <?php
     } // can edit
?>
    <!-- DOWNLOAD -->
    <div class="tab-pane fade" id="download" role="tabpanel">
        <?php require_once('source_download.php'); ?>
    </div>

    <?php
     if(Authorisation::isGod()){
?>
    <!-- USERS -->
    <div class="tab-pane fade" id="users" role="tabpanel">
        <?php require_once('source_users.php'); ?>
    </div>
    <?php
     } // is god
?>
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