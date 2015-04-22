<?php


  function dbconnect()
  {
    // this is the connection to the local MySql
    // eportfolio database which holds the
    // lecturer student relationships
    //
    // Connections to MSSQL servers seem
    // to require local coding within the function
    // being used so I haven't got this running quite
    // how I wanted.
    
    $username=EP_DB_USERNAME;
    $password=EP_DB_PASSWORD;
    $database=EP_DB_DATABASE;
    $server=EP_DB_SERVER;

    

    $link =  mysqli_connect($server,$username,$password) or die("<div class='greybox'><h1>Installation</h1><p><b>It looks as though you need to enter your local database settings in your config file.</b></p><p>Enter the settings marked crucial in your <span style='color:red;'>/etc/epmanager3/config.php</span> file then <a href='index.php'>reload this page</a>.</p></div><p class='spacer'>&nbsp;</p>");
    
    mysqli_select_db($link,$database) or die("<div class='greybox'><h1>Installation</h1><p><b>There may be a problem with the name  you supplied for the local database.</b></p><p>Check the settings marked crucial in your <span style='color:red;'>/etc/epmanager3/config.php</span> file then <a href='index.php'>reload this page</a>.</p></div><p class='spacer'>&nbsp;</p>");
    
    return $link;
    
  }

  function dbdisconnect($link)
  {
    mysqli_close($link);
  }
  

 
?>
