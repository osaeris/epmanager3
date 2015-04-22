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
<h2>Add single course</h2>
<p class='info'>If a student has switched course e.g. from HCOM to DCOS or has more than one course, use this form to add a <span style='font-style:oblique;'>single</span> course relationship to a student's ePortfolio. If this is to be the students only course relationship, check the 'delete other course references' box. </p>
<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<p class='warning'>There was a problem</p><ul>";

       $courseblock='';
       $courseoccurrence='';
       
       $courseid = $_POST['courseid'];
       $student = $_POST['student'];

     if (isset($_POST["courseblock"])) {
         $courseblock = $_POST['courseblock'];
     }

     if (isset($_POST["courseoccurrence"])) {
         $courseoccurrence = $_POST['courseoccurrence'];
     }

       $ipcheckcourseid = preg_replace('[^A-Za-z0-9]', '', $courseid );
       $ipcheckcourseid = str_replace('.', '', $ipcheckcourseid );

       $ipcheckcourseblock = preg_replace('[^A-Za-z0-9]', '', $courseblock );
       $ipcheckcourseblock = str_replace('.', '', $ipcheckcourseblock );

       $ipcheckcourseoccurrence = preg_replace('[^A-Za-z0-9]', '', $courseoccurrence );
       $ipcheckcourseoccurrence = str_replace('.', '', $ipcheckcourseoccurrence );

       $ipcheckstudent = preg_replace('[^A-Za-z0-9]', '', $student );
       $ipcheckstudent = str_replace('.', '', $ipcheckstudent );


     if (!isset($_POST["clearcourses"]))
       $clearcourses = 'unchecked';
      else
       $clearcourses = 'checked'; 



       if (trim($_POST['courseid'] == '') || trim($_POST['courseid'] == 'nothing'))
       {
         $pagevalid=false;
         echo "<li>course code is not valid</li>";
       }

       if (trim($_POST['student'] == '') || trim($_POST['student'] == 'nothing'))
       {
         $pagevalid=false;
         echo "<li>student id not valid</li>";
       }

       
       if($pagevalid == true)
       {
         $courseid = $ipcheckcourseid;
         $courseblock = $ipcheckcourseblock;
         $courseoccurrence = $ipcheckcourseoccurrence;         
         $student = $ipcheckstudent;
       }



       if (check_course_student($courseid,$student) < 1)
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
       echo "  <option value='$student'>$student, to $courseid $courseblock $courseoccurrence";
       if ($clearcourses=='checked') {
         echo " - removing all other courses ";
       }
       else
       {
         echo " - keeping existing course references ";
       }
       echo "</option>\n\r";
       echo "</select>";
     ?>
     
      <input type="submit" name="create" value="add course!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="courseid" value="<?php echo $courseid; ?>">
      <input type="hidden" name="courseblock" value="<?php echo $courseblock; ?>">      
      <input type="hidden" name="courseoccurrence" value="<?php echo $courseoccurrence; ?>">
      
      <input type="hidden" name="student" value="<?php echo $student; ?>">
      <input type="hidden" name="clearcourses" value="<?php echo $clearcourses; ?>">
      

</fieldset>
    </form>

    <?php } 
       }
       else
       {
         echo "<p>that course and student combination already exists</p>";
         echo "<p><a href='addsinglecourse.php'>&larr;&nbsp;back</a></p>";
       }
?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'add course!')
    {
    
         $courseid = $_POST['courseid'];
         $clearcourses = $_POST['clearcourses'];
         
         if (isset($_POST["courseblock"])) {
             $courseblock = $_POST['courseblock'];
         }

         if (isset($_POST["courseoccurrence"])) {
             $courseoccurrence = $_POST['courseoccurrence'];
         }

         $ipcheckcourseid = preg_replace('[^A-Za-z0-9]', '', $courseid );
         $ipcheckcourseid = str_replace('.', '', $ipcheckcourseid );
         
         $ipcheckcourseblock = preg_replace('[^A-Za-z0-9]', '', $courseblock );
         $ipcheckcourseblock = str_replace('.', '', $ipcheckcourseblock );

         $ipcheckcourseoccurrence = preg_replace('[^A-Za-z0-9]', '', $courseoccurrence );
         $ipcheckcourseoccurrence = str_replace('.', '', $ipcheckcourseoccurrence );         
         

  /*  echo '<pre>';
    print_r($_POST);
    echo '<a href="'. $_SERVER['PHP_SELF'] .'">Please try again</a>';
    echo '</pre>';
*/
      if ($pagevalid == true)
      {
        
        
        foreach($_POST['studentlist'] as $student) 
        {
          //echo "student : $student , lecturer : $lecturer" . "<br />";
          if ($clearcourses=='checked') {
             //echo 'should be deleting all now';
             delete_ep_studentcourses($student,$ipcheckcourseid,0);
           }

           add_ep_course($student, $ipcheckcourseid, $ipcheckcourseblock, $ipcheckcourseoccurrence);

        }
        echo "<p>course $courseid added to ePortfolio $student</p>";
        echo "<p><a href='addsinglecourse.php'>&larr;&nbsp;back</a></p>";
      }
      else
      {
        echo "<p>There was an unknown problem.</p>";
        echo "<p><a href='addsinglecourse.php'>&larr;&nbsp;back</a></p>";
      }

    }
}
else 
{
?>
 <fieldset>
   <legend>choose course, optional block and occurrence and student id</legend>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">


<label for="student">Student ID</label>
<input class='formleft' type='text' name='student' />

<label for="courseid">course (e.g. HCOM) and optional block and occurrence (e.g. 1A F0)</label>
    <input class='formleft' type='text' name='courseid' />
       <input class='formsmall' type="text" name="courseblock" />
   <input class='formsmall' type="text" name="courseoccurrence" />


<br />
<input type="checkbox" name="clearcourses" CHECKED>Clear other course references?<br />
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
