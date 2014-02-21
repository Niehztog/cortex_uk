<?php
require_once __DIR__ . '/include/config.php';
require_once 'pageelements/header.php';
?>

<h1>Impressum</h1>

<span style="margin-bottom:10px; font-weight:bold; font-size:18px; font-family:Arial, Helvecita; color:#4A71D6;text-align: left; line-height:16px; margin-bottom:4px; margin-top:30px; font-variant:small-caps;">
	Inhaltlich veranwortlich
</span>
<br />
<?php if(strlen(IMPRINT_CONTENTS_FULLNAME)){echo IMPRINT_CONTENTS_FULLNAME . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_ADDITION_1)){echo IMPRINT_CONTENTS_ADDITION_1 . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_ADDITION_2)){echo IMPRINT_CONTENTS_ADDITION_2 . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_STREET)){echo IMPRINT_CONTENTS_STREET . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_ZIPCODE)){echo IMPRINT_CONTENTS_ZIPCODE;}?>&nbsp;
<?php if(strlen(IMPRINT_CONTENTS_CITY)){echo IMPRINT_CONTENTS_CITY . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_COUNTRY)){echo IMPRINT_CONTENTS_COUNTRY . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_PHONE)){echo 'Tel.:&nbsp;' . IMPRINT_CONTENTS_PHONE . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_FAX)){echo 'Fax:&nbsp;' . IMPRINT_CONTENTS_FAX . '<br />';}?>
<?php if(strlen(IMPRINT_CONTENTS_EMAIL) && false !== strpos(IMPRINT_CONTENTS_EMAIL, '@')) { ?>
e-mail:
<script type="text/javascript"><!--
	var mailadr="<?php echo substr(IMPRINT_CONTENTS_EMAIL, 0, strpos(IMPRINT_CONTENTS_EMAIL, '@'));?>";
	var maildom="<?php echo substr(IMPRINT_CONTENTS_EMAIL, strpos(IMPRINT_CONTENTS_EMAIL, '@')+1);?>";
	document.write('<a href="mailto:'+mailadr+'@'+maildom+'">'+mailadr+'@'+maildom+'</a>');
	//--></script>
<noscript><?php echo str_replace(array('.', '@'), array('[dot]','[at]'), IMPRINT_CONTENTS_EMAIL);?></noscript><br />
<?php } ?>
<a href="<?php if(strlen(IMPRINT_CONTENTS_LINK)){echo IMPRINT_CONTENTS_LINK;}?>" target="_blank"><?php if(strlen(IMPRINT_CONTENTS_LINK)){echo IMPRINT_CONTENTS_LINK;}?></a>

<br/><br/>

<span style="margin-bottom:10px; font-weight:bold; font-size:18px; font-family:Arial, Helvecita; color:#4A71D6;text-align: left; line-height:16px; margin-bottom:4px; margin-top:30px; font-variant:small-caps;">
	Technisch verantwortlich
</span>	
<br />
<?php if(strlen(IMPRINT_TECHNICAL_FULLNAME)){echo IMPRINT_TECHNICAL_FULLNAME . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_ADDITION_1)){echo IMPRINT_TECHNICAL_ADDITION_1 . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_ADDITION_2)){echo IMPRINT_TECHNICAL_ADDITION_2 . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_STREET)){echo IMPRINT_TECHNICAL_STREET . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_ZIPCODE)){echo IMPRINT_TECHNICAL_ZIPCODE;}?>&nbsp;
<?php if(strlen(IMPRINT_TECHNICAL_CITY)){echo IMPRINT_TECHNICAL_CITY . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_COUNTRY)){echo IMPRINT_TECHNICAL_COUNTRY . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_PHONE)){echo 'Tel.:&nbsp;' . IMPRINT_TECHNICAL_PHONE . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_FAX)){echo 'Fax:&nbsp;' . IMPRINT_TECHNICAL_FAX . '<br />';}?>
<?php if(strlen(IMPRINT_TECHNICAL_EMAIL) && false !== strpos(IMPRINT_TECHNICAL_EMAIL, '@')) { ?>
e-mail:
<script type="text/javascript"><!--
	var mailadr="<?php echo substr(IMPRINT_TECHNICAL_EMAIL, 0, strpos(IMPRINT_TECHNICAL_EMAIL, '@'));?>";
	var maildom="<?php echo substr(IMPRINT_TECHNICAL_EMAIL, strpos(IMPRINT_TECHNICAL_EMAIL, '@')+1);?>";
	document.write('<a href="mailto:'+mailadr+'@'+maildom+'">'+mailadr+'@'+maildom+'</a>');
	//--></script>
<noscript><?php echo str_replace(array('.', '@'), array('[dot]','[at]'), IMPRINT_TECHNICAL_EMAIL);?></noscript><br />
<?php } ?>
<a href="<?php if(strlen(IMPRINT_TECHNICAL_LINK)){echo IMPRINT_TECHNICAL_LINK;}?>" target="_blank"><?php if(strlen(IMPRINT_TECHNICAL_LINK)){echo IMPRINT_TECHNICAL_LINK;}?></a>

<?php
require_once 'pageelements/footer.php';
?>