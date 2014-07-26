ZeroPub
=======

Paquet Composer pour utiliser ZeroPub.

## Installation

Pour installer le SDK, vous aurez besoin de [Composer](http://getcomposer.org/) dans votre projet.

```PHP
# Installer Composer
curl -sS https://getcomposer.org/installer | php

# Ajouter la dépendance ZeroPub
php composer.phar require zeropub/zeropub-php:~1.0
``` 

Ensuite, utilisez l'autoloader de Composer dans votre application pour charger automatiquement le SDK ZeroPub dans
votre projet :

```PHP
require 'vendor/autoload.php';

use ZeroPub\ZeroPub;
```

## Créer une instance ZeroPub

Une seule instance ZeroPub doit être créée tout au long du script.

```PHP
$zeroPub = new ZeroPub(); // Création de l'instance

$zeroPub->setSiteId(1); // ID du site sur ZeroPub
$zeroPub->setSiteSecret('.szMXUdDR3z2_hYkHts7sj!JXqMsA4WE'); // Clé secrète
$zeroPub->setActive(ZeroPub::ACTIVITY_DEBUG); // Niveau d'activité du site
$zeroPub->setHostname('myhost.mydomain.net'); // Nom d'hôte sur lequel est actif ZeroPub
$zeroPub->setScriptName('thebig/script'); // Nom du script ZeroPub
```

Enfin on enregistre la classe qui génère les sorties HTML :

```PHP
use ZeroPub\Provider\Output;

$zeroPub->setOutput(new Output());
```

Vous pouvez faire votre propre classe en implémentant `ZeroPub\Provider\OutputInterface`.

## Enregistrer une zone ZeroPub



## Afficher une zone ZeroPub

Grâce au nom de la zone, il vous suffit d'appeler `displayAd` qui utilisera la sortie déclarée :

```PHP
echo $zeroPub->displayAd('header_ad');
```

## Afficher les infos de l'utilisateur

Pour afficher les informations de l'utilisateur (retournées via un appel AJAX) et avoir l'état de son abonnement
ZeroPub, vous devez générer le code HTML :

```PHP
echo $zeroPub->displayInfo();
```

## Afficher les scripts ZeroPub

Enfin, pour que ZeroPub fonctionne, vous devez charger les scripts. La méthode `displayScript` génère l'HTML :

```PHP
echo $zeroPub->displayScripts();
```

## Mettre en place les callbacks

Pour communiquer avec les serveurs ZeroPub à plusieurs moments de l'utilisation du script (identification utilisateur,
mise à jour des configurations), vous devez mettre en place des URL de callback publiques.

### Connexion d'un utilisateur

Exemple :

```
http://www.monsiteweb.com/external/zp-user-connect?zp_hash=2bd34a8716a353ea8a22dbfdb3a6b8ef&zp_back=http%3A%2F%2Fwww.monsiteweb.com%2F
```

Un utilisateur qui vient de regarder une vidéo peut se connecter avec un identifiant de réseau social pour garder sa
participation enregistrée. N'importe quel utilisateur qui a regardé une vidéo sera aussi enregistré via un cookie
anonyme s'il ne souhaite pas se connecter.

À chaque fois, l'utilisateur sera redirigé sur cette URL après son identification. Les paramètres GET sont :

- **zp_hash** - La valeur du cookie `zp_hash` qui identifie l'utilisateur
- **zp_back** - L'URL vers laquelle rediriger l'utilisateur après le traitement du callback.

Un prototype de script PHP correspondant à cette logique :

```PHP
if (isset($_GET['zp_back']) && isset($_GET['zp_hash'])) {

    setcookie('zp_hash', $_GET['zp_back'], time() + 86400 * 365 * 10, '/');

    header('Location: ' . $_GET['zp_back']);
    exit;

}
```

### Déconnexion d'un utilisateur

Exemple :

```
http://www.monsiteweb.com/external/zp-user-disconnect
```

Pour une raison ou une autre, l'utilisateur peut décider de se déconnecter de son compte ZeroPub. Vous devez fournir
une URL qui permettra de supprimer le cookie `zp_hash` sur votre domaine.

Un prototype de script PHP correspondant à cette logique :

```PHP
setcookie('zp_hash', null, time() - 1, '/');
```

### API interne avec les serveurs ZeroPub

Pour permettre de garder la configuration en phase avec les informations données sur l'interface éditeur, nous
devons communiquer avec votre site via une API interne sur une URL que vous devez fournir. Exemple :

```
http://www.monsiteweb.com/external/zp-api
```

C'est un appel POST qui contient au moins deux paramètres :

- **secret** - Doit correspondre à la valeur de votre clé secrète ZeroPub
- **action** - Définit l'action à faire par l'API, valeurs ci-dessous.

#### Les actions de l'API

- **active** - Indique le nouveau niveau d'activité du site
  - `active` peut être à 0, 1 ou 2
- **hostname** - Indique le nouveau nom de domaine et le nom des scripts ZeroPub
  - `hostname`
  - `script_name`
- **ads** - Envoie toutes les zones configurées sur l'interface éditeur ZeroPub. Les variables POST sont des tableaux
  avec les noms correspondants aux paramètres habituellement remplis dans le modèle `Ad`.
  - `names[]`
  - `htmls[]`
  - `classes[]`
  - `widths[]`
  - `heights[]`
  - `placeholders[]`
- **version** - Demande la version du script utilisée sur votre site. Doit renvoyer un JSON avec les informations
  indiquées dans le prototype.

Prototype de script pour prendre en charge ces actions. Beaucoup ont besoin d'inclure **votre propre logique** pour
les changements de configuration.

```PHP
if (isset($_POST['secret']) && $_POST['secret'] == $zeroPub->getSiteSecret()) {

    switch ($_POST['action']) {

        case 'active':
            if (in_array($_POST['active'], array(ZeroPub::ACTIVITY_NONE, ZeroPub::ACTIVITY_ACTIVE, ZeroPub::ACTIVITY_DEBUG))) {
                // Changer la valeur de l'activité du site dans la configuration
            }
            break;

        case 'hostname':
            // $_POST['hostname'] le nouveau nom de domaine ZeroPub
            // $_POST['script_name'] le nouveau nom du script ZeroPub
            break;

        case 'ads':
            $numberOfAds = count($_POST['names']);

            for ($i=0; $i<$numberOfAds; $i++) {
                // $_POST['names'][$i] le nom de la zone
                // $_POST['htmls'][$i] le code HTML de la zone
                // $_POST['classes'][$i] le nom de la classe CSS
                // $_POST['widths'][$i] la largeur de la zone
                // $_POST['heights'][$i] la hauteur de la zone
                // $_POST['placeholders'][$i] le placeholder si la zone est bloquée
            }
            break;

        case 'version':
            header('Content-type: text/json');

            echo json_encode(array(
                    'version' => $zeroPub->getVersion(),
                    'type' => 'composer',
                    'update' => false
                )
            );

            exit;
            break;

    }

}
```

Vous pouvez configurer ces 3 URLs de callback dans votre interface éditeur.

## Options disponibles

### Activer l'interprétation de PHP

Si vous avez du code PHP dans vos zones publicitaires, vous pouvez les mettre dans la chaîne de caractère ZeroPub.
Cependant, il faudra explicitement activer l'exécution de PHP dans les zones :

```PHP
$zeroPub->allowPhp();
```

Cette option est désactivée par défaut.

### Changer la version du script

Par défaut la version du scrip de production est la version 1. Si vous avez besoin de tester une autre version,
vous pouvez le spécifier :

```PHP
$zeroPub->setScriptVersion(2);
```

### Forcer le niveau d'activation du site

Par défaut le niveau d'activation du site dépend de la configuration dans votre interface éditeur ZeroPub. Vous pouvez
forcer un autre niveau à des fins de test en modifiant l'appel des scripts :

```PHP
echo $zeroPub->displayScripts(3);
```

Les niveaux vont de 1 à 3 et correspondent à ceux de l'interface éditeur.

## Exceptions

Toutes les exceptions sorties par `ZeroPub` sont des instances de `ZeroPub\Exception\ZeroPubException`.