# API en Symfony 6.4 pour un SaaS

API pour un SaaS/CRM de gestion de client et de suivi de commandes.

## Prérequis

- PHP 8.1 ou +
- Composer
- MySQL ou autre SGBDR

## Configuration

1. **Cloner le dépôt actuel**

    Si vous avez Git installé et configuré sur votre environnement de travail, utilisez la commande suivante :

    ```
    git clone git@github.com:Maxime-LEGENTIL/api-saas.git
    ```

2. **Création du fichier d'environnement**

    Dupliquez l'intégralité du fichier `.env` que vous allez mettre dans un fichier `.env.local` (à créer par vous) à la racine du projet.

3. **Configuration de la base de données**

    Veuillez indiquer les informations de connexion à votre base de donnée dans cette partie du `.env.local` :

    ```
    DATABASE_URL="mysql://nomUtilisateur:motDePasse@127.0.0.1:3306/nomDeLaDatabase?serverVersion=versionMySQL&charset=utf8mb4"
    ```

    Il suffit de remplacer les informations suivantes :

    - nomUtilisateur
    - motDePasse
    - nomDeLaDatabase
    - versionMySQL

## Installation

1. **Dépendances**

    Afin d'installer les dépendances liées au projet, veuillez exécuter la commande suivante afin que le dossier vendor soit présent à la racine du projet :

    ```
    composer install
    ```

2. **Création de la base de données**

    Créez la base de données avec la commande suivante :

    ```
    php bin/console doctrine:database:create OU php bin/console do:da:cr
    ```

3. **Création de la migration**

    Appliquez les migrations pour créer les tables nécessaires :

    ```
    php bin/console doctrine:database:create OU php bin/console do:da:cr
    ```

4. **Jeux de données**

    Afin d'installer des données fictives au sein de votre base de données, veuillez utiliser la commande suivante :

    ```
    php bin/console doctrine:fixtures:load OU php bin/console do:fi:lo
    ```

## Réalisation

Projet réalisé par Maxime LE GENTIL.

- https://maxime-l.net/

## Endpoints

Les endpoints sont accessible depuis la route `/api/doc` une fois le serveur lancé.

![Screenshot doc API](api-screenshot.png)
