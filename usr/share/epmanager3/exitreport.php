<?php include("functions.php"); ?>
<?php session_start(); 
           if($_SESSION['logged']!='true') {
               redirect(0, "login.php");
           }
$themearray=array();
/*
simple list of theme names
*/

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


<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
    $pagevalid=true;
    $errorcode="<h3>There was a problem</h3><ul>";

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch students!' && $pagevalid == true)
    {

// if the page has been posted for the first time then
// a list of students will be expected so get the 
// students for this course and put them in a multiple
// select list

        if (!isset($_POST['courseid'])) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>course code is not valid</li>";
        }

        if ($_POST['courseid'] == '' || $_POST['courseid'] == 'nothing') {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>course code is not valid</li>";
        }

        if ($pagevalid == true) {
            $coursecode = $_POST['courseid'];
            $errorcode = '';
        }

        // if this is a fetch student post
        // then show a form with check boxes
        // count_course_internal only looks to the epmanager database
        // for course involvement not to outside DB

        if (count_course_internal_student_list($coursecode,$courseblock,$courseocc)>0 && $pagevalid == true) {
?>

         <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

<h2>ILP Exit report</h2>
<p class='info'>Select the students you wish to report on.</p>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Stage 2 of 2 - Select students by holding CTRL + Click. Select groups by SHIFT + Click</legend>
            <?php get_internal_course_student_list($coursecode, $courseblock, $courseocc); ?>

            <br />
            <input type="submit" name="fetch" value="get report!" />
            <input type="hidden" name="action" value="submitted" />


        </fieldset>
    </form>

    <?php } 
       else
       {// they couldn't find any students !!!!
           $errorcode .='</ul>';
           echo $errorcode;
           echo "<h2>ILP Exit review</h2>";
           echo "<p class='info'>No students found <a href='exitreport.php'>&larr;&nbsp;start over</a></p>";

       } 

   }




    if (isset($_POST['fetch']) && $_POST['fetch'] == 'get report!' && $pagevalid == true)
    {

// If the user has chosen some students then move
// on to listing the themes available
     

        if ((!isset($_POST['studentlist']))) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>No students were selected!</li>";
        }

        if ($pagevalid == true) {
            $students = $_POST['studentlist'];
          

?>

<h2>ILP Exit report</h2>
<p class='info'>Here are the ILP Exit reviews for selected students.</p>

<?php


$count= count($students);
echo "<div style='font-size:80%;'>\n\r";
echo "<h3>{$count} students</h3>\n\r";
              
           foreach($students as $student) {
              echo "<hr />\n\r";
              $nicename=get_mis_nicename($student);
              echo "<p style='font-size:120%;'><b>{$student}</b>, {$nicename}\n<br />";
              $exitreview=get_exit_review($student);
              echo $exitreview;

            }
echo "</div>\n\r";
            echo "<p><a href='exitreport.php'>&larr;&nbsp;get another report</a></p>";
        }
        else
        {// they didn't pick any themes !!!!
?>


<p class='info'>You must select at least one student. </p>
<p><a href='exitreport.php'>&larr;&nbsp;start over</a></p>

<?php
           
        }
     }
}
else //this is the first load content
{
?>
<h2>Exit Review Report</h2>
<p class='info'>On this screen you can view all of the exit review entries for students on course code provided. </p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Stage 1 of 2 - Select course</legend>
<label for="courseid">Course ID (e.g. <b>HCOM</b>)</label>
   <input class='formleft' type="text" name="courseid" /><br />



    <input type="submit" name="fetch" value="fetch students!" /><br />   
    
    <input type="hidden" name="action" value="submitted" />
</fieldset>
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
