<?php include("functions.php");  ?>
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
<h2>Add single verifier</h2>
<p class='info'>If a verifier has left or another verifier is taking over a single ePortfolio, use this form to add a <span style='font-style:oblique;'>single</span> verifier to a student's ePortfolio.</p>
<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here
echo "<p><a href='addsingleverifier.php'>&larr;&nbsp;back</a></p>";
       $pagevalid=true;
       $errorcode="<p class='warning'>There was a problem</p><ul>";

       $verifier = $_POST['verifier'];
       $student = $_POST['student'];
       $verifierpass = $_POST['verifierpass'];
       $verifierdomain = $_POST['verifierdomain'];       

       $ipcheckstudent = preg_replace('[^A-Za-z0-9]', '', $student );
       $ipcheckstudent = str_replace('.', '', $ipcheckstudent );

       $ipcheckverifier = preg_replace('[^A-Za-z0-9]', '', $verifier );
       $ipcheckverifier = str_replace('.', '', $ipcheckverifier );

       $ipcheckverifierpass = preg_replace('[^A-Za-z0-9]', '', $verifierpass );
       $ipcheckverifierpass = str_replace('.', '', $ipcheckverifierpass );

      // $ipcheckverifierdomain = preg_replace('[^A-Za-z0-9]', '', $verifierdomain );
       $ipcheckverifierdomain= urlencode($verifierdomain);

       if (trim($_POST['verifier'] == '') || trim($_POST['verifier'] == 'nothing'))
       {
         $pagevalid=false;
         echo "<li>verifier is not valid</li>";
       }

       if ($_POST['student'] == '' || $_POST['student'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>student id not valid</li>";
       }

       if ($_POST['verifierpass'] == '' || $_POST['verifierpass'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>verifier password not valid</li>";
       }
       
       if ($_POST['verifierdomain'] == '' || $_POST['verifierdomain'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>verifier domain not valid</li>";
       }       

       if (check_ep_user($student,$student)==FALSE) {
         $pagevalid=false;
         echo "<li>student does not have an eportfolio</li>";
       }



       if($pagevalid == true)
       {
         $verifier = $ipcheckverifier;
         $student = $ipcheckstudent;
         $verifierpass = $ipcheckverifierpass;    
         $verifierdomain = $ipcheckverifierdomain;
       }

    
         if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch student!' && $pagevalid == true)
         {

         // if this is a fetch student post
         // then show a form with check boxes
         // here. Remember that the course code
         // verifier and session have to be passed
         // hidden this time.
         
    ?>

     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>confirm selection</legend>
     <?php
       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$student'>$student, verified by $verifier</option>\n\r";
       echo "</select>";
     ?>
     
      <input type="submit" name="create" value="add verifier!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="verifier" value="<?php echo $verifier; ?>">
      <input type="hidden" name="student" value="<?php echo $student; ?>">
      <input type="hidden" name="verifierpass" value="<?php echo $verifierpass; ?>">
      <input type="hidden" name="verifierdomain" value="<?php echo $verifierdomain; ?>">      
      

</fieldset>
    </form>

    <?php  
       }
     
?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'add verifier!')
    {

         $verifier = $_POST['verifier'];
         $student = $_POST['student'];
         $verifierpass = $_POST['verifierpass'];
         $verifierdomain = $_POST['verifierdomain'];         




      if ($pagevalid == true)
      {
        foreach($_POST['studentlist'] as $student) 
        {
           add_ep_verifier($student,$ipcheckverifier,$verifierpass,$verifierdomain);
           echo "$student,$ipcheckverifier,$verifierpass,$verifierdomain";
           exit;
        }
        echo "<p>verifier $ipcheckverifier added to ePortfolio $student</p>";
        echo "<p><a href='addsingleverifier.php'>&larr;&nbsp;back</a></p>";
      }
      else
      {
        echo "<p>There was an unknown problem.</p>";
        echo "<p><a href='addsingleverifier.php'>&larr;&nbsp;back</a></p>";
      }
    }
}
else 
{
?>
 <fieldset>
   <legend>choose verifier id to add and student id to add to</legend>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">



<label for="verifier">verifier ID to add</label>
    <input type="text" name="verifier" />
    
<label for="verifierpassword">Manual password for verifier</label>
    <input type="text" name="verifierpass" />
    
<label for="verifierdomain">Email domain for verifier (e.g. sqa.org.uk)</label>
    <input type="text" name="verifierdomain" />
    
<label for="student">student ID to add to</label>
    <input type="text" name="student" /><br />

    <input type="submit" name="fetch" value="fetch student!" /><br />    
    
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
