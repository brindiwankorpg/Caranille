<?php
// On active la mise en mémoire tampon de sortie pour éviter les erreurs "Headers already sent" lors des redirections
ob_start();
// On démarre le module des sessions de PHP
session_start();
// On récupère le temps Unix actuel une première fois
$timeStart = microtime(true);
// On inclut le fichier de configuration qui contient les paramètres de connexion SQL ainsi que la création d'un objet $bdd pour les requêtes SQL
require_once("../../kernel/config.php");
if (!isset($version))
{
    $version = "2.0.0";
}
// On récupère les informations de configuration du jeu
require_once("../../kernel/configuration/index.php");
// Si la session $_SESSION['token'] est vide c'est que le joueur a validé un formulaire
if (empty($_SESSION['token']))
{
	// On génère un token cryptographiquement sécurisé qu'on stocke dans une session pour sécuriser les formulaires contre les attaques CSRF
	$_SESSION['token'] = bin2hex(random_bytes(32));
}
// Si le joueur est connecté on va récupérer toutes les informations du joueur (Compte, Personnage, Combat en cours...)
if (isset($_SESSION['account']['id']))
{
    // On récupère toutes les informations du compte
    require_once("../../kernel/account/index.php");
    // On récupère toutes les informations du personnage grâce au compte
    require_once("../../kernel/character/index.php");
    // On vérifie si le personnage est actuellement dans un combat de monstre. Si c'est le cas on récupère toutes les informations du monstre
    require_once("../../kernel/battle/index.php");
    // On vérifie le nombre d'invitations de combat du joueur
    require_once("../../kernel/battleInvitation/index.php");
    // On récupère toutes les informations des équipements équipés au personnage
    require_once("../../kernel/equipment/index.php");
    // On récupère toutes les informations des types d'équipement
    require_once("../../kernel/equipmentType/index.php");
    // On vérifie le nombre d'offres sur le marché
    require_once("../../kernel/market/index.php");
    // On vérifie le nombre de messages de notifications non lus
    require_once("../../kernel/notification/index.php");
    // On vérifie le nombre de messages de conversation privée non lus
    require_once("../../kernel/privateConversation/index.php");
    // On vérifie si le personnage est actuellement dans un lieu. Si c'est le cas on récupère toutes les informations du lieu
    require_once("../../kernel/place/index.php");
    // On vérifie le nombre d'échanges en cours
    require_once("../../kernel/trade/index.php");
    // On vérifie le nombre de demandes d'échange en cours
    require_once("../../kernel/tradeRequest/index.php");
}
?>
