<?php
require_once __DIR__ . '/../include/class/settings/EmailBlocking.class.php';

$blockClass = new EmailBlocking();

if(!empty($_POST['new_email'])) {
    try {
        $blockClass->addBlock($_POST['new_email']);
    } catch (RuntimeException $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
        storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Fehler beim blockieren der Email Adresse %1$s', htmlspecialchars($_POST['new_email'], ENT_QUOTES, 'UTF-8'))));
    }
}

if(!empty($_GET['del'])) {
    $id = (int)$_GET['del'];
    try {
        $blockClass->delBlock($id);
    }
    catch(RuntimeException $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
        storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Fehler beim freigeben der blockierten Email Adressen mit der id %1$d', $id)));
    }
}

try {
    $blockedEmails = $blockClass->getBlockList();
}
catch(RuntimeException $e) {
    trigger_error($e->getMessage(), E_USER_WARNING);
    storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Fehler beim laden der blockierten Email Adressen')));
}

require_once 'pageelements/header.php';
?>


<h1>Blockierte E-Mail Adressen</h1>

<form action="admin.php?menu=settings" method="post" name="settings" id="settings">

    <label for="new_email">E-Mail Adresse:</label>
    <input type="email" name="new_email" id="new_email" maxlength="45"/>
    <input type="submit" name="add" value="Blockieren"/>

</form>

<br/>

<div>
    <ul>
        <?php
        foreach($blockedEmails as $blockedEmail) {
            echo '<li>'.$blockedEmail['email'].' <i>(seit ' . $blockedEmail['created'].')</i><a href="?menu=settings&del=' . $blockedEmail['id'].'"><button name="del">Freigeben</button></a></li>';
        }
        ?>
    </ul>
</div>





<?php
require_once 'pageelements/footer.php';