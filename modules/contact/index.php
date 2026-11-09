<?php 
require_once("../../kernel/kernel.php");
require_once("../../html/header.php");

$accountQuery = $bdd->query("SELECT * FROM car_accounts 
WHERE accountId = 1");

// On fait une boucle pour récupérer les résultats
while ($account = $accountQuery->fetch())
{
    $accountEmail = $account['accountEmail'];
}
$accountQuery->closeCursor();
?>

<h1>Nous contacter</h1>
Pour toute question/suggestion et soumissions de bogues merci de me contacter à cette adresse Email : <?php echo htmlspecialchars($accountEmail)?><br /><br />

<b>Attention</b> : Pour toute demande de changement de nom d'utilisateur ou d'adresse e-mail, vous devez être en mesure de répondre à votre question secrète qui vous sera posée par e-mail.<br /><br />

Tout compte utilisateur n'ayant pas créé de question secrète (via Mon compte -> Sécurité) ne pourra obtenir de réponse favorable du support.<br /><br />

Bien cordialement,

<?php require_once("../../html/footer.php"); ?>
