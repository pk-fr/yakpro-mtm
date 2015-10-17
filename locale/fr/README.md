# YAK Pro - mysql to mysqli converter

**YAK Pro** vient de  **Y**et **A**nother **K**iller **Pro**duct.

Gratuit, Open Source, Publié sous les termes de la licence MIT.  

Ce programme utilise le meilleur parser php [PHP-Parser](https://github.com/nikic/PHP-Parser) pour analyser le php.  
[PHP-Parser](https://github.com/nikic/PHP-Parser) est une remarquable bibliothèque développée par [nikic](https://github.com/nikic).

Télécharger l'archive zip et décompressez la dans le sous-répertoire PHP-Parser .
ou alors utilisez git clone.

Le fichier de configuration yakpro-mtm.cnf est auto-documenté et contient les options de configuration !
Un petit coup d'oeil vaut le détour.  

Démo : [yakpro-mtm demo](http://mysql-to-mysqli.yakpro.com/?demo).

Pré-requis:  php 5.3 ou supérieur, [PHP-Parser](https://github.com/nikic/PHP-Parser).


## Pourquoi un convertisseur mysql vers mysqli ?

Historiquement, les requètes mysql étaient gérées en php par l'extension mysql.  
Cette extension est obsolète depuis PHP 5.5.0, mais beaucoup de logiciels php l'utilisent encore.  
**Dans le futur php 7, cette extension est retirée**, ce qui constitue une rupture de rétro-compatibilité !

Les quelques alternatives, pour les personnes désireuses de porter leur logiciel sous **php7** sont:  
1) Ré-écriture complète avec PDO, qui est une couche d'abstraction de bases de données.  
   La logique d'implémentation n'est pas la même, et requière beaucoup de travail.  
2) Ré-écriture avec mysqli, qui possède 2 types d'interfaces :  
   - Une interface orientée objet.  
   - Une interface procédurale, assez similaire à celle de mysql, mais avec quelques changements dont entre autre, inversion de l’ordre des paramètres ou nouveaux paramètres nécessaires.  
  
Ce convertisseur utilise la forme procédurale.  
Il est adapté aux personnes qui n’envisagent pas une ré-écriture de leurs anciens programmes impliquant un changement de logique, et qui veulent le porter vers mysqli à moindre coût, en faisant le moins de changements possibles.  

   
### Principales fonctionnalités de YAK Pro - mysql to mysqli converter :

- Si votre logiciel utilise toujours le même paramètre "link", vous pouvez le spécifier.
- Converti récursivement le répertoire d'un projet.
- Un mécanisme de type Makefile, basé sur l'horodatage des fichiers, permet de ne re-convertir que les fichiers  
  ayant été modifiés depuis la dernière conversion.


## Installation :
    Placez l'arborescence téléchargée (ou faites un git clone ...)  ou vous voulez ...

        chmod a+x yakpro-mtm.php     vous aidera grandement!

    Créer un lien symbolique yakpro-mtm dans /usr/local/bin pointant sur le fichier yakpro-mtm.php
    serait une bonne idée !

    Placez le répertoire PHP-Parser (obtenu par téléchargement ou git clone)
    au même niveau que le fichier yakpro-mtm.php.

    Créez une copie du fichier yakpro-mtm.cnf
    Lire la section "Algorithme de chargement du fichier de configuration"
    pour savoir où le placer, et modifiez le selon vos besoins.

    C'est tout! vous n'avez plus qu'à tester !

####

## Utilisation :

`yakpro-mtm`
La conversion se fait selon le fichier de configuration!
Veuillez consulter la section "Algorithme de chargement du fichier de configuration" de  
cette documentation.

`yakpro-mtm fichier_source`
La conversion est dirigée vers la sortie standard (stdout)

`yakpro-mtm fichier_source -o fichier_cible`
fichier_cible contiendra le résultat de la conversion.

`yakpro-mtm répertoire_source -o répertoire_cible`
Exécutera une conversion récursive du code vers le répertoire répertoire_cible/yakpro-mtm
(le répertoire cible est automatiquement créé si il n'existe pas déjà !)

`yakpro-mtm --config-file chemin_du_fichier_de_config`
Permet de spécifier un fichier de config.

`yakpro-mtm --clean`
Le répertoire cible doit être renseigné dans le fichier de configuration!  
Supprime récursivement le répertoire répertoire_cible/yakpro-mtm


## Algorithme de chargement du fichier de configuration :
(Le premier trouvé sera utilisé)

    --config-file répertoire_cible
    La valeur de la variable d'environnement YAKPRO_MTM_CONFIG_FILE
    si elle existe et est non vide.

    détermination du nom de fichier :
           La valeur de la variable d'environnement YAKPRO_MTM_CONFIG_FILENAME
           si elle existe et est non vide,
           yakpro-mtm.cnf sinon.

    Le fichier est ensuite recherché dans les répertoires suivants :
            La valeur de la variable d'environnement YAKPRO_MTM_CONFIG_DIRECTORY
                                                si elle existe et est non vide.
            répertoire_de_travail_courant
            répertoire_de_travail_courant/config
            home_directory
            home_directory/config
            /usr/local/YAK/yakpro-mtm
            source_code_directory/default_conf_filename

      Si aucun fichier de configuration n'est trouvé, les valeurs par défaut sont utilisées.

      Le fichier de configuration par défaut est le fichier yakpro-mtm.cnf situé à la racine du dépot.
      Ne modifiez pas directement ce fichier, car il sera ré-écrasé à chaque mise à jour !
      Utilisez votre propre fichier yakpro-mtm.cnf ( par exemple à la racine de votre projet )

      Lorsque vous travaillez sur des répertoires,
      Lorsque vous modifiez un ou plusieurs fichier, yapro-mtm utilise l'horodatage des fichiers
      pour ne re-convertir que les fichiers modifiés depuis la conversion précédente.
      Celà vous permettra de gagner un temps précieux !

      Attention: les fichiers qui ne sont plus présents dans le source ne sont pas retirés de la cible !...
                 utilisez l'option  --clean  et re-convertissez l'ensemble du projet.

## Autre options de la ligne de commande :
( modifient les options du fichier de configuration )

    --silent                        ommet l'affichage des messages de niveau Information.
    --debug                         (utilisation interne pour le debug) affichage de l'arbre syntaxique.

    --no-default-link-name          ne pas utiliser de nom de 'link' par défaut.
    --default-link-name  name       utilise name comme nom de 'link' par défaut (ne pas préfixer par $)

    --indent-mode  mode             défini le mode d'indentation ( standard ou yakpro ).

    -h ou
    --help                          affiche l'aide.

####

