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


?>




<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button"
            role="tab" aria-controls="list" aria-selected="true">List</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button"
            role="tab" aria-controls="profile" aria-selected="false">Profile</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button"
            role="tab" aria-controls="contact" aria-selected="false">Contact</button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
        <p>&nbsp;</p>
        <p class="lead">
            This displays the list - perhaps with a filter box.
        </p>
    </div>
    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">asfdsad</div>
    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">w234542345</div>
</div>




<?php
    require_once('footer.php');
?>