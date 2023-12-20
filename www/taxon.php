<?php
require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

$wfo_id = @$_GET['id'];

// stick taxon -use the one in the session if we have one
if(!$wfo_id && @$_SESSION['wfo_id']){
    $wfo_id = $_SESSION['wfo_id'];
}
$_SESSION['wfo_id'] = $wfo_id;

require_once('header.php');
?>
<table style="width: auto; float: right; margin-top: 1em;">
    <tr>
        <td style="text-align: right; vertical-align: top;"><strong>Lookup name: </strong></td>
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

    $index = new SolrIndex();
    $solr_doc = $index->getDoc($wfo_id);

    if($solr_doc){
        /*
        echo "<pre>";
        print_r($solr_doc);
        echo "</pre>";
*/
        echo "<h2>{$solr_doc->full_name_string_html_s}</h2>";
        echo "<p><strong>{$solr_doc->role_s}</strong> | <a href=\"https://list.worldfloraonline.org/{$solr_doc->id}\">{$wfo_id}</a>";

        if($solr_doc->role_s == 'synonym'){
            $accepted_wfo = substr($solr_doc->accepted_id_s, 0, 14);
            echo " of <a href=\"taxon.php?id=$accepted_wfo\">{$accepted_wfo} : $solr_doc->accepted_full_name_string_html_s</a>";
        }
        echo "</p>";

    }else{
        echo "<h2>Not Found</h2>";
        echo "<p>No taxon was found for <a href=\"https://list.worldfloraonline.org/{$wfo_id}\">{$wfo_id}</a>. Is it a valid id?</p>";
    }

}else{
    echo "<h2>Taxon</h2>";
    echo "<p>Use the form on the right to look up a name in the index.</p>";
}

?>

<hr />

<div style="float: left; width: 49%;">
    <h3>Facets in DB</h3>

    <?php
    // List of all the facets and values this one has

    $response = $mysqli->query("select q_number, label_en from wiki_cache where q_number in
            (select distinct(facet_id) from facets)
            order by label_en;");
    $facets = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    foreach($facets as $facet){
        echo "<h4>{$facet['label_en']}&nbsp;[<a href=\"score.php?wfo_id={$wfo_id}&facet_id={$facet['q_number']}\" />score</a>]</h4>";
  
        echo "<table style=\"width: 100%;\">";
        echo "<tr><th>Label</th><th># sources</th></tr>";
        $sql = "select s.value_id, count(s.source_id) as n
                FROM wfo_scores AS s
                JOIN facets as f on s.value_id = f.value_id and f.facet_id = {$facet['q_number']}
                WHERE s.wfo_id = \"{$wfo_id}\" 
                group by s.value_id;";
        $response = $mysqli->query($sql);
        $fvalues = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();
        if($fvalues){
            foreach($fvalues as $fval){
                 $item = WikiItem::getWikiItem($fval['value_id']);
                 echo "<tr><td>{$item->getLabel()}</td><td>{$fval['n']}</td></tr>";
            }
        }else{
            echo "<tr><td>None scored.</td></tr>";
        }
        echo "</table>";
    
    }

 ?>

</div>

<div style="float: right; width: 49%; border-left: 1px gray solid; padding-left: 1em;">

    <?php
    echo "<h3>Facets in Index ";
    if($solr_doc->role_s == 'accepted'){
        echo "[<a href=\"update_index.php?wfo_id=$wfo_id\">Update</a>]";
    }
    echo "</h3>";
    
        if($solr_doc->role_s == 'accepted'){
            echo "<p>These are the details in the index.</p>";
            foreach($solr_doc as $prop => $val){
                $matches = array();
                if(preg_match('/^(Q[0-9]+)_ss$/', $prop, $matches)){
                    $facet_q = $matches[1];
                    $facet = WikiItem::getWikiItem($facet_q);
                    echo "<h3>{$facet->getLabel()} [$facet_q]</h3>";
                    echo "<ul>";
                    asort($solr_doc->{$prop});
                    foreach($solr_doc->{$prop} as $val_q){
                        $val = WikiItem::getWikiItem($val_q);
                        echo "<li>{$val->getLabel()} [$val_q]</li>";
                    }
                    echo "</ul>";
                };
            }
        }else{
            echo "We don't index names that are not accepted.";
        }


    ?>
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
                select.innerHTML = `<option disabled>Nothing found for "${query_string}" </option>`;
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