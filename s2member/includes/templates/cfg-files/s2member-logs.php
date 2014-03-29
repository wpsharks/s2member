<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

<IfModule authz_core_module>
	Require all denied
</IfModule>
<IfModule !authz_core_module>
	deny from all
</IfModule>

# Disallow directory indexing here.
	Options -Indexes