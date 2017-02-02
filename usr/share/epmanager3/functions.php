<?php
/**
* This file contains all of the functions used to create portfolios / check form entries etc
* These functions are all for epmanager operations
* User specific functions are found in userfunctions.php
**/
  include('userfunctions.php'); //functions about a users wordpress install
  include('htmlfunctions.php'); //functions specific to IMS operations

  $students;  // global variable for student array from ep_students
  $admins;    // global variable for site admins from ep_admins
  $student_lecturers; // global variable for lecturer-student relationship from ep_lecturer
  $student_joined_info; // global variable JOINing student table to student_lecturers
  $course_student_list; // global variable to hold the list of students from a course code;

  function getHeader() {
    global $headerString;

    $headerString = $headerString ."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
    $headerString = $headerString ." \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
    $headerString = $headerString ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
    $headerString = $headerString ."  <head>\n";
    $headerString = $headerString ."    <meta http-equiv=\"expires\" content=\"0\" />\n";
    $headerString = $headerString ."    <meta name=\"title\" content=\"ePortfolio manager\" />\n";
    $headerString = $headerString ."    <meta name=\"description\" content=\"ePortfolio manager\" />\n";
    $headerString = $headerString ."    <meta name=\"keywords\" content=\"Portfolio manager\" />\n";
    $headerString = $headerString ."    <meta name=\"author\" content=\"steve baxter\" />\n";
    $headerString = $headerString ."    <meta name=\"rating\" content=\"General\" />\n";
    $headerString = $headerString ."    <meta name=\"robots\" content=\"index,follow\" />\n";
    $headerString = $headerString ."    <title>ePortfolio Manager</title>\n";
    $headerString = $headerString ."    <link rel=\"stylesheet\" href=\"style.css\"  type=\"text/css\" />\n";
    $headerString = $headerString ."  </head>\n";

    return $headerString;

  }

  function StripIllegalCharacters($string) {
  
    $tempstring=str_replace('.', '', $string );
    return preg_replace('/(?!\.)(\W*)/', '', $tempstring);
     
  }

  function getFooter() {
    global $footerString;

    $footerString = '';
    $revision = EP_VERSION;
    $footerString = $footerString . "<div id='footer'><img src='images/FinalLogoColour.png' alt='dumfries and galloway college logo' /><br />&copy;&nbsp;2007-2015 (Revision $revision)</div>";

    return $footerString;
  }

  //hash then encrypt a string
  //http://www.stargeek.com/php_scripts.php?script=16&cat=misc
  function Encrypt($string) {
   
    $crypted = crypt(md5($string), md5($string));
    return $crypted;
  }

  function login($user, $password) { //attempt to login false if invalid true if correct
    $auth = false;

    // Check for LDAP login
    if(MANAGER_LDAP=='on') {
      $auth = ldap_checklogin($user,$password); 
      if($auth==true) {
          return $auth;
      }
    }
    // No LDAP go back to database check
    $link=dbconnect();
    $user=mysqli_real_escape_string($link,$user);
     
    $query =  "select user_pass from ep_admins where user_login = '$user'";
    $result =mysqli_query($link,$query);
    $pass = mysqli_fetch_row($result);

    dbdisconnect($link);

    if ($pass[0] === (Encrypt($password))) {
       $auth = true;
    }

    return $auth;
  }

  function ldap_checklogin($username,$password) {

    $login=false;
    $ldapusername="{$username}@college.dumgal.ac.uk";

    $ldapserver = "ldap://" . MAN_LDAP_SERVER;
    $ad = ldap_connect($ldapserver);
  
    ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
    $bd = ldap_bind($ad,$ldapusername,$password);

    /* FAILOVER  */
    if(!$bd) {
        $ldapserver = "ldap://" . MAN_LDAP_FAILOVER_SERVER;
        $ad = ldap_connect($ldapserver);
        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bd = ldap_bind($ad,$ldapusername,$password);
    }

    // IF LDAP was successful set login to true
    if(!$bd) {
        $login=false;
    }
    else
    {
        $login=true;
    }
    // MUST CHECK FOR BLANK PASSWORD SEE http://www.php.net/manual/en/function.ldap-bind.php
    if($password=='') {
       $login=false;
    }
    if($password==null) {
       $login=false;
    }
   
    return $login;
  }

  function get_level($user) { //return the user privelidge
    $level =-1;
    
    $link=dbconnect();
    $user=mysqli_real_escape_string($link,$user);
    $query =  "select user_status from ep_admins where user_login = '$user'";
  
    $numlevels = mysqli_query($link,$query) or die("Select Failed!");
    $numlevel = mysqli_fetch_array($numlevels);

    dbdisconnect($link);
    return $numlevel[0];
  }

  function reset_user_password($user) { //return the user privelidge
     
    $link=dbconnect();
    $user=mysqli_real_escape_string($link,$user);
    $query =  "UPDATE ep_admins SET user_pass='5fpJJfAcBuNxY' where user_login = '$user';";
  
    mysqli_query($link,$query) or die("Select Failed!");
    dbdisconnect($link);
    return true;
  }

  function get_menu() {
    
    global $menustring;
     
    $menustring = "<div id='menu'>\n";
    $menustring .= "    <p class='bold'>public functions</p>\n";
    $menustring .= "    <ul>\n";

    //check if the logged session is set and if not true
    if ((isset($_SESSION['logged'])) && ($_SESSION['logged']!='true')) {

      $menustring .= "        <li><a href='http";
      
      if (INTERNET_EPSECURE=='on') {
        $menustring .= "s";
      }

      $menustring .= "://".INTERNET_ROOT."/login.php'>LOG IN</a></li>\n";
    }

    //check if the logged session is set and if true
    if ((isset($_SESSION['logged'])) && ($_SESSION['logged']=='true')) {
    
        $menustring .= "        <li><a href='logout.php'>LOGOUT</a></li>\n";
    }

    //check if the logged session is present if not present login link
    if ((!isset($_SESSION['logged']))==true) { 
      $menustring .= "        <li><a href='login.php'>LOGIN</a></li>\n"; 
    }
    $menustring .= "        <li><a href='index.php'>HOME</a></li>\n";
    $menustring .= "        <li><a href='help.php'>HELP</a><br /><br /></li>\n";
    
    if ((isset($_SESSION['logged'])) && ($_SESSION['logged']=='true') && (isset($_SESSION['level'])) && ($_SESSION['level']>-1)) {
      $menustring .= "        <li><a href='viewbylecturer.php'>View ePortfolios by lecturer</a></li>\n";
      $menustring .= "        <li><a href='viewbycoursecode.php'>View ePortfolios by course code</a></li>\n";    
    }
    
    $menustring .=  "   </ul>\n";

    if ((isset($_SESSION['logged'])) && ($_SESSION['logged']=='true' && (isset($_SESSION['level']))  && $_SESSION['level']>0 )) {    
      $menustring .= "    <p class='bold'>eportfolio functions</p>\n";
      $menustring .=  "   <ul>\n";

      $menustring .= "        <li><a href='singlecreate.php'>Create a single student eportfolio</a></li>\n";
      $menustring .= "        <li><a href='singledelete.php'>Delete ePortfolios</a></li>\n";
      $menustring .= "        <li><a href='restoreeilp.php'>Restore eILP post/s for a student</a><br /></li>\n";
      
      $menustring .= "        <li><a href='resetpass.php'>Reset ePortfolio user password</a><br /></li>\n";

      $menustring .= "        <li><a href='addsinglecourse.php'>Add a single student course relationship</a></li>\n";
      $menustring .= "        <li><a href='singletheme.php'>Add themes to a single eportfolio</a><br /><br /></li>\n";

      $menustring .=  "    </ul>\n";
      $menustring .= "    <p class='bold'>group functions</p>\n";
      $menustring .=  "   <ul>\n";

            if (MIS_INTEGRATION=='on') {
                $menustring .= "        <li><a href='groupcreate.php'>Create portfolios by course group</a></li>\n";
                $menustring .= "        <li><a href='addgrouplecturer.php'>Add course group lecturer</a></li>\n";
                $menustring .= "        <li><a href='removegrouplecturer.php'>Remove course group lecturer</a></li>\n";
                $menustring .= "        <li><a href='exitreport.php'>Show eILP exit reviews for a group</a><b>&nbsp;(new!)</b><br /></li>\n";
            }

            $menustring .= "        <li><a href='groupthemes.php'>Manage group themes</a></li>\n";
            $menustring .=  "    </ul>\n";

            $menustring .= "    <p class='bold'>lecturer/verifier functions</p>\n";
            $menustring .=  "    <ul>\n";

            $menustring .= "        <li><a href='addsinglelecturer.php'>Add single lecturer</a></li>\n";
            $menustring .= "        <li><a href='removesinglelecturer.php'>Remove single lecturer</a></li>\n";
            $menustring .= "        <li><a href='addsingleverifier.php'>Add single external verifier</a></li>\n";     
            $menustring .= "        <li><a href='removesingleverifier.php'>Remove single verifier</a></li>\n";            

            $menustring .=  "    </ul>\n";

            $menustring .= "    <p class='bold'>Interoperability</p>\n";
            $menustring .=  "    <ul>\n";

            $menustring .= "        <li><a href='htmlexport.php'>Export standalone web site</a><br /></li>\n";
            $menustring .=  "       <li><a href='backupportfolio.php'>Backup eportfolio</a></li>\n";
            $menustring .= "        <li><a href='restoreportfolio.php'>Restore eportfolio</a></li>\n";
            $menustring .=  "    </ul>\n";

            if ($_SESSION['level']>1)  {
                $menustring .= "    <p class='bold'>admin functions</p>\n";
                $menustring .=  "    <ul>\n";
                $menustring .= "        <li><a href='settings.php'>SETTINGS</a></li>\n";

                //This one should be a check_admin type thing. Only site admins should see this page
               $menustring .= "        <li><a href='usermanager.php'>USER MANAGEMENT</a></li>\n";
            }

            $menustring .=  "    </ul>\n";
        

   }
  
  $menustring .= "</div>\n";
  return $menustring;

}

  function get_ep_students($scope='all',$keyword='nothing')  {
    // This function will retrieve records from the
    // ep_students table based on the parameters
    // provided. The records will be added to the 
    // students array to allow manipulation.
    //
    // Available scopes are:
    //  'all': just get everything
    //  'id': retrieve based on matching student id
    // 
    // **Mainly for Debugging**
    global $students;

    switch($scope) {
       CASE "all":
         $query="SELECT * FROM ep_students";
         break;
       CASE "id":
         $query="SELECT * FROM ep_students WHERE student_id='$keyword'";
         break;
    }

    $link=dbconnect();
    $keyword=mysqli_real_escape_string($link,$keyword);
 
    $result=mysqli_query($link,$query);

    while( $row=mysqli_fetch_assoc($result) )
    {
     $students[] = $row['student_eportfolio_id'];
     $students[] = $row['student_eportfolio_folder'];
     $students[] = $row['student_id'];
     $students[] = $row['student_nicename'];
    }

    dbdisconnect($link);
    
  }

  function get_max_userid($student) {
  //This will find the last id number
  //used in the users table to allow
  //user_meta to be updated successfully
  $link=dbconnect();

      $query = "SELECT MAX(ID) FROM $student" . "_users; ";

      $numstudents = mysqli_query($link,$query) or die("Select Max ID Failed!");
      $numstudent = mysqli_fetch_array($numstudents);

      dbdisconnect($link);

      return $numstudent[0];
  
   }

  function delete_ep_lecturer($student,$lecturer) {
  // This function will remove a lecturer
  // user from an individuals eportfolio
  // this will affect the $student_users tables
  // as well as the $student_usermeta tables
  // first get the userID of the lecturer from the
  // users table then delete all the usermeta rows
  // for this user, then finally delete from users
  if (check_lecturer_ep($lecturer)==TRUE)
   {
    global $lecturerID;
    $link=dbconnect();
    
    $query = "SELECT ID, user_login from $student" . "_users WHERE user_login='$lecturer'";   

    $result=mysqli_query($link,$query);
    $num=mysqli_num_rows($result);

    if ($num > 0)
    {
      $i=0;
      while($i < $num)
      {
       $lecturerID=mysqli_result($result,$i,"ID");
       $i++;
      }
    }
    // At this point we know the ID for lecturer so do some deleting

    $query="DELETE FROM ep_student_lecturers WHERE lecturer_id='$lecturer' AND student_id='$student'";
    mysqli_query($link,$query) or die(mysqli_error($link));
   
    $query="DELETE FROM $student" . "_users WHERE ID='$lecturerID'";
    mysqli_query($link,$query) or die(mysqli_error($link));

    dbdisconnect($link);
  
   }

  }


  function delete_ep_verifier($student,$verifier) {
  // This function will remove a lecturer
  // user from an individuals eportfolio
  // this will affect the $student_users tables
  // as well as the $student_usermeta tables
  // first get the userID of the lecturer from the
  // users table then delete all the usermeta rows
  // for this user, then finally delete from users
 
    global $verifierID;
    
    $link=dbconnect();
    $query = "SELECT ID, user_login from $student" . "_users WHERE user_login='$verifier'";   

    $result=mysqli_query($link,$query);
    $num=mysqli_num_rows($result);

    if ($num > 0)
    {
      $i=0;
      while($i < $num)
      {
       $verifierID=mysqli_result($result,$i,"ID");
       $i++;
      }
    }
    // At this point we know the ID for lecturer so do some deleting

    $query="DELETE FROM $student" . "_usermeta WHERE user_id='$verifierID'";
    mysqli_query($link,$query) or die(mysqli_error($link));
   
    $query="DELETE FROM $student" . "_users WHERE ID='$verifierID'";
    mysqli_query($link,$query) or die(mysqli_error($link));

    dbdisconnect($link);
  
   

  }



  function add_ep_course($student,$courseid,$courseblock,$courseoccurrence) {

      $link=dbconnect();
      $student=mysqli_real_escape_string($link,$student);
      $courseid=mysqli_real_escape_string($link,$courseid);
      $courseblock=mysqli_real_escape_string($link,$courseblock);
      $courseoccurrence=mysqli_real_escape_string($link,$courseoccurrence);
      
      $query = 'INSERT INTO `ep_student_courses` VALUES (\''.$student.'\',\''.$courseid.'\',\''.$courseblock.'\',\''.$courseoccurrence.'\')';

      mysqli_query($link,$query) or die(mysqli_error($link));

      dbdisconnect($link);
  
  }

  function delete_ep_studentcourses($student,$courseid,$scope) {
  // scope 1 = individual course, 0 = all 
      $link=dbconnect();
      $student=mysqli_real_escape_string($link,$student);
      $courseid=mysqli_real_escape_string($link,$courseid);
      $scope=mysqli_real_escape_string($link,$scope);
      
         if ($scope==0) {
             $query = 'DELETE FROM `ep_student_courses` WHERE student_id = \''.$student.'\'; ';

             mysqli_query($link,$query) or die(mysqli_error($link));
         }

         if ($scope==1) {
             $query = 'DELETE FROM `ep_student_courses` WHERE student_id = \''.$student.'\' and course_id =\''.$courseid.'\'; ';

             mysqli_query($link,$query) or die(mysqli_error($link));
         }

      dbdisconnect($link);
  
  }

  function add_ep_lecturer($lecturer,$student) {
  // This function must add a lecturer
  // to the ep_student_lecturers table
  // including the student id (the easy bit)
  // further - the 'wordpress_users' and wordpress_usermeta tables
  // must also be updated and the user_meta
  // table to reflect the new user (the hard bit)
  // Checks that the relationship does not already exist before
  // proceeding

   if (check_lecturer_student($lecturer,$student)==FALSE) {
       $maxuservalue = get_max_userid($student) + 1;
       include_once('libraries/class-phpass.php');
       $wp_hasher = new PasswordHash(8, true);
       $lecturerpass= $wp_hasher->HashPassword( trim( LECTURER_PASS ) );
       $lecturerdomain=INTERNET_DOMAIN;
       $link=dbconnect();
       $lecturer=mysqli_real_escape_string($link,$lecturer);
       $student=mysqli_real_escape_string($link,$student);
       $query = 'INSERT INTO `ep_student_lecturers` VALUES (\''.$student.'\',\''.$lecturer.'\')';

       mysqli_query($link,$query) or die(mysqli_error($link));

       $query="INSERT INTO `{$student}_users` VALUES ($maxuservalue, '{$lecturer}', '{$lecturerpass}', '{$lecturer}', '$lecturer@$lecturerdomain', 'http://', NOW(), '', 0, '{$lecturer}');";       
   
       mysqli_query($link,$query) or die(mysqli_error($link));

       $query="INSERT INTO `{$student}_usermeta` VALUES (NULL,$maxuservalue,'first_name','firstname'),
    (NULL,$maxuservalue,'last_name','surname'),
    (NULL,$maxuservalue,'nickname','$lecturer'),
    (NULL,$maxuservalue,'description','Lecturer'),
    (NULL,$maxuservalue,'rich_editing','true'),
    (NULL,$maxuservalue,'comment_shortcuts','false'),
    (NULL,$maxuservalue,'admin_color','fresh'),
    (NULL,$maxuservalue,'use_ssl','0'),
    (NULL,$maxuservalue,'show_admin_bar_front','true'),
    (NULL,$maxuservalue,'{$student}_capabilities','a:1:{s:11:\"contributor\";b:1;}'),
    (NULL,$maxuservalue,'{$student}_user_level','1'),(NULL,$maxuservalue,'dismissed_wp_pointers','wp330_toolbar,wp330_saving_widgets,wp340_choose_image_from_library,wp340_customize_current_theme_link,wp350_media,wp360_revisions,wp360_locks');";

       mysqli_query($link,$query) or die(mysqli_error($link));

       dbdisconnect($link);
       
        echo "<p>lecturer $lecturer added to ePortfolio $student</p>";
        echo "<p><a href='addsinglelecturer.php'>&larr;&nbsp;back</a></p>";
    }
}

  function get_students_by_lecturer($lecturer)  {
    global $students;
    $link=dbconnect();
    $lecturer=mysqli_real_escape_string($link,$lecturer);
    $query = "SELECT student_id FROM ep_student_lecturers WHERE lecturer_id='$lecturer'";

    $result=mysqli_query($link,$query) or dir(mysqli_error($link));
    
    $num=mysqli_num_rows($result);
    
    while( $row=mysqli_fetch_assoc($result) )
    {
      $students[] = $row['student_id'];
    }

    dbdisconnect($link);
    return $students;
   
  }

  function get_students_by_course($courseid,$courseblock,$courseocc)  {
    global $students;

    $link=dbconnect();    
    $courseid=mysqli_real_escape_string($link,$courseid);
    $courseblock=mysqli_real_escape_string($link,$courseblock);
    $courseocc=mysqli_real_escape_string($link,$courseocc);
    
    $query = "SELECT student_id FROM ep_student_courses WHERE course_id='$courseid'";

    if ($courseblock!='') {
        $query = $query . " and course_block = '$courseblock' ";
    }
    
    if ($courseocc!='') {
        $query = $query . " and course_occurrence = '$courseocc' ";
    }
   


    $result=mysqli_query($link,$query);
    
    $num=mysqli_num_rows($result);
    
    while( $row=mysqli_fetch_assoc($result) )
    {
      $students[] = $row['student_id'];
    }

    dbdisconnect($link);
   
  }

  function get_ep_admins()  {
    // This function will retrieve records from the
    // ep_admins table.
    // The records will be added to the
    // admins array to allow manipulation.
    // **Mainly for Debugging**

    global $adminlist;

    $link=dbconnect();

    $query="SELECT * FROM ep_admins ORDER BY user_status";

    $result=mysqli_query($link,$query);

    $output_HTML = "<select  class='formleft' multiple name='userlist[]'>\n\r";

    while( $row=mysqli_fetch_assoc($result) )
    {

     $userid = $row['user_id'];
     $userlogin = $row['user_login'];
     $nicename = $row['user_nicename'];
     $userstatus = $row['user_status'];

     switch ($userstatus)
     {

        case "0":
           $userstatus="viewer";
        break;

        case "1":
            $userstatus="lecturer";
        break;

        case "2":
             $userstatus="administrator";
        break;


      }

     $output_HTML = $output_HTML . "<option value='$userlogin'>$userlogin,$userstatus</option>\n\r";

    }

    $output_HTML = $output_HTML . "</select>\n\r";

    dbdisconnect($link);
    echo $output_HTML;

  }

  function add_new_epuser($login,$nicename,$password,$status) {

       $link=dbconnect();

       
       $login=mysqli_real_escape_string($link,$login);
       $nicename=mysqli_real_escape_string($link,$nicename);
       $password=mysqli_real_escape_string($link,$password);
       $status=mysqli_real_escape_string($link,$status);
       $passwordcrypt=Encrypt($password);

   $query = 'INSERT INTO `ep_admins` VALUES (0,\''.$login.'\',\''.$passwordcrypt.'\',\''.$nicename.'\',\''.$status.'\')';
   
   mysqli_query($link,$query) or die(mysqli_error($link));

      dbdisconnect($link);

  }

  function delete_epuser($userid) {

       $link=dbconnect();
       $userid=mysqli_real_escape_string($link,$userid);
       

   $query = "DELETE FROM `ep_admins` WHERE user_login = '$userid';";
   
   mysqli_query($link,$query) or die(mysqli_error($link));

      dbdisconnect($link);

  }

  function update_user_status($user,$newstatus) {

       $link=dbconnect();

   $query = "UPDATE `ep_admins` SET user_status = $newstatus WHERE user_login = '$user';";
   
   mysqli_query($link,$query) or die(mysqli_error($link));

      dbdisconnect($link);

  }

  function get_ep_student_lecturers()  {
    // This function will retrieve records from the
    // ep_student_lecturers table.
    // The records will be added to the
    // student_lecturers array to allow manipulation.
    // **Mainly for Debugging**

    global $student_lecturers;

    $query="SELECT * FROM ep_student_lecturers";

    $link=dbconnect();

    $result=mysqli_query($link,$query);

    while( $row=mysqli_fetch_assoc($result) )
    {
     $student_lecturers[] = $row['student_id'];
     $student_lecturers[] = $row['lecturer_id'];
    }

    dbdisconnect($link);
  }

  function check_user_loginexists($user)  {
  // This will check the existence of a user_login
  // within the ep_admins table
    $link=dbconnect();
      $query = "SELECT COUNT(user_login) FROM ep_admins WHERE user_login='$user'";

      $numstudents = mysqli_query($link,$query) or die("Select Failed!");
      $numstudent = mysqli_fetch_array($numstudents);
      


      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }



  }

  function check_ep_user($eportfolio,$userid) {
  // This will check the existence of a user_login
  // within the quoted eportfolio table prefix _users
    $link=dbconnect();
      $query = "SELECT COUNT(*) FROM " . $eportfolio . "_users WHERE user_login='$userid'";

      $numstudents = mysqli_query($link,$query) or die("Eportfolio user not found!");
      $numstudent = mysqli_fetch_array($numstudents);
      


      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }

  }

  function check_course_student($courseid,$studentid) {
  // This will check the existence of a user_login
  // within the quoted eportfolio table prefix _users
    $link=dbconnect();
      $query = "SELECT COUNT(*) FROM ep_student_courses WHERE student_id='$studentid' AND course_id ='$courseid' ; ";

      $numstudents = mysqli_query($link,$query) or die("Select Check Course Student Failed!");
      $numstudent = mysqli_fetch_array($numstudents);
      


      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }

  }

  function check_lecturer_student($lecturer,$student) {
  // This will check the ep_student_lecturers table
  // for a pre-existing lecturer-student relationship

    $link=dbconnect();
      $query = "SELECT COUNT(*) FROM ep_student_lecturers WHERE lecturer_id='$lecturer' AND student_id='$student'";

      $numstudents = mysqli_query($link,$query) or die("Select Check Lecturer Student Failed!");
      $numstudent = mysqli_fetch_array($numstudents);



      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
   }

  function reset_ep_password($eportfolio,$userid) {
   // Checks whether the supplied username has
   // an eportfolio.
   // Use this before creating one or to make
   // sure that the portfolio exists before
   // deleting it.
      include_once('libraries/class-phpass.php');
      $wp_hasher = new PasswordHash(8, true);
      $adminpass=$wp_hasher->HashPassword( trim( 'password' ) );
      $link=dbconnect();
      $query = 'UPDATE ' . $eportfolio . '_users SET user_pass="'.$adminpass.'" WHERE user_login="'.$userid.'";';

      mysqli_query($link,$query) or die("Update Failed!");

      dbdisconnect($link);

  }

  function check_student_ep($student) {

  // Checks whether the supplied username has
  // an eportfolio.
  // Use this before creating one or to make
  // sure that the portfolio exists before
  // deleting it.

      $link=dbconnect();
      $query = "SELECT COUNT(*) FROM ep_students WHERE student_id='$student'";



      $numstudents = mysqli_query($link,$query) or die("Select Failed!");
      $numstudent = mysqli_fetch_array($numstudents);
      
      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }

  }

  function check_lecturer_ep($lecturer) {
  // This will check the ep_student_lecturers table
  // for a pre-existing entry anywhere for this lecturer     
    $link=dbconnect();
      $query = "SELECT COUNT(*) FROM ep_student_lecturers WHERE lecturer_id='$lecturer'";

      $numstudents = mysqli_query($link,$query) or die("Select Failed!");
      $numstudent = mysqli_fetch_array($numstudents);
      


      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
  }

  function check_course_ep($courseid,$courseblock,$courseocc) {
  // This will check the ep_student_lecturers table
  // for a pre-existing entry anywhere for this lecturer     
    $link=dbconnect();
      $query = "SELECT COUNT(*) FROM ep_student_courses WHERE course_id='$courseid' ";
      
    if ($courseblock!='') {
        $query = $query . " and course_block = '$courseblock' ";
    }
    
    if ($courseocc!='') {
        $query = $query . " and course_occurrence = '$courseocc' ";
    }
          

      $numstudents = mysqli_query($link,$query) or die("Select Failed!");
      $numstudent = mysqli_fetch_array($numstudents);
      


      dbdisconnect($link);

      if ($numstudent[0] > 0)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
  }

  function get_student_joined_info($student='')  {

    // This function will retrieve 
    // all the information about a single student
    // from the ep_students table and 
    // left join to student_lecturers
    // This will allow editing a single students
    // lecturers which may be more than one.

    global $student_joined_info;

    $query =          "SELECT ep_student_lecturers.student_id,ep_students.student_nicename, ep_student_lecturers.lecturer_id ";
    $query = $query . "FROM ep_student_lecturers ";
    $query = $query . "LEFT JOIN ep_students ON ep_students.student_id = ep_student_lecturers.student_id WHERE ep_student_lecturers.student_id = '$student' ";
    
    $link=dbconnect();
    $result=mysqli_query($link,$query);

    while( $row=mysqli_fetch_assoc($result) )
    {
     $student_joined_info[] = $row['student_id'];
     
     $student_joined_info[] = $row['lecturer_id'];
  
     $student_joined_info[] = $row['student_nicename'];

    }
    dbdisconnect($link);
   }

  function delete_portfolio($student)  {

   // This function will check for the existence of 
   // the portfolio then delete user tables
   // and files 

   $userdirectory = PORTFOLIO_PATH.'/'.$student;
   
   if(file_exists($userdirectory))
   {
     
     full_rmdir($userdirectory);
   }
   else
   {
     echo "That folder doesn't exist. Perhaps the ePortfolio is already gone! Proceeding to remove database tables....";
   }

   $link=dbconnect();

   $query = 'DROP TABLE IF EXISTS '.$student.'_categories,';
   $query = $query.$student.'_comments,'; 
   $query = $query.$student.'_commentmeta,'; 
   $query = $query.$student.'_linkcategories,'; 
   $query = $query.$student.'_links,'; 
   $query = $query.$student.'_options,';

   $query = $query.$student.'_post2cat,';
   $query = $query.$student.'_postmeta,';
   $query = $query.$student.'_posts,';
   $query = $query.$student.'_terms,';
   $query = $query.$student.'_term_relationships,';
   $query = $query.$student.'_term_taxonomy,';
   $query = $query.$student.'_uam_accessgroups,';
   $query = $query.$student.'_uam_accessgroup_to_object,';
   $query = $query.$student.'_usermeta,';
   $query = $query.$student.'_users;';
  
   mysqli_query($link,$query) or die(mysqli_error($link));
   
   $query = 'DELETE FROM ep_students WHERE student_id = \''.$student.'\';';
   mysqli_query($link,$query) or die(mysqli_error($link));
   
   $query = 'DELETE FROM ep_student_lecturers WHERE student_id = \''.$student.'\';';
   mysqli_query($link,$query) or die(mysqli_error($link));

   $query = 'DELETE FROM ep_student_courses WHERE student_id = \''.$student.'\';';
   mysqli_query($link,$query) or die(mysqli_error($link));

   $query = 'DROP TABLE IF EXISTS `'.$student.'_podpress_statcounts`;';
   mysqli_query($link,$query) or die(mysqli_error($link));

   $query = 'DROP TABLE IF EXISTS `'.$student.'_podpress_stats`;';
   mysqli_query($link,$query) or die(mysqli_error($link));

   dbdisconnect($link);

   echo "ePortfolio $student has been deleted.<br />";
  }

  function full_rmdir($dirname){

     // Sources
     // brousky - gmail
     // http://uk3.php.net/manual/en/function.rmdir.php#75473

     // babca (plutanium.cz)
     // http://uk3.php.net/manual/en/function.rmdir.php#75594

     // ljubiccica at yahoo dot com
     // http://uk3.php.net/manual/en/function.rmdir.php#75691

        if ($dirHandle = opendir($dirname)){
            $old_cwd = getcwd();
            chdir($dirname);

            while ($file = readdir($dirHandle)){
                if ($file == '.' || $file == '..') continue;

                if (is_dir($file)){
                    if (!full_rmdir($file)) return false;
                }else{
                    if (!unlink($file)) return false;
                }
            }

            closedir($dirHandle);
            chdir($old_cwd);
            if (!rmdir($dirname)) return false;

            return true;
        }else{
            return false;
        }
    }

  function get_local_nicename($student=NULL) {

  // This first checks to local database server
  // to see if the nicename is stored before
  // bothering the MIS system
   if (check_student_ep($student)==TRUE)
   {
    global $nicename;
    
    $query = "SELECT student_nicename, student_id from ep_students WHERE student_id='$student'";   
    $link=dbconnect();
    $result=mysqli_query($link,$query);
    $num=mysqli_num_rows($result);

    if ($num > 0)
    {
      $i=0;
      while($i < $num)
      {
       $nicename=mysqli_result($result,$i,"student_nicename");
       $i++;
      }
    }
    else
    {
      $nicename="unknown";
    }
    dbdisconnect($link);
    
    
    }
    else
    {
      $nicename="unknown";
    }
    return $nicename;

  }

  function create_webfolders($student) {
  //This function will copy the joebloggs folder
  //from /usr/share/epmanager/joebloggs to the 
  //specified LOCAL_PATH, renaming it on the way
  //and changing the username and password settings
  //in wp-config.php to reflect your settings in 
  //config.php



   if (is_dir('/tmp/joebloggs')!=true) {
       $archive_target="/etc/epmanager3/joebloggsupdated.zip";
        $archive = new PclZip($archive_target);

         if ($archive->extract(PCLZIP_OPT_PATH, '/tmp/') == 0) {
              die("Error : ".$archive->errorInfo(true));
          }
    }
    // Now set the EP_SSO_SALT value in the SSO plugin before copying
    // This can't be discovered later without including the config.php
    // file from epmanager into this plugin. That would be very dangerous
    // as the user would be able to read all of the epmanager db values
    // as a compromise this SALT is written into each eportfolio on 
    // creation. If you change the salt in the future you need to seach 
    // and replace the value in any existing eportfolios for it to continue 
    // to work.
    // 
    // Maybe introduce another file /etc/epmanager3/salt.php to
    // get round this
    /*
    exec('cat /tmp/joebloggs/wp-content/plugins/wp3-singlesignonlink.php | sed "{ s/verysecretkey/'.EP_SSO_SALT.'/g }" > /tmp/wp3-singlesignonlink.php');
    
    exec('cp /tmp/wp3-singlesignonlink.php /tmp/joebloggs/wp-content/plugins/wp3-singlesignonlink.php');
    */
    full_copy("/tmp/joebloggs",PORTFOLIO_PATH.'/'.$student) ;

    
   
  }

  function create_manager_config_file($netroot='localhost/epmanager', $neteproot='localhost/epmanager/eportfolios', $epsecure='unchecked', $localpath='/usr/share/epmanager',  $portfoliopath='/usr/share/epmanager/eportfolios',   $netdomain='localhost', $lecturerpass='lecturer', $localserver='localhost', $localdb='eportfolio', $localuser='epuser', $localpass='eppass', $misint='unchecked', $missystem='sits', $misdbms='mssql', $misserver='localhost', $misdb='misdb', $misuser='misuser', $mispass='mispass',  $cmislink='off', $cmisurl='http://your-cmis/yoursearch.php?id=', $ssosalt='put your own long value here', $ldap='off', $ldapserver='put your primary LDAP server here', $ldapfailoverserver='put your failover server here', $ldapsuffix='put your LDAP suffix here' ) {


  global $stringData;

    $myFile =  "/etc/epmanager3/config.php";
    
    $fh = fopen($myFile, 'w') or die("can't open file. please allow web server write permission temporarily");
   
    fwrite($fh, $stringData);
    $stringData = "<?php\n";
    fwrite($fh, $stringData);


    $stringData = "// Crucial settings - These are crucial and must be filled in before running installer \n\n";
    fwrite($fh, $stringData);

    $stringData = "define('EP_DB_SERVER', '" . $localserver . "');// the server with epmanager mysql database\n";
    fwrite($fh, $stringData);

    $stringData = "define('EP_DB_DATABASE', '" . $localdb . "');// the live epmanager database\n";
    fwrite($fh, $stringData);

    $stringData = "define('EP_DB_USERNAME', '" . $localuser . "');// the epmanager database username\n";
    fwrite($fh, $stringData);

    $stringData = "define('EP_DB_PASSWORD', '" . $localpass . "');// the epmanager database password\n\n";
    fwrite($fh, $stringData);


    $stringData = "// Basic settings - These are crucial but can be edited anytime BEFORE creating ePortfolios \n\n";
    fwrite($fh, $stringData);

    $stringData = "define('INTERNET_ROOT', '" . $netroot . "'); // URL of the base manager\n";
    fwrite($fh, $stringData);

    $stringData = "define('INTERNET_EPROOT', '" . $neteproot . "'); // URL used throughout Wordpress\n";
    fwrite($fh, $stringData);

    $stringData = "define('INTERNET_EPSECURE', '" . $epsecure . "'); // URL used throughout Wordpress\n";
    fwrite($fh, $stringData);

    $stringData = "define('LOCAL_PATH', '" . $localpath . "');// This is needed because files are copied and deleted\n";
    fwrite($fh, $stringData);

    $stringData = "define('PORTFOLIO_PATH', '" . $portfoliopath . "');// Portfolios may be mounted elsewhere\n";
    fwrite($fh, $stringData);


    $stringData = "define('INTERNET_DOMAIN', '" . $netdomain . "');// Domain for default lecturer email address\n";
    fwrite($fh, $stringData);

    $stringData = "define('LECTURER_PASS', '" . $lecturerpass . "');// Default lecturer password (default is lecturer)\n\n";
    fwrite($fh, $stringData);



    $stringData = "// Student Records Database Settings - These are entirely optional \n\n";
    fwrite($fh, $stringData);

    if ($misint=='checked')
    $stringData = "define('MIS_INTEGRATION', 'on');//  For MIS integration features - change to 'on' or off\n";
    else
    $stringData = "define('MIS_INTEGRATION', 'off');//  For MIS integration features - change to 'on' or off\n\n";

    fwrite($fh, $stringData);
 
    $stringData = "define('MIS_SYSTEM', '" . $missystem . "');//  Choose MIS System\n";
    fwrite($fh, $stringData);

    $stringData = "define('MIS_DBMS', '" . $misdbms . "');//  Choose DBMS System your MIS runs on\n\n";
    fwrite($fh, $stringData);



    $stringData = "// If you state true to MIS_INTEGRATION, Set up a connection to your MIS system \n\n";
    fwrite($fh, $stringData);

    $stringData = "define('STUDENT_DB_SERVER', '" . $misserver . "');// the server with student records system\n";
    fwrite($fh, $stringData);

    $stringData = "define('STUDENT_DB_DATABASE', '" . $misdb . "');// the student records database\n";
    fwrite($fh, $stringData);

    $stringData = "define('STUDENT_DB_USERNAME', '" . $misuser . "');// the student records database user\n";
    fwrite($fh, $stringData);

    $stringData = "define('STUDENT_DB_PASSWORD', '" . $mispass . "');// the student records database user password\n";
    fwrite($fh, $stringData);



    $stringData = "// CMIS Link to be placed in student Dashboard for lecturers\n\n";
    fwrite($fh, $stringData);

    if ($cmislink=='on')
    $stringData = "define('CMIS_LINK', 'on'); \n";
    else
    $stringData = "define('CMIS_LINK', 'off'); \n";

    fwrite($fh, $stringData);
    
    $stringData = "define('CMIS_URL', '" . $cmisurl . "');\n\n";
    fwrite($fh, $stringData);


    $stringData = "// Whether to enable lecturer link to student records on the student dashboard\n";
    fwrite($fh, $stringData);

    $stringData = "// Note that the student id will be appended to this URL\n";
    fwrite($fh, $stringData);




    $stringData = "// Single Sign On Settings \n\n";
    fwrite($fh, $stringData);

    $stringData = "/* There is a separate login available via link from external site which uses the salt value here to create a hash you must change this value to something else preferably very long and complex even if you dont intend to use it\nurl to sso will be like:\nhttp://YOURSERVER/username/wp-login.php?login=username&hash=blahblah\nsee changes to pluggable-functions.php in a newly created portfolio for more information\n\n. To build the expected hash in pluggable-functions.php the initial hash is md5 of (currentusername + php date as dMyh + EP_SSO_SALT) but you can make this as complex as you like by yourself */\n\n";
    fwrite($fh, $stringData);

    $stringData = "define('EP_SSO_SALT', '" . $ssosalt . "');// xxx\n";
    fwrite($fh, $stringData);

    if ($ldap=='on')
    $stringData = "define('MANAGER_LDAP', 'on'); \n";
    else
    $stringData = "define('MANAGER_LDAP', 'off'); \n";

    fwrite($fh, $stringData);

    $stringData = "define('MAN_LDAP_SERVER', '" . $ldapserver . "');// xxx\n";
    fwrite($fh, $stringData);
    $stringData = "define('MAN_LDAP_FAILOVER_SERVER', '" . $ldapfailoverserver . "');// xxx\n";
    fwrite($fh, $stringData);
    $stringData = "define('MAN_LDAP_SUFFIX', '" . $ldapsuffix . "');// xxx\n";
    fwrite($fh, $stringData);


    $stringData = "?>\n";
    fwrite($fh, $stringData);

    fclose($fh);

  }

  function create_student_config_file($student)  {
  // This function creates a wp-config.php file
  // based on the settings in config.php
    $stringData='';//just a temp string

    $myFile =  PORTFOLIO_PATH;
    $myFile = $myFile . '/' . $student;
    $myFile  = $myFile . "/wp-config.php";
    
    $fh = fopen($myFile, 'w') or die("can't open file");
   
    fwrite($fh, $stringData);
    $stringData = "<?php\n";
    fwrite($fh, $stringData);
    $stringData = "define('DISALLOW_FILE_EDIT', true);\n";
    fwrite($fh, $stringData);
    $stringData = "define('DB_NAME', '" . EP_DB_DATABASE . "');\n";
    fwrite($fh, $stringData);
    $stringData = "define('DB_USER', '" . EP_DB_USERNAME . "');\n";
    fwrite($fh, $stringData);
    $stringData = "define('DB_PASSWORD', '" . EP_DB_PASSWORD . "');\n";
    fwrite($fh, $stringData);
    $stringData = "define('DB_HOST', '" . EP_DB_SERVER . "');\n";
    fwrite($fh, $stringData);
    $stringData = "\$table_prefix  = '{$student}_';\n";
    fwrite($fh, $stringData);
    $stringData = "define ('WPLANG', '');\n";
    fwrite($fh, $stringData);
    $stringData = "define ('WP_DEBUG', false);\n";
    fwrite($fh, $stringData);  
    $stringData = "if ( !defined('ABSPATH') );\n";
    fwrite($fh, $stringData);        
    $stringData = "        define('ABSPATH', dirname(__FILE__).'/');\n";
    fwrite($fh, $stringData);
    $stringData = "require_once(ABSPATH.'wp-settings.php');\n";
    fwrite($fh, $stringData);


    fclose($fh);

  }

  function dirList ($directory) {

    // create an array to hold directory list
    $results = array();

    // create a handler for the directory
    $handler = opendir($directory);

    // keep going until all files in directory have been read
    while ($file = readdir($handler)) {

        // if $file isn't this directory or its parent, 
        // add it to the results array
        if ($file != '.' && $file != '..')
            $results[] = $file;
    }

    // tidy up: close the handler
    closedir($handler);

    // done!
    return $results;

}

  function full_copy( $source, $target )    {
  
    //swizec at swizec dot com
    //http://uk2.php.net/manual/en/function.copy.php#77238

        if ( is_dir( $source ) )
        {
            @mkdir( $target );
           
            $d = dir( $source );
           
            while ( FALSE !== ( $entry = $d->read() ) )
            {
                if ( $entry == '.' || $entry == '..' )
                {
                    continue;
                }
               
                $Entry = $source . '/' . $entry;           
                if ( is_dir( $Entry ) )
                {
                    full_copy( $Entry, $target . '/' . $entry );
                    continue;
                }
                copy( $Entry, $target . '/' . $entry );
            }
           
            $d->close();
        }else
        {
            copy( $source, $target );
        }
    }

  function make_single_backup($student) {
  //make a single .sql file in the temp directory
 
      $backupFile = '/tmp/'. $student .'.sql';
      if (file_exists($backupFile)) {
          unlink ($backupFile);
      }

          $command = "mysqldump -u ".EP_DB_USERNAME." -p".EP_DB_PASSWORD." ".EP_DB_DATABASE." {$student}_commentmeta {$student}_comments {$student}_options {$student}_postmeta {$student}_posts {$student}_terms {$student}_term_relationships {$student}_term_taxonomy {$student}_uam_accessgroups {$student}_uam_accessgroup_to_object {$student}_usermeta {$student}_users > $backupFile";
          system($command);

          $eproot=INTERNET_EPROOT;
          $eproot=str_replace("/","\/",$eproot);
          $portfoliopath=PORTFOLIO_PATH;
          $portfoliopath=str_replace("/","\/",$portfoliopath);
          exec('cat /tmp/'.$student.'.sql | sed "{ 
              s/'.$eproot.'/interneteproot/g      
              s/'.$student.'@'.INTERNET_DOMAIN.'/STUDENTEMAIL/g 
              s/'.$portfoliopath.'/portfoliopath/g 
          }" > /tmp/'.$student.'.sql.back');
     
          $command="cp /tmp/$student.sql.back $backupFile";
          system($command);
     
          $archive_target="/tmp/epmanagerbackup/epmanager_".$student.".zip";
          $archive_source=PORTFOLIO_PATH.'/'.$student;    
  
          if (file_exists($archive_target)) {
              unlink ($archive_target);
          }

          $archive = new PclZip($archive_target);
          chmod ($backupFile, 0777);

          $v_list = $archive->add($archive_source,PCLZIP_OPT_REMOVE_PATH,PORTFOLIO_PATH);
          $v_list = $archive->add($backupFile,PCLZIP_OPT_REMOVE_PATH,'tmp'); 

          if ($v_list == 0) {
              die("Error : ".$archive->errorInfo(true));
          }
   
          if (file_exists($backupFile)) {
              unlink ($backupFile);
          }     
  }


  function make_backup($studentarray,$type)  {
  //added type "multi" or "single"
  //multi will create an archive of archives
  //single will deliver the old style single backup zip
  //
  //if single create sql file, archive and deliver
  //
  //if multi create sql file and archive for each student
  //then create master archive and deliver

  //clearn out the backup folder first

       if(file_exists("/tmp/epmanagerbackup")) {
           full_rmdir("/tmp/epmanagerbackup");
           mkdir("/tmp/epmanagerbackup");
       }  
       else
       {
           mkdir("/tmp/epmanagerbackup");
       }
 

    if($type=="single") {
        $student=$studentarray[0];
        make_single_backup($student);
        redirect(2,"http://".INTERNET_ROOT."/downloader.php?id=$student&type=normal");
    }
   
    if($type=="multi") {
   
       foreach ($studentarray as $student) {
           make_single_backup($student);
       }
       
       $datehold=date('dMY');
       $archive_target="/tmp/epmanager_bigzip_".$datehold.".zip";

       if(file_exists($archive_target)) {
           unlink($archive_target);
       }

       $archive_source="/tmp/epmanagerbackup";
       $archive = new PclZip($archive_target);
       $v_list = $archive->add($archive_source,PCLZIP_OPT_REMOVE_PATH,'tmp');
       if ($v_list == 0) 
       {
           die("Error : ".$archive->errorInfo(true));
       }      
   
       redirect(2,"http://".INTERNET_ROOT."/downloader.php?id=$student&type=big");   
    }

}

  function chmodr($path, $filemode) {
  
      $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

      foreach($iterator as $item) {
         // chmod($item, $filemode);
          chmod($item, octdec($filemode));
      }
  }


   function restore_backup($student,$oldstudent,$lecturer) {

  //check if student has an eportfolio first
  //get the nicename from MIS because this is an imported
  //ePortfolio probably not from this institution.
  //
  //This has been extended because if the filename
  //and studentid didnt match it didnt work


  $nicename=get_mis_nicename($student);
  //There's not always an MIS nicename
  if(($nicename=='')||($nicename==NULL)) {
      $nicename='unknown';
  }
  
  $archive_target="/tmp/epmanager_".$student.".zip";
  
  create_webfolders($student);


  // Read in file from form field
  $archive = new PclZip($archive_target);
  
  if ($archive->extract(PCLZIP_OPT_PATH, '/tmp/') == 0) {
    die("Error : ".$archive->errorInfo(true));
  }
  // copy over the uploads folder overwriting the old one
  full_copy("/tmp/$student/wp-content/uploads",PORTFOLIO_PATH.'/'.$student.'/wp-content');
  $query_target="/tmp/temp.sql";
  
          $eproot=INTERNET_EPROOT;
          $eproot=str_replace("/","\/",$eproot);
          $portfoliopath=PORTFOLIO_PATH;
          $portfoliopath=str_replace("/","\/",$portfoliopath);
          exec('cat /tmp/'.$oldstudent.'.sql | sed "{ 
              s/'.$oldstudent.'/'.$student.'/g     
              s/interneteproot/'.$eproot.'/g      
              s/updatedeportfolios/eportfolios/g   
              s/STUDENTEMAIL/'.$student.'@'.INTERNET_DOMAIN.'/g 
              s/portfoliopath/'.$portfoliopath.'/g 
          }" > /tmp/temp.sql.back');  
          $command="cp /tmp/temp.sql.back /tmp/temp.sql";
          system($command);


    $uploadspath=PORTFOLIO_PATH.'/'.$student."/wp-content/uploads";
    chmodr($uploadspath, "0777");

    $link=dbconnect();

    $query = "DELETE FROM ep_students WHERE student_id='$student'";
    mysqli_query($link,$query) or die(mysqli_error($link));

    $query = "DELETE FROM ep_student_lecturers WHERE student_id='$student'";
    mysqli_query($link,$query) or die(mysqli_error($link));

    $command = "mysql -u ".EP_DB_USERNAME." -p".EP_DB_PASSWORD." ".EP_DB_DATABASE." < $query_target";

    system($command);

    if (file_exists("/tmp/".$student))
      full_rmdir("/tmp/".$student);

    if (file_exists("/tmp/".$student.".sql"))
      unlink ("/tmp/".$student.".sql");

    create_student_config_file($student);

     //update the ep_students table with nicename from student records and username
     $query = 'insert into ep_students VALUES (NULL,\''.PORTFOLIO_PATH.'/'.$student.'\',\''.$student.'\',\''.$nicename.'\');';

     mysqli_query($link,$query) or die(mysqli_error($link));

     $query = 'update '.$student.'_options set option_value=\'http://'.INTERNET_EPROOT.'/'.$student.'\' where option_name=\'home\';'; 
     mysqli_query($link,$query) or die(mysqli_error($link));
     
     $query = 'update '.$student.'_options set option_value=\'http://'.INTERNET_EPROOT.'/'.$student.'\' where option_name=\'siteurl\';'; 
     mysqli_query($link,$query) or die(mysqli_error($link)); 


     dbdisconnect($link);

     add_ep_lecturer($lecturer,$student);

    
     unlink($archive_target);


  
  }


  function redirect($time, $topage) {
   //http://www.phpfreaks.com/quickcode/Redirect/532.php
   //by Harry Mavidis

    echo "<meta http-equiv=\"refresh\" content=\"{$time}; url={$topage}\" /> ";
  }
//count_course_student_list($courseid,$courseblock,$courseocc,$session,"all"
  function count_course_student_list($coursecode='',$courseblock='',$courseocc='',$session='',$scope='all')  {
  // A precursor for get_course_student_list this
  // function returns a count to see if it's worth
  // getting the student list data
    global $studentcode;

    $db_server = STUDENT_DB_SERVER;
    $db_database = STUDENT_DB_DATABASE;
    $db_username = STUDENT_DB_USERNAME;
    $db_password = STUDENT_DB_PASSWORD;

    switch (MIS_SYSTEM)
    {
      case "sits":

          switch (MIS_DBMS)
          {
             
               case "mysql":
               //needs to be coded
               break;

               case "mssql":
                   $db = mssql_connect($db_server,$db_username,$db_password) or die("ERROR CONNECTING TO: ".$db_server."<br>".mysqli_error($link));
                   mssql_select_db($db_database,$db) or die("COULD NOT SELECT DATABASE: ".$db_database."<br>".mysqli_error($link));

                   $php_errormsg = "";
                   $existing_test=FALSE;

                   $output_HTML = "";

                   $query = "SELECT sce_stuc as StudentCode, sce_srtn + ',' + sce_stuc as FullName , sce_blok, sce_occl ";
                   $query = $query . "FROM dbo.srs_sce WHERE (ISNULL(RTRIM(dbo.srs_sce.sce_stac),'')";
                   $query = $query . "='C') and sce_crsc = '$coursecode' and sce_ayrc = '$session' ";

                   if ($courseblock!='') {
                       $query = $query . " and sce_blok = '$courseblock' ";
                   }
    
                   if ($courseocc!='') {
                       $query = $query . " and sce_occl = '$courseocc' ";
                   }

                   $result = mssql_query($query,$db) or die ('Query failed: '.$query);
                   $num=mssql_num_rows($result);
    
                   $tempcount=0;
                   $i=0;

                   switch ($scope) 
                   {
                     case "all":
                       $tempcount= $num;
                       break;
                     
                     case "not":
                       while ($i < $num)
                       {
                         $studentcode=mssql_result($result,$i,"StudentCode");
              
                         if (check_student_ep($studentcode) < 1)
                         $tempcount=$tempcount;
   
                         $i++;
                       }
                     break;

                     case "existing":
                      while ($i < $num)
                      {
                       $studentcode=mssql_result($result,$i,"StudentCode");

                       if (check_student_ep($studentcode) > 0)
                           $tempcount=$tempcount+1;
                  
                       $i++;
                      }
                     break;

                   }


                   mssql_close();

              }
              break;

      case "unite":
      //needs to be coded
       $tempcount=0;
      break;

      case "femis":
      //needs to be coded
       $tempcount=0;
      break;
     }

     return $tempcount;

    


  }

  function count_course_internal_student_list($coursecode,$courseblock,$courseocc) {
     $query = "SELECT count(student_id) as count from ep_student_courses WHERE ep_student_courses.course_id = '$coursecode' ;"; 

     $link=dbconnect();
     $result=mysqli_query($link,$query) or die(mysqli_error($link));
     $num = mysqli_result($result,0,"count");

    return $num;
  }

  function get_internal_course_student_list($coursecode,$courseblock,$courseocc) {
    //
    //This function will return a list of students
    //against a course code INTERNALLY from epmanager
    //(i.e. no SITS or external DB) this is for the
    //benefit of the theme installer

    $link=dbconnect();
    $existing_warning='';

    $query = "SELECT ep_student_courses.student_id, student_nicename FROM ep_student_courses JOIN ep_students ON ep_student_courses.student_id = ep_students.student_id ";

    
    $query.= " WHERE course_id = '$coursecode' ";
    if(($courseblock!='')||($courseocc!='')) {
        $query .= " AND course_block='$courseblock' AND course_occurrence='$courseocc' ";
    }
    $query .= ";";

    $result=mysqli_query($link,$query) or die(mysqli_error($link));
    $num=mysqli_num_rows($result);


    if ($num > 0)
    {
      
      $output_HTML =  "<select size='20' style='width:300px' class='formleft' multiple name='studentlist[]'>\n\r";
      $i=0;
      while ($i < $num)
      {
        
        $studentcode=mysqli_result($result,$i,"student_id");
        $studentnicename=mysqli_result($result,$i,"student_nicename");

        if (check_student_ep($studentcode) > 0)
        {

            $output_HTML = $output_HTML . "  <option value='$studentcode'>$studentcode, $studentnicename</option>\n\r";
          
         
        }
       
        
          $i++;
      }


      $output_HTML = $output_HTML . "</select>";


     

      
        $output_HTML = $existing_warning . $output_HTML;
      
    }
    else
    {
      $output_HTML = "no students found";
    }

    echo $output_HTML;

  }

  function get_course_student_list($coursecode='',$courseblock='',$courseocc='',$session='',$scope='all')  {
    //
    //This function is dependant on your MIS system. Reword
    //This SQL query to get StudentCode and FullName
    //fields back from your own system.
    //
    //
    // The scope options are:
    //
    // all : Return all students, eportfolio or not
    // existing : Return only students who already have an eportfolio
    // not : Return only students who do not already have an ePortfolio
    global $studentcode;

    $db_server = STUDENT_DB_SERVER;
    $db_database = STUDENT_DB_DATABASE;
    $db_username = STUDENT_DB_USERNAME;
    $db_password = STUDENT_DB_PASSWORD;

    $db = mssql_connect($db_server,$db_username,$db_password) or die("ERROR CONNECTING TO: ".$db_server."<br>".mysqli_error($link));
    mssql_select_db($db_database,$db) or die("COULD NOT SELECT DATABASE: ".$db_database."<br>".mysqli_error($link));

    $php_errormsg = "";
    $existing_test=FALSE;

    $output_HTML = "";

    $query = "SELECT sce_stuc as StudentCode, sce_srtn + ',' + sce_stuc as FullName , sce_blok, sce_occl ";
    $query = $query . "FROM dbo.srs_sce WHERE (ISNULL(RTRIM(dbo.srs_sce.sce_stac),'')";
    $query = $query . "='C') and sce_crsc = '$coursecode' and sce_ayrc = '$session' ";
    
    if ($courseblock!='') {
        $query = $query . " and sce_blok = '$courseblock' ";
    }
    
    if ($courseocc!='') {
        $query = $query . " and sce_occl = '$courseocc' ";
    }
    

    $result = mssql_query($query,$db) or die ('Query failed: '.$query);
   

    $num=mssql_num_rows($result);
   

    if ($num > 0)
    {
      
      $output_HTML =  "<select  class='formleft' multiple name='studentlist[]'>\n\r";
      $i=0;
      while ($i < $num)
      {
        
        $studentcode=mssql_result($result,$i,"StudentCode");



        $name=mssql_result($result,$i,"FullName");

        $name=preg_replace('/\'/', '', $name);

        if (check_student_ep($studentcode) > 0)
        {
          if ($scope == "all")
          {
            $output_HTML = $output_HTML . "  <option class='warning' value='$studentcode'>$name</option>\n\r";
            $existing_test=TRUE;
          }

          if ($scope == "existing")
          {
            $output_HTML = $output_HTML . "  <option value='$studentcode'>$name</option>\n\r";
            $existing_test=TRUE;
          }
        }
        else 
          if ($scope == "not" || $scope == "all")
            $output_HTML = $output_HTML . "  <option value='$studentcode'>$name</option>\n\r";
          
        
          $i++;
      }
      $output_HTML = $output_HTML . "</select>";


	switch ($scope) {
	case "all":
    		$existing_warning = "<p class='warning'>Students listed in bold and red already have an ePortfolio. </p>";
    		break;
	case "not":
    		$existing_warning = "<p class='warning'>Students listed do not have an ePortfolio.</p>";
    		break;
	case "existing":
    		$existing_warning = "<p class='warning'>Students listed have an ePortfolio.</p>";
    		break;
	}

     

      
        $output_HTML = $existing_warning . $output_HTML;
      
    }
    else
    {
      $output_HTML = "no students found";
    }
    mssql_close();




    echo $output_HTML;
  }

 

  function get_mis_nicename($student=NULL) {
  // When creating the portfolio, get the nicename
  // from the MIS system because there will 
  // be no local record of the student nicename
  // There are embedded switch statements first for
  // the MIS system and below that the DBMS
  // Sorry I can only do SITS on MSSQL because that
  // is what we use here.
  if (MIS_INTEGRATION=='on') {
    global $nicename;
    $db_server = STUDENT_DB_SERVER;
    $db_database = STUDENT_DB_DATABASE;
    $db_username = STUDENT_DB_USERNAME;
    $db_password = STUDENT_DB_PASSWORD;

    switch (MIS_SYSTEM)
    {
      case "sits":

          switch (MIS_DBMS)
          {
             
               case "mysql":  //MySQL
                 //needs to be coded
                 $nicename="unknown";
               break;

               case "mssql":  //Microsoft SQL Server
                 
                    $db = mssql_connect($db_server,$db_username,$db_password) or die("ERROR CONNECTING TO: ".$db_server."<br>".mysqli_error($link));
                    mssql_select_db($db_database,$db) or die("COULD NOT SELECT DATABASE: ".$db_database."<br>".mysqli_error($link));

                    $php_errormsg = "";

                    $query = 'SELECT stu_name FROM ins_stu ';
                    $query = $query . ' WHERE stu_code = \''.$student.'\' ';
  
                    $result = mssql_query($query,$db) or die ('Query failed: '.$query);
                    $num=mssql_num_rows($result);

                    if ($num > 0)
                    {
                     $i=0;
                      while ($i < $num)
                      {
       
                        $nicename=mssql_result($result,$i,"stu_name");
                        $nicename=preg_replace('/\'/', '', $nicename);

                        $i++;
                      }
                    }
                    else
                    { 
                  //if there is no MIS entry, try for a local entry
                  //if this fails there is no previous record for this
                  //student
                    $nicename=get_local_nicename($student);
                  }
                  mssql_close();
                
              break;

              case "oracle":  //Oracle DBMS
                 //needs to be coded
                 $nicename="unknown";
              break;
 
          }
      break;

      case "unite":
      //needs to be coded
       $nicename="unknown";
      break;

      case "femis":
      //needs to be coded
      $nicename="unknown";
      break;


    }

    

    if(($nicename=='')||($nicename==NULL)) {
        $nicename='unknown';
    }
    return $nicename;

  }//does the function run at all (if MIS=TRUE)
}

/**
*   This doesn't exist in mysqli
*
**/
function mysqli_result($res, $row, $field=0) {
    $res->data_seek($row);
    $datarow = $res->fetch_array();
    return $datarow[$field];
} 




?>
