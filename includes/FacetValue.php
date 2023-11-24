<?php


/**
 * 
 * Represents facets (tags) we use
 * 
 */
class FacetValue extends Facet{

    private Facet $facet;

    public function __construct($facet, $title, $description, $uri, $id = null) {
        parent::__construct($title, $description, $uri, $id);
        $this->facet = $facet;
    }


    /**
     * Returns a facet value based on the db row
     * 
     */
    public static function getFacetValue($id){

        global $mysqli;
        $sql = "SELECT * FROM facet_values WHERE id = $id;";
        $response = $mysqli->query($sql);
        $rows = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();

        if(count($rows) == 0){
            return null;
        }else{
            $facet = Facet::getFacet($rows[0]['name_id']);
            return new FacetValue($facet, $rows[0]['title'],$rows[0]['description'], $rows[0]['uri'], $rows[0]['id']);
        }

    }

    public function save(){

        global $mysqli;

        $title_safe = $mysqli->real_escape_string($this->title);
        $description_safe = $mysqli->real_escape_string($this->description);
        $uri_safe = $mysqli->real_escape_string($this->uri);

        if($this->id){
            // we already exist so update
            $mysqli->query("UPDATE facet_values 
                    SET 
                    `title` = '$title_safe',
                    `description` = '$description_safe',
                    `uri` = '$uri_safe',
                    `name_id` = {$this->facet->getId()}
                    WHERE
                    `id` = {$this->id} ");
        }else{
            // we are creating
            $mysqli->query("INSERT INTO facet_values 
                    (`title`,`description`,`uri`, `name_id`)
                    VALUES
                    ('$title_safe','$description_safe','$uri_safe', {$this->facet->getId()})");
            $this->id = $mysqli->insert_id;
        }

        if($mysqli->error){
            echo $mysqli->error;
            exit;
        }
        
        return $this->id;
        
    }

    public function getFacetOfValue(){return $this->facet;}

}