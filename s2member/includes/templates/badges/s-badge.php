<?php
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
?>

<div class="ws-plugin--s2member-s-badge">
<a href="http://www.s2member.com/" onclick="window.open('http://www.s2member.com/s-badges/s-details.php?v=%%v%%&amp;site_url=%%site_url%%%%no_cache%%%%display_on_failure%%', '_popup', 'width=752,height=702,left='+((screen.width/2)-(752/2))+',screenX='+((screen.width/2)-(752/2))+',top='+((screen.height/2)-(702/2))+',screenY='+((screen.height/2)-(702/2))+',location=0,menubar=0,toolbar=0,scrollbars=0,resizable=1'); return false;" title="s2Member&reg;"><img src="//www.s2member.com/s-badges/s-badge.php?v=%%v%%&amp;site_url=%%site_url%%%%no_cache%%%%display_on_failure%%" style="border:0;" alt="s2Member&reg;" title="<?php echo esc_attr (_x ("s2Member&reg; ( Security for WordPress&reg; )", "s2member-front", "s2member")); ?>" /></a>
</div>