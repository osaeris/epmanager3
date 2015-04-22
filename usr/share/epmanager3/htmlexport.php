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
       <h2>Export an ePortfolio to HTML pages</h2>
       <p class='info'>When you want to move an eportfolio from one institution to another, this export may help. The resulting zip file can also be used on it's own to build your own website. All of your pictures, sounds and wordpress posts are included so you need not lose anything.</p>
       <?php

get_menu();


if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

       $pagevalid=true;
       $errorcode="<h3>There was a problem</h3><ul>";


       $student = $_POST['studentid'];
    
     //  $ipcheckstudent = preg_replace('[^A-Za-z0-9]', '', $student );
     //  $ipcheckstudent = str_replace('.', '', $ipcheckstudent );

       if (trim($_POST['studentid'] == ''))
       {
         $pagevalid=false;
         $errorcode=$errorcode . "<li>student id is not valid</li>";
       }

    if (check_student_ep($student) == false)
         {
           $pagevalid = false;
           $errorcode=$errorcode .  "<li>student not found</li>";
         }
       
       
       
       if($pagevalid == true)
       {
        

         $student = $_POST['studentid'];
         
         
       }


  if($pagevalid == false)
       {
          $errorcode=$errorcode .  "</ul>";
          echo $errorcode;
        echo "<p><a href='htmlexport.php'>&larr;&nbsp;back</a></p>";
       }


    if (isset($_POST['fetch']) && $_POST['fetch'] == 'check student!' && $pagevalid == true)
    {

     // if this is a fetch student post
     // then show a form with single check box
     // here. 

       
        $student = $_POST['studentid'];


        
    ?>
     
     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset>
    <legend>confirm student id to start the process</legend>
     <?php      


       echo "<select name='studentlist[]'>\n\r";
       echo "  <option value='$student'>$student</option>\n\r";
       echo "</select>";
?>
      <input type="submit" name="backup" value="backup portfolio!" />
      <input type="hidden" name="action" value="submitted" />

      <input type="hidden" name="studentid" value="<?php echo $student; ?>">
     
</fieldset>

    </form>

    <?php } ?>

    <?php

    if (isset($_POST['backup']) && $_POST['backup'] == 'backup portfolio!')
    {
     // If the page gets this far its
     // time to run the delete portfolio
     // function

      if (($pagevalid == true) && (check_student_ep($student) == true))
      {
        echo '<h2>Creating HTML export of ePortfolio!</h2>';
         echo '<p>Your zipped download should start shortly!</p>';
        



        foreach($_POST['studentlist'] as $student) {
            
            html_export($student);
         }
        echo "<p><a href='htmlexport.php'>&larr;&nbsp;back up another!</a></p>";
      }
      else
      {
        echo 'there was a problem';
        
      }

    }
}
else 
{
?>


    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset>
    <legend>enter a student id</legend>


    <label for="studentid">student ID</label>
    <input type="text" name="studentid" /><br />
      <input type="submit" name="fetch" value="check student!" />
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
