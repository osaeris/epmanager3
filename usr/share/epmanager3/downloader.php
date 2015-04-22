<?php 
            
            $id = $_GET['id'];  //id = student id for singles
            $type= $_GET['type']; // either normal,ims,html or big 
            
            switch($type) {
               case "ims":
                  $filename = "/tmp/epmanagerbackup/epmanager_IMS_".$id.".zip";                  
                  break;   
               case "normal":
                  $filename = "/tmp/epmanagerbackup/epmanager_".$id.".zip";                  
                  break;
               case "html":
                  $filename = "/tmp/epmanagerbackup/epmanager_HTML_".$id.".zip";                  
                  break;
               case "big":
                  $datehold=date('dMY');
                  $filename="/tmp/epmanager_bigzip_".$datehold.".zip";                     
                  break;
               default:
                  $filename = "/tmp/epmanagerbackup/epmanager_".$id.".zip";                   
                  break;
            }
  
            $file_extension = strtolower(substr(strrchr($filename,"."),1));

            switch ($file_extension) {
                case "zip": $ctype="application/zip"; break;
                default: $ctype="application/zip";
            }

            if (!$file_extension=="zip") {
                die("ZIP FILE ARCHIVES ONLY");
            }

            if (!file_exists($filename)) {
                die("NO FILE HERE");
            }

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            header("Content-Type: $ctype");
            header("Content-Disposition: attachment; filename=\"".basename($filename)."\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".@filesize($filename));
            set_time_limit(0);
            @readfile("$filename") or die("File not found.");


?>