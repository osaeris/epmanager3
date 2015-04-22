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
<h2>Remove single lecturer</h2>
<p class='info'>If a lecturer has left or another lecturer is taking over a single ePortfolio, use this form to remove a <span style='font-style:oblique;'>single</span> lecturer from a student's ePortfolio. Remember to add the new lecturer on the create course group lecturer page and select the appropriate students.</p>
<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<h3>There was a problem</h3><ul>";


       
       $lecturer = $_POST['lecturer'];
       $student = $_POST['student'];

       
       if ($_POST['lecturer'] == '' || $_POST['lecturer'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>lecturer is not valid</li>";
       }

       if ($_POST['student'] == '' || $_POST['student'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>student id not valid</li>";
       }

       
       
       if($pagevalid == true)
       {
       
         
         $lecturer = $_POST['lecturer'];
         $student = $_POST['student'];
       }

      

       if (check_lecturer_student($lecturer,$student) > 0)
       { 

         if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch student!' && $pagevalid == true)
         {

         // if this is a fetch student post
         // then show a form with check boxes
         // here. Remember that the course code
         // lecturer and session have to be passed
         // hidden this time.

    ?>

     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>confirm lecturer selection</legend>
     <?php
       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$student'>$student, $lecturer to be removed</option>\n\r";
       echo "</select>";
     ?>
     
      <input type="submit" name="create" value="remove lecturer!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="lecturer" value="<?php echo $lecturer; ?>">
      <input type="hidden" name="student" value="<?php echo $student; ?>">
      

</fieldset>
    </form>

    <?php } 
       }
       else
       {
         echo "<p>that lecturer and student combination is false</p>";
        echo "<p><a href='removesinglelecturer.php'>&larr;&nbsp;back</a></p>";
       }
?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'remove lecturer!')
    {

         $lecturer = $_POST['lecturer'];

 /*   echo '<pre>';
    print_r($_POST);
    echo '<a href="'. $_SERVER['PHP_SELF'] .'">Please try again</a>';
    echo '</pre>'; */

      if ($pagevalid == true)
      {
        
        
        foreach($_POST['studentlist'] as $student) 
        {
          //echo "student : $student , lecturer : $lecturer" . "<br />";
           delete_ep_lecturer($student, $lecturer);
        }
        echo "<p>lecturer $lecturer removed from ePortfolio $student</p>";
        echo "<p><a href='removesinglelecturer.php'>&larr;&nbsp;back</a></p>";
      }
      else
      {
        echo "<p>There was an unknown problem.</p>";
        echo "<p><a href='removesinglelecturer.php'>&larr;&nbsp;back</a></p>";
      }

    }
}
else 
{
?>
 <fieldset>
   <legend>choose lecturer id to remove and student id to remove from</legend>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    
<label for="lecturer">lecturer ID to remove</label>
    <input type="text" name="lecturer" />

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
