<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php ob_start(); ?>
<?php require "../autoloader/autoload.php"; ?>
<script src="../public/js/table.js" defer></script>
<?php
$conx = new Connexion();
$prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent";
$res = $conx->pdo->query("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
    left join personne d on(e.idParent=d.idPersonne)");
$res->setFetchMode(PDO::FETCH_CLASS, "Personne");
$tab = $res->fetchAll();

?>
<div class="container">
    <header class='row page-garde'>
        <h1>le livre de la famille</h1>
    </header>

    <div class="row page preface">
        <h2>Preface</h2>
        <p>Ceci est un livre permettant pour vous autant pour moi d'apprendre sur le passé de nos ancetre et le present de nous meme,<br>
            soit disant c'est un outil qui nous permet de nous connaitre nous-même. <br>

        </p>
        <p>
            De nombreuse familles sur cette planette possedent des informations sur les lignées de leur famille,<br>
            mais malheuresement avec cette occasion la plupart de ces personnes membres de ces familles n'en <br>
            connaissent pas beaucoups ou Parfois rien sur ces informations. Ces informations pour certaines <br> encore ne sont ni
            ecrites ni gardées en document, elles sont juste gardées en memoires par quelques personnes.

        </p>
        <p>De nous jours nous nous sommes reunit par plusieurs plates formes (des sites web,des reseau sociaux et plus) notament une qui trés populaire dans l'écchelle mondiale (WhatsAPP),<br>
            cette plate forme (réseau social) est utilisable de façon à y creer des groupes (<q>groupes de WhatsAPP</q>) pour se retrouver en masse. <br>
            cette derniere qui pour certaines personnes l'ont permis de se retrouver et se communiquer en famille. <br>
            <cite> Malgrés son efficacité elle nous permet pas de stocker des données en base de donnes de façon administrative,<br>
                Ceci dit qu'on peut s'informer qu'à partir des textos ou des fichiers(audio,photo,videos,pdf, fichier de texte...etc).</cite>
        </p>
        <p>
            Ce livre à pour principal objectif de jouer un role d'un <q>Adapter</q> avec les plates formes pour s'informer rapidement et efficacement. <br>
            il est concu de maniere <q> web site </q> afin d'y pouvoir naviguer rapidement rien qu'en cliquant sur les elements. <br>
            pour y voir plus clairement il se presente comme un site Web ou une application mobile (application pour les telephones), car pour <br>
            utiliser cette derniere vous faites que <em> <code> defiler</code> ,<code> cliquer</code> ou <code> scroller</code></em>.
            Ceci dit que le livre marche de la meme facons(<em> <code> defilement</code>,<code> clique</code> ou <code> scroll</code> </em>), <br>
        </p>
        <p>
        <blockquote>
            Le principe d'utilisation se presente comme suivant: <br>
            <em>
                Premierement chercher un element sur le sommaire puis cliquer sur cet element pour voir les liens qui lui correspondent (parent ,fresres et soeurs ou enfants) .</em>
            <em>
                Une fois arrivé sur l'element vous pouvez voir ce dont vous cherchiez,si vous voulez chercher encore plus sur ses descendances
                ,ceux de ses freres ou ses parent
                alors cliquez sur le nom de l'element parent ,frere,soeur ou enfant.
            </em>
        

        </blockquote>
        </p>
    </div>


    <div class="row page">
        <h2>Le Sommaire </h2>
        <table class='striped'>
            <thead>
                <tr>
                    <th class="sorters"><data value='id'> ID</data></th>
                    <th class="sorters"><data value='nom'> NOM</data></th>
                    <th class="sorters"><data value='nomGeneration'> GENERATION</data></th>
                    <th class="sorters"><data value='sexe'> SEXE</data></th>
                    <th class="sorters"><data value='parent'> PARENT</data></th>
                </tr>
            </thead>
            <tbody id="contains">
                <?php foreach ($tab as $value) : ?>
                    <tr>
                        <td><?= $value->idPersonne ?></td>
                        <td><a href="#<?= $value->idPersonne ?>"><?= ucwords($value->nomPersonne) ?></a></td>
                        <td><?= $value->nomGeneration ?></td>
                        <td><?= $value->sexe ?></td>
                        <td><a href="#<?= $value->idPersonne ?>"><?= ucwords($value->parent) ?></a></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($tab as $value) : ?>
        <page>
            <div class="row elements">
                <h2 id="<?= $value->idPersonne ?>"><?= ucwords($value->nomPersonne) ?></h2>
                <?php
                $id = $value->idPersonne;
                $element = new Personnage($conx, $id);
                Html::$target = $id;
                ?>

                <fieldset>
                    <legend>
                        <h3>Le Parent</h3>
                    </legend>

                    <?= Html::table($element->parent, true) ?>
                </fieldset>
                <fieldset>
                    <legend>
                        <h3>les freres et soeurs</h3>
                    </legend>

                    <?= Html::table($element->freres, true) ?>
                </fieldset>

                <fieldset>
                    <legend>
                        <h3>Les enfants</h3>
                    </legend>

                    <?= html::table($element->enfants, true) ?>
                </fieldset>
                <fieldset>
                    <legend>
                        <h3> Autres Details</h3>
                    </legend>
                    <div class='content'>
                    <?= html::infos($element->possession) ?>
                </div>
                </fieldset>

            </div>
        </page>
    <?php endforeach ?>
    <div class="row page"><img src="../public/image/lam.jpg" alt="ibrahima" width="100px"></div>
</div>


<?php
$title = "le Livre";
$content = ob_get_clean();
require "layout.php";
?>
<style>
    .nav-bar {
        display: none;
    }
</style>

<script>
    document.addEventListener("dblclick", function() {
        const test = document.getElementsByClassName('nav-bar');
        if (test[0].style.display == "flex") {
            test[0].style.display = "none"
        } else {
            test[0].style.display = "flex"
        }

    })
</script>