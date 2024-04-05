<?php
    require_once('header.php');

    if(!$user){
        echo '<div class="alert alert-danger" role="alert">You do not have permission to access this resource.</div>';
        require_once('footer.php');
        exit;
    }

?>
<h1>Users</h1>
<p class="lead">
    These are the users registered with the system.
</p>
<ul class="list-group">
    <?php
    $response = $mysqli->query("SELECT * FROM `users` ORDER BY `username`;");
    while($u = $response->fetch_assoc()){
        echo '<li class="list-group-item">';
        echo '<div class="row">';
        
        echo '<div class="col">';
        echo "<strong>{$u['username']}</strong> ({$u['role']}) ";
        echo '</div>'; // end of col

        // if they are god then show the edit buttons
        
        echo '<div class="col" style="text-align: right;">';
        if($user['role'] == 'god'){
            if($u['role'] == 'editor'){
                    echo '<a class="btn btn-sm btn-outline-secondary" href="user_set_role.php?role=god&user_id='. $u['id'] .'" role="button">Make god</a>';
            }else{
            echo '<a class="btn btn-sm btn-outline-secondary" href="user_set_role.php?role=editor&user_id='. $u['id'] .'" role="button">Make editor</a>';
            }
            echo '&nbsp;<a class="btn btn-sm btn-outline-secondary" href="user_password_reset.php?username='.$u['username'].'&user_id='. $u['id'] .'" role="button">Reset password</a>';
            echo '&nbsp;<a class="btn btn-sm btn-outline-danger" href="user_delete.php?user_id='. $u['id'] .'" role="button">Delete</a>';
        }
        echo '</div>'; // end of col
        echo '</div>'; // end of row
        echo '</li>';
    }
    
    // can add a new user if god
    if($user['role'] == 'god'){
        echo '<li class="list-group-item" style="text-align: right;">';
        echo '<a class="btn btn-sm btn-success" href="user_create.php" role="button">Add user</a>';
        echo '</li>';
    } // is god
?>
</ul>
<?php

    if($user && $user['role'] == 'god'){

    }
    require_once('footer.php');
?>