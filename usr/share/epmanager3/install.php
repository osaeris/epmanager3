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
        <h2>Install</h2>
        

<?php

   

        /* if MySQl OK  the the following will all work*/
      
        $link=dbconnect();
        $sql = "USE ". EP_DB_DATABASE . ";";
        mysqli_query($link,$sql) or die(mysqli_error());


        $sql = "DROP TABLE IF EXISTS `ep_admins`;";
        mysqli_query($link,$sql) or die(mysqli_error());


        $sql = 'CREATE TABLE `ep_admins` ('
        . ' `user_id` bigint(11) NOT NULL auto_increment,'
        . ' `user_login` varchar(60) NOT NULL default \'\','
        . ' `user_pass` varchar(64) NOT NULL default \'\','
        . ' `user_nicename` varchar(50) NOT NULL default \'\','
        . ' `user_status` int(11) NOT NULL default \'0\' COMMENT \'0 = viewer, 1  = lecturer, 2 = admin\','
        . ' PRIMARY KEY  (`user_id`),'
        . ' UNIQUE KEY `user_login` (`user_login`)'
        . ' ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;';
        mysqli_query($link,$sql) or die(mysqli_error());

        $sql = 'INSERT INTO `ep_admins` ('
        . ' `user_id`, `user_login`, `user_pass`, `user_nicename`,'
        . ' `user_status`) VALUES '
        . ' (1, \'admin\', \'2198ZHchCPvo2\', \'administrator\', 2);';
        mysqli_query($link,$sql) or die(mysqli_error());

        $sql = "DROP TABLE IF EXISTS `ep_student_lecturers`;";
        mysqli_query($link,$sql) or die(mysqli_error());

        $sql = 'CREATE TABLE `ep_student_lecturers` ('
        . ' `student_id` varchar( 20 ) NOT NULL default \'\' ,'
        . ' `lecturer_id` varchar( 20 ) NOT NULL default \'\' '
        . ' ) ENGINE = MYISAM DEFAULT CHARSET = latin1;';
      

        mysqli_query($link,$sql) or die(mysqli_error());

        $sql = 'DROP TABLE IF EXISTS `ep_students`;';
        mysqli_query($link,$sql) or die(mysqli_error());

        $sql = 'CREATE TABLE `ep_students` ('
        . '  `student_eportfolio_id` bigint(11) NOT NULL auto_increment,'
        . '  `student_eportfolio_folder` varchar(64) NOT NULL default \'\''
        . '  ,`student_id` varchar(20) NOT NULL default \'\','
        . '  `student_nicename` varchar(20) NOT NULL default \'\''
        . '  , PRIMARY KEY  (`student_eportfolio_id`))'
        . '  ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=161 ;';
        mysqli_query($link,$sql) or die(mysqli_error());


	$sql = 'DROP TABLE IF EXISTS `ep_student_courses`;';
        mysqli_query($link,$sql) or die(mysqli_error());

	$sql = 'CREATE TABLE IF NOT EXISTS `ep_student_courses` ('
	. ' `student_id` varchar(255) NOT NULL,'
	. ' `course_id` varchar(255) NOT NULL,'
	. ' `course_block` varchar(5) NULL,'
	. ' `course_occurrence` varchar(5) NULL'	
	. ' ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
        mysqli_query($link,$sql) or die(mysqli_error());
        
        dbdisconnect($link);
        //If SQL script runs without error write out the file:


        create_manager_config_file(INTERNET_ROOT, INTERNET_EPROOT, 'unchecked', '/usr/share/epmanager',  PORTFOLIO_PATH,  INTERNET_DOMAIN, LECTURER_PASS, EP_DB_SERVER, EP_DB_DATABASE, EP_DB_USERNAME, EP_DB_PASSWORD, 'unchecked', 'sits', 'mssql', 'localhost', 'misdb', 'misuser', 'mispass','unchecked', 'ldapnameorip', '389', 'ldapsearchdn', 'ldapsearchpass', 'ldaproot','description','put your own long value here');





        echo "<p class='warning'>Settings file successfully saved. You should now configure the rest of 
        your settings to complete the installation.</p>\n";

        echo "<p>You are required to login to complete the settings for epmanager. At this point there is one user only: username:<b>admin</b> password:<b>admin</b>. You should change the password for the admin user as soon as possible.</p>\n";

        echo "<p><a href='login.php'>&larr;&nbsp;login</a></p>\n";
 


?>








        <p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
