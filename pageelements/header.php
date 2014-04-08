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
<link rel="stylesheet" href="css/reset.css" />

<link rel="stylesheet" href="css/menu.css" />
<link rel="stylesheet" href="css/main.css" />

<link rel="stylesheet" href="css/copyright.css" />
<link rel="stylesheet" href="externals/fullcalendar/fullcalendar.css" />
<link rel="stylesheet" href="css/fullcalendar.custom.css" />
<link rel="stylesheet" href="externals/fullcalendar/fullcalendar.print.css" media='print' />

<?php //jquery ui wird benötigt für die anmeldeseite (datepicker zur auswahl geburtsdatum)?>
<link rel="stylesheet" href="css/smoothness/jquery-ui-1.10.4.custom.min.css" />

<?php
if('admin.php'===basename($_SERVER['PHP_SELF'])) {
	?>
	<link rel="stylesheet" href="css/form_add.css" />
	<link rel="stylesheet" href="externals/jquery-timepicker/timePicker.css" />
	<?php
}

if('index.php' !== basename($_SERVER['PHP_SELF'])) {
	?>
	<style type="text/css" media="screen">
		/*@import "externals/datatables/media/css/jquery.dataTables.css";*/
		@import "externals/datatables/media/css/jquery.dataTables_themeroller.css";
	</style>
	<?php
}
?>

<script type="text/javascript" src="externals/jquery/jquery.min.js"></script>
<script type="text/javascript" src="externals/jquery-ui/ui/minified/jquery-ui.custom.min.js"></script>
<script type="text/javascript" src="externals/jquery-ui/ui/minified/i18n/jquery-ui-i18n.min.js"></script>
<script type="text/javascript" src="externals/jquery-insertatcaret/jquery.insertatcaret.min.js"></script>
<script type="text/javascript" src="externals/fullcalendar/fullcalendar.min.js"></script>
<script type="text/javascript" src="js/main.js"></script>
<?php
if('index.php' !== basename($_SERVER['PHP_SELF'])) {
	?>
	<script type="text/javascript" src="js/backend.js"></script>
	<script type="text/javascript">jQuery.migrateMute = true;</script>
	<script type="text/javascript" src="externals/jquery-migrate/jquery-migrate.min.js"></script>
	<script type="text/javascript" src="externals/jquery-timepicker/jquery.timePicker.min.js"></script>
	<script type="text/javascript" src="externals/jquery-querystring/jquery.QueryString.js"></script>
	<script type="text/javascript" charset="utf-8" src="externals/datatables/media/js/jquery.dataTables.js"></script>
	<script type="text/javascript" src="externals/datatables-columnfilter/media/js/jquery.dataTables.columnFilter.js"></script>
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

    <nav role="navigation" id="menu-container">
        <?php
        require_once 'pageelements/menu.php';
        ?>
    </nav>
</div>

<div id="mitte">

	<div id="content">
	
<?php 
displayMessagesFromSession();
?>