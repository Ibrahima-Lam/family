# Proposition : nouveau schéma et amélioration du projet

## 1. Diagnostic du schéma actuel

Le modèle actuel est volontairement minimal, mais il atteint ses limites :

- `etre(idPersonne, idParent)` ne gère **qu’un seul parent** par personne. Impossible de distinguer père/mère, parents adoptifs, ou de stocker deux parents.
- `personne.generation` est **dénormalisé** : calculé dans `ApiController` (`parent.generation + 1`), il devient faux dès qu’on déplace une branche, et 30 générations sont pré-créées dont la plupart sont vides.
- `nomPersonne` mélange prénom, filiation et surnoms (`Hamet teguela/Diadji teguela`, `dit`, `/`, `\`) → recherche et dédoublonnage difficiles.
- `posseder` + `supplement` forment un **EAV** complexe pour 5 lignes : adresse, téléphone, surnom et date de naissance sont mélangés.
- `user.password` est en **clair** (`lam` / `amadou`), aucun email, aucun contrôle d’accès réel.
- Aucune contrainte `CHECK`, aucun index hors clés primaires, `PRAGMA foreign_keys` est **désactivé** par défaut, pas de `created_at` / `updated_at`.
- `addPersonne` insère `personne` puis `etre` **sans transaction** (`app/Models/Personne.php:106`) → risque de personne orpheline.
- Les jointures `left natural join etre` sont fragiles et dupliquent parfois les lignes (`app/Models/Personne.php:14`).
- La suppression ne nettoie pas `posseder` et le lien parent-enfant est manipulé par `UPDATE` global sur `etre` (`app/Models/Personne.php:128`).

## 2. Nouveau schéma proposé (SQLite / portable SQL)

```sql
PRAGMA foreign_keys = ON;

CREATE TABLE personne (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    legacy_id       INTEGER UNIQUE,              -- ancien idPersonne
    nom_complet     TEXT NOT NULL,               -- source de vérité, ex. "Hamet Téguela"
    nom_normalise   TEXT NOT NULL,               -- minuscules sans accents, pour la recherche
    prenom          TEXT,
    nom_famille     TEXT,
    sexe            TEXT NOT NULL DEFAULT 'U'
                    CHECK (sexe IN ('M','F','U')),
    date_naissance  TEXT,                        -- ISO 8601
    date_deces      TEXT,
    notes           TEXT,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT
);
CREATE INDEX idx_personne_nom ON personne(nom_normalise);
CREATE INDEX idx_personne_legacy ON personne(legacy_id);

CREATE TABLE alias (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    personne_id INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    type        TEXT NOT NULL CHECK (type IN ('dit','surnom','orthographe','nom_usage')),
    valeur      TEXT NOT NULL,
    UNIQUE (personne_id, type, valeur)
);

CREATE TABLE relation_parentale (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    enfant_id   INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    parent_id   INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    role        TEXT NOT NULL CHECK (role IN ('pere','mere','adoptif','tuteur')),
    certitude   TEXT NOT NULL DEFAULT 'confirmee'
                CHECK (certitude IN ('confirmee','probable','a_verifier')),
    source_id   INTEGER REFERENCES source(id) ON DELETE SET NULL,
    UNIQUE (enfant_id, parent_id),
    CHECK (enfant_id <> parent_id)
);
CREATE INDEX idx_relation_enfant ON relation_parentale(enfant_id);
CREATE INDEX idx_relation_parent ON relation_parentale(parent_id);

CREATE TABLE union_familiale (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    type        TEXT NOT NULL DEFAULT 'mariage'
                CHECK (type IN ('mariage','union','concubinage','divorce')),
    date_debut  TEXT,
    date_fin    TEXT,
    lieu_id     INTEGER REFERENCES lieu(id) ON DELETE SET NULL,
    notes       TEXT
);

CREATE TABLE union_membre (
    union_id    INTEGER NOT NULL REFERENCES union_familiale(id) ON DELETE CASCADE,
    personne_id INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    role        TEXT NOT NULL CHECK (role IN ('conjoint','conjointe','partenaire')),
    PRIMARY KEY (union_id, personne_id)
);

CREATE TABLE union_enfant (
    union_id  INTEGER NOT NULL REFERENCES union_familiale(id) ON DELETE CASCADE,
    enfant_id INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    PRIMARY KEY (union_id, enfant_id)
);

CREATE TABLE lieu (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    nom       TEXT NOT NULL,
    region    TEXT,
    pays      TEXT,
    latitude  REAL,
    longitude REAL
);

CREATE TABLE evenement (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    type        TEXT NOT NULL
                CHECK (type IN ('naissance','deces','mariage','divorce','residence','profession','autre')),
    date_debut  TEXT,
    date_fin    TEXT,
    lieu_id     INTEGER REFERENCES lieu(id) ON DELETE SET NULL,
    description TEXT
);

CREATE TABLE personne_evenement (
    personne_id  INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    evenement_id INTEGER NOT NULL REFERENCES evenement(id) ON DELETE CASCADE,
    role         TEXT,
    PRIMARY KEY (personne_id, evenement_id)
);

CREATE TABLE source (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    titre     TEXT NOT NULL,
    type      TEXT,                              -- oral, acte, recensement, web...
    reference TEXT,
    url       TEXT,
    fiabilite INTEGER CHECK (fiabilite BETWEEN 1 AND 5)
);

CREATE TABLE media (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    chemin       TEXT NOT NULL,
    type         TEXT,
    legende      TEXT,
    personne_id  INTEGER REFERENCES personne(id) ON DELETE SET NULL,
    evenement_id INTEGER REFERENCES evenement(id) ON DELETE SET NULL
);

CREATE TABLE coordonnee (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    personne_id INTEGER NOT NULL REFERENCES personne(id) ON DELETE CASCADE,
    type        TEXT NOT NULL CHECK (type IN ('adresse','telephone','email','autre')),
    valeur      TEXT NOT NULL
);

CREATE TABLE utilisateur (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    email         TEXT NOT NULL UNIQUE,
    nom           TEXT NOT NULL,
    password_hash TEXT NOT NULL,                 -- password_hash(..., PASSWORD_ARGON2ID)
    role          TEXT NOT NULL DEFAULT 'lecteur'
                  CHECK (role IN ('admin','editeur','lecteur')),
    actif         INTEGER NOT NULL DEFAULT 1,
    created_at    TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE journal_audit (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER REFERENCES utilisateur(id) ON DELETE SET NULL,
    action         TEXT NOT NULL,                -- create/update/delete
    table_cible    TEXT NOT NULL,
    cible_id       INTEGER,
    avant          TEXT,
    apres          TEXT,
    created_at     TEXT NOT NULL DEFAULT (datetime('now'))
);
```

La génération ne doit plus être stockée. On la dérive :

```sql
CREATE VIEW v_personne_generation AS
WITH RECURSIVE gen(id, generation) AS (
    SELECT p.id, 1
    FROM personne p
    WHERE NOT EXISTS (
        SELECT 1 FROM relation_parentale r
        WHERE r.enfant_id = p.id AND r.role IN ('pere','mere')
    )
    UNION ALL
    SELECT r.enfant_id, g.generation + 1
    FROM gen g
    JOIN relation_parentale r ON r.parent_id = g.id
    WHERE r.role IN ('pere','mere')
    LIMIT 100000
)
SELECT id, MAX(generation) AS generation
FROM gen
GROUP BY id;
```

> Prévoir une validation anti-cycle à l’écriture (un parent ne peut pas être son propre descendant) et un `LIMIT` de sécurité.

## 3. Migration depuis l’existant

| Ancien | Nouveau |
|---|---|
| `personne.idPersonne` | `personne.legacy_id` + nouvel `id` |
| `personne.nomPersonne` | `nom_complet` + `nom_normalise` ; surnoms extraits vers `alias` |
| `personne.sexe` | `sexe` avec `CHECK` |
| `personne.generation` | supprimé, remplacé par `v_personne_generation` |
| `etre.idPersonne/idParent` | `relation_parentale(enfant_id, parent_id, role = sexe du parent)` |
| `generation` | supprimée (ou conservée temporairement comme cache) |
| `supplement` + `posseder` | `alias` (surnom), `coordonnee` (adresse/téléphone), `personne.date_naissance` |
| `user` | `utilisateur` avec `password_hash` + email |

Prévoir un script d’import **idempotent** basé sur `legacy_id`, avec `--dry-run` et rapport de conflits, réutilisant la logique Python de `tools/`.

## 4. Améliorations applicatives prioritaires

### Sécurité

- Remplacer le routage dynamique `?p=Classe/methode` par un routeur à **whitelist** (`public/index.php:12` permet d’instancier n’importe quelle classe).
- Ajouter une authentification réelle (`password_hash` / `password_verify`), des rôles, `session_regenerate_id`, cookies `HttpOnly` / `SameSite`, HTTPS.
- CSRF sur **toutes** les mutations ; suppression uniquement en POST/DELETE (actuellement `delete` accepte un id en GET).
- Ne jamais afficher `$e->getMessage()` en production (`public/index.php:24`).
- Échapper systématiquement les sorties : `src/Altoperson.php` et `src/Arbre.php` concatènent des noms non échappés.

### Architecture / données

- Activer `PRAGMA foreign_keys = ON`, `journal_mode = WAL`, `busy_timeout`.
- Encapsuler `add` / `edit` / `delete` dans des **transactions**.
- Séparer modèles et requêtes : créer des repositories (`PersonneRepository`, `RelationRepository`) et arrêter les `SELECT *` / `natural join`.
- Remplacer le chargement complet en mémoire de `Src\Arbre` et `Src\Altoperson` par du SQL récursif.
- Ajouter un système de migrations SQL versionnées.
- Corriger les bugs existants :
  - `$parent["nom"]` sur des objets (`src/Altoperson.php:28`) ;
  - `"<dt>" . $dt ?? null` (précédence des opérateurs) ;
  - ternaire incomplet dans `Html::table` ;
  - `new Altoperson(45)` codé en dur (`app/Controllers/PersonneController.php:47`) ;
  - `Lien::getText` compare `ucfirst($genre)` à `"F"`.

### Qualité / tests

- PHPUnit : tests unitaires des liens de parenté, tests d’intégration SQLite en mémoire.
- PHPStan niveau 6, PHP-CS-Fixer, GitHub Actions.
- Remplacer `tests/smoke.php` (vérifie seulement l’autoload) par de vrais tests fonctionnels.

### Fonctionnalités

- Recherche accent-insensible via `nom_normalise` ou FTS5.
- Export **GEDCOM** et PDF du livre.
- API REST versionnée (`/api/v1/personnes`) avec codes HTTP corrects.
- Événements, lieux, sources et médias (le schéma ci-dessus les prévoit).
- Interface responsive / accessible, impression, liens profonds.

## 5. Plan recommandé

1. **P0** — Sauvegarde, migrations + import validé, activation des FK, transactions, correction des bugs bloquants.
2. **P1** — Authentification/rôles, routeur whitelist, CSRF complet, échappement, tests.
3. **P2** — Refactor repositories, suppression de `generation` / `posseder`, recherche normalisée.
4. **P3** — Événements/sources/médias, GEDCOM/PDF, CI et déploiement.
