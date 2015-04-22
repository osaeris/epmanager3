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
    $pagevalid=true;
    $errorcode="<h3>There was a problem</h3><ul>";

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'fetch student!')
    {

// if the page has been posted for the first time then
// a list of students will be expected so get the 
// students for this course and put them in a multiple
// select list

       $student = $_POST['student'];
       $ipcheckstudent = preg_replace('[^A-Za-z0-9]', '', $student );
       $ipcheckstudent = str_replace('.', '', $ipcheckstudent );

        if (!isset($_POST['student'])) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>student id is not valid</li>";
        }


        if ($pagevalid == true) {
            $coursecode = $ipcheckstudent;
            $errorcode = '';
        }

        // if this is a fetch student post
        // then show a form with check boxes
        // count_course_internal only looks to the epmanager database
        // for course involvement not to outside DB

        if (check_student_ep($student)>0 && $pagevalid == true) {
            if (MIS_INTEGRATION=='on') {
                $studentnicename=get_mis_nicename($student);
            }
            else
            {
                $studentnicename=get_local_nicename($student);
            }
?>

         <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

<h2>Manage group themes</h2>
<p class='info'>Check the student you wish to install the new themes for then proceed to the theme selection page.</p>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Stage 2 of 3 - Check student details</legend>
            <select  class='formleft' name='studentlist[]'>
              <option value='<?php echo $student ?>'><?php echo $student . ',' . $studentnicename ?></option>
</select>

            <br />
            <input type="submit" name="fetch" value="list themes!" />
            <input type="hidden" name="action" value="submitted" />


        </fieldset>
    </form>

    <?php } 
       else
       {// they couldn't find any students !!!!
           $errorcode .='</ul>';
           echo $errorcode;
           echo "<h2>Manage student themes</h2>";
           echo "<p class='info'>No student found <a href='singletheme.php'>&larr;&nbsp;start over</a></p>";

       } 

   }




    if (isset($_POST['fetch']) && $_POST['fetch'] == 'list themes!' && $pagevalid == true)
    {

// If the user has chosen some students then move
// on to listing the themes available
     

        if ((!isset($_POST['studentlist']))) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>No students were selected!</li>";
        }

        if ($pagevalid == true) {
            $students = $_POST['studentlist'];
            $studentser = implode('-',$students); 
            $themefolder=LOCAL_PATH . '/approvedthemes/';
            $myarray=dirList($themefolder);
       



?>

<h2>Manage group themes</h2>
<p class='info'>Select the themes to install for these students then click install themes button.</p>


    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Stage 3 of 3 - Select themes by clicking the checkbox next to each theme.</legend>

<?php

   foreach ($myarray as $key => $value) {
        if(substr($value,-3,3)=="png") {
            echo '<img width="27%" src="http://'.INTERNET_ROOT.'/approvedthemes/'.$value.'"  alt="'.$value.' theme" />';
            echo '<input type="checkbox" name="themes[ ]" value="'.$value.'" />';
            echo "\n\n"; 
        }

    }
?>
           
            <br />

            <input type="hidden" name="action" value="submitted" />
            <input type="hidden" name="studentserial" value="<?php echo $studentser; ?>" />
            <input type="submit" name="fetch" value="install themes!" />

        </fieldset>
    </form>


<p><a href='singletheme.php'>&larr;&nbsp;start over</a></p>

<?php


     }
     else
     {// they didn't pick any students!!!!
?>
<h2>Manage group themes</h2>
<p class='info'>You must select some students from the list. </p>
<p><a href='singletheme.php'>&larr;&nbsp;start over</a></p>

<?php
        
     }


  }



    if (isset($_POST['fetch']) && $_POST['fetch'] == 'install themes!' && $pagevalid == true)
    {
?>

<h2>Manage group themes</h2>

<?php
        if ((!isset($_POST['studentserial']))) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>No students were selected!</li>";
        }


        if ((!isset($_POST['themes']))) {
            $pagevalid=false;
            $errorcode=$errorcode . "<li>No themes selected!</li>";
        }


        if ($pagevalid == true) {
            $stuarray = $_POST['studentserial'];
            $students = explode('-',$stuarray); 

            $themenames = $_POST['themes'];
            // Note that $themenames will be an array.

            echo '<p class="info">The following actions have been completed:</p>';
            echo "\n\n";

            echo "<b>installed themes:</b><br />";
            echo "\n\n";

            foreach($students as $student) {

                //clear out the themes folder to start with
                $deletetarget =  PORTFOLIO_PATH.'/'.$student . "/wp-content/themes";
                if (is_dir($deletetarget)) {
                    full_rmdir($deletetarget);
                }
                //recreate the empty theme folder
                if (!is_dir($deletetarget)) {
                    mkdir($deletetarget);
                }

                //for each theme extract to temp if not done already
                foreach ($themenames as $s) {

                   $currenttheme=substr($s,-strlen($s),strlen($s)-4);
                   $currentthemearchive=LOCAL_PATH . "/approvedthemes/" . substr($s,-strlen($s),strlen($s)-4) . ".zip";
                   $currentthemeextractdir="/tmp/" . $currenttheme;
                   $currentthemetarget=PORTFOLIO_PATH.'/'.$student . "/wp-content/themes/" . $currenttheme;
                   echo $currenttheme . '<br />';

                   if (is_dir($currentthemeextractdir)) {
                        full_copy($currentthemeextractdir, $currentthemetarget);
                   }
                   else
                   {
                        $archive = new PclZip($currentthemearchive);

                        if ($archive->extract(PCLZIP_OPT_PATH, '/tmp/') == 0) {
//                            die("Error : ".$archive->errorInfo(true));
                        }
                        else
                        {
  //                       echo "full copy 4<br />";
                            full_copy($currentthemeextractdir, $currentthemetarget);
                        }
                   }
               }
               //everyone gets default theme
               $defaultthemearray=array("twentytwelve","twentythirteen","twentyfourteen");
               foreach ($defaultthemearray as $s) {

                   
                   $currentthemearchive=LOCAL_PATH . "/approvedthemes/$s.zip";
                   $currentthemeextractdir="/tmp/" . $s;
                   $currentthemetarget=PORTFOLIO_PATH.'/'.$student . "/wp-content/themes/" . $s;
                   echo $s . '<br />';

                   if (is_dir($currentthemeextractdir)) {
                        full_copy($currentthemeextractdir, $currentthemetarget);
                   }
                   else
                   {
                        $archive = new PclZip($currentthemearchive);
                        if ($archive->extract(PCLZIP_OPT_PATH, '/tmp/') == 0) {
                          //  die("Error : ".$archive->errorInfo(true));
                        }
                        else
                        {
                            full_copy($currentthemeextractdir, $currentthemetarget);
                        }
                   }
               }              

           }
        

            echo "<b>for students:</b><br />";
            echo "\n\n";

            foreach($students as $student) {
                echo "&nbsp;&nbsp;".$student."<br />";
                echo "\n\n";
            }

            echo "<p><a href='singletheme.php'>&larr;&nbsp;install more themes</a></p>";
        }
        else
        {// they didn't pick any themes !!!!
?>


<p class='info'>You must select at least one theme. </p>
<p><a href='singletheme.php'>&larr;&nbsp;start over</a></p>

<?php
           
        }
     }
}
else //this is the first load content
{
?>
<h2>Manage group themes</h2>
<p class='info'>On this screen you can assign a set of themes to a student individually.</p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Stage 1 of 3 - Select student</legend>
<label for="student">Student ID (e.g. <b>baxters</b>)</label>
   <input class='formleft' type="text" name="student" /><br />



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
