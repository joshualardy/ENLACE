# Audit UX — Page Découvrir
## ENLACE — Plateforme de mise en relation musicale

---

## 📊 Ce que la page raconte aujourd'hui

À un nouvel utilisateur, la page "Découvrir" transmet actuellement :

- **Une base de données fonctionnelle** : la structure est claire, les filtres opèrent correctement, mais l'expérience reste transactionnelle
- **Un manque de contexte** : aucun élément ne guide la découverte ou ne donne du sens à la liste présentée
- **Une absence de signaux d'activité** : rien n'indique que la plateforme est vivante, que les profils sont actifs, récents ou pertinents
- **Une lecture uniforme** : tous les profils se valent visuellement, aucune hiérarchie ne suggère où commencer
- **Des tags sous-exploités** : ils sont présents mais ne servent pas vraiment la découverte ou la compréhension rapide

**Résultat** : La page fonctionne mais ne raconte pas d'histoire. Elle liste sans guider, présente sans contextualiser.

---

## 🎯 Recommandations — Hiérarchisées par impact

### 1. **Remplacer le sous-titre par un contexte éditorial dynamique** 
**Impact : Élevé | Complexité : Faible**

**Intention produit** : Donner du sens à la page dès l'arrivée, surtout quand des filtres sont actifs. Remplacer le message générique par quelque chose qui contextualise réellement ce que l'utilisateur voit.

**Où** : Ligne 104, sous le titre "Découvrir"

**Ce que ça change** :
- Sans filtres : "Profils actifs sur ENLACE" ou simplement retirer le sous-titre (la page se suffit)
- Avec filtres actifs : "12 profils à Paris" ou "Beatmakers et producteurs disponibles" — le sous-titre reflète ce qui est affiché
- Après recherche : "Résultats pour 'jazz'" — confirme l'action de l'utilisateur

**Implémentation** : Logique conditionnelle PHP simple qui adapte le texte selon les filtres actifs.

---

### 2. **Améliorer l'état vide avec des suggestions contextuelles**
**Impact : Élevé | Complexité : Faible**

**Intention produit** : Transformer un échec de recherche en opportunité de découverte. L'état vide actuel est fonctionnel mais ne guide pas.

**Où** : Lignes 264-271, section `.decouvrir-no-results`

**Ce que ça change** :
- Au lieu de "Aucun profil trouvé. Essayez de modifier vos critères", proposer des actions concrètes :
  - Si recherche textuelle : "Aucun résultat pour '[terme]'. Essayez un autre terme ou explorez par ville ou talent."
  - Si filtres combinés : "Aucun profil ne correspond à ces critères. [Lien] Voir tous les profils disponibles" ou suggérer des alternatives proches
  - Si aucun profil sur la plateforme : Message différent, plus éditorial

**Implémentation** : Conditions PHP qui adaptent le message selon le type de recherche échouée.

---

### 3. **Rendre les tags cliquables et exploitables comme filtres secondaires**
**Impact : Moyen-Élevé | Complexité : Moyenne**

**Intention produit** : Les tags deviennent des portes d'entrée vers d'autres profils similaires. Ils passent de décoratifs à fonctionnels, sans alourdir l'interface.

**Où** : Lignes 234-250, section `.decouvrir-user-card-tags`

**Ce que ça change** :
- Au survol, le tag indique qu'il est cliquable (légère variation visuelle)
- Clic sur un tag = application du filtre correspondant + scroll vers le haut de la grille
- Les tags déjà filtrés sont visuellement distincts (légèrement plus opaques ou avec une bordure différente)
- Pas de changement visuel majeur, juste une interaction discrète

**Implémentation** : JavaScript qui intercepte le clic, met à jour l'URL avec le filtre, recharge la page. CSS pour les états hover et actif.

---

### 4. **Ajouter un indicateur de fraîcheur discret sur les profils récents**
**Impact : Moyen | Complexité : Faible**

**Intention produit** : Signal d'activité sans être intrusif. Indique que la plateforme est vivante et que certains profils sont nouveaux.

**Où** : Dans la card utilisateur, positionné discrètement (coin supérieur de l'image ou près du nom)

**Ce que ça change** :
- Profils créés dans les 7 derniers jours : petit indicateur "Nouveau" ou simplement une bordure plus lumineuse
- Profils mis à jour récemment (si métadonnée disponible) : indicateur "Mis à jour" encore plus discret
- Style minimal : texte petit, couleur de la palette existante, pas de badge voyant

**Implémentation** : Comparaison de `user_registered` avec date actuelle, affichage conditionnel d'un élément HTML/CSS.

---

### 5. **Transformer le compteur de résultats en élément informatif**
**Impact : Moyen | Complexité : Faible**

**Intention produit** : Le compteur actuel est fonctionnel mais froid. L'enrichir légèrement pour qu'il raconte quelque chose.

**Où** : Lignes 186-188, section `.decouvrir-results-count`

**Ce que ça change** :
- Au lieu de "12 profils trouvés" :
  - "12 profils" (plus sobre)
  - Ou "12 profils à découvrir" si aucun filtre
  - "12 profils correspondant à tes critères" si filtres actifs
  - "1 profil" au singulier (actuellement géré mais peut être amélioré)
- Style : légèrement plus discret, peut-être en italique ou avec une taille réduite

**Implémentation** : Logique conditionnelle PHP qui adapte le texte selon le contexte.

---

### 6. **Améliorer la hiérarchie visuelle dans les cards — distinguer l'intention (offre/recherche)**
**Impact : Moyen | Complexité : Faible**

**Intention produit** : Actuellement, la distinction visuelle existe (bordure colorée) mais n'est pas assez lisible. Rendre l'intention du profil plus évidente sans label agressif.

**Où** : Cards utilisateur, lignes 208-262

**Ce que ça change** :
- Ajouter un micro-indicateur textuel discret : "Propose" ou "Recherche" en très petit, positionné près du nom ou en haut de la card
- Ou utiliser une icône subtile (offre = flèche sortante, recherche = flèche entrante)
- Style : très discret, couleur de la palette, taille réduite, ne doit pas dominer

**Implémentation** : Affichage conditionnel basé sur `service_type`, style CSS minimal.

---

### 7. **Introduire une respiration éditoriale avant la grille (si résultats nombreux)**
**Impact : Faible-Moyen | Complexité : Faible**

**Intention produit** : Ajouter une pause visuelle et éditoriale avant de plonger dans la liste. Surtout utile quand il y a beaucoup de résultats.

**Où** : Entre la section filtres (ligne 189) et la grille (ligne 192)

**Ce que ça change** :
- Si plus de 10 résultats : afficher une ligne de séparation subtile ou un espacement légèrement augmenté
- Optionnel : un texte très court et sobre comme "Profils correspondants" (mais peut-être trop, à tester)
- L'objectif est surtout de créer une respiration, pas d'ajouter du contenu

**Implémentation** : Condition PHP simple, ajustement CSS de l'espacement.

---

### 8. **Améliorer la bio tronquée — indiquer qu'il y a plus à lire**
**Impact : Faible | Complexité : Faible**

**Intention produit** : Actuellement, la bio est tronquée à 20 mots sans indication. Ajouter un signal discret qu'il y a plus d'information.

**Où** : Ligne 232, dans `.decouvrir-user-card-bio`

**Ce que ça change** :
- Si la bio dépasse 20 mots : ajouter "..." à la fin (déjà géré par `wp_trim_words` mais peut être stylisé)
- Ou ajouter un indicateur visuel très discret (petite icône "lire plus" en fin de ligne, visible au survol)
- Style : très discret, ne doit pas alourdir

**Implémentation** : Vérification de la longueur de la bio, affichage conditionnel d'un indicateur CSS.

---

## 📋 Priorisation recommandée

**Phase 1 (Impact immédiat, faible effort)** :
1. Sous-titre contextuel dynamique
2. État vide amélioré
3. Compteur de résultats enrichi

**Phase 2 (Impact moyen, effort modéré)** :
4. Tags cliquables
5. Indicateur de fraîcheur

**Phase 3 (Raffinements)** :
6. Hiérarchie visuelle intention
7. Respiration éditoriale
8. Bio tronquée améliorée

---

## 🎨 Principes de design respectés

- ✅ Aucune refonte structurelle
- ✅ Pas de features gadgets
- ✅ Ton sobre et culturel
- ✅ Vocabulaire naturel, pas marketing
- ✅ Améliorations discrètes et intentionnelles
- ✅ Respect de la palette existante (#1A2332, beige, bleu urbain)
- ✅ Hiérarchie visuelle préservée

---

## 💡 Notes d'implémentation

- Toutes les suggestions utilisent les données déjà disponibles dans le template
- Aucune nouvelle feature backend n'est requise (sauf peut-être pour "mis à jour récemment" si cette métadonnée n'existe pas)
- Les modifications CSS sont minimales et s'intègrent dans le système existant
- Le JavaScript nécessaire est léger (gestion des clics sur tags)

---

*Audit réalisé le [date] — Prêt pour discussion équipe*

