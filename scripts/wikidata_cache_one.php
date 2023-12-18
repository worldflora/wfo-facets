<?php

require_once('../config.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

echo "\nCache a single Wikidata Item at a time\n";


while( $input = readline('Enter Q number: ') ){

    if(!preg_match('/^Q[0-9]+$/', $input )){
        break;
    }

    $item = WikiItem::getWikiItem($input);

    echo "$input\n";
    
}