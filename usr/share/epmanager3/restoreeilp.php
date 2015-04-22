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
<?php
if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{
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

    if ($missingcount==$studentcount) {
        $pagevalid=false;
        $errorcode.="<li>None of the IDs provided have an eportfolio</li>\n\r";    
    }

    $errorcode.="</ul>\n\r"; 


    if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch student!' && $pagevalid == true)
    {
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

        if ($pagevalid==true) {
?>

<h2>Restore eILP posts</h2>
<p class='info'>Select which posts you would like to recover and for which students <b><?php echo $studentid ?></b>. </p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Stage 2 of 2 - Select eILP posts</legend>
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
   <br />
   <input type="checkbox" name="eilposts[ ]" value="21" />&nbsp;
   1st Meeting Goals<br />

   <input type="checkbox" name="eilposts[ ]" value="22" />&nbsp;
   2nd Meeting Goals<br />

   <input type="checkbox" name="eilposts[ ]" value="23" />&nbsp;
   3rd Meeting Goals<br />

   <input type="checkbox" name="eilposts[ ]" value="24" />&nbsp;
   Exit review<br />
   
   <input type="checkbox" name="eilposts[ ]" value="25" />&nbsp;
   Self Assessment Rating<br /><br />

   <input type="submit" name="fetch" value="restore posts!" />
   <input type="hidden" name="action" value="submitted" />
   <input type="hidden" name="studentid" value="<?php echo $studentid; ?>" />
</fieldset>
</form>

<?php
            $errorcode .= "<p><a href='restoreeilp.php'>&larr;&nbsp;start over</a></p>";
            echo $errorcode;
        }
        else
        {
            $errorcode .= "</ul>";
            echo $errorcode;
            echo "<p><a href='restoreeilp.php'>&larr;&nbsp;start over</a></p>";
        }
    }

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'restore posts!') {
   
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
        
        if ((!isset($_POST['eilposts']))) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>No posts selected!</li>";
        }
        
        if ($pagevalid==false) {
            $errorcode.="</ul>";
            $errorcode .= "<p><a href='restoreeilp.php'>&larr;&nbsp;start over</a></p>";
        }
        else
        {      
            $errorcode='';
            $postarray = $_POST['eilposts'];

            $eilpArray = array('21','22','23','24','25');
            $eilpPosts = array('21'=>'1st Meeting Goals','22'=>'2nd Meeting Goals','23'=>'3rd Meeting Goals','24'=>'Exit Review','25'=>'Self Assessment Rating');

            $errorcode .="<p class='info'>The following actions were completed</b></p><ul>";
            
            foreach($_POST['studentlist'] as $student) {
                foreach($postarray as $post) {
                    if (in_array($post,$eilpArray)) {
                        restore_eilp_post($post,$student);
                        $errorcode .="<li>" . $eilpPosts[$post] . " restored for $student</li>";
                    }
                    else
                    {
                        $errorcode .="<li>" . $eilpPosts[$post] . " not restored  for $student</li>";
                    } 
                }
            }
                
            $errorcode .="</ul>";
            $errorcode .="<p><a href='restoreeilp.php'>&larr;&nbsp;restore more eILP posts</a></p>";
                
        }
        echo $errorcode;
    }
}
else //this is the first load content
{
?>
<h2>Restore eILP posts</h2>
<p class='info'>On this screen you can recover lost (or beyond repair!) eILP posts. Enter a single user id or a comma separated list of user ids then decide which post/s to recover on the next screen. </p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Stage 1 of 2 - Select user id</legend>
<label for="studentid">Student ID (e.g. <b>baxters</b>, <b>0300433</b>) or list (<b>baxters,0300433,joebloggs</b>)</label>
   <input class='formleft' type="text" name="studentid" /><br />
   <input type="submit" name="fetch" value="fetch student!" /><br />
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
