<?php 
require_once("../../kernel/kernel.php");

// S'il n'y a aucune session c'est que le joueur n'est pas connecté alors on le redirige vers l'accueil
if (empty($_SESSION['account'])) { exit(header("Location: ../../index.php")); }
// S'il y a actuellement un combat on redirige le joueur vers le module battle
if ($battleRow > 0) { exit(header("Location: ../../modules/battle/index.php")); }

require_once("../../html/header.php");

// Si les variables $_POST suivantes existent
if (isset($_POST['token'])
&& isset($_POST['opponentCharacterId']))
{
    // Si le token de sécurité est correct
    if ($_POST['token'] == $_SESSION['token'])
    {
        // On supprime le token de l'ancien formulaire
        $_SESSION['token'] = NULL;
        
        // On vérifie si tous les champs numériques contiennent bien un nombre entier positif
        if (ctype_digit($_POST['opponentCharacterId'])
        && $_POST['opponentCharacterId'] >= 1)
        {
            // On récupère l'id du formulaire précédent
            $opponentId = $_POST['opponentCharacterId'];
    
            // On fait une requête pour vérifier si le personnage existe bien et qu'il ne s'agit pas du joueur
            $opponentQuery = $bdd->prepare("SELECT * FROM car_characters
            WHERE characterId = ?
            AND characterId != ?");
            $opponentQuery->execute([$opponentId, $characterId]);
            $opponentRow = $opponentQuery->rowCount();
    
            // Si le personnages a été trouvé
            if ($opponentRow == 1)
            {
                // On recherche le personnage
                while ($opponent = $opponentQuery->fetch())
                {
                    // On récupère les informations de l'opposant
                    $opponentHp = $opponent['characterHpTotal'];
                    $opponentMp = $opponent['characterMpTotal'];
                }
                $opponentQuery->closeCursor();
                
                // Insertion du combat dans la base de données avec les données
                $addBattle = $bdd->prepare("INSERT INTO car_battles VALUES(
                NULL,
                :characterId,
                :opponentId,
                'Arena',
                :opponentHp,
                :opponentMp)");
                $addBattle->execute([
                'characterId' => $characterId,
                'opponentId' => $opponentId,
                'opponentHp' => $opponentHp,
                'opponentMp' => $opponentMp]);
                $addBattle->closeCursor();
    
                // On redirige le joueur vers le combat
                exit(header("Location: ../../modules/battle/index.php"));
            }
            // Si le personnage n'existe pas
            else
            {
                $opponentQuery->closeCursor();
                echo "Erreur : Personnage indisponible";
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

require_once("../../html/footer.php"); ?>
