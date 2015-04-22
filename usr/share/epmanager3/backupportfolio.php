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
       <h2>Backup ePortfolios</h2>
       <p class='info'>When you want to move an eportfolio from one EPManager system to another, save the ePortfolio to disk, network share or USB stick using this page then use the restore eportfolio facility on the target system.</p>
       
       <p class='info'>To create a single archive file containing several eportfolio backups enter a comma separated list. Please note that selecting a large number of eportfolios may result in a timeout error. It is recommended to backup no more than 10 eportfolios at one time.</p>
       
       
       <?php

get_menu();
global $errorcode;

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

    $errorcode="<p class='warning'>There was a problem</p><ul>";
    $pagevalid=true;
    
    $missingarray=array();
    $studentarray=array();
 
    $student = $_POST['studentid'];

    if(stristr($student,',')==true) {
        $studentarray=explode(',',$student);
        foreach ($studentarray as $item) {
            if (check_student_ep($item)==false) {
                array_push($missingarray,$item);
            }
        }          
    }
    else
    {
        $studentarray=$student;
    }
    
 

    $missingcount=count($missingarray);
    $studentcount=count($studentarray);
    
    if (!isset($_POST['studentid']) || $_POST['studentid'] == null) {
        $pagevalid=false;
        $errorcode.="<li>You must provide at least one student ID</li>\n\r";
    }

    if ($missingcount==$studentcount) {
        $pagevalid=false;
        $errorcode.="<li>None of the IDs provided have an eportfolio</li>\n\r";    
    }

    $errorcode.="</ul>\n\r";



    if (isset($_POST['fetch']) && $_POST['fetch'] == 'check student!' && $pagevalid == true)
    {
       
     // if this is a fetch student post
     // then show a form with single check box
     // here. 
   
    
     if($missingcount > 0) {
         $errorcode= "<p>The following IDs do not have an ePortfolio and have been skipped:</p><ul>\n\r ";
         foreach ($missingarray as $item) {
            $errorcode.="    <li>$item</li>\n\r";
         }  
         $errorcode.="</ul>\n\r";
     }
     else
     {
         $errorcode='';
     }
    


          
    ?>
     
     <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset>
    <legend>confirm student id/s to start the backup process</legend>
    <select  class='formleft' multiple name='studentlist[]'>
<?php   
    if(count($studentarray)==1) {
         echo "            <option value='$studentarray' selected>$studentarray</option>\n\r";
    }
    else
    {
        foreach ($studentarray as $item) {  
            if(check_student_ep($item)==true) {
                echo "            <option value='$item'>$item</option>\n\r";
            }
        }
    }
?>    
    </select>

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

    
        $errorcode= '<h2>Backing up ePortfolios!</h2>';
        $errorcode.= '<p>There was a problem</p>';

       $pagevalid=true;
    
        if(isset($_POST['studentlist'])) {
            $studentarray=$_POST['studentlist'];
            if(count($studentarray)==0) {
               $pagevalid=false;
               $errorcode.="<li>No students selected</li>\n\r";
            }
        }
        else
        {
               $pagevalid=false;
               $errorcode.="<li>No students provided</li>\n\r";       
        }

        if ($pagevalid==false) {
           $errorcode.="</ul>";
        }
        else
        {
            $count=count($studentarray);
            if ($count==1) {
              make_backup($studentarray,"single");
              $errorcode="<p>Backup completed. Your file will be delivered shortly</p>\n\r";
            }
            else
            {
              make_backup($studentarray,"multi");
              $errorcode="<p>Backup completed. Your file will be delivered shortly. If you have selected a lot of eportfolios this may take some time.</p>\n\r";
            }
            
            
        }

      
    }
}
else 
{
?>


    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset>
    <legend>Enter a student id or comma separated list</legend>


    <label for="studentid">student ID</label>
    <input type="text" id="studentid" name="studentid" /><br />
      <input type="submit" name="fetch" value="check student!" />
      <input type="hidden" name="action" value="submitted" />
</fieldset>
    </form>

<?php
}
echo $errorcode;

if (isset($_POST['action'])) {
    echo "<p><a href='backupportfolio.php'>&larr;&nbsp;go back</a></p>\n\r";
}
?> 
<p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
