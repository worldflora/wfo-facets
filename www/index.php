<?php
    require_once('header.php');
?>

<h1>WFO Plant List: Facet Service</h1>

<p class="lead">
    Faceted searching is very common in information retrieval system such as internet shopping sites.
    It enables the user to restrict their search to items that have a particular feature or set of features - a
    certain colour or size for example.
    It would be very useful to subset the list of over 400,000 recognized taxa in the WFO Plant List in a
    similar way - perhaps by country, life form or threat status.
</p>
<p>
    This service provides a way to tag the accepted taxa in the WFO Plant List with useful attributes for searching.
    It handles data from multiple sources that binds specific attributes to WFO name IDs and then calculates the
    values for accepted taxa and inserts those into the plant list index in such a way that the provenance of the data
    isn't lost and can be displayed to the end user. Some definitions:
</p>
<dl>
    <dt>Facet</dt>
    <dd>Some feature of a plant, the equivalent of a character<sup>*</sup> in plant identification e.g. "Life Form".
    </dd>
    <dt>Facet Value</dt>
    <dd>A form that a facet takes, the equivalent of a character state e.g. "Tree" for lifeform.</dd>
    <dt>Slug</dt>
    <dd>A short, human friendly code to represent a facet value in data files. e.g. "lf:tree" for "Life Form, Tree".
        Slugs are unique.</dd>
    <dt>Source</dt>
    <dd>The service does not contain original information but collates it from multiple sources.
        Each source is a simple list of WFO IDs mapped to a facet value. The source also specifies the provenance
        for the list. Note that provenance is per list not per individual ID mapping. Each source only contains
        information about a single facet values.
    </dd>
</dl>

<p>
    This service does not store any information about nomenclature or taxonomy but only the WFO IDs of names.
    It calls the WFO Plant List API for the latest version of the data.
</p>

<p>
    <sup>*</sup> We use facet and facet value in preference to the character and character state terms because there is
    no intention to build an identification system. This is purely about information retrieval and presentation.
</p>

<a class="btn btn-lg btn-primary" href="sources.php" role="button">View the current sources &raquo;</a>

<?php
    require_once('footer.php');
?>