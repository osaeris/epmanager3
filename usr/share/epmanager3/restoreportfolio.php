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
      <h2>Restore a portfolio</h2>
       <p class='info'>Use this form to restore a previously saved ePortfolio from disk. EPManager allows ePortfolios to be transferred between installations by storing users files and data in a ZIP archive.</p>
<?php



  if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
  {

    $target_path = "/tmp/";
    $student = $_POST['studentid'];
    $oldstudent = $_POST['oldstudentid'];    
    $lecturer = $_POST['lecturerid'];


    if (check_student_ep($student)<1)
    {

      $target_path = $target_path . "epmanager_" . $student . ".zip";

      if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) 
      {

        restore_backup($student,$oldstudent,$lecturer);
        
       
          echo "<p>Restore was successfull. The ePortfolio should be available at <a href='http://" . INTERNET_EPROOT . "/" . $student . "'>" . $student . "</a></p>";
          echo "<a href='restoreportfolio.php'>&larr;&nbsp;restore another</a></p>";   
        
      
      }
      else
      {
        echo "<p>There was an error uploading the file.</p>";
        echo "<a href='restoreportfolio.php'>&larr;&nbsp;back</a></p>";
      }
    }
    else
    {
      echo "<p class='warning'>That student already has an ePortfolio. Delete the existing one first.</p>";
      echo "<a href='restoreportfolio.php'>&larr;&nbsp;back</a></p>";
    }
  }
  else
  {
?>


       <form enctype="multipart/form-data" action="restoreportfolio.php" method="POST">
       <fieldset>
        <legend>select your zip file and confirm the student ID and lecturer ID you wish to assign</legend>
         <input type="hidden" name="MAX_FILE_SIZE" value="10000000000" />
   
         <label for="uploadedfile">choose file</label>
           <input name="uploadedfile" type="file" /><br />
  
         <label for="studentid">student id</label>
           <input type="text" name="studentid" /><br />
           
         <label for="oldstudentid">old student id (from previous site)</label>
           <input type="text" name="oldstudentid" /><br />           
    
         <label for="lecturerid">lecturer id</label>
           <input type="text" name="lecturerid" /><br />
    
         <input type="hidden" name="action" value="submitted" />
         <input type="submit" value="Upload File" />
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