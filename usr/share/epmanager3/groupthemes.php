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

        if (count_course_internal_student_list($coursecode,$courseblock='',$courseocc='')>0 && $pagevalid == true) {
?>

         <!-- PUT STUDENT LIST IN SELECT-OPTION HTML SETUP HERE -->

<h2>Manage group themes</h2>
<p class='info'>Select the students you wish to install the new themes for then proceed to the theme selection page.</p>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Stage 2 of 3 - Select students by holding CTRL + Click. Select groups by SHIFT + Click</legend>
            <?php get_internal_course_student_list($coursecode,$courseblock='',$courseocc=''); ?>

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
           echo "<h2>Manage group themes</h2>";
           echo "<p class='info'>No students found <a href='groupthemes.php'>&larr;&nbsp;start over</a></p>";

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
            $myarray=dirList(LOCAL_PATH . '/approvedthemes/');


?>

<h2>Manage group themes</h2>
<p class='info'>Select the themes to install for these students then click install themes button.</p>


    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Stage 3 of 3 - Select themes by clicking the checkbox next to each theme.</legend>

<?php

   foreach ($myarray as $key => $value) {
        if(substr($value,-3,3)=="png") {
            echo '<img style="width:13%;" src="http://'.INTERNET_ROOT.'/approvedthemes/'.$value.'" height="100" alt="'.$value.' theme" />';
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


<p><a href='groupthemes.php'>&larr;&nbsp;start over</a></p>

<?php


     }
     else
     {// they didn't pick any students!!!!
?>
<h2>Manage group themes</h2>
<p class='info'>You must select some students from the list. </p>
<p><a href='groupthemes.php'>&larr;&nbsp;start over</a></p>

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
                $x=0;//counter
                //clear out the themes folder to start with
                $deletetarget =  PORTFOLIO_PATH.$student . "/wp-content/themes";
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
                   $currentthemetarget=PORTFOLIO_PATH.$student . "/wp-content/themes/" . $currenttheme;

                   $themearray[0][0]=$x;//how many themes are in the array
                   $themearray[$x][1]=$currenttheme;//put the array names into the counter positions (this is repeated for each student which is a bit redundant)                

                   if (is_dir($currentthemeextractdir)) {
                        full_copy($currentthemeextractdir, $currentthemetarget);
                   }
                   else
                   {
                        $archive = new PclZip($currentthemearchive);
                        if ($archive->extract(PCLZIP_OPT_PATH, '/tmp/') == 0) {
                            die("Error : ".$archive->errorInfo(true));
                        }
                        else
                        {
                            full_copy($currentthemeextractdir, $currentthemetarget);
                        }
                   }
                   $x++; 
               }
/*               //everyone gets default theme
               $currenttheme="default";
               $currentthemearchive=LOCAL_PATH . "approvedthemes/default.zip";
               $currentthemeextractdir="/tmp/" . $currenttheme;
               $currentthemetarget=PORTFOLIO_PATH.$student . "/wp-content/themes/" . $currenttheme;
               
                  if (is_dir($currentthemeextractdir)) {
                        full_copy($currentthemeextractdir, $currentthemetarget);
                   }
                   else
                   {
                        $archive = new PclZip($currentthemearchive);
                        if ($archive->extract(PCLZIP_OPT_PATH, '/tmp/') == 0) {
                            die("Error : ".$archive->errorInfo(true));
                        }
                        else
                        {
                            full_copy($currentthemeextractdir, $currentthemetarget);
                        }
                   }*/
           }

            //recap the names of the themes installed
            $themecount= $themearray[0][0];

            $count=0;
            while($count < $themecount+1) {
                echo $themearray[$count][1];
                echo '<br />';
                $count++;
            }

            echo "<b>for students:</b><br />";
            echo "\n\n";

            foreach($students as $student) {
                echo "&nbsp;&nbsp;".$student."<br />";
                echo "\n\n";
            }

            echo "<p><a href='groupthemes.php'>&larr;&nbsp;install more themes</a></p>";
        }
        else
        {// they didn't pick any themes !!!!
?>


<p class='info'>You must select at least one theme. </p>
<p><a href='groupthemes.php'>&larr;&nbsp;start over</a></p>

<?php
           
        }
     }
}
else //this is the first load content
{
?>
<h2>Manage group themes</h2>
<p class='info'>On this screen you can assign a set of themes to a group of eportfolio students by course. This may be useful if you have a construction group and a hairdressing group. Each will most likely want rather different themes. Start by selecting a course code and session. </p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 <fieldset>
   <legend>Stage 1 of 3 - Select course parameters</legend>
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
