# ⚔️ Caranille - Un éditeur de RPG textuel en PHP ⚔️

[![Version](https://img.shields.io/badge/Version-2.0-blueviolet.svg?style=for-the-badge)](https://github.com/Caranille/Caranille)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-orange.svg?style=for-the-badge)](LICENCE.txt)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%207.4-777bb4.svg?style=for-the-badge&logo=php)](https://www.php.net/)

**Caranille** est un éditeur gratuit de jeu de rôle en ligne (ORPG) en PHP basé sur le web, léger, modulaire et entièrement personnalisable. Conçu en PHP, il permet à n'importe quel créateur de lancer son propre univers médiéval-fantastique sans nécessiter de compétences avancées en programmation grâce à un panneau d'administration extrêmement complet.

---

## ✨ Fonctionnalités Majeures

### 📖 Scénario & Immersion
* **Chapitres d'Histoire** : Créez une trame narrative captivante découpée en chapitres, chacun disposant d'une introduction et d'une conclusion scénarisées.
* **Quêtes & Progression** : Guidez vos joueurs à travers l'univers en liant leur progression à l'avancement de l'histoire.

### 🧙‍♂️ Personnalisation du Gameplay
* **Classes de Personnages** : Définissez une ou plusieurs classes (Guerrier, Mage, Voleur, Prêtre, etc.) possédant chacune des caractéristiques initiales propres.
* **Attributs & Statistiques** : Gérez les points de caractéristiques (Force, Magie, Défense, Agilité, etc.) distribués par les joueurs à chaque montée de niveau.

### 🗺️ Monde & Exploration
* **Gestion des Lieux** : Modélisez vos villes, plaines, donjons et zones d'exploration.
* **Bestiaire Complet** : Créez des monstres uniques dotés de statistiques précises et de tables de butin personnalisées.
* **Objets & Équipements** : Concevez des armes, armures, boucliers et potions consommables. Déterminez s'ils sont obtenus sur les monstres (butins) ou achetables en boutique.
* **Boutiques & Marchands** : Configurez des magasins locaux pour permettre aux aventuriers de s'équiper en échange de pièces d'or.

---

## 👥 Interaction & Multi-joueurs

Caranille propose une expérience communautaire riche et dynamique grâce à ses modules interactifs intégrés :

| Module | Description |
| :--- | :--- |
| **🔄 Système d'Échanges** | Permettez aux joueurs connectés de s'échanger des équipements et des objets en toute sécurité. |
| **⚔️ Arène PvP (JvJ)** | Proposez des duels intenses. Le personnage adverse est contrôlé par une intelligence artificielle basée sur ses statistiques. |
| **⚖️ Hôtel des Ventes** | Un marché libre où chaque joueur peut mettre en vente ses équipements au prix de son choix pour les autres joueurs. |
| **💬 Canaux de Discussion** | Un Chat global pour toute la communauté ainsi qu'un système de messagerie privée (Chuchotements) pour les discussions secrètes. |

---

## 🛠️ Panel d'Administration & Événements

Administrez votre communauté et animez votre jeu en temps réel directement depuis le navigateur :

* **🚀 Boosters Temporaires** : Configurez des événements spéciaux pour augmenter en pourcentage (`%`) le taux d'expérience (XP), le taux de butin (loot) ou le gain d'or.
* **🎁 Distribution de Récompenses** : Offrez instantanément de l'expérience ou de l'or à un joueur ciblé ou à l'intégralité des joueurs connectés.
* **⚙️ Gestion Globale** : Un tableau de bord intuitif pour modifier le niveau maximum, la base d'expérience requise, les points de caractéristiques par niveau, et le statut d'ouverture du jeu.

---

## 🚀 Démarrage Rapide

> [!NOTE]
> Caranille intègre un assistant d'installation graphique étape par étape qui configure automatiquement la base de données et le premier compte administrateur.

### Prérequis
* Un serveur web (Apache / Nginx) compatible PHP 7.4+.
* Une base de données MySQL ou MariaDB.

### Installation pas-à-pas
1. **Téléchargement** : Déposez l'intégralité des fichiers de Caranille dans le répertoire racine de votre serveur web (ex: `htdocs` ou `var/www/html`).
2. **Accès Web** : Ouvrez votre navigateur et naviguez vers l'adresse locale ou distante du projet (ex: `http://localhost/Caranille` ou `http://localhost:8000`).
3. **Configuration** : Suivez les instructions affichées par l'assistant d'installation :
   * Renseignez vos identifiants de base de données.
   * Créez votre compte administrateur de jeu.
4. **C'est prêt !** Connectez-vous et commencez à façonner votre univers.

---

## 📜 Historique des Versions

* **v2.0** : Audit de sécurité complet (installation verrouillée, contrôles serveur sur les objets, équipements, parchemins, boutiques et échanges, comptes bannis réellement bloqués) et fermeture systématique des requêtes SQL (closeCursor).
* **v1.12.1** : Correction générale de l'orthographe et de la grammaire (commentaires, messages joueurs et administration) pour un rendu plus professionnel.
* **v1.12.0** : Version initiale stable.

---

## 🛡️ Licence

Ce projet est distribué sous la licence **Creative Commons Attribution - Utilisation non commerciale - Partage dans les mêmes conditions 4.0 International (CC BY-NC-SA 4.0)**.
Consultez le fichier [LICENCE.txt](LICENCE.txt) pour lire l'intégralité des termes et conditions de la licence.
