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
<h2>Create a single student ePortfolio</h2>
<p class='info'>Use this screen to create a single ePortfolio for either a student or a member of staff. The student ID and lecturer ID you use are not constrained so you can use your own institutions username style. </p>


<?php
   
if (isset($_POST['action']) && $_POST['action'] == 'submitted')  {
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

    $pagevalid=true;
    $errorcode="<p class='warning'>There was a problem</p><ul>";

    $student = ($_POST['studentid']);
    $lecturer = ($_POST['lecturer']);
    $adminpass= ($_POST['adminpass']);
    $courseid= ($_POST['courseid']);
    $courseblock=($_POST['courseblock']);
    $courseocc=($_POST['courseocc']);

    $ipcheckstudent = StripIllegalCharacters($student);
    $ipchecklecturer = StripIllegalCharacters($lecturer);
    $ipcheckadminpass = StripIllegalCharacters($adminpass);
    $ipcheckcourseid = StripIllegalCharacters($courseid);
    

    if (trim($ipcheckstudent == '') || trim($ipcheckstudent == 'nothing')) {
        $pagevalid=false;
        $errorcode .= "<li>student id is not valid</li>";
    }

    if (trim($ipchecklecturer == '') || trim($ipchecklecturer == 'nothing')) {
        $pagevalid=false;
        $errorcode .= "<li>lecturer is not valid</li>";
    }

    if (trim($ipcheckadminpass == '') || trim($ipcheckadminpass == 'nothing')) {
        $pagevalid=false;
        $errorcode .= "<li>password is not valid</li>";
    }

    if (trim($ipcheckcourseid == '') || trim($ipcheckcourseid == 'nothing')) {
        //this is valid, course id is optional
        $courseid=NULL;
    }



    if($pagevalid == true) {
        $errorcode= "";
        $student = $ipcheckstudent;
        $lecturer = $ipchecklecturer;
        $adminpass = $ipcheckadminpass;
        $courseid = $ipcheckcourseid;
    }
    else
    { 
        $errorcode .= "</ul>";
        $errorcode .= "<p><a href='singlecreate.php'>&larr;&nbsp;back</a></p>";
    }

    echo $errorcode;

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'check student!' && $pagevalid == true) {
    // if this is a fetch student post
    // then show a form with single check box     
    // here. 

        $student = $ipcheckstudent;
        $lecturer = $ipchecklecturer;
        $adminpass = $ipcheckadminpass;

        if (check_student_ep($student) > 0) {
            echo "<p class='warning'>This student already has an ePortfolio. Proceeding will reset student to default ePortfolio. Make sure you have a backup of this ePortfolio before proceeding!</p>"; 
        }
    ?>
     
     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

 <fieldset>
   <legend>confirm the student ID and lecturer username</legend>
<?php      
       echo "<p><b>Course:</b> {$courseid}:{$courseblock}:{$courseocc}</p>\n\r";

       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$student'>$student, lectured by $lecturer</option>\n\r";
       echo "</select>";
?>
      <input type="submit" name="create" value="create portfolio!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="studentid" value="<?php echo $student; ?>">
      <input type="hidden" name="lecturer" value="<?php echo $lecturer; ?>">
      <input type="hidden" name="adminpass" value="<?php echo $adminpass; ?>">
      <input type="hidden" name="courseid" value="<?php echo $courseid; ?>">
      <input type="hidden" name="courseblock" value="<?php echo $courseblock; ?>">
      <input type="hidden" name="courseocc" value="<?php echo $courseocc; ?>">

</fieldset>

    </form>


<p><a href='singlecreate.php'>&larr;&nbsp;back</a></p>
<?php 

    }

 ?>

 <?php

    if (isset($_POST['create']) && $_POST['create'] == 'create portfolio!') {
    // If the page gets this far its
    // time to dust off those HUGE sql
    // statements and create some portfolios
    // 1. check for the existence of the eP first
    // in the ep_students table. If not existing
    // 2. then create the default entries in wordpress database remembering
    // the lecturer entry. 3. Create an entry in ep_student_lecturer too.
    // 4. then do the php create a folder thingy
    // write out a new wp-config.php solely for
    // current student and 5. copy the rest of the
    // wordpress files over to /var/www/wordpress/joebloggs

        if ($pagevalid == true) {
            echo "<p class='warning'>ePortfolio created for</p>";
            echo "<ul>";

            echo "  <li>student id:     <b>" . $student . "</b></li>";
            echo "  <li>lecturer id:    <b>" . $lecturer . "</b></li>";
            echo "  <li>password: <b>" . $adminpass . "</b></li>";
            echo "  <li>course:         <b>" . $courseid . "</b></li>";
            echo "  <li>course block:   <b>" . $courseblock . "</b></li>";
            echo "  <li>course occurrence:  <b>" . $courseocc . "</b></li>";

            foreach($_POST['studentlist'] as $student) {
                create_portfolio($student, $lecturer, $adminpass, $courseid, $courseblock, $courseocc );
                echo "<li>url: <a href='http://". INTERNET_EPROOT . "/" . $student . "'>" . $student . "</a>";
            }

            echo "\n";
            echo "</ul>";
            echo "<p><a href='singlecreate.php'>&larr;&nbsp;back</a></p>";
        }
    }
}
else 
{
?>

<p><span class='warning'>Don't use any exotic characters in usernames or passwords (;'!"£$%^# etc) (they will be removed anyway!)</span></p>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
        <legend>Enter student ID and lecturer ID</legend>

            <label for="studentid">student ID</label>
            <input  class='formleft' type="text" name="studentid" id="studentid" /><br />

            <label for="lecturer">lecturer ID</label>
            <input  class='formleft' type="text" name="lecturer" id="lecturer" /><br />
 
            <label for="adminpass">password for this user (for users with single sign on account pick a password)</label>
            <input  class='formleft' type="text" name="adminpass" id="adminpass" /><br />

            <label for="courseid">(optional) enter the course for this student (e.g. HCOM) and optional block and occurrence (e.g. 1A F0)</label>
            <input class='formleft' type="text" name="courseid" id="courseid" />
            <input class='formsmall' type="text" name="courseblock" />
            <input class='formsmall' type="text" name="courseocc" />

            <input type="submit" name="fetch" value="check student!" /><br />    
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
