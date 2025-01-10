<?php
require_once('../include/language_codes.php');
require_once('header.php');

    // if god they can create new snippet sources
    if($user && $user['role'] == 'god'){
        echo '<div style="float: right;">';

        echo '<a class="btn btn-sm btn-success" href="snippet_sources_index.php"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="Update the index with the label info for the snippet sources." 
            role="button">Index Snippet Source Labels</a>';

        echo '&nbsp;<a class="btn btn-sm btn-success" href="snippets_index.php"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="Update the index with the metadata for snippets changed since last index run." 
            role="button">Index Snippets Metadata</a>';

        echo '&nbsp;<a 
            class="btn btn-sm btn-outline-secondary"
            href="snippet_source_create.php"
            role="button"
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="Create a new source of text snippets of kind and language." 
            >Create snippet source</a>';
        echo '</div>';
    } // is god

?>

<h1>Snippets</h1>
<p>Snippets of text describing taxa.</p>

<h2>Sources</h2>

<table class="table">

    <thead>
      <th scope="col">#</th>
      <th scope="col">Category</th>
      <th scope="col">Language</th>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
    </thead>


<?php

// we have to do some jiggery pokery because you can't just sort on enumerations

// get the categories
$result = $mysqli->query("SHOW COLUMNS FROM `snippet_sources` LIKE 'category'");
$row = $result->fetch_assoc();
$result->close();

$type = $row['Type'];
preg_match("/'(.*)'/i", $type, $matches);
$categories = explode(',', $matches[1]);
array_walk($categories, function(&$v){$v = str_replace("'", "", $v);});
sort($categories); // now in order
$categories_string = "'" . implode("','", $categories) . "'";

// languages are already in order
$languages_string = "'" . implode("','", array_keys($language_codes)) . "'";

$result = $mysqli->query("SELECT * 
    FROM snippet_sources as ss 
    JOIN sources as s ON s.id = ss.source_id
    ORDER BY
        FIELD(`category`, 'publish',{$categories_string}),
        FIELD(`language`, 'publish',{$languages_string})    
    ;");

while($row = $result->fetch_assoc()){

    echo "<tr>";

    echo "<th scope=\"row\"><a href=\"snippet_source.php?source_id={$row['source_id']}\">{$row['source_id']}</a></th>";
    echo "<td>{$row['category']}</td>";

    $lang_label = $language_codes[$row['language']];
    echo "<td>{$lang_label}</td>";
    
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['description']}</td>";
    echo "<tr/>";
}

?>
</table>
<?php
require_once('footer.php');
?>