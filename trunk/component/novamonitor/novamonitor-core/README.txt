README du monitoring d'applications NovaForge
*********************************************

Cet outil de monitoring a pour but de monitorer les serveurs d'applications Java / JEE en recette et en production.

Ce n'est pas un outil de simulation de requêtes utilisateur,
c'est un outil de mesure et de statistiques sur le fonctionnement réel d'une application
selon l'usage qui en est fait par les utilisateurs.

Auteur : Emeric Vernat, Bull (emeric.vernat@bull.net)
Licence : GPL comme GForge
Version Java requise en exécution : 1.5 minimum, 1.6 recommandé pour fonctions complémentaires 
												 (heap dump, stack traces et system load average)
Dépendance requise : JRobin (LGPL) pour les courbes d'évolution
Dépendance optionnelle : iText (LGPL ou MPL) pour les rapports au format pdf en plus de html
Langage : IHM et documentation d'installation en français

Pour le guide d'installation et le guide développeur voir le fichier texte src/site/apt/user_guide.apt
Pour d'autres guides voir le répertoire src/site/resources/
