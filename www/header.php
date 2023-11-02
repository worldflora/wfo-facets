<!DOCTYPE html>
<html>

<head>
    <title>WFO Facet Service</title>
    <style>
    body {
        font-family: sans-serif;
        padding-left: 2em;
        padding-right: 2em;
    }

    table,
    td,
    th {
        text-align: left;
        border: 1px solid black;
        border-collapse: collapse;
        padding: 0.5em;
    }

    table {
        width: 60em;
    }

    th {
        white-space: nowrap;
    }



    div#navbar {
        padding: 1em;
        width: 100%;
        border: none;
        padding-left: 0px;
        padding-right: 0px;
        border-bottom: 1px gray solid;
    }

    .aside {
        background-color: #eee;
        color: black;
        border: solid 1px gray;
        padding: 0.5em;
    }
    </style>

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
    </script>
</head>

<body>
    <div id="navbar">
        <strong>WFO Facet Service: </strong>
        <a href="index.php">Facets</a>
        |
        <a href="taxon.php">Taxon</a>
    </div>
    <!-- end header.php -->