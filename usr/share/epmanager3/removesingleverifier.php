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
<h2>Remove single verifier</h2>
<p class='info'>If a verifier has left or another verifier is taking over a single ePortfolio, use this form to remove a <span style='font-style:oblique;'>single</span> verifier from a student's ePortfolio. </p>
<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<h3>There was a problem</h3><ul>";
     
       $verifier = $_POST['verifier'];
       $student = $_POST['student'];

       
       if ($_POST['verifier'] == '' || $_POST['verifier'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>verifier is not valid</li>";
       }

       if ($_POST['student'] == '' || $_POST['student'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>student id not valid</li>";
       }
      
       if($pagevalid == true)
       {
          
         $verifier = $_POST['verifier'];
         $student = $_POST['student'];
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
   <legend>confirm verifier selection</legend>
     <?php
       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$student'>$student, $verifier to be removed</option>\n\r";
       echo "</select>";
     ?>
     
      <input type="submit" name="create" value="remove verifier!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="verifier" value="<?php echo $verifier; ?>">
      <input type="hidden" name="student" value="<?php echo $student; ?>">
      

</fieldset>
    </form>

    <?php } 
       
     
?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'remove verifier!')
    {

         $verifier = $_POST['verifier'];

 /*   echo '<pre>';
    print_r($_POST);
    echo '<a href="'. $_SERVER['PHP_SELF'] .'">Please try again</a>';
    echo '</pre>'; */

      if ($pagevalid == true)
      {
        
        
        foreach($_POST['studentlist'] as $student) 
        {
          //echo "student : $student , verifier : $verifier" . "<br />";
           delete_ep_verifier($student, $verifier);
        }
        echo "<p>verifier $verifier removed from ePortfolio $student</p>";
        echo "<p><a href='removesingleverifier.php'>&larr;&nbsp;back</a></p>";
      }
      else
      {
        echo "<p>There was an unknown problem.</p>";
        echo "<p><a href='removesingleverifier.php'>&larr;&nbsp;back</a></p>";
      }

    }
}
else 
{
?>
 <fieldset>
   <legend>choose verifier id to remove and student id to remove from</legend>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    
<label for="verifier">verifier ID to remove</label>
    <input type="text" name="verifier" />

<label for="student">student ID to remove from</label>
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
