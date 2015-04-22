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
        <h2>Delete ePortfolios</h2>
        <p class='info'>Delete a single ePortfolio or comma separated list of ePortfolios using this form.</p>
<?php
$errorcode='';

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here
 /*   echo '<pre>';
    print_r($_POST);
    echo '<a href="'. $_SERVER['PHP_SELF'] .'">Please try again</a>';
    echo '</pre>';*/
    
    
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
    


    if (isset($_POST['fetch']) && $_POST['fetch'] == 'check student!' && $pagevalid == true) {
     // if this is a fetch student post
     // then show a form with check list
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
            <legend>Select ePortfolios with CTRL+Click select groups with SHIFT+Click</legend>
      
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

            <input type="submit" name="delete" value="delete portfolio!" />
            <input type="hidden" name="action" value="submitted" />
            <input type="hidden" name="studentid" value="<?php echo $student; ?>">
        </fieldset>
    </form>



    <?php 
    
    }
   
    
    ?>

    <?php

    if (isset($_POST['delete']) && $_POST['delete'] == 'delete portfolio!') {
    // If the page gets this far its
    // time to run the delete portfolio
    // function
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
            foreach($_POST['studentlist'] as $student) {
                delete_portfolio($student);
            }
            $errorcode="<p>Delete completed.</p>\n\r";
        }
    }
}
else 
{
?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
       <fieldset>
           <legend>enter the student ID e.g. 0300566 or list e.g. 0300566,0300222,0200544</legend>
           <label for="studentid">student ID/s</label>
           <input type="text" id="studentid" name="studentid" /><br />
           <input type="submit" name="fetch" value="check student!" /><br />    
           <input type="hidden" name="action" value="submitted" />
       </fieldset>
    </form>


<?php
}
echo $errorcode;

if (isset($_POST['action'])) {
    echo "<p><a href='singledelete.php'>&larr;&nbsp;go back</a></p>\n\r";
}

?> 


        <p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
