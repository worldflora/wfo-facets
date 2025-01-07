<?php

// Add or remove users

// we can be getted to as a stand alone page as 
// well called free standing
if(@$_GET['user_id'] &&  @$_GET['source_id']){

    // they have passed a user and a source so 
    // we toggle the membership - if they are god
    require_once('../config.php');
    require_once('../include/Authorisation.php');

    if(Authorisation::isGod()){
        $user_id = (int)$_GET['user_id'];
        $source_id = (int)$_GET['source_id'];

        // are they there?
        $response = $mysqli->query("SELECT * FROM user_sources WHERE user_id = $user_id AND source_id = $source_id;");
        $user_sources = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();

        if(count($user_sources) > 0){
            $mysqli->query("DELETE FROM user_sources WHERE user_id = $user_id AND source_id = $source_id;");            
        }else{
            $response = $mysqli->query("INSERT INTO user_sources (user_id, source_id) VALUES ($user_id, $source_id);");
        }
    }

    header("Location: facet_source.php?source_id=$source_id&tab=users-tab");
    exit;

}


echo "<p class=\"lead\">Add or remove the users who can edit the data in this data source.</p>";

// fetch all users and flag if they own this or not

$sql = "SELECT u.id as user_id, u.`username` as user_name, us.source_id as source_id
from `users` as u left join user_sources as us on us.user_id = u.id and us.source_id = $source_id
order by us.source_id desc, u.username asc;";

$response = $mysqli->query($sql);
$users = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

echo '<ul class="list-group">';
foreach($users as $u){
    echo '<li class="list-group-item">';
    echo '<strong>';
    echo $u['user_name'];
    echo '</strong>';

    $button_colour = $u['source_id'] ? 'danger' : 'success';
    $button_label = $u['source_id'] ? 'Remove' : 'Add';

    echo '<div style="float:right; max-width: 20%; text-align: right;">';
    echo "<a class=\"btn btn-sm btn-outline-$button_colour\" href=\"source_users.php?source_id=$source_id&user_id={$u['user_id']}\" role=\"button\">$button_label</a>";
    echo '</div>';
    echo '</li>';
}
echo '</ul>';