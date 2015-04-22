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
        <h2>User management</h2>
        <p>Use this page to add or remove ePortfolio manager users. These are users who have permission to add or remove ePortfolios and in the case of administrators, add or remove users and ePortfolios.</p>

<?php

$flag='';//error flag
$login='';//current login selected

if (isset($_POST['action']) && $_POST['action'] == 'submitted') 
{


  /*  echo '<pre>';
    print_r($_POST);
    echo '<a href="'. $_SERVER['PHP_SELF'] .'">Please try again</a>';
    echo '</pre>'; */


// if the page has been posted then
// act on the data in the form
// VALIDATION can go in the top
// part here then process the options

    if (isset($_POST['create']) && $_POST['create'] == 'create new user!')
    {
        
        $pagevalid=true;
        $errormessage="<p class='warning'>There was a problem!</p><ul>\n\r";

        //echo "code to create new user, check inputs.";

        $login = $_POST['login'];
        $password1 = $_POST['pass1'];
        $password2 = $_POST['pass2'];
        $nicename  = $_POST['nicename'];
        $status = $_POST['statuslist'];
        


       if ($_POST['login'] == '' || $_POST['login'] == 'nothing')
       {
          $errormessage = $errormessage . "  <li>You must provide a user login ID</li>";
          $pagevalid=false;
       }

       if ($_POST['pass1'] == '' || $_POST['pass1'] == 'nothing')
       {
          $errormessage = $errormessage . "  <li>You must enter a password</li>";
          $pagevalid=false;
       }
     
       if ($_POST['pass2'] == '' || $_POST['pass2'] == 'nothing')
       {
          $errormessage = $errormessage . "  <li>You must confirm your password</li>";
          $pagevalid=false;
       }

       if ($_POST['nicename'] == '' || $_POST['nicename'] == 'nothing')
                 $nicename="unknown";
       else
                 $nicename=$_POST['nicename'];

       if ($pagevalid==true)
       {
          if ($_POST['pass1'] != $_POST['pass2'])
          {
            $errormessage = $errormessage . "  <li>Passwords must match</li>";
            $pagevalid=false;
          }  
       }

      

       if (check_user_loginexists($_POST['login']) == 1)
       {
            $errormessage = $errormessage . "  <li>that username already exists!</li>";
            $pagevalid=false;
       }



       if ($pagevalid==true)
       {
        
        $errormessage = "<p class='warning'>User <b>$login</b> added.</p>";

       /* echo "login:" . $login . "<br />"  ;
        echo "nicename:" . $nicename . "<br />"  ;
        echo "pass1:" . $password1 . "<br />"  ;
        echo "pass2:" . $password2 . "<br />"  ;
        echo "status:" . $status . "<br />"  ;*/

        add_new_epuser($login,$nicename,$password1,$status);

        $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Add another user</a></p>";

       }
       else
       {
        $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Try again</a></p>";
       }
       echo $errormessage;
       

    }

    if (isset($_POST['delete']) && $_POST['delete'] == 'delete checked users!')
    {

        $pagevalid=true;
        $errormessage="<p class='warning'>There was a problem!</p><ul>\n\r";

    
     

     foreach($_POST['userlist'] as $user) 
     {

            if ($user == 1)
            {
              $flag=1;
              $pagevalid=false;
            }
     }


 

       if ($flag==1)
       {
          $errormessage = $errormessage . "  <li>you cannot delete the admin user!</li></ul>";
       }

       if ($pagevalid==true)
       {
         foreach($_POST['userlist'] as $user) 
          {
            {
               delete_epuser($user);
            }
          }
          $errormessage = "<p class='warning'>User <b>$login</b> deleted.</p>";            
          $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Delete another user</a></p>";
          
       }
       else
       {
             $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Try again</a></p>";        
       }
       




        
        
        echo $errormessage;
     
    }

    if (isset($_POST['ustatus']) && $_POST['ustatus'] == 'alter user status!')
    {
        $pagevalid=true;
        $errormessage="<p class='warning'>There was a problem!</p><ul>\n\r";
    
        foreach($_POST['userlist'] as $user) 
        {
         // echo "user : $user " . "<br />";
         // echo "status : " . $_POST['statuslist'] . "<br />";
           $newstatus = $_POST['statuslist'];

            if ($user == 1)
            {
              $flag=1;
              $pagevalid=false;
              $errormessage = $errormessage . "  <li>you cannot update the admin user!</li></ul>";
            }

           
        }

       if ($pagevalid==true)
       {
         foreach($_POST['userlist'] as $user) 
          {
            {
              update_user_status($user,$newstatus);
            }
          }
          $errormessage = "<p class='warning'>User <b>$user</b> updated.</p>";            
          $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Update another user</a></p>";
          
       }
       else
       {
             $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Try again</a></p>";        
       }


       
       
        echo $errormessage;
    }


    if (isset($_POST['reset']) && $_POST['reset'] == 'reset password of checked users!')
    {
    
        
        $pagevalid=true;

        foreach($_POST['userlist'] as $user) 
          {
            $userid=$_POST['userlist'][0];
        
            if (check_user_loginexists($userid)!=1)
            {
              $flag=1;
              $pagevalid=false;
            }
            else
            {
              reset_user_password($user);
            }
          }

        if ($pagevalid==true)
        {
          $errormessage = "<p class='warning'>User passwords reset.</p>";
          $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Update another user</a></p>";
        }
        else
        {
          $errormessage = "<p class='warning'>User not found or this is the admin user.</p>";
          $errormessage = $errormessage . "<p><a href='usermanager.php'>&larr;&nbsp;Update another user</a></p>";

        }



        echo $errormessage;
    }

}
else
{
//just display the list of users
?>





 <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

 <fieldset>
   <legend>Update user information - Select multiple users with CTRL + click</legend>
    <p>If resetting a password, the defaulted password for a user is <b>password</b>. </p>
    <ul>
       <li><b>viewer:</b>&nbsp;A viewer can view a list of ePortfolios by lecturer</li>
       <li><b>lecturer:</b>&nbsp;A lecturer can create / delete and back up ePortfolios</li>
       <li><b>administrator:</b>&nbsp;An administrator can create / delete / alter users for this system</li>
    </ul>
      <?php

          get_ep_admins();

      ?>&nbsp;   <input type="submit" name="delete" value="delete checked users!" />&nbsp;
     <input type="submit" name="reset" value="reset password of checked users!" />&nbsp;      
      <br />
     <label for="statuslist">alter checked user's status</label>
     <select  class='formleft' name='statuslist'>
        <option value="0">viewer</option>
        <option value="1">lecturer</option>
        <option value="2">administrator</option>

     </select>&nbsp;      <input type="submit" name="ustatus" value="alter user status!" /><br />   


      

      <input type="hidden" name="action" value="submitted" />
      
  </fieldset>
 
</form>


  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

 <fieldset>
   <legend>Add a new user</legend>
      <label for="login">chosen user ID</label>
      <input type="text" name="login" /><br />

     <label for="nicename">Friendly name for this user</label>
      <input type="text" name="nicename" /><br />

      <label for="pass1">enter a password</label>
      <input type="password" name="pass1" /><br />

      <label for="pass2">confirm password</label>
      <input type="password" name="pass2" /><br />

     <label for="statuslist">initial user status</label>
     <select  class='formleft' name='statuslist'>
        <option value="0">viewer</option>
        <option value="1">lecturer</option>
        <option value="2">administrator</option>

     </select>

      <input type="submit" name="create" value="create new user!" /><br />   
      <input type="hidden" name="action" value="submitted" />
      
  </fieldset>
 
</form>



<?php } ?>


        <p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
