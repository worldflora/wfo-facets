<?php


/**
 * 
 * Represents facets (tags) we use
 * 
 */
class Facet extends WfoFacets{

    protected int $id;
    protected string $title;
    protected ?string $description;
    protected ?string $uri;

    public function __construct($title, $description, $uri, $id = null) {
        $this->title = $title;
        $this->description = $description;
        $this->uri = $uri;
        $this->id = (int)$id;
    }


    /**
     * Returns a facet based on the db row
     * 
     */
    public static function getFacet($init_val){

        global $mysqli;

        if(is_numeric($init_val)){
            $sql = "SELECT * FROM facet_names WHERE id = $init_val;";
        }else{
            $title_safe = $mysqli->real_escape_string($init_val);
            $sql = "SELECT * FROM facet_names WHERE title = '$title_safe';";
        }

        $response = $mysqli->query($sql);
        $rows = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();

        if(count($rows) == 0){
            return null;
        }else{
            return new Facet($rows[0]['title'],$rows[0]['description'], $rows[0]['uri'],$rows[0]['id']);
        }

    }

    public function save(){

        global $mysqli;

        $title_safe = $mysqli->real_escape_string($this->title);
        $description_safe = $mysqli->real_escape_string($this->description);
        $uri_safe = $mysqli->real_escape_string($this->uri);

        if($this->id){
            // we already exist so update
            $mysqli->query("UPDATE facet_names 
                    SET 
                    `title` = '$title_safe',
                    `description` = '$description_safe',
                    `uri` = '$uri_safe'
                    WHERE
                    `id` = {$this->id} ");
        }else{
            // we are creating
            $mysqli->query("INSERT INTO facet_names 
                    (`title`,`description`,`uri`)
                    VALUES
                    ('$title_safe','$description_safe','$uri_safe')");
            $this->id = $mysqli->insert_id;
        }

        if($mysqli->error){
            echo $mysqli->error;
            exit;
        }
        
        return $this->id;
        
    }

    public function getId(){return $this->id;}
    public function getTitle(){return $this->title;}
    public function setTitle($title){$this->title = $title;}
    public function getDescription(){return $this->description;}
    public function setDescription($description){$this->description = $description;}
    public function getUri(){return $this->uri;}
    public function setUri($uri){$this->uri = $uri;}

}