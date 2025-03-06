<?php

class Auth
{
    private $db;
    private $session;


    public function __construct(Connexion $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    public function getUserAuth(array $roles): ?User
    {
        if ($this->session->exist("user")) {
            $user = $this->session->get("user");
            $id = $user->id;
            $role = $user->role;
            if ($this->checkUser($id) && in_array($role, $roles)) {
                return $user;
            }
        }
        header("location:index.php");
        exit();
        return null;
    }


    public function checkUser($id): bool
    {
        $res = $this->db->pdo->query("Select id from user where id='$id'")->fetch();

        return ($res) ? true : false;
    }

    public function getUser(string $nom, string $password): ?User
    {
        $user = $this->db->pdo->query("Select * from user where nom='$nom' and password='$password'")->fetchObject("User");
        $this->session->set("user", $user);
        return $user;
    }
}
