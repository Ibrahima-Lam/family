<?php
$title = "Liens";
?>
<div class="container">
    <div class="row">
        <h2> Les Liens </h2>
        <fieldset>
            <legend>
                <h3>choisir des elements pour voir leur lien</h3>
            </legend>
            <form action="" method="POST">
                <select name="id1" id="">
                    <?= $options1 ?>
                </select>
                <select name="id2" id="">
                    <?= $options2 ?>
                </select>
                <input type="submit" name="envoi" value="Envoyer" class="btn btn-success">
            </form>
        </fieldset>

        <br>
        <br>
        <fieldset>
            <legend>
                <h3>Lien</h3>
            </legend>

            <?php if ($id1): ?>
                <P>
                    <b><u><?= e($element1->nomPersonne ?? '') ?></u></b> est le/la <mark><?= e($links->getDifference($id1)) ?></mark> de <strong><i><?= e($element2->nomPersonne ?? '') ?></i></strong>
                </P>
            <?php endif ?>

            <?php if ($id2): ?>
                <p>
                    <b><u><?= e($element2->nomPersonne ?? '') ?></u></b> est le/la <mark> <?= e($links->getDifference($id2)) ?></mark> de <strong><i><?= e($element1->nomPersonne ?? '') ?></i></strong>
                </p>
            <?php endif ?>
        </fieldset>
        <fieldset>
            <legend>

                <h3>La Liaison</h3>
            </legend>
            <?php if ($links->isDirect()) : ?>
                <?php $child = $links->getChild(); ?>
                <?php foreach ($parents1 as $key => $value) : ?>

                    <?php if ($key == 0) : ?>
                        <strong><i><u><?= e($value->nomPersonne ?? '') ?></u></i></strong><span>&lt;=</span>
                    <?php else : ?>
                        <span><?= e($value->nomPersonne ?? '') ?> &lt;=</span>
                    <?php endif ?>

                <?php endforeach ?>
                <strong><?= e($child->nomPersonne ?? '') ?></strong>

            <?php else : ?>

                <?php foreach ($parents1 as $key => $value) : ?>

                    <?php if ($key == 0) : ?>
                        <strong><i><u><?= e($value->nomPersonne ?? '') ?></u></i></strong><span>&lt;=</span>
                    <?php else : ?>
                        <span><?= e($value->nomPersonne ?? '') ?> &lt;=</span>
                    <?php endif ?>

                <?php endforeach ?>
                <strong><?= e($element1->nomPersonne ?? '') ?></strong>

                <br>
                <br>

                <?php foreach ($parents2 as $key => $value) : ?>

                    <?php if ($key == 0) : ?>
                        <strong><i><u><?= e($value->nomPersonne ?? '') ?></u></i></strong><span>&lt;=</span>
                    <?php else : ?>
                        <span><?= e($value->nomPersonne ?? '') ?> &lt;=</span>
                    <?php endif ?>

                <?php endforeach ?>
                <strong><?= e($element2->nomPersonne ?? '') ?></strong>
            <?php endif ?>
        </fieldset>
    </div>

</div>