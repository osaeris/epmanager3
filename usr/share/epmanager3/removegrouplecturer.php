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
<h2>Remove group lecturer</h2>
<p class='info'>On this form you can remove a lecturer from a group of students. Use this in conjunction with add group lecturer to change the lecturer for a particular group. If you select a lecturer who is already attached to an ePortfolio, no change will be made to that ePortfolio.</p>
<?php
get_menu(); 
if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<p class='warning'>There was a problem</p><ul>";


       $coursecode = $_POST['cid'];

       $courseblock = $_POST['courseblock'];
       $courseocc = $_POST['courseocc'];

       $lecturer = $_POST['lecturer'];
       $session = $_POST['session'];

       $ipcheckcourse = preg_replace('[^A-Za-z0-9]', '', $coursecode );
       $ipcheckcourse = str_replace('.', '', $ipcheckcourse );

       $ipchecklecturer = preg_replace('[^A-Za-z0-9]', '', $lecturer );
       $ipchecklecturer = str_replace('.', '', $ipchecklecturer );

       if (trim($_POST['cid'] == '') || trim($_POST['cid'] == nothing))
       {
         $pagevalid=false;
        $errorcode=$errorcode . "<li>course code is not valid</li>";
       }

       if (trim($_POST['lecturer'] == '') || trim($_POST['lecturer'] == nothing))
       {
         $pagevalid=false;
         $errorcode=$errorcode .  "<li>lecturer is not valid</li>";
       }


       
       
       if($pagevalid == true)
       {
         

         $coursecode = $ipcheckcourse;
         $lecturer = $ipchecklecturer;



  
//         if (count_course_student_list($coursecode,$session,"existing")>0)  
         if (count_course_internal_student_list($coursecode,$courseblock,$courseocc)>0)  
         {

            if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch students!' && $pagevalid == true)
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
   <legend>Select students by holding CTRL + Click. Select groups by SHIFT + Click</legend>

     
      <?php 

       get_internal_course_student_list($coursecode,$courseblock,$courseocc); ?><br />

     
      <input type="submit" name="create" value="remove lecturer!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="lecturer" value="<?php echo $lecturer; ?>">
      <input type="hidden" name="cid" value="<?php echo $coursecode; ?>">
</fieldset>
    </form>


<?php      }

         }
        else
        {
          echo "<p class='warning'>no students found</p>";
          echo "<p><a href='removegrouplecturer.php'>&larr;&nbsp;back</a></p>";
        }
       }
     else
     {
         $errorcode=$errorcode . "</ul>";
         $errorcode=$errorcode . "<p><a href='removegrouplecturer.php'>&larr;&nbsp;back</a></p>";
         echo $errorcode;
     }
?>


    <?php

    if (isset($_POST['create']) && $_POST['create'] == 'remove lecturer!')
    {



      if ($pagevalid == true)
      {
        
        
        foreach($_POST['studentlist'] as $student) 
        {
          //echo "student : $student , lecturer : $lecturer" . "<br />";
           //check for association first
           if (check_lecturer_student($lecturer, $student) > 0)
               delete_ep_lecturer($student,$lecturer);
        }

        echo "<p>Lecturer $lecturer removed successfully.</p>";
        echo "<p><a href='removegrouplecturer.php'>&larr;&nbsp;back</a></p>";
      }
    

    }
}
else 
{
?>


     <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
     <fieldset>
       <legend>confirm the course and lecturer</legend>
      <label for="cid">course code (e.g. <b>HCOM</b>), Block (e.g. <b>1A</b>), Occurrence (e.g. <b>F0</b>)</label>
      <input type="text" name="cid" />
      <input type="text" name="courseblock" size="2" />
      <input type="text" name="courseocc" size="2" />

      <label for="lecturer">lecturer username (e.g. <b>bloggsj</b>)</label>
      <input type="text" name="lecturer" /><br />

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
