<?php include("functions.php"); ?>
<?php session_start(); 
           if($_SESSION['logged']!='true') {
               redirect(0, "login.php");
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
        <h2>Reset user password</h2>
	<p class='info'>If LDAP is enabled, users can login using their normal network credentials. For occasions where users have no network account (schools link, training programs) it is necessary to choose an admin password for those users.</p>
        <p class='info'>If a user has forgotten their password, it can be reset using this form. Enter the blog ID and the user. You will be asked to confirm this before the password is reset. Passwords will be reset to <em>password</em> so obviously, encourage the user to change this as soon as possible.</p>
        <p class='info'>Alternatively, users can request that their password be reset using the <em>forgotten password</em> link on the login form.</p>
	<p class='info'>This form is of no use if the user has forgotten their LDAP (Active Directory or eDirectory) password. That would be an issue for network techs.</p>
<?php
if (isset($_POST['action']) && $_POST['action'] == 'submitted') {
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

    $pagevalid=true;
    $errorcode="<h3>There was a problem</h3><ul>";

    $eportfolio = $_POST['eportfolio'];
    $userid = $_POST['userid'];

    if ($_POST['eportfolio'] == '' || $_POST['eportfolio'] == 'nothing') {
        $pagevalid=false;
        echo "<li>eportfolio ID must not be blank</li>";
    }

    if ($_POST['userid'] == '' || $_POST['userid'] == 'nothing') {
        $pagevalid=false;
        echo "<li>user id must not be blank</li>";
    }

    if ($pagevalid == true) {
        $eportfolio = $_POST['eportfolio'];
        $userid = $_POST['userid'];
    }

    if (check_ep_user($eportfolio,$userid) > 0) { 

        if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch eportfolio!' && $pagevalid == true) {


    ?>

     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>confirm eportfolio and user selection</legend>
     <?php
       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$eportfolio'>$eportfolio,$userid</option>\n\r";
       echo "</select>";
     ?>
     
      <input type="submit" name="create" value="reset password!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="eportfolio" value="<?php echo $eportfolio; ?>">
      <input type="hidden" name="userid" value="<?php echo $userid; ?>">
      

</fieldset>
    </form>

    <?php } 
    }
    else
    {
        echo "<p class='warning'>that combination of eportfolio and user does not exist</p>";
        echo "<p><a href='resetpass.php'>&larr;&nbsp;back</a></p>";
    }
?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'reset password!') {

        $eportfolio = $_POST['eportfolio'];
        $userid = $_POST['userid'];

        if ($pagevalid == true) {

            foreach($_POST['studentlist'] as $student) {
                reset_ep_password($eportfolio,$userid);
            }
    
        echo "<p>password reset for user <em>$userid</em> in eportfolio <em>$eportfolio</em> </p>";
        echo "<p><a href='resetpass.php'>&larr;&nbsp;back</a></p>";
        }
        else
        {
            echo "<p>There was an unknown problem.</p>";
            echo "<p><a href='resetpass.php'>&larr;&nbsp;back</a></p>";
        }

    }
}
else 
{
?>
 <fieldset>
   <legend>select eportfolio ID and user ID to reset password</legend>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    
<label for="eportfolio">ePortfolio ID (student or lecturer ID)</label>
    <input type="text" name="eportfolio" />

<label for="userid">user ID to reset password (e.g. <em>admin</em> or <em>gillespied</em>)</label>
    <input type="text" name="userid" /><br />
    <input type="submit" name="fetch" value="fetch eportfolio!" /><br />    
    
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
