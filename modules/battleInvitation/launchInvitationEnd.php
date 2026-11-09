<?php
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['battleInvitationCharacterId'])
&& isset($_POST['token'])
&& isset($_POST['launch']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['battleInvitationCharacterId'])
        && $_POST['battleInvitationCharacterId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $battleInvitationCharacterId = $_POST['battleInvitationCharacterId'];

            // On fait une requête pour vérifier si l'invitation de combat choisi existe
            $battleInvitationQuery = $bdd->prepare("SELECT * FROM car_battles_invitations, car_battles_invitations_characters, car_monsters
            WHERE battleInvitationId = battleInvitationCharacterBattleInvitationId
            AND battleInvitationMonsterId = monsterId
            AND battleInvitationCharacterId = ?
            AND battleInvitationCharacterCharacterId = ?");
            $battleInvitationQuery->execute([$battleInvitationCharacterId, $characterId]);
            $battleInvitationRow = $battleInvitationQuery->rowCount();

            // Si l'invitation de combat existe
            if ($battleInvitationRow == 1)
            {
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($battleInvitation = $battleInvitationQuery->fetch())
                {
                    // On récupère les informations de l'invitation de combat
                    $battleInvitationId = $battleInvitation['battleInvitationId'];
                    $battleInvitationName = $battleInvitation['battleInvitationName'];
                    $battleInvitationMonsterId = $battleInvitation['monsterId'];
                    $battleInvitationMonsterName = $battleInvitation['monsterName'];
                }
                $battleInvitationQuery->closeCursor();

                // On fait une requête pour vérifier si l'adversaire existe bien
                $opponentQuery = $bdd->prepare("SELECT * FROM car_monsters
                WHERE monsterId = ?");
                $opponentQuery->execute([$battleInvitationMonsterId]);
                $opponentRow = $opponentQuery->rowCount();

                // Si le monstre existe
                if ($opponentRow == 1)
                {
                    // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                    while ($opponent = $opponentQuery->fetch())
                    {
                        // On récupère les informations de l'adversaire
                        $opponentHp = $opponent['monsterHp'];
                        $opponentMp = $opponent['monsterMp'];
                        $monsterLimited = $opponent['monsterLimited'];
                        $monsterQuantity = $opponent['monsterQuantity'];
                    }
                    $opponentQuery->closeCursor();

                    // Insertion du combat dans la base de données avec les données
                    $addBattle = $bdd->prepare("INSERT INTO car_battles VALUES(
                    NULL,
                    :characterId,
                    :battleInvitationMonsterId,
                    'battleInvitation',
                    :opponentHp,
                    :opponentMp)");
                    $addBattle->execute([
                    'characterId' => $characterId,
                    'battleInvitationMonsterId' => $battleInvitationMonsterId,
                    'opponentHp' => $opponentHp,
                    'opponentMp' => $opponentMp]);
                    $addBattle->closeCursor();

                    // On met à jour les stats du monstre
                    $updateMonsterStats = $bdd->prepare("UPDATE car_monsters
                    SET monsterQuantityBattle = monsterQuantityBattle + 1
                    WHERE monsterId = :battleInvitationMonsterId");
                    $updateMonsterStats->execute(['battleInvitationMonsterId' => $battleInvitationMonsterId]);
                    $updateMonsterStats->closeCursor();

                    // On définit une date
                    $date = date('Y-m-d H:i:s');

                    // Insertion des stats du combat dans la base de données avec les données
                    $addBattleStats = $bdd->prepare("INSERT INTO car_monsters_battles_stats VALUES(
                    NULL,
                    :monsterBattleStatsMonsterId,
                    :monsterBattleStatsCharacterId,
                    'LaunchBattle',
                    :monsterBattleStatsDateTime)");
                    $addBattleStats->execute([
                    'monsterBattleStatsMonsterId' => $battleInvitationMonsterId,
                    'monsterBattleStatsCharacterId' => $characterId,
                    'monsterBattleStatsDateTime' => $date]);
                    $addBattleStats->closeCursor();

                    // On supprime l'invitation
                    $deleteBattleInvitationCharacter = $bdd->prepare("DELETE FROM car_battles_invitations_characters
                    WHERE battleInvitationCharacterId = :battleInvitationCharacterId");
                    $deleteBattleInvitationCharacter->execute(array('battleInvitationCharacterId' => $battleInvitationCharacterId));
                    $deleteBattleInvitationCharacter->closeCursor();

                    // On vérifie s'il reste encore des joueurs en attente pour cette invitation
                    $battleInvitationRemainingQuery = $bdd->prepare("SELECT * FROM car_battles_invitations, car_battles_invitations_characters
                    WHERE battleInvitationId = battleInvitationCharacterBattleInvitationId
                    AND battleInvitationId = ?");
                    $battleInvitationRemainingQuery->execute([$battleInvitationId]);
                    $battleInvitationRemainingRow = $battleInvitationRemainingQuery->rowCount();
                    $battleInvitationRemainingQuery->closeCursor();

                    // S'il n'existe plus d'invitation de combat pour ce adversaire
                    if ($battleInvitationRemainingRow == 0)
                    {
                        // On supprime l'invitation
                        $deleteBattleInvitation = $bdd->prepare("DELETE FROM car_battles_invitations
                        WHERE battleInvitationId = :battleInvitationId");
                        $deleteBattleInvitation->execute(array('battleInvitationId' => $battleInvitationId));
                        $deleteBattleInvitation->closeCursor();
                    }

                    // On redirige le joueur vers le combat
                    exit(header("Location: ../../modules/battle/index.php"));
                }
                // Si le monstre n'existe pas
                else
                {
                    $opponentQuery->closeCursor();
                    echo "Erreur : L'adversaire n'existe pas";
                }
            }
            // Si l'invitation de combat n'existe pas
            else
            {
                $battleInvitationQuery->closeCursor();
                echo "Erreur : Cette invitation de combat n'existe pas";
            }
        }
        // Si tous les champs numériques ne contiennent pas un nombre
        else
        {
            echo "Erreur : Les champs de type numérique ne peuvent contenir qu'un nombre entier";
        }
    }
    // Si le token de sécurité n'est pas correct
    else
    {
        echo "Erreur : La session a expiré, veuillez réessayer";
    }
}
// Si toutes les variables $_POST n'existent pas
else
{
    echo "Erreur : Tous les champs n'ont pas été remplis";
}

require_once("../../html/footer.php");
