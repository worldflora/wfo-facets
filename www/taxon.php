<?php
require_once('../config.php');
require_once('header.php');

$wfo_id = @$_GET['id'];
/*
$response = $mysqli->query("SELECT * FROM facet_names WHERE id = $fname_id");
$facets = $response->fetch_all(MYSQLI_ASSOC);
$fname = $facets[0];
*/

?>
<table style="width: auto; float: right; margin-top: 1em;">
    <tr>
        <td style="text-align: right; vertical-align: top;"><strong>Lookup a name: </strong></td>
        <td>
            <form>
                <input id="wfo_lookup_input" type="text" style="width: 30em;">
                <select style="width: 33em; display: none;" size="6" id="wfo_lookup_select"
                    onclick="document.location = 'taxon.php?id=' + this.value;">
                    <option value="" disabled>Search results appear here.</option>
                </select>
            </form>
        </td>
    </tr>
    <tr>
        <td style="text-align: right; vertical-align: top;"><strong>WFO ID:</strong></td>
        <td>
            <form method="GET" action="taxon.php">
                <input type="text" id="id" name="id" placeholder="Enter WFO ID" style="width: 20em;">
                <input type="submit" value="Fetch" />
            </form>
        </td>
    </tr>
</table>


<?php
if($wfo_id){
    echo "<h2>Taxon thing</h2>";
    echo "<p>{$wfo_id}</p>";
}else{
    echo "<h2>Taxon</h2>";
    echo "<p>Use the form on the right to look up a name in the index.</p>";
}

?>


<hr />

<div style="float: left; width: 50%;">
    <h3>Facets in DB</h3>
    <p>These are the details in the database.</p>
</div>

<div style="float: right; width: 50%;">
    <h3>Facets in Index</h3>
    <p>These are the details in the index.</p>
</div>

<div style="clear: both;">
    <h3>Tools</h3>
    Publish to database.
</div>

<script>
// define the GraphQL query string ahead of times
let lookup_query =
    `query NameSearch($terms: String!){
                    taxonNameSuggestion(
                        termsString: $terms
                        limit: 100
                    ) {
                        id
                        stableUri
                        fullNameStringPlain,
                        fullNameStringHtml,
                        currentPreferredUsage{
                        hasName{
                            id,
                            stableUri,
                            fullNameStringHtml
                        }
                        }
                    }
                }`;

// Listen for key up in the text area and do a search
document.getElementById("wfo_lookup_input").onkeyup = function(e) {

    let select = document.getElementById("wfo_lookup_select");
    // show the box
    select.style.display = "block";

    let query_string = e.target.value.trim();
    if (query_string.length > 3) {

        // tell them we are looking
        select.innerHTML = "<option disabled>Doing a search ...</option>";

        // call the api
        runGraphQuery(lookup_query, {
            terms: query_string
        }, (response) => {
            console.log(response.data);
            // remove the current children
            select.childNodes.forEach(child => {
                select.removeChild(child);
            });
            response.data.taxonNameSuggestion.forEach(name => {
                const opt = document.createElement("option");
                opt.innerHTML = name.id + ": " + name.fullNameStringHtml;
                opt.setAttribute('value', name.id);
                opt.wfo_data =
                    name; // pop the name object on the dom element so we can grab it later
                select.appendChild(opt);
            });

            // if we haven't found anything then put a message in
            if (select.childNodes.length == 0) {
                select.innerHTML = `<option>Nothing found for "${query_string}" </option>`;
            }

        });


    } else {
        select.innerHTML = "<option disabled>Add 4 or more letters to search</option>";
    }
};

// listen for select change on the select list and render a name if there is one
document.getElementById("wfo_lookup_select").onchange = function(e) {
    const wfo = e.target.value;
    e.target.childNodes.forEach(opt => {
        if (opt.getAttribute('value') == wfo) {
            // we've got the chosen name so lets display it like the others 
            // this is cut and paste code for demo purposes but you get the point.
            const name = opt.wfo_data;
            const target = document.getElementById("wfo_lookup-display")
            const name_link = getLinkForName(
                name); // utility function defined above so we don't have to keep building <a> tags.

            if (name.currentPreferredUsage) {
                if (name.currentPreferredUsage.hasName.id == name.id) {
                    target.innerHTML = `<strong>${name_link}</strong>`;
                } else {
                    let accepted_link = getLinkForName(name.currentPreferredUsage.hasName);
                    target.innerHTML =
                        `<strong>${accepted_link}</strong><br/>&nbsp;&nbsp;&nbsp;<strong>syn: </strong>${name_link}`;
                }
            } else {
                target.innerHTML = "<strong>Unplaced: </strong>" + name.fullNameStringHtml;
            }
        }
    });
}
</script>


<?php
require_once('footer.php');
?>