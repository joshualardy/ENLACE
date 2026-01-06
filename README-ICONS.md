# Guide d'utilisation des icônes Heroicons

Ce thème utilise la bibliothèque Heroicons pour les icônes SVG.

## Installation

1. **Installer les dépendances npm** :
```bash
npm install
```

2. **Extraire les icônes SVG** :
```bash
npm run extract-icons
```

Cette commande extrait tous les SVG des icônes Heroicons vers `assets/icons/heroicons/24/solid/`.

## Utilisation dans les templates PHP

### Méthode 1 : Fonction simple

```php
<?php the_icon('BeakerIcon'); ?>
```

ou

```php
<?php the_icon('beaker'); ?>
```

### Méthode 2 : Avec attributs personnalisés

```php
<?php 
the_icon('BeakerIcon', array(
    'class' => 'size-6 text-blue-500',
    'width' => '24',
    'height' => '24'
)); 
?>
```

### Méthode 3 : Fonction stylée (recommandée)

```php
<?php the_icon_styled('BeakerIcon', 'size-6', 'text-blue-500'); ?>
```

### Méthode 4 : Récupérer le HTML (sans l'afficher)

```php
<?php 
$icon_html = get_icon('BeakerIcon', array('class' => 'icon'));
echo $icon_html;
?>
```

## Exemples d'utilisation

### Dans un bouton

```php
<button class="btn">
    <?php the_icon_styled('PlusIcon', 'size-5', 'text-white'); ?>
    Ajouter
</button>
```

### Dans un lien

```php
<a href="#" class="link">
    <?php the_icon_styled('UserIcon', 'size-4'); ?>
    Profil
</a>
```

### Avec des classes Tailwind CSS

```php
<div class="flex items-center gap-2">
    <?php the_icon('LocationIcon', array('class' => 'w-5 h-5 text-gray-500')); ?>
    <span>Bruxelles</span>
</div>
```

## Liste des icônes disponibles

Toutes les icônes Heroicons sont disponibles. Voici quelques exemples courants :

- `UserIcon` / `user` - Utilisateur
- `LocationIcon` / `location` - Localisation
- `HeartIcon` / `heart` - Cœur/Favori
- `BookmarkIcon` / `bookmark` - Signet
- `ChatBubbleIcon` / `chatbubble` - Message
- `PlusIcon` / `plus` - Ajouter
- `XMarkIcon` / `xmark` - Fermer
- `PencilIcon` / `pencil` - Modifier
- `TrashIcon` / `trash` - Supprimer
- `SearchIcon` / `search` - Rechercher
- `BellIcon` / `bell` - Notification
- `SettingsIcon` / `settings` - Paramètres

Pour voir toutes les icônes disponibles, consultez la [documentation Heroicons](https://heroicons.com/).

## Notes

- Les icônes sont extraites au format SVG 24x24 solid
- Les icônes utilisent `fill="currentColor"` par défaut, donc elles héritent de la couleur du texte
- Vous pouvez utiliser n'importe quel nom d'icône avec ou sans le suffixe "Icon"
- Les icônes sont sensibles à la casse (utilisez la casse exacte du nom de fichier)
