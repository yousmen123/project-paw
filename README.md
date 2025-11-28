# Gestion d’Assiduité – Projet Yousra

Ce projet est une application web permettant la gestion de l’assiduité des étudiants, la justification des absences, la génération de rapports et l’administration des cours et des sessions.

## Fonctionnalités

- Authentification des utilisateurs (connexion/déconnexion)
- Gestion des étudiants, cours et sessions
- Saisie et suivi des présences et absences
- Justification des absences
- Génération de rapports d’assiduité
- Interface d’administration (dashboard)
- Base de données SQL incluse (`yousra.db.sql`)

## Structure du projet

```
attendance.php         # Gestion des présences
courses.php            # Administration des cours
dashboard.php          # Tableau de bord
index.php              # Page d’accueil
justifications.php     # Gestion des justifications d’absence
login.php / logout.php # Authentification
reports.php            # Rapports d’assiduité
sessions.php           # Gestion des sessions
students.php           # Administration des étudiants
config/database.php    # Configuration de la base de données
css/style.css          # Styles
js/script.js           # Scripts JS
includes/header.php    # En-tête commun
includes/footer.php    # Pied de page commun
yousra.db.sql          # Script de création de la base de données
```

## Installation

1. Cloner le dépôt ou copier les fichiers dans le dossier `htdocs` de XAMPP.
2. Importer le fichier `yousra.db.sql` dans votre serveur MySQL.
3. Configurer l’accès à la base de données dans `config/database.php`.
4. Démarrer Apache et MySQL via XAMPP.
5. Accéder à l’application via `http://localhost/yousra project/index.php`.

## Prérequis

- PHP 7.x ou supérieur
- Serveur Apache (XAMPP recommandé)
- MySQL

## Auteur

Projet réalisé par Yousra (2025).
