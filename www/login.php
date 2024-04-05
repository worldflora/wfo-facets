<?php
    require_once('header.php');

    // if they are posting they are trying to login
    if($_POST){
        
        // do they exist
        $username_safe = $mysqli->real_escape_string($_POST['username']);

        $response = $mysqli->query("SELECT * FROM `users` WHERE `username` = '$username_safe';");
        $row = $response->fetch_assoc();
        if($row){
            if(password_verify($_POST['password'], $row['password_hash'])){
                // they are good to go.
                $_SESSION['user'] = $row;
                echo '<script>window.location.href = "index.php"</script>';   
            }
        }

        echo '<div class="alert alert-danger" role="alert">Sorry, those credentials are not recognised.</div>';
    }

?>


<h1>Login</h1>
<p class="lead">
<form method="POST" action="login.php">
    <div class="mb-3">
        <label for="exampleInputEmail1" class="form-label">Username</label>
        <input type="txt" class="form-control" name="username" aria-describedby="username_help">
        <div id="username_help" class="form-text">Use the login details you were given by the administrator.</div>
    </div>
    <div class="mb-3">
        <label for="exampleInputPassword1" class="form-label">Password</label>
        <input type="password" class="form-control" name="password" aria-describedby="password_help">
        <div id="password_help" class="form-text">If you have lost your password you must request a new one from the
            administrator.</div>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
</p>

<?php
    require_once('footer.php');
?>