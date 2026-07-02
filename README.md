# FICHEMAG FOR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
!                                                                                         !
! Parlé de chaque composant :                                                             !
! logo / attributs / marque / prix / qr code / code barre / entete / contact + horaire ...!
!                                                                                         !
!                                                                                         !
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


## Fonctionnalité

Ce module offre un modèle de PDF pour le module Produit.

Il ajoute par défaut huit attributs au module Produit :

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

Pour utiliser le modèle ainsi que l'activer par défaut, on va dans les paramètre de fichemag :
- Connectez-vous en tant que super-administrateur.
- Allez dans "Accueil" > "Configuration" > "Modules/Applications".
- Allez dans les réglages du module Fichemag.
- Vous pouvez choisir le modèle par défaut ici, dans "Modèle de document".
- \(Assurez-vous que l'état du modèle "fiche Magasin" soit activé, sinon il ne vous sera pas proposé dans les fiches produits !!\)

### Les attributs :
Les huit attributs des caractéristiques sont paramétrables lors de la création du produit, puis modifiables depuis l'affichage de celui-ci.

### Générer
Pour générer une fiche produit de ce module, il vous suffit d'aller sur un produit dans la liste des produits du module de Dolibarr et d'y choisir le modèle "fiche Magasin" dans les "Modèles de documents".
Une modification des attributs nécessitera une régénération du modèle !

## Configuration

### Attributs

Vous pouvez changer les caractéristiques à montrer dans le PDF pour cela.

Si vous souhaitez rajouter, retirer ou changer des caractéristiques (attributs) dans le modèle de PDF :
- Connectez-vous en tant que super-administrateur.
- Allez dans "Accueil" > "Configuration" > "Modules/Applications".
- Allez dans les réglages du module Fichemag.
- Les attributs se situent dans l'onglet "Attributs supplémentaires".

Ici, il est possible de modifier les attributs déjà mis par le module ou d'en rajouter (avec une limite de 8 maximum, sans compter l'attribut marque. au risque que des éléments se chevauchent.), pour que l'attribut soit pris en compte par le PDF, il faut que le "Code de l'attribut" commence par "fichemag_" pour qu'il soit pris en compte.

### QR code & Code barre
L'option Code-barres ne fonctionne que si le module Code-barres est activé.
Le QR code ne fonctionnera que si le Dolibarr est ouvert sur le web avec hébergement ainsi qu'un nom de domaine.

Si toutes les conditions sont remplies et que les deux paramètres QR code et Code-barres sont activés, on peut choisir l'emplacement du code-barres dans "Style du Code-barres" ('sous le prix', 'sous le code-barres', 'sur le côté').

### En-tête, contact & Horaire
Le texte en en-tête est modifiable depuis les paramètres du module ainsi que les Horaires du magasin dans le pied de page.

Si aucun logo, numéro de téléphone ou mail n'est pas saisie dans la configuration de société, le PDF s'adaptera.
Vous pouvez désactiver l'affichage du numéro de téléphone ou du mail depuis le paramétrage du module dans "Contenue du PDF".

## Installation

Ce module ne fonctionne que si le module Produits est activé.
Le modèle de PDF implémente les codes-barres, mais peut fonctionner sans.

### Avec un dossier ZIP

Pour déployer un module contenu dans un fichier ZIP :
- Connectez-vous en tant que super-administrateur.
- Allez dans « Accueil » > « Configuration » > « Modules/Applications ».
- Allez dans l'onglet "Déployer/Installer un module externe"
Il ne vous reste plus qu'à choisir le fichier ZIP pour le déployer.

### Depuis le repository

Cette méthode requiert Git installé sur la machine.

Déplacer vous dans le dossier custom de votre site Dolibarr "/htdocs/custom" depuis le terminal de commande et cloné le répository du module.

```shell
cd ....../htdocs/custom
git clone https://github.com/Marie-Theo/FicheMag fichemag
```

### Dernière étape

Il ne vous reste plus qu'à activer le module depuis « Modules/Applications ».