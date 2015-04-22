<?php
/* There is a separate login available via link from external site which uses the salt value here to create a hash you must change this value to something else preferably very long and complex even if you dont intend to use it
url to sso will be like:
http://YOURSERVER/username/wp-login.php?log=username&hash=blahblah&external=true
see wp-content/plugins/wp3-singlesignonlink.php in a newly created portfolio for more information

*/

define('EP_SSO_SALT', '389');// xxx

?>
