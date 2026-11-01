<?php
$title = 'Ajouter une personne';
?>

<script src="assets/js/ajout.js" defer></script>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Nouvelle entrée</span>
            <h1>Ajouter des personnes</h1>
            <p>Choisissez une fiche unique, ou préparez un lot dans un tableau de formulaires.</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-success" data-open-single>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Formulaire rapide
            </button>
        </div>
    </div>

    <div class="form-layout">
        <form action="" method="post" class="form panel" id="form-ajout" data-multi-form data-mode="table">
            <?= csrf_field() ?>

            <div class="form-intro">
                <div>
                    <span class="eyebrow">Fiches à créer</span>
                    <h2>Tableau de fiches</h2>
                    <p>Complétez une ligne par personne, jusqu’à 10 fiches. Le parent est facultatif : laissez vide pour créer une racine.</p>
                </div>
                <span class="badge badge-muted" data-row-count>1 fiche</span>
            </div>

            <noscript>
                <p class="alert alert-danger">L’ajout de plusieurs fiches nécessite JavaScript. Activez-le pour continuer.</p>
            </noscript>

            <p class="alert alert-danger" data-form-error role="alert" hidden></p>

            <div class="form-table-head" data-table-head aria-hidden="true">
                <span>#</span>
                <span>Nom</span>
                <span>Surnom</span>
                <span>Parent</span>
                <span>Sexe</span>
                <span></span>
            </div>

            <div class="person-rows" data-rows></div>

            <div class="form-actions">
                <a class="btn btn-default" href="?p=personne">Annuler</a>
                <button type="button" class="btn btn-default" data-add-row>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Ajouter une fiche
                </button>
                <button type="submit" class="btn btn-success" data-submit>
                    <span data-submit-label>Créer les fiches</span>
                </button>
            </div>
        </form>

        <aside class="panel form-aside">
            <div>
                <span class="eyebrow">Aide à la saisie</span>
                <h2>Gagner du temps</h2>
            </div>

            <div class="form-aside-block">
                <div class="control">
                    <label for="gene">Filtrer les parents par génération</label>
                    <select name="generation" id="gene">
                        <option value="0">Toutes les générations</option>
                        <?= $gen ?>
                    </select>
                    <p class="field-help">Le filtre remplace les choix de parent sans effacer les fiches déjà commencées.</p>
                </div>

                <label class="check-row" for="apply-parent">
                    <input type="checkbox" id="apply-parent">
                    <span>Reporter le parent de la première fiche sur toutes les autres.</span>
                </label>
            </div>

            <p class="form-aside-note">Les noms déjà connus sont proposés automatiquement dans le champ Nom.</p>
        </aside>
    </div>
</div>

<datalist id="personnes-noms"></datalist>

<template id="person-row-template">
    <div class="person-row" data-row>
        <div class="person-row-head">
            <div>
                <strong>Fiche <span data-row-number>__INDEX__</span></strong>
                <span class="badge badge-muted">Nouvelle personne</span>
            </div>
            <button type="button" class="icon-button" data-remove-row aria-label="Supprimer cette fiche">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 7h12M9 7V5h6v2M8 7l1 13h6l1-13M10 11v5M14 11v5"/></svg>
            </button>
        </div>

        <div class="form-grid">
            <div class="control control-wide">
                <label for="nom-__INDEX__">Nom <span class="required-mark" aria-hidden="true">*</span></label>
                <input type="text" id="nom-__INDEX__" name="personnes[__INDEX__][nom]" data-field="nom" class="field" placeholder="Prénom et nom" autocomplete="off" list="personnes-noms" required>
                <p class="field-help">Utilisez le nom complet ou le nom d’usage principal.</p>
            </div>

            <div class="control">
                <label for="surnom-__INDEX__">Surnom <span class="badge badge-muted">facultatif</span></label>
                <input type="text" id="surnom-__INDEX__" name="personnes[__INDEX__][surnom]" data-field="surnom" class="field" placeholder="Autre nom d’usage" autocomplete="off">
            </div>

            <div class="control">
                <label for="parent-__INDEX__">Parent</label>
                <select id="parent-__INDEX__" name="personnes[__INDEX__][parent]" data-field="parent" class="field">
                    <option value="">Aucun parent (racine)</option>
                    <?= $html ?>
                </select>
            </div>

            <fieldset class="control fieldset-reset control-wide">
                <legend>Sexe <span class="required-mark" aria-hidden="true">*</span></legend>
                <div class="choice-grid">
                    <label class="choice-chip">
                        <input type="radio" name="personnes[__INDEX__][sexe]" value="M" checked>
                        <span>Masculin</span>
                    </label>
                    <label class="choice-chip">
                        <input type="radio" name="personnes[__INDEX__][sexe]" value="F">
                        <span>Féminin</span>
                    </label>
                </div>
            </fieldset>
        </div>
    </div>
</template>

<dialog id="single-person-dialog" class="dialog form-dialog" aria-labelledby="single-dialog-title">
    <div class="dialog-head">
        <strong id="single-dialog-title">Ajouter une personne</strong>
        <button type="button" class="icon-button" data-close-single aria-label="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div class="dialog-body">
        <form action="" method="post" class="form" id="single-person-form" data-single-form>
            <?= csrf_field() ?>
            <p class="alert alert-danger" data-single-error role="alert" hidden></p>

            <div class="form-grid">
                <div class="control control-wide">
                    <label for="single-nom">Nom <span class="required-mark" aria-hidden="true">*</span></label>
                    <input type="text" id="single-nom" name="nom" class="field" placeholder="Prénom et nom" autocomplete="off" list="personnes-noms" required autofocus>
                    <p class="field-help">Le nom complet ou le nom d’usage principal.</p>
                </div>

                <div class="control">
                    <label for="single-surnom">Surnom <span class="badge badge-muted">facultatif</span></label>
                    <input type="text" id="single-surnom" name="surnom" class="field" placeholder="Autre nom d’usage" autocomplete="off">
                </div>

                <div class="control">
                    <label for="single-parent">Parent</label>
                    <select id="single-parent" name="parent" class="field">
                        <option value="">Aucun parent (racine)</option>
                        <?= $html ?>
                    </select>
                </div>

                <fieldset class="control fieldset-reset control-wide">
                    <legend>Sexe <span class="required-mark" aria-hidden="true">*</span></legend>
                    <div class="choice-grid">
                        <label class="choice-chip">
                            <input type="radio" name="sexe" value="M" checked>
                            <span>Masculin</span>
                        </label>
                        <label class="choice-chip">
                            <input type="radio" name="sexe" value="F">
                            <span>Féminin</span>
                        </label>
                    </div>
                </fieldset>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-default" data-close-single>Annuler</button>
                <button type="submit" class="btn btn-success" data-single-submit>
                    <span data-single-submit-label>Créer la fiche</span>
                </button>
            </div>
        </form>
    </div>
</dialog>
