# Installation locale — OS::JOB v0.14.1

**Usage local uniquement.** Cette version n'intègre pas d'authentification.

1. Installer XAMPP (Apache, PHP 8.1+ avec PDO MySQL, MariaDB). L'environnement historique utilisait PHP 8.2 / MariaDB 10.4.
2. Copier le dossier `os-job` dans `C:\xampp\htdocs\os-job`.
3. Dans phpMyAdmin, créer une **base vide** `os_job` en `utf8mb4_unicode_ci`.
4. Importer `database/schema.sql`, puis `database/seed_local.sql` **dans cet ordre**, dans cette base vide. Ne pas importer ces scripts sur une base contenant déjà des données.
5. Copier `app/config/database.example.php` vers `app/config/database.php`, puis adapter les identifiants MariaDB.
6. Ouvrir `http://localhost/os-job/public/index.php`.
7. Vérifier l'ouverture du tableau de bord, du profil, des recherches et du Centre d'import.

**Limite de validation :** le schéma a été dérivé de l'export de la v0.14.1 en supprimant toutes les données utilisateur. L'installation complète sur une base neuve **n'a pas été exécutée** ici : vérifier la recette avant de déclarer cette procédure stable.

Ne jamais placer cette version sur un hébergement accessible depuis Internet. Le compte `local@os-job.invalid` est un identifiant technique, **pas un système de connexion**. Protéger les dossiers non publics et sauvegarder vos données locales.
