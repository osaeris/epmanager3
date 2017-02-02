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
        <h2>Global Settings</h2>
        <p>Enter the settings for your own circumstances. Alternatively, edit the file config.php.</p>



<?php
get_menu(); 
if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

   /*   echo '<pre>INITIAL POSTBACK';
        print_r($_POST);
        echo '<a href="'. $_SERVER['PHP_SELF'] .'">Please try again</a>';
        echo '</pre>';  */
//pre-installer settings
$localserver = ''; //local mysql server
$localdb = ''; // local database name
$localuser = ''; // local database user account
$localpass = ''; // local database password
//basic settings - post install
$netroot = ''; // URL of the epmanager
$neteproot = ''; // URL of the eportfolio folder
$epsecure = ''; // SSL login flag (off/on)
$localpath = ''; // local path to epmanager
$portfoliopath = ''; // local path to eportfolios
$netdomain = ''; // domain for default lecturer email address
$lecturerpass = ''; // default lecturer password
//student records integration settings
$mis = '';// temporary value for mis checkbox (checked/unchecked)
$misint = '';// MIS integration flag (off/on)
$missystem = ''; // MIS system
$misdbms = ''; // MIS DBMS system
$misserver = ''; // MIS server IP address
$misdb = '';  // name of MIS database
$misuser = ''; // MIS database username
$mispass = ''; // MIS database password

//CMIS URL for lecturers
$cmislink = ''; // Link to CMIS system for quick student search
$cmisurl = ''; // Full URL to your CMIS system (could be https or http)

$ssosalt='';//SSO SALT Value
$testing = ''; // Tells the page if a test field is being used 
$ldap = '';
$ldapserver='';
$ldapfailoverserver='';
$ldapsuffix='';

       $pagevalid=true;
       $errorcode="<p class='warning'>There was a problem</p><ul>";

        $netroot = $_POST['netroot'];
 
      if (!isset($_POST["epsecure"])) {
       $epsecure = 'off';
      }
      else
      {
        if ($_POST['epsecure'] == '' || $_POST['epsecure'] == 'nothing')
          $epsecure = 'off';       	
        else
          $epsecure = 'on'; 	
      }

        $neteproot = $_POST['neteproot']; 
        $localpath = $_POST['localpath']; 
        $portfoliopath = $_POST['portfoliopath']; 
        $netdomain = $_POST['netdomain']; 
        $lecturerpass = $_POST['lecturerpass']; 

        $localserver = $_POST['localserver']; 
        $localdb = $_POST['localdb']; 
        $localuser = $_POST['localuser']; 
        $localpass = $_POST['localpass']; 

        if (!isset($_POST["misint"])) {
            $misint = 'off';
        }
        else
        {
            if ($_POST['misint'] == '' || $_POST['misint'] == 'nothing')
                $misint = 'off';       	
            else
                $misint = $_POST['misint']; 	
        }

        $missystem = $_POST['missystem'];
        $misdbms = $_POST['misdbms'];
        $misserver = $_POST['misserver']; 
        $misdb = $_POST['misdb']; 
        $misuser = $_POST['misuser']; 
        $mispass = $_POST['mispass']; 



        if (!isset($_POST["cmislink"])) {
            $cmislink = 'off';
        }
        else
        {
            if ($_POST['cmislink'] == '' || $_POST['cmislink'] == 'nothing')
              $cmislink = 'off';       	
            else
              $cmislink = $_POST['cmislink']; 
        }


        $cmisurl = $_POST['cmisurl'];
        $ssosalt= $_POST['ssosalt'];

        if (!isset($_POST["ldap"])) {
            $ldap = 'off';
        }
        else
        {
            $ldap = 'on'; 
        }
        $ldapserver=$_POST['ldapserver'];
        $ldapfailoverserver=$_POST['ldapfailoverserver'];
        $ldapsuffix=$_POST['ldapsuffix'];
        
       if ($_POST['netroot'] == '' || $_POST['netroot'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your Internet root setting should not be blank</li>";
       }





       if ($_POST['neteproot'] == '' || $_POST['neteproot'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your Internet path to ePortfolios should not be blank</li>";
       }

       if ($_POST['localpath'] == '' || $_POST['localpath'] == 'nothing' || (!is_dir($_POST['localpath'])) )
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your chosen local path should exist</li>";
       }



       if ($_POST['portfoliopath'] == '' || $_POST['portfoliopath'] == 'nothing' || (!is_dir($_POST['portfoliopath'])) )
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your chosen portfolio path should exist</li>";
       }

       if ($_POST['netdomain'] == '' || $_POST['netdomain'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your email domain should not be blank</li>";
       }

       if ($_POST['lecturerpass'] == '' || $_POST['lecturerpass'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your default lecturer password should not be blank.</li>";
       }

       if ($_POST['localserver'] == '' || $_POST['localserver'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your local database server name should not be blank</li>";
       }

       if ($_POST['localdb'] == '' || $_POST['localdb'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your local database should not be blank</li>";
       }

       if ($_POST['localuser'] == '' || $_POST['localuser'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your local database user should not be blank</li>";
       }

       if ($_POST['localpass'] == '' || $_POST['localpass'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>Your local database password should not be blank</li>";
       }



   
      if ($misint='checked')
      {
         if ($_POST['misserver'] == '' || $_POST['misserver'] == 'nothing')
         {
           $pagevalid=false;
          $errorcode=$errorcode .  "<li>Your MIS server should not be blank if you have checked MIS integration</li>";
         }

         if ($_POST['misdb'] == '' || $_POST['misdb'] == 'nothing')
         {
           $pagevalid=false;
          $errorcode=$errorcode .  "<li>Your MIS database name should not be blank if you have checked MIS integration</li>";
         }

         if ($_POST['misuser'] == '' || $_POST['misuser'] == 'nothing')
         {
           $pagevalid=false;
          $errorcode=$errorcode .  "<li>Your MIS database username should not be blank if you have checked MIS integration</li>";
         }

         if ($_POST['mispass'] == '' || $_POST['mispass'] == 'nothing')
         {
           $pagevalid=false;
          $errorcode=$errorcode .  "<li>Your MIS database password should not be blank if you have checked MIS integration</li>";
         }


      }
    


  

      if ($cmislink=='on')
      {
         if ($_POST['cmisurl'] == '' || $_POST['cmisurl'] == 'nothing')
         {
           $pagevalid=false;
          $errorcode=$errorcode .  "<li>Your CMIS URL should not be blank if you have checked CMIS Link</li>";
         }
      }
   
      if ($ldap=='on') {
        if ($_POST['ldapserver'] == '' && $_POST['ldapfailoverserver'] == '')
        {
           $pagevalid=false;
          $errorcode=$errorcode .  "<li>Your LDAP servers should not both be blank if you have checked LDAP Link</li>";
        }

      }
   

    if (isset($_POST['student']) && $_POST['student'] == 'test!' && $pagevalid == true)
    {
      $testing='true';
      $student = $_POST['studentid']; 

     

      echo "<p>The MIS system reports that student's name as : <b>";
      echo get_mis_nicename($student);
      echo "</b><br />If this is wrong or unknown, check your MIS connection settings.</p>";
      echo "<p><a href='settings.php'>&larr;&nbsp;back to settings</a></p>\n";

    }

 
?>
  

<?php
       // echo get_mis_nicename($student);

  if (!$testing=='true') {
      if ($pagevalid == true)
      {

       if (substr($localpath,-1) != "/")
          $localpath = $localpath . "/";
      
       if (substr($portfoliopath,-1) != "/")
          $portfoliopath = $portfoliopath . "/";





          create_manager_config_file($netroot, $neteproot, $epsecure, $localpath, $portfoliopath, $netdomain, $lecturerpass, $localserver, $localdb, $localuser, $localpass, $mis, $missystem, $misdbms, $misserver, $misdb, $misuser, $mispass,  $cmislink, $cmisurl, $ssosalt, $ldap, $ldapserver, $ldapfailoverserver, $ldapsuffix);




          echo "<p class='warning'>Settings file successfully saved</p>\n";
          echo "<p><a href='settings.php'>&larr;&nbsp;back</a></p>\n";
      }
      else
      {
        echo $errorcode;
      }
    }
  



}
else

{
?>


     <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
     <fieldset>
       <legend>paths</legend>

       <label for="netroot">Internet path to epmanager (e.g. <b>www.mydomain.com/epmanager</b>)</label>
       <input type="text" name="netroot" size="60" value="<?php echo INTERNET_ROOT; ?>" /><br />

<br />
<?php if (INTERNET_EPSECURE=='on')
   { ?> <input type="checkbox" name="epsecure" CHECKED />Wordpress ePortfolio secure dashboard (you must have SSL correctly configured first!!!) ?<br />
     <?php }
           else
            { ?>
<input type="checkbox" name="epsecure" />Wordpress ePortfolio secure logins ?<br />
     <?php } ?>
<br />

       <label for="neteproot">Internet path to eportfolios (e.g. <b>www.mydomain.com/epmanager/eportfolios</b>)</label>
       <input type="text" name="neteproot" size="60" value="<?php echo INTERNET_EPROOT; ?>" /><br />


       <label for="localpath">Local path to epmanager(e.g. <b>/usr/share/epmanager/</b>)</label>
       <input type="text" name="localpath" size="60" value="<?php echo LOCAL_PATH; ?>" /><br />


       <label for="portfoliopath">Local path to eportfolios (e.g. <b>/usr/share/epmanager/eportfolios/</b>)</label>
       <input type="text" name="portfoliopath" size="60" value="<?php echo PORTFOLIO_PATH; ?>" /><br />



       <label for="netdomain">Email domain ending (e.g. <em>users@</em><b>myinstitution.ac.uk</b>)</label>
       <input type="text" name="netdomain" size="60" value="<?php echo INTERNET_DOMAIN; ?>"/><br />

       <label for="lecturerpass">Default lecturer password (when a new lecturer is added to an eportfolio)</label>
       <input type="password" name="lecturerpass" size="60" value="<?php echo LECTURER_PASS; ?>"/><br />


 </fieldset>

 <fieldset>
      <legend>local MySQL server</legend>
       <label for="localserver">Local MySql server (e.g. <b>localhost</b>)</label>
       <input type="text" name="localserver" size="60" value="<?php echo EP_DB_SERVER; ?>"/><br />

       <label for="localdb">Local MySql database name (e.g. <b>eportfolio</b>)</label>
       <input type="text" name="localdb" size="60" value="<?php echo EP_DB_DATABASE; ?>"/><br />

       <label for="localuser">Local MySql database user (with suitable priv)</label>
       <input type="text" name="localuser" size="60" value="<?php echo EP_DB_USERNAME; ?>"/><br />

       <label for="localpass">Local MySql user's password</label>
       <input type="password" name="localpass" size="60" value="<?php echo EP_DB_PASSWORD; ?>"/><br />

</fieldset>

<fieldset>
    <legend>MIS Integration settings (only works with SITS on MSSQL Server for now)</legend>

<?php if (MIS_INTEGRATION=='on')
   { ?> <input type="checkbox" name="misint" CHECKED />MIS Integration?<br />
     <?php }
           else
            { ?>
<input type="checkbox" name="misint" />MIS Integration?<br />
     <?php } ?>


<select name="missystem">
<?php

       switch (MIS_SYSTEM)
       {
          case "sits":
             echo "<option value='sits' SELECTED>SITS</option>\n";
             echo "<option value='unite'>Unit-E</option>\n";
             echo "<option value='femis'>FEMIS</option>\n";
             break;
          case "unite":
             echo "<option value='sits'>SITS</option>\n";
             echo "<option value='unite' SELECTED>Unit-E</option>\n";
             echo "<option value='femis'>FEMIS</option>\n";
             break;
          case "femis":
             echo "<option value='sits'>SITS</option>\n";
             echo "<option value='unite'>Unit-E</option>\n";
             echo "<option value='femis' SELECTED>FEMIS</option>\n";
             break;
       }
       
	 
?>
</select>


       <select name="misdbms">
<?php

       switch (MIS_DBMS)
       {
          case "mysql":
             echo "<option value='mysql' SELECTED>MySQL</option>\n";
             echo "<option value='mssql'>Microsoft SQL Server</option>\n";
             echo "<option value='oracle'>Oracle</option>\n";
             break;
          case "mssql":
             echo "<option value='mysql'>MySQL</option>\n";
             echo "<option value='mssql' SELECTED>Microsoft SQL Server</option>\n";
             echo "<option value='oracle'>Oracle</option>\n";
             break;
          case "oracle":
             echo "<option value='mysql'>MySQL</option>\n";
             echo "<option value='mssql'>Microsoft SQL Server</option>\n";
             echo "<option value='oracle' SELECTED>Oracle</option>\n";
             break;
       }
       
	 
?>
</select>

       <label for="misserver">MIS data server (e.g. <b>192.168.0.10</b>)</label>
       <input type="text" name="misserver" size="60" value="<?php echo STUDENT_DB_SERVER; ?>"/><br />

       <label for="misdb">MIS database name (e.g. <b>studentdata</b>)</label>
       <input type="text" name="misdb" size="60" value="<?php echo STUDENT_DB_DATABASE; ?>"/><br />

       <label for="misuser">MIS database user (with suitable priv)</label>
       <input type="text" name="misuser" size="60" value="<?php echo STUDENT_DB_USERNAME; ?>"/><br />

       <label for="mispass">MIS database user's password</label>
       <input type="password" name="mispass" size="60" value="<?php echo STUDENT_DB_PASSWORD; ?>"/><br />





<br /><br />

       <label for="studentid">Enter Student ID then click test! to test MIS settings</label>
       <input type="text" name="studentid" size="20" />&nbsp;
      <input type="submit" name="student" value="test!" />&nbsp;<span class='warning'>Save your settings before using this test facility!</span>

</fieldset>


<fieldset>
    <legend>Link to CMIS system for lecturers in student Dashboard</legend>
    
<?php if (CMIS_LINK=='on')
   { ?> <input type="checkbox" name="cmislink" CHECKED />CMIS Link shown?<br />
     <?php }
           else
            { ?>
<input type="checkbox" name="cmislink" />CMIS Link shown?<br />
     <?php } ?>


       <label for="cmisurl">Full URL of CMIS search (e.g. <b>https://www.cmis-system.com/search.php?id=</b>)</label>
       <input type="text" name="cmisurl" size="60" value="<?php echo CMIS_URL; ?>" /><br />

</fieldset>



<fieldset>
    <legend>LDAP settings</legend>
<?php if (MANAGER_LDAP=='on')
    { ?> <input type="checkbox" name="ldap" CHECKED />LDAP enabled?<br />
   <?php }
   else
            { ?>
<input type="checkbox" name="ldap" />LDAP enabled?<br />
     <?php } ?>

   <label for="ldapserver">Preferred domain controller</label>
       <input type="text" name="ldapserver" size="60" value="<?php echo MAN_LDAP_SERVER; ?>"/><br />
   <label for="ldapfailoverserver">Failover domain controller</label>
       <input type="text" name="ldapfailoverserver" size="60" value="<?php echo MAN_LDAP_FAILOVER_SERVER; ?>"/><br />
   <label for="ldapsuffix">LDAP Suffix e.g. @yourdomain.com</label>
       <input type="text" name="ldapsuffix" size="60" value="<?php echo MAN_LDAP_SUFFIX; ?>"/><br />
</fieldset>


 

 <fieldset>
      <legend>Single Sign On Security</legend>
       <label for="ssosalt">SSO SALT Value (put something long and complex here)</label>
       <input type="text" name="ssosalt" size="80" value="<?php echo EP_SSO_SALT; ?>"/><br />
</fieldset>


<fieldset>
   <legend>save settings</legend>
       <input type="submit" name="fetch" value="save settings!" /><br />    

</fieldset>
    
       <input type="hidden" name="action" value="submitted" />

</form>


<?php


}
?> 

        <p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
