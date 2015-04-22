<?php include("functions.php"); ?>
<?php
   session_start();
   $_SESSION['logged']='false';
   redirect(0, "index.php");
?>
