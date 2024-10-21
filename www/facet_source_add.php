<?php

// the add tab on the source page

$search = @$_REQUEST['search'];


?>


<form method="POST" action="facet_source_add.php">
    <div class="mb-3">
        <input type="txt" class="form-control" id="add_search" name="search" value="<?php echo $search ?>"
            placeholder="Type the first few letters of the plant name for suggestions" />
    </div>
</form>

<ul class="list-group" id="add_search_results">
    <li class="list-group-item">
        Use this form to add names to the list one by one from a search list.
    </li>
</ul>


<script>
// Listen for key up in the text area and do a search
document.getElementById("add_search").onkeyup = function(e) {
    let name_list = document.getElementById("add_search_results");
    nameLookup(e, name_list, <?php echo $source_id ?>, <?php echo $facet_value['facet_value_id'] ?>);
};
</script>