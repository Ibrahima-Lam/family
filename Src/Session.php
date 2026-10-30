<?php

class Session
{
    public function __construct()
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }


    public function get(string $id): mixed
    {
        return $_SESSION[$id];
    }

    public function set(string $id, $value): void
    {
        $_SESSION[$id] = $value;
    }

    public function delete(): void
    {
        session_destroy();
    }

    public function exist(string $id): bool
    {
        return isset($_SESSION[$id]);
    }
}
