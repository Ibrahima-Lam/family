<?php

class App
{
    private static $auth;

    public static function getAuth(): Auth
    {
        if (!self::$auth) {
            $conx=new Connexion();
            $session=new Session();
            self::$auth=new Auth($conx,$session);
        }
        return self::$auth;
    }
}
