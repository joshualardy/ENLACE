# Proposition de réorganisation du formulaire d'inscription ENLACE

## Analyse du formulaire actuel

### Champs existants

**Étape 1 (template-register.php) :**
- Nom (last_name) - requis
- Prénom (first_name) - requis
- Nom d'utilisateur (user_login) - requis
- Email (user_email) - requis
- Mot de passe (user_pass) - requis
- Confirmer le mot de passe (user_pass_confirm) - requis
- N° de téléphone (phone) - requis
- Ville (ville) - requis
- Choix du type de service (offer/seek)

**Étape 2a (template-offering-service.php) - Pour "J'offre mon service" :**
- Photo de profil (optionnel)
- Biographie (requis)
- Genre (requis)
- Filtres/services (requis) : Beatmaker, Chanteur, Organisateur, DJ, Ingénieur son, Compositeur, Musicien
- Productions (optionnel)

**Étape 2b (template-seeking-service.php) - Pour "Je cherche un service" :**
- Photo de profil (optionnel)
- Biographie (requis)
- Genre (requis)
- Genres musicaux préférés (requis)

---

## Proposition : Structure en 3 étapes

### Étape 1 : Identité et compte
**Objectif :** Créer les identifiants de connexion et obtenir les informations de base nécessaires pour identifier l'utilisateur.

**Champs :**
- Prénom (first_name) - requis
- Nom (last_name) - requis
- Email (user_email) - requis
- Mot de passe (user_pass) - requis
- Confirmer le mot de passe (user_pass_confirm) - requis

**Raisonnement UX :**
- Regroupe les informations essentielles pour créer un compte
- Nom et prénom permettent d'identifier la personne dès le début
- Email et mot de passe sont les identifiants de connexion
- Pas de nom d'utilisateur séparé : on utilisera l'email comme identifiant ou on générera un nom d'utilisateur automatique
- Étape courte et claire : "Qui êtes-vous et comment vous connecter ?"

**Indicateur de progression :**
- "Étape 1 sur 3 : Identité"

---

### Étape 2 : Contact et localisation
**Objectif :** Obtenir les informations de contact et de localisation nécessaires pour les interactions sur la plateforme.

**Champs :**
- Nom d'utilisateur (user_login) - requis
  - *Note : Si WordPress nécessite un user_login unique, on peut le générer automatiquement ou le demander ici*
- N° de téléphone (phone) - requis
- Ville (ville) - requis (avec datalist des villes belges)

**Raisonnement UX :**
- Séparation logique : après avoir créé le compte, on complète les informations de contact
- Le téléphone et la ville sont nécessaires pour les interactions professionnelles
- Étape courte : seulement 3 champs
- Contexte clair : "Comment vous contacter et où vous trouvez ?"

**Indicateur de progression :**
- "Étape 2 sur 3 : Contact"

---

### Étape 3 : Profil et activité
**Objectif :** Compléter le profil professionnel selon le type de service (offre ou recherche).

**Pour "J'offre mon service" :**
- Photo de profil (optionnel)
- Genre (requis)
- Biographie (requis)
- Filtres/services offerts (requis) : Beatmaker, Chanteur, Organisateur, DJ, Ingénieur son, Compositeur, Musicien
- Productions (optionnel)

**Pour "Je cherche un service" :**
- Photo de profil (optionnel)
- Genre (requis)
- Biographie (requis)
- Genres musicaux préférés (requis)

**Raisonnement UX :**
- Regroupe tout ce qui concerne le profil professionnel
- Le choix "offre/cherche" se fait à la fin de l'étape 2, avant l'étape 3
- Les champs optionnels (photo, productions) ne bloquent pas l'inscription
- Contexte clair : "Présentez-vous et votre activité"

**Indicateur de progression :**
- "Étape 3 sur 3 : Profil"

---

## Système de progression

### Indicateur visuel proposé

**Option 1 : Stepper horizontal (recommandé)**
```
[1] Identité  →  [2] Contact  →  [3] Profil
 ✓              ○              ○
```

**Option 2 : Barre de progression**
```
Étape 1 sur 3 : Identité
[████████████░░░░░░░░] 33%
```

**Recommandation :** Stepper horizontal avec numéros cliquables (pour permettre la navigation si nécessaire) et état visuel clair (complété/en cours/à venir).

### Navigation

- **Bouton "Précédent"** : Visible à partir de l'étape 2, permet de revenir en arrière
- **Bouton "Suivant"** : Valide l'étape courante et passe à la suivante
- **Bouton "Terminer"** : Sur la dernière étape, crée le compte et connecte l'utilisateur

---

## Découpage du choix "offre/cherche"

**Proposition :** Le choix se fait à la fin de l'étape 2, juste avant l'étape 3.

**Raisonnement :**
- L'utilisateur a déjà fourni ses informations de base
- Le choix détermine le contenu de l'étape 3 (champs différents selon offre/cherche)
- Plus naturel : "Maintenant, dites-nous ce que vous faites"

**Interface :**
- Deux boutons côte à côte après la validation de l'étape 2 :
  - "J'offre mon service"
  - "Je cherche un service"
- Ou un sélecteur radio avec le même libellé

---

## Avantages de cette structure

1. **Réduction de la charge cognitive**
   - Maximum 5 champs par étape
   - Chaque étape a un objectif clair et unique

2. **Amélioration du taux de complétion**
   - Étapes courtes = moins d'abandon
   - Progression visible = motivation
   - Validation étape par étape = moins d'erreurs

3. **Cohérence logique**
   - Étape 1 : Qui êtes-vous ? (identité)
   - Étape 2 : Comment vous joindre ? (contact)
   - Étape 3 : Que faites-vous ? (activité)

4. **Adaptabilité**
   - L'étape 3 s'adapte selon le choix offre/cherche
   - Les champs optionnels n'interrompent pas le flux

---

## Points d'attention

1. **Nom d'utilisateur (user_login)**
   - Si WordPress nécessite un user_login unique, deux options :
     a) Le générer automatiquement (email ou prénom.nom)
     b) Le demander à l'étape 2 (comme proposé)

2. **Validation**
   - Validation en temps réel si possible (email valide, mot de passe fort)
   - Messages d'erreur clairs et contextuels
   - Sauvegarde des données en session pour éviter la perte en cas d'erreur

3. **Accessibilité**
   - Labels clairs et associés aux champs
   - Messages d'erreur accessibles
   - Navigation au clavier fonctionnelle

4. **Mobile**
   - Étapes adaptées aux petits écrans
   - Boutons de taille suffisante
   - Champs de formulaire optimisés pour le mobile

---

## Résumé des modifications

### À déplacer

**De l'étape 1 actuelle vers l'étape 2 :**
- Nom d'utilisateur (user_login)
- Téléphone (phone)
- Ville (ville)

**Le choix "offre/cherche" :**
- Se fait à la fin de l'étape 2, avant l'étape 3

### À conserver

**Étape 1 :**
- Prénom, Nom, Email, Mot de passe, Confirmation mot de passe

**Étape 2 :**
- Nom d'utilisateur, Téléphone, Ville

**Étape 3 :**
- Tous les champs de profil (photo, biographie, genre, filtres/genres musicaux, productions)

---

## Ton et libellés

**Ton :** Professionnel, direct, sans fioritures marketing.

**Exemples de libellés :**
- Titre étape 1 : "Identité"
- Titre étape 2 : "Contact"
- Titre étape 3 : "Profil"
- Bouton suivant : "Suivant" (pas "Continuer" ou "Étape suivante")
- Bouton précédent : "Précédent"
- Bouton final : "Terminer l'inscription"

**Pas de :**
- "Presque terminé !"
- "Encore un petit effort"
- "Félicitations, vous y êtes presque"
- Emojis ou éléments décoratifs superflus

---

## Implémentation technique

### Structure des données en session

```php
$_SESSION['registration_data'] = array(
    // Étape 1
    'first_name' => '',
    'last_name' => '',
    'user_email' => '',
    'user_pass' => '',
    
    // Étape 2
    'user_login' => '',
    'phone' => '',
    'ville' => '',
    'service_type' => '', // 'offer' ou 'seek'
    
    // Étape 3 (selon service_type)
    'biographie' => '',
    'genre' => '',
    'filters' => array(), // pour 'offer'
    'music_genres' => array(), // pour 'seek'
    'profile_photo' => '', // optionnel
    'productions' => array() // optionnel, pour 'offer'
);
```

### Validation par étape

- Chaque étape valide ses propres champs avant de passer à la suivante
- Les erreurs sont affichées dans l'étape concernée
- Les données valides sont sauvegardées en session

### Création du compte

- Le compte est créé uniquement à la fin de l'étape 3
- Toutes les données sont sauvegardées en une seule transaction
- L'utilisateur est automatiquement connecté après l'inscription

---

## Conclusion

Cette structure en 3 étapes :
- Réduit la charge cognitive (maximum 5 champs par étape)
- Améliore le taux de complétion (progression visible)
- Reste cohérente avec la DA existante (sobre, élégante)
- Conserve tous les champs existants (pas de perte d'information)
- S'adapte au contexte professionnel du milieu musical

Le formulaire devient plus fluide, plus professionnel, et plus adapté à un public sérieux du milieu musical.
