<?php
    require_once('header.php');

    // nobody but the gods
    if(!$user || $user['role'] != 'god'){
        echo '<div class="alert alert-danger" role="alert">You do not have permission to access this resource.</div>';
        require_once('footer.php');
        exit;
    }


    // get the params
    $facet_value_id = (int)@$_REQUEST['facet_value_id'];
    $name = trim(@$_REQUEST['name']);
    $description = @$_REQUEST['description'];
    $link_uri = @$_REQUEST['link_uri'];
    $data_uri = @$_REQUEST['data_uri'];

    // load up the facet value we are looking at
    $response = $mysqli->query("SELECT f.id as facet_id, fv.id as facet_value_id, f.name as facet_name, fv.name as facet_value_name FROM facet_values as fv JOIN facets as f on fv.facet_id = f.id WHERE fv.id = $facet_value_id");
    $facet_value = $response->fetch_assoc();
    $response->close();

    if($_POST){

        if(strlen($name) > 7){

            $name_safe = $mysqli->real_escape_string($name);
            $description_safe = $mysqli->real_escape_string($description);
            $link_uri_safe = $mysqli->real_escape_string($link_uri);

            $mysqli->begin_transaction();

            try{
                $mysqli->query("INSERT INTO `sources` (`name`, `description`, `link_uri`) VALUES ('$name_safe', '$description_safe', '$link_uri_safe');");
                $source_id = $mysqli->insert_id;
                $mysqli->query("INSERT INTO `facet_value_sources` (`source_id`, `facet_value_id`) VALUES ( $source_id, $facet_value_id);");
                $mysqli->commit();
                
                echo '<div class="alert alert-success" role="alert">Facet "' . $name . '" created.</div>';
                echo "<script>window.location.href = \"facet_source.php?source_id={$source_id}\"</script>";

            } catch (mysqli_sql_exception $exception) {
                $mysqli->rollback();
                echo '<div class="alert alert-danger" role="alert">Database insert failed.</div>';
                echo '<div class="alert alert-danger" role="alert">' . $mysqli->error . '</div>';
                print_r($exception);
            }
                
        }else{
            echo '<div class="alert alert-danger" role="alert">The name must be greater than 7 characters long.</div>';
        }
    }

?>

<h1>Create a new facet value source</h1>
<p class="lead">
    This is a new source for
    <strong><?php echo "{$facet_value['facet_name']}: {$facet_value['facet_value_name']}" ?></strong>.
</p>
<form method="POST" action="facet_source_create.php">

    <input type="hidden" name="facet_value_id" value="<?php echo $facet_value_id ?>" />

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

    <button type="submit" class="btn btn-primary">Create</button>
</form>


<?php
    require_once('footer.php');
?>