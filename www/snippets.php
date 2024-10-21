<?php
require_once('../include/language_codes.php');
require_once('header.php');

    // if god the can create new facets
    if($user && $user['role'] == 'god'){
        echo '<div style="float: right;">';
        echo '&nbsp;<a 
            class="btn btn-sm btn-outline-secondary"
            href="snippet_source_create.php"
            role="button"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Create a new source of text snippets of kind and language." 
            >Create snippet source</a>';
        echo '</div>';
    } // is god

?>

<h1>Snippets</h1>
<p>Snippets of text describing taxa.</p>

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
    JOIN sources as s 
    ON s.id = ss.source_id 
    ORDER BY
        FIELD(`category`, 'publish',{$categories_string}),
        FIELD(`language`, 'publish',{$languages_string})    
    ;");

while($row = $result->fetch_assoc()){

    echo "<tr>";

    echo "<th scope=\"row\">{$row[source_id]}</th>";
    echo "<td>{$row['category']}</td>";
    echo "<td>{$row['language']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['description']}</td>";
    
    echo "</tr>";

    print_r($row);
    echo "<hr/>";
}

?>

<?php
require_once('footer.php');
?>