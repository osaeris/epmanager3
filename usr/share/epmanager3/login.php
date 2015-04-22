<?php 
if(!isset($_SESSION)){
    session_start(); 
}
?>
<?php include("functions.php"); ?>
<?php getHeader(); ?>
<?php echo $headerString; ?>   
<?php include("/etc/epmanager3/config.php"); ?>
<?php include("dbinfo.php"); ?>
<?php require_once('libraries/pclzip.lib.php'); ?>

 <body>
   <div id='container'>
     <div id='header'>
     </div>

    
       <?php get_menu();
        echo $menustring;
        ?>

      <div id='formdiv'>
        <h2>Login</h2>
        <p class='info'>In order to use the advanced EPManager features you must log in.</p>
        <?php


if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<h3>There was a problem</h3><ul>";


       $userid = $_POST['user'];
       $passwd = $_POST['pass'];
       

       if ($_POST['user'] == '' || $_POST['user'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>you must supply as username</li>";
       }

       if ($_POST['pass'] == '' || $_POST['pass'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>you must supply a password</li>";
       }


       
       
       if($pagevalid == true)
       {
         

         $username = $_POST['user'];
         $password = $_POST['pass'];
         
         if (login($username,$password) == 'true')
         {
           $level = get_level($username);
           if(!isset($_SESSION)){
               session_start(); 
           }
           $_SESSION['logged']='true';
           if(!isset($_SESSION)){
               session_start(); 
           }
           $_SESSION['level']=$level;
           redirect(0, "index.php");
         }
       else
         { 
?>
<p class='warning'>sorry - the username and password you provided do not match!</p>
<fieldset>
  <legend>login - enter your credentials</legend>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

  <label for="user">username</label>
    <input type="text" name="user" />

  <label for="password">password</label>    
    <input type="password" name="pass" /><br />

    <input type="submit" name="login" value="login!" /><br />    
    
    <input type="hidden" name="action" value="submitted" />
  </form>
</fieldset>


<?php
         }
       }



  
}
else 
{
?>
<fieldset>
  <legend>login - enter your credentials</legend>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

  <label for="user">username</label>
    <input type="text" name="user" />

  <label for="password">password</label>    
    <input type="password" name="pass" /><br />

    <input type="submit" name="login" value="login!" /><br />    
    
    <input type="hidden" name="action" value="submitted" />
  </form>
</fieldset>

<?php
}
?> 
<p class='spacer'>&nbsp;</p>
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>

