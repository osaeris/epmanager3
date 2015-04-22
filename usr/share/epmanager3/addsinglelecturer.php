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
<h2>Add single lecturer</h2>
<p class='info'>If a lecturer has left or another lecturer is taking over a single ePortfolio, use this form to add a <span style='font-style:oblique;'>single</span> lecturer to a student's ePortfolio.</p>
<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here
echo "<p><a href='addsinglelecturer.php'>&larr;&nbsp;back</a></p>";
       $pagevalid=true;
       $errorcode="<p class='warning'>There was a problem</p><ul>";

       $lecturer = $_POST['lecturer'];
       $student = $_POST['student'];

       $ipcheckstudent = preg_replace('[^A-Za-z0-9]', '', $student );
       $ipcheckstudent = str_replace('.', '', $ipcheckstudent );

       $ipchecklecturer = preg_replace('[^A-Za-z0-9]', '', $lecturer );
       $ipchecklecturer = str_replace('.', '', $ipchecklecturer );


       if (trim($_POST['lecturer'] == '') || trim($_POST['lecturer'] == 'nothing'))
       {
         $pagevalid=false;
         echo "<li>lecturer is not valid</li>";
       }

       if ($_POST['student'] == '' || $_POST['student'] == 'nothing')
       {
         $pagevalid=false;
         echo "<li>student id not valid</li>";
       }

       if (check_ep_user($student,$student)==FALSE) {
         $pagevalid=false;
         echo "<li>student does not have an eportfolio</li>";
       }



       if($pagevalid == true)
       {
         $lecturer = $ipchecklecturer;
         $student = $ipcheckstudent;
       }

       if (check_lecturer_student($lecturer,$student) < 1)
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
   <legend>confirm selection</legend>
     <?php
       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$student'>$student, lectured by $lecturer</option>\n\r";
       echo "</select>";
     ?>
     
      <input type="submit" name="create" value="add lecturer!" />
      <?php echo " (password: <b>".LECTURER_PASS."</b>)<br />" ?>
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="lecturer" value="<?php echo $lecturer; ?>">
      <input type="hidden" name="student" value="<?php echo $student; ?>">
      

</fieldset>
    </form>

    <?php } 
       }
       else
       {
         echo "<p>that lecturer and student combination already exists</p>";
         echo "<p><a href='addsinglelecturer.php'>&larr;&nbsp;back</a></p>";
       }
?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'add lecturer!')
    {

         $lecturer = $_POST['lecturer'];

         $ipchecklecturer = preg_replace('[^A-Za-z0-9]', '', $lecturer );
         $ipchecklecturer = str_replace('.', '', $ipchecklecturer );


      if ($pagevalid == true)
      {
        foreach($_POST['studentlist'] as $student) 
        {
           add_ep_lecturer($ipchecklecturer,$student);
        }
       
      }
      else
      {
        echo "<p>There was an unknown problem.</p>";
        echo "<p><a href='addsinglelecturer.php'>&larr;&nbsp;back</a></p>";
      }
    }
}
else 
{
?>
 <fieldset>
   <legend>choose lecturer id to add and student id to add to</legend>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    
<label for="lecturer">lecturer ID to add</label>
    <input type="text" name="lecturer" />

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
