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
        $res = $this->db->fetchOne("SELECT id FROM user WHERE id = :id", ['id' => $id]);
        return (bool) $res;
    }

    public function getUser(string $nom, string $password): ?User
    {
        $row = $this->db->fetchOne("SELECT * FROM user WHERE nom = :nom", ['nom' => $nom]);
        if ($row && password_verify($password, (string) $row['password'])) {
            $user = new User();
            $user->id = $row['id'];
            $user->nom = $row['nom'];
            $user->password = $row['password'];
            $user->role = $row['role'];
            $this->session->set("user", $user);
            return $user;
        }
        return null;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
