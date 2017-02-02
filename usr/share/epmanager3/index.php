<?php session_start(); ?>
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
        <h2>Home</h2>
        <p class='bodytext'>Welcome to ePortfolio Manager 3.</p> 
<hr />
        <p class='bodytext'>This version has been designed to use Wordpress 3.x.</p> 
        <p class='bodytext'>There will be fewer themes available but these can be personalised more than the old 2.x themes.</p> 
        <p class='bodytext'>Single Sign On (SSO) is still available in eportfolios. Technical staff should refer to the plugin wp3-singlesignonlink which is activated already in new ePortfolios.</p>

<?php

if (isset($_SESSION['logged'])) {
  if(MANAGER_LDAP=='off') {
    if ($_SESSION['logged']=='true') {
        echo "<p>You can <a href='changepassword.php'>change your EPManager login password using this form</a></p>";
    }
  }
  else
  {
      echo "<p>If you wish to change your password please do so on your network as LDAP is enabled.</p>";
  }
}

    $link=dbconnect();

    $query = "SELECT COUNT(user_login) FROM ep_admins WHERE user_login='admin'";

    $numstudents = mysqli_query($link,$query) or die("<div class='greybox'><h1>Installation</h1><p><b>Database connection was successful. Now it's time to run the install script.</b></p><p>click <a href='install.php'>INSTALL</a> to complete the installation of EPManager</p></div><p class='spacer'>&nbsp;</p>");

    $numstudent = mysqli_fetch_array($numstudents);

    dbdisconnect($link);
?>

        <p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
