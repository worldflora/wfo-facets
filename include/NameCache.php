<?php

 /**
 * A wrapper around the name cache table that
 */
class NameCache{

    public static function cacheName($wfo_id){

        global $mysqli;

        // is it present in the cache
        $response = $mysqli->query("SELECT * FROM name_cache WHERE wfo_id = '$wfo_id';");
        if($response->num_rows < 1){
            // we don't have it so we need to call to get it

            $graph_ql_query = '
                query NameFetch($id: String!){
                    taxonNameById(nameId: $id){
                        fullNameStringPlain
                    }
                }
            ';

            $graph_ql_variables = (object)array('id' => $wfo_id);

            $payload = (object) array(
                'query' => $graph_ql_query,
                'variables' => $graph_ql_variables
            );

            $ch = curl_init( "https://list.worldfloraonline.org/gql.php" );
            # Setup request to send json via POST.
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($payload) );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            # Return response instead of printing.
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            # Send request.
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result);

            $name = $result->data->taxonNameById->fullNameStringPlain;

            $name_safe = $mysqli->real_escape_string($name);
            $mysqli->query("INSERT INTO name_cache (`wfo_id`, `name`) VALUES ('$wfo_id', '$name_safe');");

        }

    }

}