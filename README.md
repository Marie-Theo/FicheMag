# FICHEMAG FOR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Fonctionnalité

Ce module offre un modèle de PDF pour le module produit.
Il rajoute huit attributs au module Produit par défaut:

  - Marque
  - Processeur
  - Mémoire vive
  - Stockage
  - Ecran
  - Carte Graphique
  - Système
  - Usage
  - Garantie

<!--
![Screenshot fichemag](img/screenshot_fichemag.png?raw=true "FicheMag"){imgmd}
-->

## Utilisation

Les huit attributs sont paramètrables lors de la création du produit puis modifiable depuis l'affichage de celui-ci.

Si vous voulez que le modèle soit celui généré par défaut dans la liste des Modèles de documents:
  - Connectez-vous en tant que super-administrateur
  - Allez dans "Accueil"> "Configuration"> "Modules/Applications"
  - Allez dans les setting du module Produits
  - Vous pouvez choisir le modèle par défaut ici, dans "Modèle de document pour la fiche produit"
  - \(Assurez-vous que l'état du modèle "fiche Magasin" soit activé, sinon il ne vous sera pas proposé dans les fiches produits !!\)

Pour générer une fiche produit de ce module, il vous suffit d'aller sur un produit dans la liste de produits du module de Dolibarr et d'y choisir le modèle "fiche Magasin" dans les modèles de documents.
Une modification des attributs nécessitera une regénération du modèle !

## Configuration

Si vous souhaitez rajouter ou retirer des attributs dans le modèle de PDF :
  - Connectez-vous en tant que super-administrateur
  - Allez dans "Accueil"> "Configuration"> "Modules/Applications"
  - Allez dans les setting du module Produits
  - Les attributs ce situe dans l'onglet "Attributs supplémentaires".

Ici il est possible de modifier les attributs déjà mis par le module ou dans rajouter (huit maximum sans compter l'attribut marque), pour que l'attribut soit pris en compte par le PDF il faut que le "Code de l'attribut" commence par "fichemag_" pour qu'il soit pris en compte.

## Installation

Ce module ne fonctionne que si le module Produits est activé, le modèle utilisé implémante les code-barres mais peut fonctionner sans.

## Avec un dossier ZIP

Si le module est prêt à etre déployé 






### From the ZIP file and GUI interface

If the module is a ready-to-deploy zip file, so with a name `module_xxx-version.zip`,
go to menu `Home> Setup> Modules> Deploy external module` and upload the zip file.

<!--

Note: If this screen tells you that there is no "custom" directory, check that your setup is correct:

- In your Dolibarr installation directory, edit the `htdocs/conf/conf.php` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading `//`) and assign the proper value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```
-->

### From a GIT repository

Clone the repository in `$dolibarr_main_document_root_alt/fichemag`

```shell
cd ....../htdocs/custom
git clone https://github.com/Marie-Theo/FicheMag fichemag
```

<!--

-->

### Final steps

Using your browser:

  - Log into Dolibarr as a super-administrator
  - Go to "Setup"> "Modules"
  - You should now be able to find and enable the module



## Licenses

### Main code

GPLv3 or (at your option) any later version. See file COPYING for more information.

### Documentation

All texts and readme's are licensed under [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).
