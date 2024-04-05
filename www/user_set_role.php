<?php
    require_once('header.php');

    if(!$user || $user['role'] != 'god'){
        echo '<div class="alert alert-danger" role="alert">You do not have permission to access this resource.</div>';
        require_once('footer.php');
        exit;
    }
    $user_id = (int)$_GET['user_id'];
    $role_safe = $mysqli->real_escape_string($_GET['role']);
    $mysqli->query("UPDATE `users` SET `role` = '$role_safe' WHERE `id` = $user_id;");
    if($mysqli->error){
        echo '<div class="alert alert-danger" role="alert">'.$mysqli->error. '</div>';
        require_once('footer.php');
        exit; 
    }
?>
<h1>Setting role ...</h1>
<script>
window.location.href = "users.php"
</script>
<?php
    require_once('footer.php');
?>