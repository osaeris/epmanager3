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
<h2>View ePortfolios by lecturer</h2>
<p class='info'>This form allows lecturers to list all of their ePortfolio students on one page including links to the actual websites.</p>
<?php

if (isset($_POST['action']) && $_POST['action'] == 'submitted') {
// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here

    $pagevalid=true;
    $errorcode="<p class='warning'>There was a problem</p><ul>";

    $lecturer = $_POST['lecturer'];
    $ipchecklecturer = preg_replace('[^A-Za-z0-9]', '', $lecturer );
    $ipchecklecturer = str_replace('.', '', $ipchecklecturer );

    if ($_POST['lecturer'] == '' || $_POST['lecturer'] == 'nothing')  {
        $pagevalid=false;
        $errorcode=$errorcode . "<li>lecturer is not valid</li>";
    }

    if ($pagevalid == true)  {
        $lecturer = $ipchecklecturer;
    }
    else
    { 
        $errorcode=$errorcode . "</ul>";
    }

    if ($pagevalid == false)  {
        echo $errorcode;
        echo "<p><a href='viewbylecturer.php'>&larr;&nbsp;back</a></p>";
    }

    if (isset($_POST['fetch']) && $_POST['fetch'] == 'check lecturer!' && $pagevalid == true) {
    // if this is a fetch student post
    // then show a form with single check box
    // here. 

        $lecturer = $_POST['lecturer'];
        $ipchecklecturer = preg_replace('[^A-Za-z0-9]', '', $lecturer );
        $ipchecklecturer = str_replace('.', '', $ipchecklecturer );
        $ep_exists = check_lecturer_ep($ipchecklecturer);

        if ($ep_exists > 0) {
            $output_HTML="<h3>Students for $ipchecklecturer</h3><ul>";
            $students=get_students_by_lecturer($ipchecklecturer);

            foreach( $students as $key => $value)  {
	        $output_HTML=$output_HTML . "<li><a href='http://" . INTERNET_EPROOT . "/" . $value . "' 
target='_blank' title='open this eportfolio in a new window'>" . get_local_nicename($value) . ", $value</a></li>";
            }

            $output_HTML=$output_HTML . "</ul>";

        }
        else
	{
            $output_HTML= "<p class='warning'>That lecturer has no ePortfolio students.</p>";
        }

        echo $output_HTML;
        echo "<p><a href='viewbylecturer.php'>&larr;&nbsp;back</a></p>";

    }
}
else 
{
?>

   <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
   <fieldset>
     <legend>Lecturer selection</legend>
   <label for="lecturer">lecturer ID:</label>
    <input type="text" name="lecturer" />&nbsp;<input type="submit" name="fetch" value="check lecturer!" />
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
