<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");
?>

<hr>
Informations
<hr>

<p><img src="<?php echo htmlspecialchars($characterPicture)?>" height="100" width="100"></p>

Pseudo : <?php echo htmlspecialchars($characterName)?><br />
Classe : <?php echo htmlspecialchars($characterclasseName)?><br />

<hr>
Statistiques
<hr>

Niveau : <?php echo htmlspecialchars($characterLevel)?><br />
HP : <?php echo htmlspecialchars($characterHpMin) . "/" . htmlspecialchars($characterHpTotal)?><br />
MP : <?php echo htmlspecialchars($characterMpMin) . "/" . htmlspecialchars($characterMpTotal)?><br />
Force : <?php echo htmlspecialchars($characterStrengthTotal)?><br />
Magie : <?php echo htmlspecialchars($characterMagicTotal)?><br />
Agilité : <?php echo htmlspecialchars($characterAgilityTotal)?><br />
Défense : <?php echo htmlspecialchars($characterDefenseTotal)?><br />
Défense Magique : <?php echo htmlspecialchars($characterDefenseMagicTotal)?><br />
Sagesse : <?php echo htmlspecialchars($characterWisdomTotal)?><br />
Prospection : <?php echo htmlspecialchars($characterProspectingTotal)?><br />
Défaite(s) en arène : <?php echo htmlspecialchars($characterArenaDefeate)?><br />
Victoire(s) en arène : <?php echo htmlspecialchars($characterArenaVictory)?><br />
Expérience : <?php echo htmlspecialchars($characterExperience) . "/" . htmlspecialchars($experienceLevel)?><br />
Prochain niveau dans : <?php echo htmlspecialchars($experienceRemaining)?><br />
Expérience totale : <?php echo htmlspecialchars($characterExperienceTotal)?><br />
Argent : <?php echo htmlspecialchars($characterGold)?><br />

<hr>
Équipements
<hr>

<?php echo htmlspecialchars($itemArmorNameShow)?> : <?php echo htmlspecialchars($equipmentArmorName)?><br />
<?php echo htmlspecialchars($itemBootsNameShow)?> : <?php echo htmlspecialchars($equipmentBootsName)?><br />
<?php echo htmlspecialchars($itemGlovesNameShow)?> : <?php echo htmlspecialchars($equipmentGlovesName)?><br />
<?php echo htmlspecialchars($itemHelmetNameShow)?> : <?php echo htmlspecialchars($equipmentHelmetName)?><br />
<?php echo htmlspecialchars($itemWeaponNameShow)?> : <?php echo htmlspecialchars($equipmentWeaponName)?><br />

<?php require_once("../../html/footer.php"); ?>
