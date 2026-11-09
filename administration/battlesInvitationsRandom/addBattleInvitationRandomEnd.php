<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// Si le joueur n'a pas les droits administrateurs (Accès 2) on le redirige vers l'accueil
if ($accountAccess < 2) { exit(header("Location: ../../index.php")); }

require_once("../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['adminBattleInvitationMonsterId'])
&& isset($_POST['adminBattleInvitationPicture'])
&& isset($_POST['adminBattleInvitationName'])
&& isset($_POST['adminBattleInvitationDescription'])
&& isset($_POST['adminBattleInvitationRateOld'])
&& isset($_POST['adminBattleInvitationRateNew'])
&& isset($_POST['token'])
&& isset($_POST['finalAdd']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;

        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['adminBattleInvitationMonsterId'])
        && ctype_digit($_POST['adminBattleInvitationRateNew'])
        && ctype_digit($_POST['adminBattleInvitationRateOld'])
        && $_POST['adminBattleInvitationMonsterId'] >= 1
        && $_POST['adminBattleInvitationRateOld'] >= 0
        && $_POST['adminBattleInvitationRateNew'] >= 0)
        {
            // On récupère les informations du formulaire
            $adminBattleInvitationMonsterId = $_POST['adminBattleInvitationMonsterId'];
            $adminBattleInvitationPicture = $_POST['adminBattleInvitationPicture'];
            $adminBattleInvitationName = $_POST['adminBattleInvitationName'];
            $adminBattleInvitationDescription = $_POST['adminBattleInvitationDescription'];
            $adminBattleInvitationRateOld = $_POST['adminBattleInvitationRateOld'];
            $adminBattleInvitationRateNew = $_POST['adminBattleInvitationRateNew'];
            $date = date('Y-m-d H:i:s');
            
            // On ajoute l'invitation de combat dans la base de données
            $addInvitationBattle = $bdd->prepare("INSERT INTO car_battles_invitations VALUES(
            NULL,
            :adminBattleInvitationMonsterId,
            :adminBattleInvitationPicture,
            :adminBattleInvitationName,
            :adminBattleInvitationDescription,
            :date,
            :date)");
            $addInvitationBattle->execute([
            'adminBattleInvitationMonsterId' => $adminBattleInvitationMonsterId,
            'adminBattleInvitationPicture' => $adminBattleInvitationPicture,
            'adminBattleInvitationName' => $adminBattleInvitationName,
            'adminBattleInvitationDescription' => $adminBattleInvitationDescription,
            'date' => $date]);
            $addInvitationBattle->closeCursor();
            
            // On fait une requête pour récupérer l'Id de l'invitation faite à l'instant
            $battleInvitationQuery = $bdd->query("SELECT * FROM car_battles_invitations 
            ORDER BY battleInvitationId DESC 
            LIMIT 1");
            
            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
            while ($battleInvitation = $battleInvitationQuery->fetch())
            {
                // On récupère les informations de l'invitation
                $adminBattleInvitationId = $battleInvitation['battleInvitationId'];
            }
            $battleInvitationQuery->closeCursor();

            // On fait une requête pour vérifier si le monstre choisi existe
            $monsterQuery = $bdd->prepare("SELECT * FROM car_monsters
            WHERE monsterId = ?");
            $monsterQuery->execute([$adminBattleInvitationMonsterId]);
            $monsterRow = $monsterQuery->rowCount();

            // Si le monstre existe
            if ($monsterRow == 1) 
            {
                // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
                while ($monster = $monsterQuery->fetch())
                {
                    $adminBattleInvitationMonsterName = $monster['monsterName'];
                }
            }
            $monsterQuery->closeCursor();

            $battleInvitationNumber = 0;
            
            // On fait une recherche dans la base de données de tous les personnages
            $characterQuery = $bdd->query("SELECT * FROM car_characters");

            // On fait une boucle sur le ou les résultats obtenus pour récupérer les informations
            while ($character = $characterQuery->fetch())
            {
                $adminCharacterId = $character['characterId'];
                $adminCharacterName =  $character['characterName'];
                
                // On fait une requête pour vérifier si le joueur a déjà vaincu le monstre
                $characterBestiaryQuery = $bdd->prepare("SELECT * FROM car_characters, car_bestiary
                WHERE characterId = bestiaryCharacterId
                AND characterId = ?
                AND bestiaryMonsterId = ?");
                $characterBestiaryQuery->execute([$adminCharacterId, $adminBattleInvitationMonsterId]);
                $characterBestiaryRow = $characterBestiaryQuery->rowCount();
                $characterBestiaryQuery->closeCursor();

                // On fait une requête pour vérifier si le joueur a déjà une invitation en attente pour ce monstre
                $characterBattleInvitationQuery = $bdd->prepare("SELECT * FROM car_battles_invitations, car_battles_invitations_characters
                WHERE battleInvitationId = battleInvitationCharacterBattleInvitationId
                AND battleInvitationMonsterId = ?
                AND battleInvitationCharacterCharacterId = ?");
                $characterBattleInvitationQuery->execute([$adminBattleInvitationMonsterId, $adminCharacterId]);
                $characterBattleInvitationRow = $characterBattleInvitationQuery->rowCount();
                $characterBattleInvitationQuery->closeCursor();

                // On génère un nombre entre 1 et 100
                $numberRandom = mt_rand(1, 100);
                
                if ($characterBattleInvitationRow == 1)
                {
                    echo htmlspecialchars($adminCharacterName) . " n'est pas éligible (Invitation en attente pour ce même monstre)<br />";
                }
                else
                {
                    // Si le joueur a déjà vaincu le monstre
                    if ($characterBestiaryRow >= 1)
                    {
                        // Si le taux d'obtention est en dessous de la valeur demandée on envoie l'invitation
                        if ($numberRandom <= $adminBattleInvitationRateOld)
                        {
                            // On ajoute l'invitation de combat dans la base de données
                            $addInvitationBattleCharacter = $bdd->prepare("INSERT INTO car_battles_invitations_characters VALUES(
                            NULL,
                            :adminBattleInvitationId,
                            :adminCharacterId)");
                            $addInvitationBattleCharacter->execute([
                            'adminBattleInvitationId' => $adminBattleInvitationId,
                            'adminCharacterId' => $adminCharacterId]);
                            $addInvitationBattleCharacter->closeCursor();
                            
                            $notificationDate = date('Y-m-d H:i:s');
                            $notificationMessage = "Félicitations : Vous avez reçu une invitation de combat contre le monstre $adminBattleInvitationMonsterName.<br />Rendez-vous dans le menu Personnage -> Invitation de combat pour y prendre part.";
                            
                            // On envoie une notification au joueur
                            $addNotification = $bdd->prepare("INSERT INTO car_notifications VALUES(
                            NULL,
                            :adminCharacterId,
                            :notificationDate,
                            :notificationMessage,
                            'No')");
                            $addNotification->execute(array(
                            'adminCharacterId' => $adminCharacterId,  
                            'notificationDate' => $notificationDate,
                            'notificationMessage' => $notificationMessage));
                            $addNotification->closeCursor();
                            
                            echo htmlspecialchars($adminCharacterName) . " (Déjà vaincu) a été invité <br />";
                            $battleInvitationNumber++;
                        }
                    }
                    // Si le joueur n'a pas vaincu le monstre
                    else
                    {
                        // Si le taux d'obtention est en dessous de la valeur demandée on envoie l'invitation
                        if ($numberRandom <= $adminBattleInvitationRateNew)
                        {
                            // On ajoute l'invitation de combat dans la base de données
                            $addInvitationBattleCharacter = $bdd->prepare("INSERT INTO car_battles_invitations_characters VALUES(
                            NULL,
                            :adminBattleInvitationId,
                            :adminCharacterId)");
                            $addInvitationBattleCharacter->execute([
                            'adminBattleInvitationId' => $adminBattleInvitationId,
                            'adminCharacterId' => $adminCharacterId]);
                            $addInvitationBattleCharacter->closeCursor();
                            
                            $notificationDate = date('Y-m-d H:i:s');
                            $notificationMessage = "Félicitations : Vous avez reçu une invitation de combat contre le monstre $adminBattleInvitationMonsterName.<br />Rendez-vous dans le menu Personnage -> Invitation de combat pour y prendre part.";
                            
                            // On envoie une notification au joueur
                            $addNotification = $bdd->prepare("INSERT INTO car_notifications VALUES(
                            NULL,
                            :adminCharacterId,
                            :notificationDate,
                            :notificationMessage,
                            'No')");
                            $addNotification->execute(array(
                            'adminCharacterId' => $adminCharacterId,  
                            'notificationDate' => $notificationDate,
                            'notificationMessage' => $notificationMessage));
                            $addNotification->closeCursor();
                            
                            echo htmlspecialchars($adminCharacterName) . " (Jamais vaincu) a été invité <br />";
                            $battleInvitationNumber++;
                        }
                    }
                }
            }
            $characterQuery->closeCursor();

            // Si il y a eu au moins un joueur invité
            if ($battleInvitationNumber > 0)
            {
                echo "Les invitations de combats ont bien été envoyées aux joueurs sélectionnés.";
            }
            // Sinon on supprime l'invitation
            else
            {
                echo "Aucun joueur n'a été invité avec le taux d'obtention, l'invitation N°" . htmlspecialchars($adminBattleInvitationId) . " est donc supprimée";
                
                // On supprime l'invitation de combat de la base de données
                $battleInvitationDeleteQuery = $bdd->prepare("DELETE FROM car_battles_invitations
                WHERE battleInvitationId = ?");
                $battleInvitationDeleteQuery->execute([$adminBattleInvitationId]);
                $battleInvitationDeleteQuery->closeCursor();
            }
            ?>
            
            <hr>
                
            <form method="POST" action="index.php">
                <input type="submit" class="btn btn-secondary btn-lg" name="back" value="Retour">
            </form>
                
            <?php
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

require_once("../html/footer.php");
