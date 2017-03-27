# Quizzbox-Network
Projet tutoré LP CISIIE.
Créez, partagez et jouez à des quizzs facilement, seul ou avec des amis, sur un appareil portable.
Plateforme communautaire.
- Serveur 1 : https://livekiller44.cf/quizzbox/
- Serveur 2 : https://quizzbox.cf/Quizzbox-Network/

# Requirements
- Un serveur apache2 avec mod_rewrite, PHP 5.6 et une base de données MySQL ou MariaDB, serveur hébergé chez soi avec un nom de domaine ou un serveur dédié/mutualisé chez un hébergeur.
- Le serveur doit être configuré pour l'envoi de mails

# Configuration serveur
```
git clone https://github.com/hitoshi54/Quizzbox-Network.git
```
Dans le répertoire conf de Quizzbox-Network se trouve config.ini.modele.
C'est le modèle du fichier de configuration que vous devez créer pour la connexion à la base de données (config.ini).

Le dossier "upload" doit avoir les droits nécessaires pour pouvoir créer/supprimer des fichiers
