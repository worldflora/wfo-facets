<?php

// the add tab on the source page

$search = @$_REQUEST['search'];


?>


<form method="POST" action="source_add.php">

    <div class="mb-3">
        <input type="txt" class="form-control" id="search" name="search" value="<?php echo $search ?>"
            placeholder="Type the first few letters of the plant name for suggestions" />
    </div>



</form>

<ul class="list-group" id="search_results">
    <li class="list-group-item">
        Use this form to add names to the list one by one from a search list.
    </li>
</ul>


<script>
const graphQlUri = "https://list.worldfloraonline.org/gql.php";

function runGraphQuery(query, variables, giveBack) {

    const payload = {
        'query': query,
        'variables': variables
    }

    var options = {
        'method': 'POST',
        'contentType': 'application/json',
        'headers': {},
        'body': JSON.stringify(payload)
    };

    const response = fetch(graphQlUri, options)
        .then((response) => response.json())
        .then((data) => giveBack(data));

    return;
}

// define the GraphQL query string ahead of times
let lookup_query =
    `query NameSearch($terms: String!){
                    taxonNameSuggestion(
                        termsString: $terms
                        limit: 100
                    ) {
                        id
                        stableUri
                        fullNameStringPlain
                        fullNameStringHtml
                        nomenclaturalStatus
                        role
                        rank
                        currentPreferredUsage {
                            pathString
                            hasName {
                                id
                                stableUri
                                fullNameStringHtml
                            }
                        }
                    }
                }`;

// Listen for key up in the text area and do a search
document.getElementById("search").onkeyup = function(e) {

    let name_list = document.getElementById("search_results");

    let query_string = e.target.value.trim();
    if (query_string.length > 3) {

        // tell them we are looking
        name_list.innerHTML = "<li class=\"list-group-item\">Searching...</li>";

        // call the api
        runGraphQuery(lookup_query, {
            terms: query_string
        }, (response) => {

            //console.log(response.data);

            // remove the current children
            name_list.childNodes.forEach(child => {
                name_list.removeChild(child);
            });

            response.data.taxonNameSuggestion.forEach(name => {
                //console.log(name);
                name_list.appendChild(getNameListItem(name, <?php echo $source_id ?>,
                    <?php echo $facet_value['facet_value_id'] ?>, true));
            });

            // if we haven't found anything then put a message in
            if (name_list.childNodes.length == 0) {
                name_list.innerHTML =
                    `<li class=\"list-group-item\">Nothing found for "${query_string}" </li>`;
            }
        });


    } else {
        name_list.innerHTML = "<li class=\"list-group-item\">SAdd 4 or more letters to search</li>";
    }
};
</script>