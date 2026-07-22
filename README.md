README.MD 
VITE & GOURMAND 
PROJET 
Vite & Gourmand 
TYPE DE PROJET 
Application web de gestion de commandes pour une entreprise de 
traiteur 
REALISE DANS LE CADRE 
TP Développeur Web & Web Mobile 
AUTEUR 
BRUN Eline 
DATE 
22/07/2026 
Sommaire 
1. Présentation du projet 
2. Fonctionnalités 
3. Technologies utilisées 
4. Architecture du projet 
5. Installation locale 
6. Configuration 
7. Base de données 
8. Comptes de démonstration 
9. Gestion des versions Git 
10. Déploiement 
11. Auteur 
→ # Vite & Gourmand 
## Présentation du projet 
Vite & Gourmand est une application web de gestion de 
commandes développée pour une entreprise de traiteur. 
L'application permet aux clients de consulter les menus 
disponibles, de réaliser des commandes en ligne et de suivre 
leur évolution. 
Elle propose également des espaces dédiés aux employés et aux 
administrateurs afin de faciliter la gestion quotidienne de 
l'activité. 
Le projet a été réalisé dans le cadre de la formation 
Développeur Web et Web Mobile. --- 
# Fonctionnalités 
## Espace client - Créer un compte ; - Se connecter à son espace personnel ; - Consulter les menus disponibles ; - Filtrer les menus selon différents critères  - Ajouter des menus à son panier - Passer une commande - Bénéficier d’un calcul automatique de frais de livraison selon 
l’adresse renseignée - Suivre l’état d’avancement d’une commande - Publier un avis - Consulter son historique de commande - Consulter le récapitulatif des commandes passées - Modifier ses informations personnelles ainsi que son mot de 
passe --- 
## Espace employé - Consulter les commandes  - Filtrer les commandes - Modifier l’état des commandes - Gérer les menus  - Créer un nouveau menu - Gérer les stocks - Gérer les avis clients - Modifier son mot de passe --- 
## Espace administrateur - Consulter les statistiques commerciales sous forme de cartes 
(CA & nombre de commandes) - Consulter les statistiques commerciales sous forme de 
graphique (nombre de commandes par menu) - Consulter les commandes  - Filtrer les commandes - Modifier l’état des commandes - Gérer les employés - Créer un employé - Gérer les menus  - Créer un nouveau menu - Gérer les stocks - Gérer les avis clients - Consulter les messages reçus - Modifier son mot de passe --- 
# Technologies utilisées 
## Front-end - HTML5 - CSS3 - Bootstrap 
- JavaScript 
## Back-end - PHP 8 - PDO 
## Bases de données - MariaDB (données métier) - MongoDB (statistiques) 
## Outils - Visual Studio Code - XAMPP - phpMyAdmin - MongoDB Compass - Composer - Git / GitHub - Figma - Trello 
## API externe - Google Maps API pour le calcul des distances et des frais de 
livraison --- 
# Architecture du projet 
L'application repose sur une architecture trois tiers 
Utilisateur 
↓ 
Interface Web 
(HTML / CSS / JavaScript / Bootstrap) 
↓ 
Serveur applicatif 
(PHP) 
↓ 
Bases de données 
(MariaDB + MongoDB) 
# Installation locale 
## Prérequis 
Avant d'installer l'application, les éléments suivants doivent être 
installés : - PHP 8 - Apache - MariaDB - Composer - MongoDB --- 
## Installation 
##Cloner le dépôt 
```bash 
git clone URL_DU_DEPOT 
##Installer les dépendances PHP 
Depuis le dossier du projet : 
composer install 
##Installer la base de données 
Importer les fichiers SQL présents dans le dossier : 
database/ 
Les fichiers permettent : 
• La création des tables ;  
• L'insertion des données nécessaires au fonctionnement de 
l'application.  
#Configuration 
Créer ou modifier le fichier de configuration : 
config.php 
Renseigner : 
• Les informations de connexion MariaDB ;  
• Les paramètres MongoDB ;  
• La clé API Google Maps.  
DB_HOST 
DB_NAME 
DB_USER 
DB_PASSWORD 
GOOGLE_API_KEY 
##Lancer l'application 
Démarrer Apache et MariaDB depuis XAMPP. 
Accéder ensuite à l'application : 
http://localhost/vite-gourmand 
#Base de données 
L'application utilise deux systèmes de stockage. 
MariaDB 
MariaDB contient les données principales : 
• Utilisateurs ;  
• Employés ; 
• Commandes ;  
• Menus ;  
• Plats ;  
• Stocks ;  
• Avis ;  
• Thèmes ; 
• Régimes ; 
• Allergènes; 
• Réinitialiser mot de passe ; 
• Formulaire de contact.  
Les scripts SQL sont disponibles dans : 
database/ 
MongoDB 
MongoDB est utilisée pour stocker les statistiques nécessaires au 
tableau de bord administrateur. 
Elle permet notamment de suivre : 
• Nombre de commandes par menu ;  
• Chiffre d'affaires ;  
• Évolution des ventes.  
#Comptes de démonstration 
##CLIENT 
Email : client@vite-gourmand.fr 
Mot de passe : Clie_clie280* 
##ADMINISTRATEUR 
Email : admin@vite-gourmand.fr 
Mot de passe : Admin_admin280 
##EMPLOYE 
Email : employee@vite-gourmand.fr 
Mot de passe : Empl_empl280 
#Gestion des versions Git 
Le projet utilise une organisation basée sur plusieurs branches. 
main 
Branche contenant la version stable de l'application. 
develop 
Branche utilisée pour regrouper les fonctionnalités avant 
validation. 
feature/* 
Branches dédiées au développement de nouvelles fonctionnalités. 
Exemple : 
feature/gestion-commandes 
feature/statistiques 
feature/authentification 
Une fois une fonctionnalité terminée et testée, elle est fusionnée 
dans la branche develop, puis la branche develop est fusionnée dans 
main après validation. 
#Déploiement 
L'application est destinée à être déployée sur un hébergement web. 
Les étapes principales sont : 
1. Transfert des fichiers de l'application.  
2. Configuration du domaine.  
3. Importation de la base de données.  
4. Configuration des paramètres de connexion.  
5. Vérification du fonctionnement.  
6. Tests après mise en ligne.  
URL de démonstration : 
https://vite-gourmand.oo.gd 
#Auteur 
Projet réalisé par : 
Eline Brun 
Formation : 
Développeur Web et Web Mobile
