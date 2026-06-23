# FICHEMAG FOR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Features

Ce module offre un modèle de PDF pour le module produit.
Il rajoute huits attributs au module Produit:

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

##

Si vous s


## Personalisation

Si vous souhaité rajouter ou retirer des attributs dans le modèle de PDF, il vous sufira d'allé dans "Configuration"> "Modules/Applications" allé dans les setting du module Produits, les attributs ce situe dans l'onglet "Attributs supplémentaires".

Ici il est possible de modifier les attributs déjà mis par le module ou dans rajouter, pour que l'attribut soit pris en compte pas le PDF il faut que "Code de l'attribut" commence par "fichemag_"

## Installation

Ce module ne fonctionne que si le module Produits est activée, le modèle utilise implémante les code barre mais peut fonctionné sans le module implémenté.

### From the ZIP file and GUI interface

If the module is a ready-to-deploy zip file, so with a name `module_xxx-version.zip` (e.g., when downloading it from a marketplace like [Dolistore](https://www.dolistore.com)),
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
