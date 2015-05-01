<?php
//These functions are all devoted
//to exporting HTML
//files.
//File is a bit scrappy just now :-(
//


function html_create_html_file($student,$id,$content,$title) {
    //this function will create an html file in 
    //the prep folder for the ims zip package
    //Will remove spaces from the posttitle and use
    //as the filename along with the postID to
    //avoid naming conflicts
    //id, title and content
    $fileintro=''; //putting a <p><b> at the top with the title inside
    $filetitle = ereg_replace('[^A-Za-z0-9]', '_', $title );
    $filetitle = str_replace('.', '', $filetitle );

    $myFile =  "/tmp/" . $student . "/" . $id . "_" . $filetitle . ".html";
    $fh = fopen($myFile, 'w') or die("can't open file");
    
    fwrite($fh, $content);

    fclose($fh);
    $fileref=$id . "_" . $filetitle;
    return $fileref;
}

function html_create_index_file($student,$content) {
    //this function will create an html file in 
    //the prep folder for the ims zip package
    //Will remove spaces from the posttitle and use
    //as the filename along with the postID to
    //avoid naming conflicts
    //id, title and content

    $myFile =  "/tmp/" . $student . "/index.html";
    $fh = fopen($myFile, 'w') or die("can't open file");
    
    fwrite($fh, $content);

    fclose($fh);
    $fileref="index.html";
    return $fileref;
}


function get_html_css() {
    //This function will return a basic block of css 
    //to include on every output html page.
    $tempstring="";
    $tempstring="<style type='text/css'>\n\r";
    $tempstring.=" body {font-size:0.8em;}\n\r";
    $tempstring.=" h1 {margin-left:205px;padding:3px;}\n\r";
    $tempstring.=" #menu {position:absolute;width:200px;padding:3px;background:#eee;}\n\r";
    $tempstring.=" #content {margin-left:210px;padding:3px;}\n\r";
    $tempstring.=" #comments {margin-left:210px;padding:3px;}\n\r";
    $tempstring.=" a:link, a:visited {color:blue;}\n\r";
    $tempstring.=" a:hover, a:active {color:red;}\n\r";

    $tempstring.="</style>\n\r";

    return $tempstring;
}


function get_html_header($student) {

    $headerString='';

    $headerString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $headerString .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
    $headerString .= " \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
    $headerString .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
    $headerString .= "  <head>\n";
    $headerString .= "    <meta http-equiv=\"expires\" content=\"0\" />\n";
    $headerString .= "    <meta name=\"title\" content=\"ePortfolio\" />\n";
    $headerString .= "    <meta name=\"description\" content=\"Student ePortfolio\" />\n";
    $headerString .= "    <meta name=\"keywords\" content=\"Portfolio eportfolio\" />\n";
    $headerString .= "    <meta name=\"author\" content=\"".$student."\" />\n";
    $headerString .= "    <meta name=\"rating\" content=\"General\" />\n";
    $headerString .= "    <meta name=\"robots\" content=\"index,follow\" />\n";
    $headerString .= "    <title>".$student." Student ePortfolio</title>\n";
    $headerString .= get_html_css();
    $headerString .= "  </head>\n";
    $headerString .= "  <body>\n";

    return $headerString;

}

function get_html_footer() {

    $footerString='';

    $footerString = $footerString ."  </body>\n";
    $footerString = $footerString ."</html>\n";

    return $footerString;

}

function html_export($student) {

    //This function should create a
    //zip package containing
    //an html representation of the 
    //whole eportfolio

    // Attachment array should allow changing
    // files in the wp-content area to be referenced
    // relatively instead of absolute so
    // http://localhost/baxters/wp-content/uploads/2007/01/hello.txt becomes
    // files/hello.txt in the output html
    $menuarray=array();//an array to store the menu in so it can be sorted
    /*
    Structure:
    0,0 = categoryid
    0,1 = catname
    0,2 = post title
    0,3 = post link
    0,4 = postcount
    */
    $firstrecordpast=false;//helps build the <ul> structure in the menu
    $commentstring='';//a place to hold comments until writing into the html file
    $menudiv='';//build up the html for the menu from this
    $cssstring='';//put in some basic CSS to lay out the page
    $attachmentarray=array();
    $postcount=0;//follow the number of posts used to populate menu as well
    $attachmentcount=0;//carry the total number of attachments found
    /*
    Structure:
    0,1 = original path (file a)
    0,2 = replacement path  (file a)
    1,1 = original path  (file b)
    1,2 = replacement path  (file b)..........
    */

    if (!is_dir("/tmp/" . $student)) {
        mkdir("/tmp/" . $student);
        mkdir("/tmp/" . $student . "/files");
    }
    else
    {
        full_rmdir("/tmp/" . $student);
        mkdir("/tmp/" . $student);
        mkdir("/tmp/" . $student . "/files");
    }

    $menudiv=get_html_export_menu($student);
    
    $link=dbconnect();
    $query = "SELECT * from {$student}_posts WHERE NOT post_type = 'revision'; ;";

    $result=mysqli_query($link,$query);
    $count = mysqli_num_rows($result);

    dbdisconnect($link);

    if($count==0) {
        echo "there are no posts in this ePortfolio";
    }
    else
    {
        $attachmentcount=0;
        $postcount=0;
        
        while( $row=mysqli_fetch_assoc($result) ) {
                    
            $postcount++;
            $postid= $row["ID"];
            $postcategoryarray=get_post_category($postid,$student);

            if(count($postcategoryarray)>0) {
                $categoryid=(int)$postcategoryarray[0];
                $categoryname=$postcategoryarray[1];
            }
            else
            {
                $categoryid=1;
                $categoryname='ePortfolio awaiting category';                   
            }
             
            $postdate = get_post_date($postid,$student);
            $postguid = get_post_guid($postid,$student);

            if (check_attachment($postid,$student)==true) {
                $attachmentcount++;
                //if it's an attachment (i.e. a file like a photo or audio file) we need to copy the file
                //into the temp directory 
                $posttitle=get_post_title($postid,$student);  //title of wordpress post
                $attachmentpath=PORTFOLIO_PATH . '/' . $student . '/wp-content/uploads/'. get_attachment_path($postid,$student); //path to the attachment in uploads
                $attachtarget="/tmp/". $student . "/files/" . basename($attachmentpath); // temp copy target for posted file
                $producttarget='files/' . basename($attachmentpath); //target for the location of the file in your zip

                full_copy($attachmentpath,$attachtarget);

                $attachmentarray[$attachmentcount][0]=$postguid;
                $attachmentarray[$attachmentcount][1]=$producttarget;
           }
           else
           {
               //This is a post so we need a postID.htm file and a productID.xml file
               //and to add these into the tree there's HTML in the posts so it's
               //not practical to store that as xml. Easier just to make an HTML
               //file with the contents of the post and refer to it.

               //Create a resource file .htm with the post_content
               $posttitle=get_post_title($postid,$student);
               if($posttitle=='') {
                   $posttitle='unknown';
               }
               
               if (post_has_comments($postid,$student)==true) {

                   //get an array of comments to work on
                   $comments=get_comments_array($postid,$student);
                   /* 
                   0,0 = how many comments attach to the post id
                   1,0 = comment_ID 
                   1,1 = comment_author
                   1,2 = comment_date
                   1,3 = comment_content 
                   */

                   $nocomments=$comments[0][0];

                   if($nocomments>0) { //it should be !
                       $commentstring = "<p><b>comments:</b></p>\n\r";
                       $test=1;
                       while($test < $nocomments+1) {

                          $commentid=$comments[$test][0];
                          $commentauthor=$comments[$test][1];
                          $commentdate=$comments[$test][2];
                          $commentcontent=$comments[$test][3];

                          $commentstring .= "<hr />\n\r";
                          $commentstring .= "<p>Comment by: <b>" . $commentauthor . "</b> on <b>" .  $commentdate . "</b><br />\n\r";
                          $commentstring .= $commentcontent;
                          $commentstring .= "</p>\n\r";

                          $test++;
                       }
                       $commentstring .= "\n\r";
                    }
                }
                else
                {
                    $commentstring="";//reset the comments string
                }
               
                for($x=1;$x<$attachmentcount+1;$x++) {
                    $postcontent=str_replace($attachmentarray[$x][0],$attachmentarray[$x][1],$postcontent);
                }               
               
            //let's create an xHTML page if possible
            $postcontent=get_html_header($student);
            $postcontent.=$menudiv;
            $postcontent.="<h1>" . $posttitle . "</h1>\r\n";
            $postcontent.=get_post_content($postid,$student);//get the HTML out of the post
            $postcontent.="<div id='comments'>" . $commentstring . "</div>\r\n";//get the HTML out of the post
            $postcontent.=get_html_footer();

            $filename = html_create_html_file($student,$postid,$postcontent,$posttitle); //create an HTML file of post content    
               
            //create an index / welcome page

            $postcontent=get_html_header($student);
            $postcontent.=$menudiv;
            $postcontent.="<h1>Student ePortfolio</h1>\r\n";
            $postcontent.="<div id='content'><p>Welcome to your exported ePortfolio. Follow the links in the menu to view your posts and files.</p></div>";//get the HTML out of the post
            $postcontent.=get_html_footer();
 
            html_create_index_file($student,$postcontent);
               
            if(file_exists("/tmp/epmanagerbackup")) {
                full_rmdir("/tmp/epmanagerbackup");
                mkdir("/tmp/epmanagerbackup");
            }  
            else
            {
                mkdir("/tmp/epmanagerbackup");
            }
    
            $archive_target="/tmp/epmanagerbackup/epmanager_HTML_".$student.".zip";
            $archive_source="/tmp/".$student;    
     
            if (file_exists($archive_target))
                unlink ($archive_target);

                $archive = new PclZip($archive_target);
                $v_list = $archive->add($archive_source,PCLZIP_OPT_REMOVE_PATH,'tmp/'.$student);

                if ($v_list == 0) {
                    die("Error : ".$archive->errorInfo(true));
                }
   

                $redirectlocation="http://" . INTERNET_ROOT . "/downloader.php?id=$student&type=html";

                redirect(2,$redirectlocation);               
            }
        }
    }
}


function get_html_export_menu($student) {
    //loop through the resources again creating xhtml files and entries in the menu 
    //theres a relationship id which carries to each record allowing sets of
    //comments to be included in one html file
       
    $postcount=0;
      
    $link=dbconnect();
    $query = "SELECT * from {$student}_posts WHERE NOT post_type = 'revision'; ";

    $result=mysqli_query($link,$query);
    $count = mysqli_num_rows($result);

    dbdisconnect($link);

    if($count==0) {
        echo "there are no posts in this ePortfolio";
    }
    else
    {
        //loop through the resources creating a menu div to place in each html file
        while( $row=mysqli_fetch_assoc($result) ) { 
     
            $postcount++;
            // $categoryid = $row["term_taxonomy_id"];
            // $categoryname = get_cat_name($categoryid,$student);
            $postid= $row["ID"];
            $postguid = get_post_guid($postid,$student);

            if (check_attachment($postid,$student)==true) {

                $posttitle=get_post_title($postid,$student);  //title of wordpress post
                if($posttitle=='') {
                    $posttitle='unknown';
                }
                $attachmentpath=get_attachment_path($postid,$student); //path to the attachment in uploads
                $producttarget='files/' . basename($attachmentpath); //target for the location of the file in your zip

                $menuarray[$postcount][0]=99;             //0 = a file
                $menuarray[$postcount][1]="my files"; //1 = categoryname
                $menuarray[$postcount][2]=$posttitle;    //2 = post title
                $menuarray[$postcount][3]=$producttarget;//3 = post link
                $menuarray[$postcount][4]=$postcount;    //4 = post count
            }
            else
            {         
      
                //This is a post so we need a postID.htm file and a productID.xml file
                //and to add these into the tree there's HTML in the posts so it's
                //not practical to store that as xml. Easier just to make an HTML
                //file with the contents of the post and refer to it.
                //See if the post has been placed in a category (term taxonomy these days)
                $postcategoryarray=get_post_category($postid,$student);
                if(count($postcategoryarray)>0) {
                    $categoryid=(int)$postcategoryarray[0];
                    $categoryname=$postcategoryarray[1];
                }
                else
                {
                    $categoryid=1;
                    $categoryname='ePortfolio awaiting category';                   
                }
               
 
                //Create a resource file .htm with the post_content
                $posttitle=get_post_title($postid,$student);
                
                if($posttitle=='') {
                    $posttitle='unknown';
                }
                //this just copies the functionality from html_create file function
                //to save creating the files twice just to get their name
                $filetitle = ereg_replace('[^A-Za-z0-9]', '_', $posttitle );
                $filetitle = str_replace('.', '', $filetitle );
                $filename = $postid . "_" . $filetitle;

                $diskfile = $filename . '.html'; // a new filename comes back for the menu

                
                    $menuarray[$postcount][0]=$categoryid;   //0 = category id
                    $menuarray[$postcount][1]=$categoryname; //1 = categoryname
                    $menuarray[$postcount][2]=$posttitle;    //2 = post title
                    $menuarray[$postcount][3]=$diskfile;     //3 = post link
                    $menuarray[$postcount][4]=$postcount;    //4 = post count
               

            }
        }
        
        sort($menuarray);
        $previouscategory="999";

        //now build the menu div
        $menudiv = "    <div id='menu'>\r\n";
        $firstrecordpast==false;

        $menudiv .= "  <h3>menu</h3>          <ul><li><a href='index.html'>home</a></li></ul>\r\n";   

        for ($x=1;$x<count($menuarray);$x++) {
            $currentcategory=$menuarray[$x][0];
            $currentcatname=$menuarray[$x][1];
            $currentposttitle=$menuarray[$x][2];
            $currentlink=$menuarray[$x][3];
            $currentcount=$menuarray[$x][4];

            if($currentcategory!=$previouscategory) {
                if($firstrecordpast==true) {
                    $menudiv .= "            </ul>\r\n";   
                }
                $menudiv .= "            <h3>".$currentcatname."</h3>\r\n";     
                $menudiv .= "            <ul>\r\n";     
            }

            $firstrecordpast=true;
            $menudiv .= "            <li><a href='".$currentlink."'>".$currentposttitle."</a></li>\r\n";  

            $previouscategory=$menuarray[$x][0];
        }

        $menudiv .= "        </ul>\r\n";
        $menudiv .= "    </div>\r\n";

        return $menudiv;
    }
}


?>
