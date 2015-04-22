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
<h2>Create ePortfolios by course group</h2>
<p class='info'>On this screen you can retrieve a list of students studying a particular course and create ePortfolios for them. If any students already have an ePortfolio they will be highlighted. If you create ePortfolios for existing students this will overwrite their existing ePortfolio with a new default one.</p>

<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<p class='warning'>There was a problem</p><ul>";


       $courseid = $_POST['courseid'];
       $lecturer = $_POST['lecturer'];
       $session = $_POST['session'];
       $adminpass = $_POST['adminpass'];
       $courseblock = $_POST['courseblock'];
       $courseocc = $_POST['courseocc'];

       $ipchecklecturer = preg_replace('/(\W*)/', '', $lecturer);
       $ipcheckadminpass = preg_replace('/(\W*)/', '', $adminpass);
       $ipcheckadminpass = preg_replace('/(\W*)/', '', $adminpass);
       $ipcheckcourseid = preg_replace('/(\W*)/', '', $courseid);

       if ($_POST['courseid'] == '' || $_POST['courseid'] == 'nothing')
       {
         $pagevalid=false;
        $errorcode=$errorcode . "<li>course id is not valid</li>";
       }

       if ($_POST['lecturer'] == '' || $_POST['lecturer'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>lecturer is not valid</li>";
       }

       if ($_POST['session'] == '' || $_POST['session'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>session is not valid</li>";
       }

       if ($_POST['adminpass'] == '' || $_POST['adminpass'] == 'nothing')
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>admin password is not valid</li>";
       }


       
       if($pagevalid == true)
       {
         $courseid = $ipcheckcourseid;
         $lecturer = $ipchecklecturer;
         $session = $_POST['session'];
         $adminpass = $ipcheckadminpass;
       }
else
{
$errorcode=$errorcode . "</ul>";
$errorcode=$errorcode . "<p><a href='groupcreate.php'>&larr;&nbsp;back</a></p>";
echo $errorcode;


}


 

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch students!' && $pagevalid == true)
    {

     // if this is a fetch student post
     // then show a form with check boxes
     // here. Remember that the course code
     // lecturer and session have to be passed
     // hidden this time.

       
      
        

        
    ?>
<?php if (count_course_student_list($courseid,$courseblock,$courseocc,$session,"all")>0)
            { ?>

     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Select students by holding CTRL + Click. Select groups by SHIFT + Click</legend>
      
      <?php echo "You searched for course: {$courseid} {$courseblock} {$courseocc} \n\r"; ?>
      <?php get_course_student_list($courseid,$courseblock,$courseocc,$session,"all"); ?>
     
      <input type="submit" name="create" value="create portfolios!" />
    
      
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="lecturer" value="<?php echo $lecturer; ?>">
      <input type="hidden" name="courseid" value="<?php echo $courseid; ?>">
      <input type="hidden" name="courseblock" value="<?php echo $courseblock; ?>">
      <input type="hidden" name="courseocc" value="<?php echo $courseocc; ?>">
      <input type="hidden" name="session" value="<?php echo $session; ?>">
      <input type="hidden" name="adminpass" value="<?php echo $adminpass; ?>">

</fieldset>
    </form>

     
      <?php }
           else
           {
             echo "<p class='warning'>no students found</p>";
	     echo "<p><a href='groupcreate.php'>&larr;&nbsp;back</a></p>";

            }




 ?>






    <?php } ?>

    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'create portfolios!')
    {
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

      if ($pagevalid == true)
      {
       
        echo '  <ul>';
        foreach($_POST['studentlist'] as $student) {
         
         create_portfolio($student, $lecturer, $adminpass, $courseid, $courseblock, $courseocc);
         }
        echo '  </ul>';
        echo "<p>ePortfolios created. They will now be included in view by lecturer for " . $lecturer . " and in view by coursecode for " . $courseid . "</p>";
        echo "<p><a href='groupcreate.php'>&larr;&nbsp;back</a></p>";
      }
      else
      {
        echo '<h2>Problem</h2>';
      }

    }
}
else 
{
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Select course parameters</legend>
<label for="courseid">Course ID (e.g. <b>HCOM</b>) and optional block and occurrence (e.g. 1A F0)</label>
   <input class='formleft' type="text" name="courseid" />
   <input class='formsmall' type="text" name="courseblock" />
   <input class='formsmall' type="text" name="courseocc" />

<label for="lecturer">Lecturer (e.g. <b>gillespied</b> for Duncan Gillespie)</label>
    <input class='formleft' type="text" name="lecturer" /><br />

<label for="adminpass">Default admin password for group (for users with no network account pick a password to give to the group) <b>letters and numbers only!!</b></label>
    <input class='formleft' type="text" name="adminpass" /><br />

<label for="session">Session (e.g. <b>2006/7</b>)</label>
    <input class='formleft' type="text" name="session" /><br /><br />
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
