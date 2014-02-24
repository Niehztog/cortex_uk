<?php
require_once dirname(__FILE__) . '/../include/functions.php';
require_once dirname(__FILE__) . '/../include/config.php';
initSession(true);
?>
<!DOCTYPE html>
<html lang="de-DE">
<head>
<meta charset="UTF-8" />
<title>CORTEX - COmputer-aided Registration Tool for EXperiments<?php if('admin.php' === basename($_SERVER['PHP_SELF'])){echo ' (Administration)';}?></title>
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>reset.css" />

<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>menu.css" />
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>main.css" />

<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>copyright.css" />
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>fullcalendar/fullcalendar.css" />
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>fullcalendar/fullcalendar.custom.css" />
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>fullcalendar/fullcalendar.print.css" media='print' />

<?php //jquery ui wird benötigt für die anmeldeseite (datepicker zur auswahl geburtsdatum)?>
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/custom-theme';} else echo 'smoothness';?>/jquery-ui-1.10.4.custom.css" />

<?php
if('admin.php'===basename($_SERVER['PHP_SELF'])) {
	?>
	<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>form_add.css" />
	<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>timePicker.css" />
	<?php
}

if('index.php' !== basename($_SERVER['PHP_SELF'])) {
	?>
	<style type="text/css" media="screen">
		/*@import "css/datatables/css/jquery.dataTables.css";*/
		@import "css/datatables/css/jquery.dataTables_themeroller.css";
	</style>
	<?php
}
?>

<script type="text/javascript" src="js/jquery/jquery-1.10.2.js"></script>
<script type="text/javascript" src="js/jquery/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="js/jquery/jquery-ui-i18n.min.js"></script>
<script type="text/javascript" src="js/jquery/jquery.insertatcaret.min.js"></script>
<script type="text/javascript" src="js/termin_creator.js"></script>
<script type="text/javascript" src="js/fullcalendar/fullcalendar.min.js"></script>
<?php
if('index.php' !== basename($_SERVER['PHP_SELF'])) {
	?>
	<script type="text/javascript">jQuery.migrateMute = true;</script>
	<script type="text/javascript" src="js/jquery/jquery-migrate-1.2.1.js"></script>
	<script type="text/javascript" src="js/jquery/jquery.timePicker.js"></script>
	<script type="text/javascript" src="js/querystring-0.9.0.js"></script>
	<script type="text/javascript" charset="utf-8" src="js/jquery/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="js/jquery/jquery.dataTables.columnFilter.js"></script>
	<?php
}
?>

<script type="text/javascript">
$( document ).ready(function() {
	$("[title]").each(function(){
	  $(this).tooltip({ content: $(this).attr("title")});
	});    // It allows html content to tooltip.
	$('input[type="button"], input[type="submit"], button').button();
});
</script>

</head>

<body>

<div id="parent">

<div id="header">
	<div class="hgroup">
		<h1 id="site-title"><span><a rel="home" href="<?php sprintf('Location: http://%1$s%2$s', $_SERVER['HTTP_HOST'], $_SERVER['PHP_SELF']);?>">CORTEX</a></span></h1>
		<h2 id="site-description">
			<a href="<?php sprintf('Location: http://%1$s%2$s', $_SERVER['HTTP_HOST'], $_SERVER['PHP_SELF']);?>">COmputer-aided Registration Tool for EXperiments</a>
		</h2>
	</div>
	<?php
	if(!CSS_OLD_STYLE) {
		?>
		<nav role="navigation" id="menu-container">
			<?php
				require_once 'pageelements/menu.php';
			?>
		</nav>
		<?php
	}
	?>
</div>

<div id="mitte">
	<?php
	if(CSS_OLD_STYLE) {
	?>
	<div id="links">
		<nav role="navigation" id="menu-container">
			<?php
				require_once 'pageelements/menu.php';
			?>
		</nav>
		
	</div>
	<?php
	}
	?>

	<div id="content">
	
<?php 
displayMessagesFromSession();
?>