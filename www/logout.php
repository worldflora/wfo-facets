<?php
    require_once('header.php');
    $_SESSION['user'] = null;
?>
<h1>Logging out ...</h1>
<script>
window.location.href = "index.php"
</script>
<?php
    require_once('footer.php');
?>