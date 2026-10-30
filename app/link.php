<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php ob_start(); ?>
<?php require "../autoloader/autoload.php";

$conx = new connexion();
?>

<div class="container">
    <div class="row">
        <h2> les liens</h2>
        <fieldset>
            <legend>
                <h3>choisir des elements pour voir leur lien</h3>
            </legend>
            <form action="" method="POST">

                <select name="id1" id="">
                    <?= html::option($conx, "select * from personne", "idPersonne", "nomPersonne", $_POST["id1"] ?? null) ?>
                </select>
                <select name="id2" id="">
                    <?= html::option($conx, "select * from personne", "idPersonne", "nomPersonne", $_POST["id2"] ?? null) ?>
                </select>
                <input type="submit" name="envoi" value="Envoyer" class="btn btn-success">
            </form>
        </fieldset>

        <br>
        <br>
        <?php
        if (isset($_POST["envoi"])) {

            $id2 = $_POST["id1"];
            $id1 = $_POST["id2"];
        } else goto end;

        if ($id1 == $id2) {
            echo "vous avez choisi la meme personne";
            goto end;
        }
        $conx = new connexion();
        $person = new Altoperson($conx, 45);

        $links = $person->getLink($id1, $id2);
        $parents1 = array_reverse($links->getFirstParents());
        $parents2 = array_reverse($links->getSecondParents());


        $element1 = $person->getElement($id1);
        $element2 = $person->getElement($id2);

        ?>
        <fieldset>
            <legend>
                <h3>Lien</h3>
            </legend>

            <P>
                <b><u><?= $element1["nom"] ?></u></b> est le/la <mark><?= $links->getDifference($id1) ?></mark> de <strong><i><?= $element2["nom"] ?></i></strong>
            </P>
            <p>
                <b><u><?= $element2["nom"] ?></u></b> est le/la <mark> <?= $links->getDifference($id2) ?></mark> de <strong><i><?= $element1["nom"] ?></i></strong>
            </p>
        </fieldset>
        <fieldset>
            <legend>

                <h3>La Liaison</h3>
            </legend>
            <?php if ($links->isDirect()) : ?>
                <?php $child = $links->getChild(); ?>
                <?php foreach ($parents1 as $key => $value) : ?>

                    <?php if ($key == 0) : ?>
                        <strong><i><u><?= $value["nom"] ?></u></i></strong><span>&lt;=</span>
                    <?php else : ?>
                        <span><?= $value["nom"] ?> &lt;=</span>
                    <?php endif ?>

                <?php endforeach ?>
                <strong><?= $child["nom"] ?></strong>

            <?php else : ?>

                <?php foreach ($parents1 as $key => $value) : ?>

                    <?php if ($key == 0) : ?>
                        <strong><i><u><?= $value["nom"] ?></u></i></strong><span>&lt;=</span>
                    <?php else : ?>
                        <span><?= $value["nom"] ?> &lt;=</span>
                    <?php endif ?>

                <?php endforeach ?>
                <strong><?= $element1["nom"] ?></strong>

                <br>
                <br>

                <?php foreach ($parents2 as $key => $value) : ?>

                    <?php if ($key == 0) : ?>
                        <strong><i><u><?= $value["nom"] ?></u></i></strong><span>&lt;=</span>
                    <?php else : ?>
                        <span><?= $value["nom"] ?> &lt;=</span>
                    <?php endif ?>

                <?php endforeach ?>
                <strong><?= $element2["nom"] ?></strong>
            <?php endif ?>
        </fieldset>
    </div>

</div>

<?php
end:
$title = "les Liens ";
$content = ob_get_clean();
require "layout.php";
?>