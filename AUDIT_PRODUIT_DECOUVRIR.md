# Audit Produit — Page Découvrir
## ENLACE — Plateforme de mise en relation professionnelle

---

## État actuel

La page fonctionne mais manque de signaux de crédibilité et d'activité. Les cards sont propres mais neutres, sans hiérarchie claire ni contexte éditorial. L'expérience ressemble à un annuaire plutôt qu'à une plateforme active.

---

## Améliorations obligatoires

### 1. Bloc de contexte éditorial avant les résultats

**Rôle** : Donner du sens aux résultats affichés, expliquer ce que l'utilisateur voit, orienter la découverte.

**Où** : Entre la section filtres (après le compteur de résultats) et la grille de profils.

**Implémentation** :
- Bloc texte court (2-3 lignes max), style sobre
- Contenu adaptatif selon les filtres actifs :
  - Sans filtres : "Profils actifs sur ENLACE. Contacte directement les professionnels qui correspondent à tes besoins."
  - Avec filtre ville : "Profils à [Ville]. Explore les talents locaux disponibles."
  - Avec filtre talent : "[Talent] disponibles sur la plateforme. Contacte-les pour discuter de ton projet."
  - Avec recherche : "Résultats pour '[terme]'. Affine ta recherche si besoin."
  - Filtres combinés : "[Talent] à [Ville]. Profils correspondant à tes critères."

**Style** : Texte en gris moyen, taille légèrement réduite (0.9rem), italique optionnel, padding vertical modéré.

**Pourquoi** : Évite la sensation de liste brute. Donne un contexte immédiat sans surcharger.

---

### 2. Signal d'activité et temporalité sur les profils

**Rôle** : Indiquer quels profils sont actifs, récents ou pertinents. Crédibilité professionnelle.

**Où** : Sur chaque card, positionné discrètement.

**Implémentation** :
- Badge "Nouveau" (déjà présent) : profils créés dans les 7 derniers jours
- Ajouter un indicateur "Actif" : basé sur la dernière activité (connexion, mise à jour du profil, message reçu) dans les 30 derniers jours
- Afficher la date de dernière mise à jour du profil si disponible (format relatif : "Mis à jour il y a 3 jours")
- Optionnel : petit indicateur visuel (point coloré) pour les profils très actifs (activité dans les 7 jours)

**Style** :
- Badge "Nouveau" : existant, à conserver
- Badge "Actif" : style similaire mais couleur différente (vert discret ou bleu)
- Date relative : texte très petit (0.7rem), gris clair, positionné sous le nom ou dans un coin discret

**Pourquoi** : Les utilisateurs veulent contacter des profils actifs, pas des comptes abandonnés. Signal de qualité et d'engagement.

**Données nécessaires** : 
- `last_login` ou métadonnée équivalente (à créer si absente)
- `profile_updated` (date de dernière modification du profil)
- Logique : si dernière activité < 30 jours = "Actif"

---

### 3. Lecture claire de l'intention du profil

**Rôle** : Distinguer immédiatement offre vs recherche. Éviter les contacts inappropriés.

**Où** : Sur la card, visible sans ambiguïté.

**Implémentation** :
- Indicateur "Propose" / "Recherche" (déjà présent mais peut être amélioré)
- Ajouter un état "Ouvert" si le profil accepte les deux (offres et recherches)
- Utiliser une icône discrète en complément du texte :
  - "Propose" : icône flèche sortante ou check
  - "Recherche" : icône flèche entrante ou recherche
- Position : à côté du nom (actuel) ou en haut de la card comme micro-badge

**Style** :
- Texte en minuscules, très petit (0.7rem)
- Couleur différente selon le type (beige pour offre, bleu pour recherche)
- Icône SVG 12px, même couleur que le texte

**Pourquoi** : Économise du temps. Un beatmaker qui cherche un chanteur ne veut pas être contacté par quelqu'un qui cherche un beatmaker.

---

### 4. Micro-curation des résultats

**Rôle** : Mettre en avant certains profils pour guider la découverte et signaler la qualité.

**Où** : Avant ou au début de la grille, selon le type de curation.

**Implémentation** :

**Option A — Section "À découvrir" (recommandé)** :
- Petite section avant la grille principale (si > 5 résultats)
- Titre discret : "À découvrir" ou "Profils récents"
- Afficher 3-4 profils récents (créés dans les 14 derniers jours) en format horizontal compact
- Cards plus petites (hauteur réduite), style identique mais format condensé
- Lien "Voir tous" vers la grille complète

**Option B — Badge de curation dans la grille** :
- Badge discret "Sélection" ou "Récent" sur certains profils dans la grille
- Critères : profils récents, actifs, avec photo et bio complète
- Style : badge très discret, couleur différente de "Nouveau" et "Actif"

**Option C — Tri par défaut intelligent** :
- Ordre par défaut : profils actifs d'abord, puis récents, puis autres
- Pas de section séparée, juste un meilleur ordre
- Indicateur discret "Trié par pertinence" sous le compteur

**Recommandation** : Option C (tri intelligent) + Option B (badge discret) pour ne pas alourdir.

**Style** : Badge "Sélection" : style similaire aux autres badges, couleur or/beige pour distinction.

**Pourquoi** : Guide la découverte sans être intrusif. Met en avant la qualité sans créer de hiérarchie artificielle.

---

## Améliorations légères sur les cards

### 5. Amélioration de la hiérarchie visuelle dans les cards

**Rôle** : Rendre la lecture plus rapide, mettre en avant l'essentiel.

**Où** : Dans la structure de chaque card.

**Implémentation** :
- Nom : taille actuelle OK, mais ajouter un léger espacement après
- Intention (Propose/Recherche) : positionner plus proche du nom, alignement horizontal amélioré
- Ville : conserver l'icône, mais réduire légèrement la taille du texte
- Bio : ajouter un léger espacement avant, peut-être réduire d'une ligne (2 lignes au lieu de 3)
- Tags : espacement avant augmenté, bordure supérieure plus visible

**Style** : Ajustements d'espacement uniquement, pas de changement visuel majeur.

**Pourquoi** : Améliore la scanabilité sans modifier le design.

---

### 6. Tags fonctionnels et exploitables

**Rôle** : Les tags doivent servir la découverte, pas juste décorer.

**Où** : Dans la section tags de chaque card.

**Implémentation** :
- Tags cliquables (déjà fait) : conserver
- Ajouter un indicateur visuel au survol : légère élévation ou changement de couleur
- Si un tag correspond au filtre actif : style distinct (bordure plus épaisse, couleur accent)
- Limiter à 3 tags visibles + compteur "+X" (déjà fait)
- Optionnel : au clic sur un tag, afficher un tooltip ou feedback visuel discret

**Style** : 
- Hover : transform translateY(-1px) + border-color plus visible
- Tag actif : border 2px au lieu de 1px, couleur primaire

**Pourquoi** : Les tags deviennent des outils de navigation, pas juste de l'information statique.

---

### 7. États vides et limites améliorés

**Rôle** : Transformer un échec de recherche en opportunité, guider l'utilisateur.

**Où** : Section "Aucun résultat".

**Implémentation** :
- Message contextuel (déjà amélioré) : conserver
- Ajouter des suggestions concrètes :
  - Si recherche textuelle : "Essaie avec un terme plus court ou explore par catégorie"
  - Si filtres combinés : "Assouplis tes critères ou [lien] vois tous les profils"
  - Si aucun profil sur la plateforme : message différent, plus éditorial
- Optionnel : afficher 3-4 profils "suggestions" (proches des critères mais ne correspondant pas exactement)

**Style** : Conserver le style actuel, peut-être ajouter un espacement avant les suggestions.

**Pourquoi** : Réduit la frustration, guide vers une action utile.

---

## Améliorations optionnelles (si temps/budget)

### 8. Indicateur de disponibilité

**Rôle** : Signaler si un profil est ouvert aux contacts en ce moment.

**Où** : Sur la card, très discret.

**Implémentation** :
- Badge "Disponible" si dernière activité < 7 jours ET profil mis à jour récemment
- Style : très discret, peut combiner avec "Actif"
- Optionnel : système de statut manuel (si l'utilisateur peut définir "disponible" / "occupé")

**Pourquoi** : Augmente le taux de réponse, mais nécessite des données supplémentaires.

---

### 9. Métriques discrètes de crédibilité

**Rôle** : Signaler la qualité d'un profil sans être voyant.

**Où** : Sur la card, très discret.

**Implémentation** :
- Si profil complet (photo + bio + tags) : petit indicateur "Profil complet"
- Si profil vérifié (si système de vérification existe) : badge "Vérifié"
- Afficher le nombre de productions si > 0 (format : "3 productions")

**Style** : Très discret, texte petit, gris clair.

**Pourquoi** : Signale la qualité sans créer de hiérarchie artificielle. À implémenter seulement si ces données existent.

---

## Priorisation d'implémentation

**Phase 1 — Impact immédiat** :
1. Bloc de contexte éditorial (obligatoire)
2. Signal d'activité amélioré (obligatoire)
3. Micro-curation via tri intelligent (obligatoire)

**Phase 2 — Raffinements** :
4. Amélioration hiérarchie visuelle cards
5. Tags fonctionnels (déjà partiellement fait, à finaliser)
6. États vides améliorés (déjà fait, à valider)

**Phase 3 — Optionnel** :
7. Indicateur de disponibilité
8. Métriques de crédibilité

---

## Notes techniques

- Toutes les améliorations utilisent les données existantes ou des métadonnées simples à ajouter
- Aucune refonte structurelle nécessaire
- Styles cohérents avec la palette existante (#1A2332, beige, bleu urbain)
- JavaScript minimal (gestion des tags déjà en place)
- Pas de dépendances externes

---

## Principes respectés

- Style sobre et contemporain
- Chaque ajout justifié par l'usage
- Pas de décoration inutile
- Références implicites aux plateformes SaaS professionnelles
- Ton naturel, pas de jargon marketing
- Pensée fonction, pas décoration

---

*Audit réalisé — Prêt pour implémentation*

