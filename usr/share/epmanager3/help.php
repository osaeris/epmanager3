<?php include("functions.php"); ?>
<?php session_start(); 
           if($_SESSION['logged']!='true') {
               redirect(0, "login.php");
           }
?>
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
        <h2>Help</h2>
        <p>Here is a rundown of the purpose and operation of the ePortfolio Manager. </p>



<a name='about'></a>
<h2>About EPManager</h2>
<p>EPManager is a system which allows ePortfolios to be created for individuals. The ePortfolio design was derived from the <a href='http://isle.paisley.ac.uk/default.aspx'>ISLE project</a>. An ePortfolio in the context of EPManager is fundamentally a Wordpress blog with default post categories and pages to be filled in by the learner as they progress. Completing the pages included by default was a requirement for students taking part in the <acronym title="Individualised Support for Learning through ePortfolios">ISLE</acronym> project, but of course you can decide for yourself whether the pages and categories are relevant to your own situation.</p>



<p>There are two types of user in an ePortfolio called student and lecturer. The student is the owner of the ePortfolio, whereas the lecturer is a low privilege user, ony able to write posts and comment on the students progress. An ePortfolio can have several lecturers (in case a student is using their ePortfolio in several different classes). A student can decide to share a post with eveyone, just with their lecturers or with no-one at all using the User Access Manager plugin.  The student can effectively keep a private record/diary of their reflections, sharing only the pieces of information they wish to share.</p>



<h3>Persistence</h3>
<p>One of the concerns at the beginning of the ISLE project was persistence of the student data. EPManager allows a student ePortfolio to be backed up to an IMS ZIP which can be imported into an IMS compliant system on another server. ePortfolio can be exported as a backup file which will restore to another installation of epmanager. There is also an HTML export options which allows the ePortfolio to be turned into a static website (say for presenting on a USB key).

<h3>Single Sign On</h3>
<p>Developers should refer to the contents of wp-content/plug3-singlesignonlink.php in an eportfolio to set up single sign on.</p>



        



        <p class='spacer'>&nbsp;</p>
       
      </div>

	<?php echo getFooter(); ?>
   </div>

</body>
</html>
