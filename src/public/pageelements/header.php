<?php
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/config.php';
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

<link rel="stylesheet" href="node_modules/fullcalendar/dist/fullcalendar.css" />
<link rel="stylesheet" href="css/fullcalendar.custom.css" />
<link rel="stylesheet" href="node_modules/fullcalendar/dist/fullcalendar.print.css" media='print' />

<?php //jquery ui wird benötigt für die anmeldeseite (datepicker zur auswahl geburtsdatum)?>
    <link rel="stylesheet" href="node_modules/jquery-ui-dist/jquery-ui.css" />
    <link rel="stylesheet" href="node_modules/jquery-ui-dist/jquery-ui.structure.css" />
    <!--link rel="stylesheet" href="node_modules/jquery-ui-dist/jquery-ui.theme.css" /-->
    <link rel="stylesheet" href="css/smoothness/jquery-ui.theme.min.css" />

<?php
if('admin.php'===basename($_SERVER['PHP_SELF'])) {
	?>
	<link rel="stylesheet" href="css/form_add.css" />
	<link rel="stylesheet" href="node_modules/jquery-timepicker/jquery.timepicker.css" />
	<?php
}

if('index.php' !== basename($_SERVER['PHP_SELF'])) {
	?>
	<style type="text/css" media="screen">
		@import "node_modules/datatables/media/css/jquery.dataTables.min.css";
        @import "node_modules/yadcf/jquery.dataTables.yadcf.css";
        @import "css/datatables.css";
	</style>
	<?php
}
?>

<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="node_modules/jquery-ui-dist/jquery-ui.js"></script>
<script type="text/javascript" src="node_modules/insert-at-caret/jquery.insert-at-caret.min.js"></script>
<script type="text/javascript" src="node_modules/moment/min/moment.min.js"></script>
<script type="text/javascript" src="node_modules/fullcalendar/dist/fullcalendar.min.js"></script>
<script type="text/javascript" src="node_modules/fullcalendar/dist/locale/de.js"></script>
<script type="text/javascript" src="js/main.js"></script>
<?php
if('index.php' !== basename($_SERVER['PHP_SELF'])) {
	?>
	<script type="text/javascript" src="js/backend.js"></script>
	<script type="text/javascript">jQuery.migrateMute = true;</script>
	<script type="text/javascript" src="node_modules/jquery-migrate/dist/jquery-migrate.min.js"></script>
	<script type="text/javascript" src="node_modules/jquery-timepicker/jquery.timepicker.js"></script>
	<script type="text/javascript" src="node_modules/jquery-querystring/output/jquery.querystring.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="node_modules/datatables/media/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="node_modules/yadcf/jquery.dataTables.yadcf.js"></script>
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
		<h1 id="site-title"><span><a href="http://www.uni-koeln.de/">Universität zu Köln</a></span></h1>
		<h2 id="site-description">
            <a href="http://psychologie.hf.uni-koeln.de/">Department Psychologie</a>&nbsp;<a href="http://www.hf.uni-koeln.de/">(Humanwissenschaftliche Fakultät)</a>
			<br/><a href="#">COmputer-aided Registration Tool for EXperiments</a>
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