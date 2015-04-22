<?php include("functions.php"); ?>
<?php session_start(); 
           if($_SESSION['logged']!='true') {
         //      redirect(0, "login.php");
           }
?><?php getHeader(); ?>
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
        <h2>Change your password</h2>
        <p class='info'>Use this screen to change your EPManager password</p>
        <?php


if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<h3>There was a problem</h3><ul>";

       if ($_POST['user'] == '' || $_POST['user'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>you must supply as username</li>";
       }

       if ($_POST['oldpass'] == '' || $_POST['oldpass'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>you must supply a password</li>";
       }

       if ($_POST['newpass'] == '' || $_POST['newpass'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>you must supply a new password</li>";
       }

       if ($_POST['confirmpass'] == '' || $_POST['confirmpass'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>you must confirm the new password</li>";
       }

       if ($_POST['newpass'] != $_POST['confirmpass'])
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li class='warning'>password fields do not match!</li>";
       }

       if (login($_POST['user'],$_POST['oldpass'])==false)
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li class='warning'>your username and password do not match!</li>";
       }

       if($pagevalid == true)
       {
         $username = $_POST['user'];
         $oldpassword = $_POST['oldpass'];
         $newpassword = $_POST['newpass'];
         $confirmpassword = $_POST['confirmpass'];

         $link= dbconnect();
         $passwordcrypt=Encrypt($newpassword);

         $passwordcrypt=mysqli_real_escape_string($link,$passwordcrypt);
         $username=mysqli_real_escape_string($link,$username);
         
         $sql = 'UPDATE `' . EP_DB_DATABASE . '`.`ep_admins` SET `user_pass` = \''.$passwordcrypt.'\' WHERE `ep_admins`.`user_login` = \''.$username.'\';';
         
         mysqli_query($link,$sql) or die(mysqli_error());

         dbdisconnect($link);
         $errorcode = "<p><b>Password changed. Next time you log in, use the new password</b></p>";
         echo $errorcode;
       }
       else
       {
         echo $errorcode;        
       }

}
else 
{

?>
<fieldset>
  <legend>Fill out the fields to change your password</legend>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

  <label for="user">username</label>
    <input type="text" name="user" />

  <label for="oldpass">old password</label>    
    <input type="password" name="oldpass" /><br />

  <label for="newpass">new password</label>    
    <input type="password" name="newpass" /><br />

  <label for="confirmpass">confim new password</label>    
    <input type="password" name="confirmpass" /><br />

    <input type="submit" name="login" value="change password!" /><br />    
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

