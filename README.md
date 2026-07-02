# FICHEMAG FOR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
!Parlé de chaque composant : logo / attributs / marque / prix / qr code / code barre ...!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


## Fonctionnalité

Ce module offre un modèle de PDF pour le module produit.

Il rajoute par défaut huit attributs au module Produit:

  - Marque
  - Processeur
  - Mémoire vive
  - Stockage
  - Ecran
  - Carte Graphique
  - Système
  - Usage
  - Garantie

## Utilisation

Les huit attributs des caractéristiques sont paramètrables lors de la création du produit puis modifiable depuis l'affichage de celui-ci.

Si vous voulez que le modèle soit celui généré par défaut dans la liste des Modèles de documents:
  - Connectez-vous en tant que super-administrateur
  - Allez dans "Accueil"> "Configuration"> "Modules/Applications"
  - Allez dans les setting du module Fichemag
  - Vous pouvez choisir le modèle par défaut ici, dans "Modèle de document pour la fiche produit"
  - \(Assurez-vous que l'état du modèle "fiche Magasin" soit activé, sinon il ne vous sera pas proposé dans les fiches produits !!\)

Pour générer une fiche produit de ce module, il vous suffit d'aller sur un produit dans la liste de produits du module de Dolibarr et d'y choisir le modèle "fiche Magasin" dans les modèles de documents.
Une modification des attributs nécessitera une regénération du modèle !

## Configuration

Si vous souhaitez rajouter ou retirer des attributs dans le modèle de PDF :
  - Connectez-vous en tant que super-administrateur
  - Allez dans "Accueil"> "Configuration"> "Modules/Applications"
  - Allez dans les setting du module Fichemag
  - Les attributs ce situe dans l'onglet "Attributs supplémentaires".

Ici il est possible de modifier les attributs déjà mis par le module ou dans rajouter (huit maximum sans compter l'attribut marque), pour que l'attribut soit pris en compte par le PDF il faut que le "Code de l'attribut" commence par "fichemag_" pour qu'il soit pris en compte.

## Installation

Ce module ne fonctionne que si le module Produits est activé, le modèle utilisé implémente les code-barres mais peut fonctionner sans.

### Avec un dossier ZIP

Pour déployer un module contenu dans un fichier ZIP,
  - Connectez-vous en tant que super-administrateur
  - Allez dans "Accueil"> "Configuration"> "Modules/Applications"
  - Allez dans l'onglet "Déployer/Installer un module externe"
Il ne vous reste plus qu'à choisir le fichier ZIP pour le déployer.

### Depuis le repository

Cette méthode requière Git d'installé sur la machine.

Déplacer vous dans le dossier custom de votre site Dolibarr "/htdocs/custom" depuis le terminal de commande et cloné le répository du module.

```shell
cd ....../htdocs/custom
git clone https://github.com/Marie-Theo/FicheMag fichemag
```

### Dernière étape

IL ne vous reste plus qu'à activer le module depuis "Modules/Applications".