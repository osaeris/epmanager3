<?php session_start(); ?>
<?php include("functions.php"); ?>
<?php getHeader(); ?>
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
<h2>View ePortfolios by course code</h2>
<p class='info'>This form allows lecturers to list all student eportfolios by course code.</p>
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
    $courseblock = $_POST['courseblock'];
    $courseocc = $_POST['courseocc'];
    
    $ipcheckcourse = preg_replace('[^A-Za-z0-9]', '', $courseid );
    $ipcheckcourse = str_replace('.', '', $ipcheckcourse );

    if ($_POST['courseid'] == '' || $_POST['courseid'] == 'nothing')  {
        $pagevalid=false;
        $errorcode=$errorcode . "<li>course code is not valid</li>";
    }

    if ($pagevalid == true) {
        $courseid = $ipcheckcourse;
    }
    else
    { 
        $errorcode=$errorcode . "</ul>";
    }
 
    if ($pagevalid == false) {
        echo $errorcode;
        echo "<p><a href='viewbycoursecode.php'>&larr;&nbsp;back</a></p>";
    }

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'check course!' && $pagevalid == true) {
    // if this is a fetch student post
    // then show a form with single check box
    // here. 

        $courseid = $_POST['courseid'];
        $ipcheckcourse = preg_replace('[^A-Za-z0-9]', '', $courseid );
        $ipcheckcourse = str_replace('.', '', $ipcheckcourse );

        if (check_course_ep($ipcheckcourse,$courseblock,$courseocc) > 0)  {
            $output_HTML="<h3>Students for $ipcheckcourse</h3><ul>";
            get_students_by_course($ipcheckcourse,$courseblock,$courseocc);

            if(count($students>0)) {

                foreach( $students as $key => $value) {
	            $output_HTML=$output_HTML . "<li><a href='http://" . INTERNET_EPROOT . "/" . $value . "' 
target='_blank' title='open this eportfolio in a new window'>" . get_local_nicename($value) . ", $value</a></li>";
                }
       
                $output_HTML=$output_HTML . "</ul>";
            }
         }
	 else
	 {
            $output_HTML= "<p class='warning'>That course has no ePortfolio students.</p>";
         }

         echo $output_HTML;
         echo "<p><a href='viewbycoursecode.php'>&larr;&nbsp;back</a></p>";
    }
}
else 
{
?>

   <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
   <fieldset>
     <legend>Course selection</legend>
   <label for="lecturer">Course Code (e.g. HCOM) and optional block and occurrence (e.g. 1A F0):</label>
    <input type="text" class='formleft' name="courseid" />
    <input type="text" class='formsmall' name="courseblock" />
    <input type="text" class='formsmall' name="courseocc" />    
    &nbsp;<input type="submit" name="fetch" value="check course!" />
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
