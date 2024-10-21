<?php
    require_once('header.php');
    
    // if god the can create new facets
    if($user && $user['role'] == 'god'){
        echo '<div style="float: right;">';
        echo '<a class="btn btn-sm btn-success" href="sources_index.php"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Update the index with the label info for the sources." 
            role="button">Index Source Labels</a>';
        echo '</div>';
    } // is god

?>

<h1>Sources</h1>

<p class="lead">
    Here is a list of all the sources we have...
</p>

<ul class="list-group">
    <?php
    $response = $mysqli->query("SELECT * FROM `sources` as s JOIN `facet_value_sources` as fvs on s.id = fvs.source_id ORDER BY `name`;");
    $sources = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    foreach($sources as $s){

        // load up the facet value we are looking at
        $response = $mysqli->query("SELECT f.id as facet_id, fv.id as facet_value_id, f.name as facet_name, fv.name as facet_value_name FROM facet_values as fv JOIN facets as f on fv.facet_id = f.id WHERE fv.id = {$s['facet_value_id']}");
        $facet_value = $response->fetch_assoc();
        $response->close();

        echo '<li class="list-group-item">';
        echo '<div class="row">';
        
        echo '<div class="col">';
        echo "<a href=\"facet_source.php?source_id={$s['id']}\"><h3>{$s['name']}</h3></a>";
        echo "<p><strong>Source for:</strong> <a href=\"facet_values.php?facet_id={$facet_value['facet_id']}\">{$facet_value['facet_name']}: {$facet_value['facet_value_name']}</a></p>";
        echo "<p>";
        echo $s['description'];
        echo "</p>";
        echo '</div>'; // end of col

        // if they are god then show the edit buttons
        
        echo '<div class="col" style="text-align: right;">';
        /*
        if($user['role'] == 'god'){
            if($u['role'] == 'editor'){
                    echo '<a class="btn btn-sm btn-outline-secondary" href="user_set_role.php?role=god&user_id='. $u['id'] .'" role="button">Make god</a>';
            }else{
            echo '<a class="btn btn-sm btn-outline-secondary" href="user_set_role.php?role=editor&user_id='. $u['id'] .'" role="button">Make editor</a>';
            }
            echo '&nbsp;<a class="btn btn-sm btn-outline-secondary" href="user_password_reset.php?username='.$u['username'].'&user_id='. $u['id'] .'" role="button">Reset password</a>';
            echo '&nbsp;<a class="btn btn-sm btn-outline-danger" href="user_delete.php?user_id='. $u['id'] .'" role="button">Delete</a>';
        }
        */
        echo '</div>'; // end of col
        echo '</div>'; // end of row
        echo '</li>';
    }
    
    // can add a new source if god
    if($user && $user['role'] == 'god'){
        echo '<li class="list-group-item" style="text-align: right;">';
        echo 'Create a new source via the facet value the source will be based on.';
        echo '</li>';
    } // is god
?>
</ul>

<?php
    require_once('footer.php');
?>