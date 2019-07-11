<?php
//This is a set of functions relating to a users
//wordpress installation. Starting from creating
//the wordpress site including stuff like
//retrieving post content and getting
//attachment paths
//$student, $lecturer, $adminpass, $courseid, $courseblock, $courseocc)
function create_portfolio($student, $lecturer, $adminpass, $courseid, $courseblock, $courseocc)  {

   $student=strtolower($student);
   $lecturer=strtolower($lecturer);
   $courseblock=strtolower($courseblock);
   $courseocc=strtolower($courseocc);

   //This function just
   //populates the wordpress tables.
   //Entries are added to the ep_students
   //and ep_student_lecturers meta table at the end
   // get the nicename from your student records system or locally
   $nicename=NULL;

   if (MIS_INTEGRATION=='on')
    $nicename =get_mis_nicename($student);
   else
    $nicename =get_local_nicename($student);

    //insert new user database tables 
    
    include_once('libraries/class-phpass.php');
    $wp_hasher = new PasswordHash(8, true);
    $adminpass=$wp_hasher->HashPassword( trim( $adminpass ) );
    $lecturerpass=LECTURER_PASS;
    $lecturerpass=$wp_hasher->HashPassword( trim( $lecturerpass ) );
    $eproot=PORTFOLIO_PATH;
    $lecturerpass= md5(LECTURER_PASS);
    $neteproot='http://' . INTERNET_EPROOT;
    $netpath=INTERNET_EPROOT;
    $netpath=str_replace("/","\/",$netpath);
    $neteproot=str_replace("/","\/",$neteproot);
    /** 
    * create base tables for the new user - they will be populated later
    *
    **/
    exec('cat /etc/epmanager3/sql/base_tables.sql | sed "{ 
         s/joebloggs/'.$student.'/g 
    }" > /tmp/base_tables.sql');
    exec('mysql -h '.EP_DB_SERVER.' -u'.EP_DB_USERNAME.' -p'.EP_DB_PASSWORD.' '.EP_DB_DATABASE.'  < /tmp/base_tables.sql');

    /**
    *
    * now populate the user tables with the default eportfolio
    * these require joebloggs and lecturer to be replaced
    **/
    $queryfilearray=array("joebloggs_users.sql","joebloggs_usermeta.sql");
    
    foreach($queryfilearray as $queryfile) {
    
        exec('cat /etc/epmanager3/sql/'.$queryfile.' | sed "{ 
           s/joebloggs/'.$student.'/g 
           s/lecturer/'.$lecturer.'/g
           s/LECTUREREMAIL/'.$lecturer.'@'.INTERNET_DOMAIN.'/g       
           s/STUDENTEMAIL/'.$student.'@'.INTERNET_DOMAIN.'/g         
        }" > /tmp/'.$queryfile);    
        exec('mysql -h '.EP_DB_SERVER.' -u'.EP_DB_USERNAME.' -p'.EP_DB_PASSWORD.' '.EP_DB_DATABASE.'  < /tmp/'.$queryfile);
    }


    /**
    *
    *  The following files only need the joebloggs reference to
    *  be changed so I can use an array and a loop
    **/
    $queryfilearray=array("joebloggs_uam_accessgroups.sql","joebloggs_uam_accessgroup_to_object.sql","joebloggs_term_taxonomy.sql","joebloggs_terms.sql","joebloggs_term_relationships.sql","joebloggs_postmeta.sql","joebloggs_comments.sql");
    
    foreach($queryfilearray as $queryfile) {
        exec('cat /etc/epmanager3/sql/'.$queryfile.' | sed "{ 
           s/joebloggs/'.$student.'/g 
        }" > /tmp/'.$queryfile);   

        exec('mysql -h '.EP_DB_SERVER.' -u'.EP_DB_USERNAME.' -p'.EP_DB_PASSWORD.' '.EP_DB_DATABASE.'  < /tmp/'.$queryfile);    
        
    }
        $queryfile = 'baseposts.sql';
        exec('cat /etc/epmanager3/sql/base_posts.sql | sed "{ 
           s/joebloggs/'.$student.'/g 
           s/interneteproot/'.$netpath.'/g
        }" > /tmp/'.$queryfile);   
        
        exec('mysql -h '.EP_DB_SERVER.' -u'.EP_DB_USERNAME.' -p'.EP_DB_PASSWORD.' '.EP_DB_DATABASE.'  < /tmp/'.$queryfile);        
   
    /**
    *
    *   The following have several replacements
    *   INTERNET_EPROOT, STUDENTEMAIL, PORTFOLIO_PATH
    **/
    $eproot=INTERNET_EPROOT;
    $eproot=str_replace("/","\/",$eproot);
    
    $portfoliopath=PORTFOLIO_PATH;
    $portfoliopath=str_replace("/","\/",$portfoliopath);
    
    exec('cat /etc/epmanager3/sql/joebloggs_options.sql | sed "{ 
         s/joebloggs/'.$student.'/g 
         s/interneteproot/'.$eproot.'/g 
         s/STUDENTEMAIL/'.$student.'@'.INTERNET_DOMAIN.'/g 
         s/portfoliopath/'.$portfoliopath.'/g 
    }" > /tmp/joebloggs_options.sql');
    exec('mysql -h '.EP_DB_SERVER.' -u'.EP_DB_USERNAME.' -p'.EP_DB_PASSWORD.' '.EP_DB_DATABASE.'  < /tmp/joebloggs_options.sql');
   
    
    //add on 
    
    $link=dbconnect();
    
   
    // update the user passwords - both student and lecturer
    $query="update {$student}_users set user_pass='$adminpass' where user_login='$student' LIMIT 1;";
    mysqli_query($link,$query) or die(mysqli_error($link));
    $query="update {$student}_users set user_pass='$lecturerpass' where user_login='$lecturer' LIMIT 1;";
    mysqli_query($link,$query) or die(mysqli_error($link));
    
    
    // include the next two lines if secure socket login
    if (INTERNET_EPSECURE=="on") {
    $query = 'update '.$student.'_options set option_value = replace(option_value,\'http:\',\'https:\') WHERE option_id = 1;';
    mysqli_query($link,$query) or die(mysqli_error($link));
    }

    $query = 'update '.$student.'_users set user_nicename=\''.$nicename.'\' where ID=1;'; //id 1 =admin
    mysqli_query($link,$query) or die(mysqli_error($link));
   

    $query = 'update '.$student.'_options set option_value=\''.$nicename.'\' where option_name=\'blogname\';'; //id 1 =admin
    mysqli_query($link,$query) or die(mysqli_error($link));
   


    $query = 'delete FROM ep_students WHERE student_id = \''.$student.'\';';
    mysqli_query($link,$query) or die(mysqli_error($link));
    //update the ep_students table with nicename from student records and username
    $query = 'insert into ep_students VALUES (NULL,\''.PORTFOLIO_PATH.$student.'\',\''.$student.'\',\''.$nicename.'\');';
    
    mysqli_query($link,$query) or die(mysqli_error($link));


    $query = 'delete FROM ep_student_lecturers WHERE student_id = \''.$student.'\';';
    mysqli_query($link,$query) or die(mysqli_error($link));
    $query = 'insert into ep_student_lecturers VALUES (\''.$student.'\',\''.$lecturer.'\');';
    mysqli_query($link,$query) or die(mysqli_error($link));


    // delete all course references
    $query = 'delete FROM ep_student_courses WHERE student_id = \''.$student.'\';';
    mysqli_query($link,$query) or die(mysqli_error($link));
    // create new course reference
    $query = 'insert into ep_student_courses VALUES (\''.$student.'\',\''.$courseid.'\',\''.$courseblock.'\',\''.$courseocc.'\');';
    mysqli_query($link,$query) or die(mysqli_error($link));

    
    
    create_webfolders($student);
    create_student_config_file($student);

    

    //put the ILP posts in for the current session
    restore_eilp_post(21,$student);
    restore_eilp_post(22,$student);
    restore_eilp_post(23,$student);
    restore_eilp_post(24,$student);
    restore_eilp_post(25,$student);

    $query = 'update '.$student.'_options set option_value = \''.$nicename.'\' WHERE option_name=\'blogname\';';
    mysqli_query($link,$query) or die(mysqli_error($link));
    dbdisconnect($link);

}


function add_ep_verifier($student,$verifierusername,$verifierpass,$verifierdomain) {
//This will allow new lecturer level users to be added with a manual password
    include_once('libraries/class-phpass.php');
    $wp_hasher = new PasswordHash(8, true);
    $verifierpass= $wp_hasher->HashPassword( trim( $verifierpass ) );
    $time=date('Y-m-d h:i:s');
     
    
    $link=dbconnect();
    
    $query="INSERT INTO `{$student}_users` VALUES (NULL, '{$verifierusername}', '{$verifierpass}', '{$verifierusername}', '{$verifierusername}@{$verifierdomain}', 'http://', NOW(), '', 0, '{$verifierusername}');";    

  
    mysqli_query($link,$query) or die(mysqli_error($link));

    $query="SELECT ID from {$student}_users WHERE user_login='{$verifierusername}' LIMIT 1;";
    $result=mysqli_query($link,$query);
    $tempID=mysqli_result($result,0,"ID");

    $query="INSERT INTO `{$student}_usermeta` VALUES (NULL,$tempID,'first_name','verifierfirstname'),
    (NULL,$tempID,'last_name','verifiersurname'),
    (NULL,$tempID,'nickname','verifier'),
    (NULL,$tempID,'description','External verifier'),
    (NULL,$tempID,'rich_editing','true'),
    (NULL,$tempID,'comment_shortcuts','false'),
    (NULL,$tempID,'admin_color','fresh'),
    (NULL,$tempID,'use_ssl','0'),
    (NULL,$tempID,'show_admin_bar_front','true'),
    (NULL,$tempID,'{$student}_capabilities','a:1:{s:11:\"contributor\";b:1;}'),
    (NULL,$tempID,'{$student}_user_level','1'),(NULL,$tempID,'dismissed_wp_pointers','wp330_toolbar,wp330_saving_widgets,wp340_choose_image_from_library,wp340_customize_current_theme_link,wp350_media,wp360_revisions,wp360_locks');";

    mysqli_query($link,$query) or die(mysqli_error($link));

    dbdisconnect($link);
    
}


function restore_eilp_post($postid,$username) {
// This function will replace a single
// eILP entry with it's default contents

    $replaceid=0;
    $replaceid=generate_ilp_id($postid);
    $currentsession=get_current_session_long_format();
    $posttime=date('Y-m-d H:i:s');
    $year=date('Y');
    $currentmonth=intval(date('n'));
    
    if($currentmonth<8) {
        $sessionyears=($year-1).'\/'.substr($year,-2);
    }
    else
    {
        $sessionyears=$year.'\/'.substr($year+1,-2);
    }
    $eilpPosts = array(21,22,23,24,25);
    

    if (in_array($postid,$eilpPosts)) {
        //do the restoration by deleting existing posts
        //with this id then recreating them
        $postreplace="100$postid";
        if($currentmonth<8) {
            $newpostidstring=($year-1);
            $newpostidstring.=($year);
            $newpostidstring.=$postid;
            $newpostid=intval($newpostidstring);
        }
        else
        {
            $newpostidstring=($year);
            $newpostidstring.=($year+1);
            $newpostidstring.=$postid;
            $newpostid=intval($newpostidstring);

        }
        $postfile="post_$postid.sql";
        $eproot=INTERNET_EPROOT;
        $eproot=str_replace("/","\/",$eproot);
      
        $link=dbconnect();
        
        $query = "DELETE from `".$username."_posts` WHERE ID = $replaceid;";
        mysqli_query($link,$query);
        $query = "DELETE from `".$username."_posts` WHERE post_parent = $replaceid;";
        mysqli_query($link,$query);        
        $query = "DELETE from `".$username."_postmeta` WHERE post_id = $replaceid;";
        mysqli_query($link,$query);
        $query = "DELETE from `".$username."_uam_accessgroup_to_object` WHERE object_id = $replaceid;";
        mysqli_query($link,$query);
        $query = "DELETE from `".$username."_term_relationships` WHERE object_id = $replaceid;";
        mysqli_query($link,$query);

            exec('cat /etc/epmanager3/sql/'.$postfile.' | sed "{ 
                 s/joebloggs/'.$username.'/g
                 s/interneteproot/'.$eproot.'/g       
                 s/sessionyears/'.$sessionyears.'/g                    
                 s/postdate/'.$posttime.'/g                   
                 s/pdgmt/'.$posttime.'/g
                 s/newpostid/'.$replaceid.'/g                    
                }" > /tmp/'.$postfile);
                
        exec('mysql -h '.EP_DB_SERVER.' -u'.EP_DB_USERNAME.' -p'.EP_DB_PASSWORD.' '.EP_DB_DATABASE.'  < /tmp/'.$postfile);

    }
    else
    {
        return false;
    }

}

function check_post_exists($postid,$student) {
//returns true if post exists in the users
//_posts table

/*local variables*/
$postcount=0;

    $link=dbconnect();
    //check that the post is real
    $query = 'SELECT COUNT(ID) as PostCount FROM '. $student .'_posts WHERE ID = '.$postid.';';
    $result=mysqli_query($link,$query);
    $postcount=mysqli_result($result,0,"PostCount");
    dbdisconnect($link);

    if ($postcount > 0) {
        return true;
    }
    else
    {
        return false;
    }

}

function check_comment_exists($commentid,$student) {
//returns true if comment exists in the users
//_comments table

/*local variables*/
$commentcount=0;

    $link=dbconnect();
    //check that the post is real
    $query = 'SELECT COUNT(comment_ID) as CommentCount FROM '. $student .'_comments WHERE comment_ID = '.$commentid.';';
    $result=mysqli_query($link,$query);
    $postcount=mysqli_result($result,0,"CommentCount");
    dbdisconnect($link);

    if ($commentcount > 0) {
        return true;
    }
    else
    {
        return false;
    }

}

function get_exit_review($student) {
//this function will return
//the contents of the eILP exit review
//for this student

$exitreview='';

      $currentsession='';
      $month = date('m');
      $currentyear= date('Y');
      if($month > 8) {
          $currentsession = $currentyear . substr($currentyear+1,-2) . $id;
      }
      else
      {
          $currentsession = $currentyear -1 . substr($currentyear,-2) . $id;
      }


      $currentsession=intval($currentsession);
      $fullid=$currentsession . '24';

      $query = 'SELECT post_date, post_content FROM '. $student .'_posts where id=24 or id=' . $fullid . ';';

        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $exitreview=mysqli_result($result,0,"post_content");
        
        $exitreview=str_replace("Your personal tutor should use this meeting to review your progress throughout the programme."," ",$exitreview);
        $exitreview=str_replace("Any goals set which you have not achieved should be discussed, and information and advice given on how to address any outstanding issues, where possible."," ",$exitreview);
             
                     
        dbdisconnect($link);
 

    return $exitreview;


}

function get_post_title($postid,$student) {
//this function will simply return
//the title of a post by id after checking
//that the post exists (of course!)

/*local variables*/
$posttitle='';
    
    if (check_post_exists($postid,$student)==true) {
        $query = 'SELECT post_title FROM '. $student .'_posts WHERE ID = '.$postid.';';
        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $posttitle=mysqli_result($result,0,"post_title");
        dbdisconnect($link);
    }
    else
    {
        $posttitle="unknown";  
    }

    return $posttitle;

}

function get_cat_name($categoryid,$student) {

//this function will simply return
//the category name of a post by cat id 

/*local variables*/
$catname='';

        $query = 'SELECT name FROM '. $student .'_terms WHERE term_id = '.$categoryid.';';

        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $catname=mysqli_result($result,0,"name");
        dbdisconnect($link);
 

    return $catname;

}

function get_post_guid($postid,$student) {
//this function will simply return
//the title of a post by id after checking
//that the post exists (of course!)

/*local variables*/
$postguid='';
    
    if (check_post_exists($postid,$student)==true) {
        $query = 'SELECT guid FROM '. $student .'_posts WHERE ID = '.$postid.';';


        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $postguid=mysqli_result($result,0,"guid");
        dbdisconnect($link);
    }
    else
    {
        $posttitle="unknown";  
    }

    return $postguid;

}

function get_post_content($postid,$student) {

/*local variables*/
$postcontent="<div id='content'>\n\r";
    
    if (check_post_exists($postid,$student)==true) {
        $query = 'SELECT post_content FROM '. $student .'_posts WHERE ID = '.$postid.';';
        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $postcontent.=mysqli_result($result,0,"post_content");
        dbdisconnect($link);
    }
    else
    {
        $postcontent="unknown";  
    }

    $postcontent.="</div>\r\n";

    return $postcontent;
}

function get_comment_author($commentid,$student) {

/*local variables*/
$commentauthor='';
    
    if (check_comment_exists($commentid,$student)==true) {
        $query = 'SELECT comment_author FROM '. $student .'_comments WHERE comment_ID = '.$commentid.';';
        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $commentauthor=mysqli_result($result,0,"comment_author");
        dbdisconnect($link);
    }
    else
    {
        $commentauthor="unknown";  
    }

    return $commentauthor;
}

function get_comments_array($postid,$student) {
/*
This function will return an array containing
all comments which are approved for a post id

0,0 = how many comments attach to the post id
1,0 = comment_ID 
1,1 = comment_author
1,2 = comment_date
1,3 = comment_content

2,0 = comment_ID
....etc etc 
*/
        $commentarray=array();
        $query = 'SELECT * FROM '. $student .'_comments WHERE comment_post_ID = \''.$postid.'\' and comment_approved=\'1\' ORDER BY comment_date_gmt;';

        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $num=mysqli_num_rows($result);

        $commentarray[0][0]=$num;

        if ($num > 0) {
            $i=0;

            while($i < $num) {
                 $commentid=mysqli_result($result,$i,"comment_ID");
                 $commentauthor=mysqli_result($result,$i,"comment_author");
                 $commentdate=mysqli_result($result,$i,"comment_date");
                 $commentcontent=mysqli_result($result,$i,"comment_content");

                 $commentarray[$i+1][0]=$commentid;     // add 1 to $i because 
                 $commentarray[$i+1][1]=$commentauthor; // position 0 is taken by the counter
                 $commentarray[$i+1][2]=$commentdate;
                 $commentarray[$i+1][3]=$commentcontent;

                 $i++;
            }
        }

        dbdisconnect($link);

        return $commentarray;

}


function get_post_date($postid,$student) {

/*local variables*/
$postdate='';
    
    if (check_post_exists($postid,$student)==true) {
        $query = 'SELECT post_date FROM '. $student .'_posts WHERE ID = '.$postid.';';
        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $postdate=mysqli_result($result,0,"post_date");
        dbdisconnect($link);
    }
    else
    {
        $postdate="unknown";  
    }

    return $postdate;
}


function get_post_last_update($postid,$student) {
// find out when a post was last updated
/*local variables*/
$postdate='';
    
    if (check_post_exists($postid,$student)==true) {
        $query = 'SELECT post_modified FROM '. $student .'_posts WHERE ID = '.$postid.';';
        $link=dbconnect();
        $result=mysqli_query($link,$query);
        $postdate=mysqli_result($result,0,"post_modified");
        dbdisconnect($link);
    }
    else
    {
        $postdate="unknown";  
    }

    return $postdate;
}



function get_mime_type($student,$file) {
//This function will hopefully
//return a useful mimetype
//based on the file referenced

$inputfile="/tmp/" . $student . "/" . $file;

if(!file_exists($inputfile)) {
  $inputfile="/tmp/" . $student . "/products/" . $file;
}
$mimetype=mime_content_type($inputfile);


    if ($mimetype=='') {
        $mimetype='unknown';
    }

    return $mimetype;

}

function get_media_mode($student,$file) {
//This function will hopefully
//return a useful mimetype
//based on the file referenced

$inputfile="/tmp/" . $student . "/" . $file;

if(!file_exists($inputfile)) {
  $inputfile="/tmp/" . $student . "/products/" . $file;
}
$mimetype=mime_content_type($inputfile);
$mediamode='unknown';

switch ($mimetype) {

    case 'text/word':
        $mediamode='Text';
        break;
    case 'text/html':
        $mediamode='Text';
        break;
    case 'text/plain':
        $mediamode='Text';
        break;
    case 'audio/mpeg':
        $mediamode='Audio';
        break;
    case 'image/jpeg':
        $mediamode='Image';
        break;
    case 'image/png':
        $mediamode='Image';
        break;
    case 'video/flv':
        $mediamode='Video';
        break;
    case 'video/mpeg':
        $mediamode='Video';
        break;
    case 'video/quicktime':
        $mediamode='Video';
        break;
    default:
        $mediamode='unknown';
        break;

   }
 
    return $mediamode;

}

function post_has_comments($postid,$userid) {
//this function looks in the _comments table
//to see if there are any comments relating
//to the current post
    
    $link=dbconnect();
    $query = 'SELECT COUNT(comment_post_ID) as CommentCount FROM '. $userid .'_comments WHERE comment_post_ID = '.$postid.';';

    $result=mysqli_query($link,$query);

    $poststatus=mysqli_result($result,0,"CommentCount");

    dbdisconnect($link);

    if($poststatus>0) {
        return true;
    }
    else
    {
        return false;
    }

}

function get_attachment_path($id,$userid) {
//post in the id of a post and the
//userid of the blog tables
//this function returns true
//if the post describes a file attachment

    $link=dbconnect();
    $query = 'SELECT meta_value FROM '. $userid .'_postmeta WHERE post_id = '.$id.' and meta_key=\'_wp_attached_file\' LIMIT 1;';

    $result=mysqli_query($link,$query);

    $poststatus=mysqli_result($result,0,"meta_value");

    dbdisconnect($link);

  
        return $poststatus;
   
}

function check_attachment($id,$userid) {
//post in the id of a post and the
//userid of the blog tables
//this function returns true
//if the post describes a file attachment

    $link=dbconnect();
    $query = "SELECT post_type FROM {$userid}_posts WHERE ID = $id and post_type='attachment' LIMIT 1;";

    $result=mysqli_query($link,$query);

    $posttype=mysqli_result($result,0,"post_type");

    dbdisconnect($link);

    if($posttype=='attachment') {
        return true;
    }
    else
    {
        return false;
    }

}


function generate_ilp_id($id) {
//This will generate an id with
//the current session at the beginning like this
//id = 25, current session is 200910
//return will be 20091025
//allows us to keep old ilp posts
//and tutorial records

      $currentsession='';
      $month = date('m');
      $currentyear= date('Y');
      if($month > 7) {
          $currentsession = $currentyear . substr($currentyear+1,-2) . $id;
      }
      else
      {
          $currentsession = $currentyear -1 . substr($currentyear,-2) . $id;
      }
      return intval($currentsession);

}


function get_current_session_long_format() {
//returns the current academic session in the form
//2009/10 for presentation purposes

      $currentsession='';
      $month = date('m');
      $currentyear= date('Y');
      if($month > 7) {
          $currentsession = $currentyear . '/' . substr($currentyear+1,-2);
      }
      else
      {
          $currentsession = $currentyear -1 . '/' . substr($currentyear,-2);
      }

      return $currentsession;

}

//These function need tidying up
    function get_last_update($student) {
         $link=dbconnect();

         $query = "SELECT post_modified FROM $student" . "_posts order by post_modified DESC limit 1; ";

         $numstudents = mysqli_query($link,$query) or die("Select Top Date Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];
     


    }


    function get_last_post_update($student,$postid) {
         $link=dbconnect();

         $query = "SELECT post_modified FROM $student" . "_posts WHERE ID=$postid limit 1; ";

         $numstudents = mysqli_query($link,$query) or die("Select Top Date Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];
     


    }



    function get_author_name($student) {
         $link=dbconnect();

         $query = "SELECT option_value FROM $student" . "_options WHERE option_name='blogname' limit 1; ";

         $numstudents = mysqli_query($link,$query) or die("Select option_value Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];
   
    }


    function get_author_email($student) {
         $link=dbconnect();

         $query = "SELECT option_value FROM $student" . "_options WHERE option_name='admin_email' limit 1; ";

         $numstudents = mysqli_query($link,$query) or die("Select option_value Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];
    
    }


    function get_author_id($student,$username) {
         $link=dbconnect();

         $query = "SELECT ID FROM $student" . "_users WHERE user_login='{$username}' limit 1; ";

         $numstudents = mysqli_query($link,$query) or die("Select option_value Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];


    }

    function get_post_parent($postid,$student) {
         $link=dbconnect();

         $query = "SELECT post_parent FROM $student" . "_posts WHERE ID='{$postid}' limit 1; ";

         $numstudents = mysqli_query($link,$query) or die("Select get_post_parent Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];


    }
    
    function get_post_category($postid,$student) {
          $link=dbconnect();

         $query = "SELECT term_taxonomy_id, name from $student" . "_term_relationships ";
         $query .= "JOIN $student" . "_terms on $student" . "_terms.term_id = $student" . "_term_relationships.term_taxonomy_id ";
         $query .= " WHERE object_id=$postid limit 1; ";
         $numstudents = mysqli_query($link,$query) or die("Select get_post_category Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

         
        return $numstudent;
     
    
    
    }

    function get_child_count($postid,$student) {
         $link=dbconnect();

         $query = "SELECT count(ID) FROM $student" . "_posts WHERE post_parent='{$postid}' ; ";

         $numstudents = mysqli_query($link,$query) or die("Select get_child_count Failed!");
         $numstudent = mysqli_fetch_array($numstudents);

         dbdisconnect($link);

      return $numstudent[0];


    }

  
    function get_child_array($postid,$student) {
         $temparray=array();
         $link=dbconnect();

         $query = "SELECT id FROM $student" . "_posts WHERE post_parent='{$postid}' ; ";




         $result=mysqli_query($link,$query);
        


        while ($row = mysqli_fetch_array($result)) {
            // This will call the above function.
            array_walk($row, 'modify_field');
            array_push($temparray, $row);
        }
        dbdisconnect($link);

      return $temparray;


    }


    function get_post_comments($postid,$student) {
         $temparray=array();
         $link=dbconnect();

         $query = "SELECT comment_post_ID, comment_author, comment_content, comment_date FROM $student" . "_comments WHERE comment_post_ID='{$postid}' ; ";

         $result=mysqli_query($link,$query);

        while ($row = mysqli_fetch_array($result)) {
            // This will call the above function.
            array_walk($row, 'modify_field');
            array_push($temparray, $row);
        }
        dbdisconnect($link);

      return $temparray;

    }


    function get_people_array($student) {
         $temparray=array();
         $link=dbconnect();

         $query = "SELECT ID, user_login, user_nicename, user_email FROM $student" . "_users ; ";

         $result=mysqli_query($link,$query);

         while ($row = mysqli_fetch_array($result)) {
            // This will call the above function.
            array_walk($row, 'modify_field');
            array_push($temparray, $row);
         }
         dbdisconnect($link);

         return $temparray;

    }



    function get_person_metadata($student,$userid) {
         $temparray=array();
         $link=dbconnect();

         $query = "SELECT * FROM $student" . "_usermeta where user_id={$userid}; ";

         $result=mysqli_query($link,$query);

        while ($row = mysqli_fetch_array($result)) {
            // This will call the above function.
            array_walk($row, 'modify_field');
            array_push($temparray, $row);
        }
        dbdisconnect($link);

      return $temparray;

    }


?>
