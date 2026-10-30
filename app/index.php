<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php
ob_start();
?>

<div class="container">
    <div class="row">
        <h2>Bienvenue sur ce site dédié à l'apprentissage des pédigrés de la famille</h2>
        <div class="text">
            <p>Ce site présente un moyen très avancé pour lire les données ou s'informer d'une maniéré très efficace, rapide et compréhensible les éléments d'une famille. Ceci dit que grâce à la nouvelle technologie et l'informatique nous avons pu mettre en place un système idéal pour lire les données concernant les lignées, la liaison, les pedigrees et descendances présentés biologiquement d'une famille afin de pouvoir repérer facilement les éléments qui s'y retrouvent. Ce système est assez dynamique dans la mesure où on veut comprendre et apprendre tous le passé (les ancêtres) et le présent (les descendants) de toute une famille, le fonctionnement c'est expliqué dans les paragraphes suivantes.

            </p>

            <p>
                Nous disposons une barre de navigation pour accéder à chacune de nos pages dans l'application. Pour cela vous allez dans la rubrique concerné pour la page que vous voulez accéder en le cliquant. Cette dernière est une technique très utilisés dans le Web pour naviguer dans les différentes pages rapidement. Si vous utilisez un téléphone cliquez sur les trois point en haut à droite pour ouvrir la barre des menus.
            </p>
            <p>
                Dans la barre des menus nous disposons premièrement <a href="sommaire.php"><strong> le sommaire</strong></a> qui représente la rubrique de la liste de tous les éléments de la famille, on peut y passer des recherches, des filtres et des navigations. Ensuite on a la rubrique <a href="personne.php"><strong> personne</strong></a> pour s'informer à une personne soit en sélectionnant une personne à la liste
                ou en cliquant sur le nom des personnes qui sont présent dans la lignés de la personne active.
                encore aussi on a la rubrique <a href="arbre.php"><strong> arbre</strong></a> pour voir l'arborescence de la famille. Puis la rubrique <a href="link.php"><strong> lien</strong></a> Pour apprendre la liaison de deux personnes, Ils faut sélectionner les personnes dans les listes qui sont présentes là bas puis cliquer sur <q>envoyer</q> pour avoir le résultat. Enfin pour la rubrique <a href="book.php"><strong> book</strong></a>
                autrement dit le livre qui résume tout le travail à faire dans une seule page, elle est conçu en plusieurs raisons parmi d'elle il y'a pouvoir Imprimer tous une seul fois et utiliser comme un livre au format <strong><q>PDF</q></strong> qui respecte le format et fonctionnement de l'application et il y'a aussi pouvoir naviguer en cas de faible connexion.
            </p>


        </div>


    </div>
</div>

<?php
$title = "Accueil";
$content = ob_get_clean();
require "layout.php";
?>