<?php
spl_autoload_register(function ($class)  {
    $file="../modele/$class.php";
        if(file_exists($file))
        {
             require $file;
        }
});

spl_autoload_register(function ($class)  {
    $file="../src/$class.php";
        if(file_exists($file))
        {
             require $file;
        }
});
spl_autoload_register(function ($class)  {
    $file="../core/$class.php";
        if(file_exists($file))
        {
             require $file;
        }
});
