<?php

class User
{
    private $conn;
    private $table_name = "_user_";

    public $id;
    public $username;
    public $password;
    public $name;
    public $stage;
    public $user_town;
    public $quota_start;
    public $quota_end;
    public $current_quota;
    public $delete_status;
    public $prefix;
    public $printer_name;


    public function __construct($db)
    {
        $this->conn = $db;
    }

    function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE delete_status = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? AND delete_status = 0 LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->name = $row['name'];
            $this->stage = $row['stage'];
            $this->user_town = $row['user_town'];
            $this->quota_start = $row['quota_start'];
            $this->quota_end = $row['quota_end'];
            $this->current_quota = $row['current_quota'];
            $this->delete_status = $row['delete_status'];
            $this->prefix = $row['prefix'];
            $this->printer_name = $row['printer_name'];
            return true;
        }
        return false;
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET
            username=:username, password=:password, name=:name, stage=:stage, user_town=:user_town, quota_start=:quota_start, quota_end=:quota_end, current_quota=:current_quota, prefix=:prefix, printer_name=:printer_name";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->stage = htmlspecialchars(strip_tags($this->stage));
        $this->user_town = htmlspecialchars(strip_tags($this->user_town));
        $this->quota_start = htmlspecialchars(strip_tags($this->quota_start));
        $this->quota_end = htmlspecialchars(strip_tags($this->quota_end));
        $this->current_quota = htmlspecialchars(strip_tags($this->current_quota));
        $this->prefix = htmlspecialchars(strip_tags($this->prefix));
        $this->printer_name = htmlspecialchars(strip_tags($this->printer_name));

        $stmt->bindParam(":username", $this->username);
        // Hashing the password before saving
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":stage", $this->stage);
        $stmt->bindParam(":user_town", $this->user_town);
        $stmt->bindParam(":quota_start", $this->quota_start);
        $stmt->bindParam(":quota_end", $this->quota_end);
        $stmt->bindParam(":current_quota", $this->current_quota);
        $stmt->bindParam(":prefix", $this->prefix);
        $stmt->bindParam(":printer_name", $this->printer_name);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . " SET
            username = :username,
            name = :name,
            stage = :stage,
            user_town = :user_town,
            quota_start = :quota_start,
            quota_end = :quota_end,
            current_quota = :current_quota,
            prefix = :prefix,
            printer_name = :printer_name
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->stage = htmlspecialchars(strip_tags($this->stage));
        $this->user_town = htmlspecialchars(strip_tags($this->user_town));
        $this->quota_start = htmlspecialchars(strip_tags($this->quota_start));
        $this->quota_end = htmlspecialchars(strip_tags($this->quota_end));
        $this->current_quota = htmlspecialchars(strip_tags($this->current_quota));
        $this->prefix = htmlspecialchars(strip_tags($this->prefix));
        $this->printer_name = htmlspecialchars(strip_tags($this->printer_name));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':stage', $this->stage);
        $stmt->bindParam(':user_town', $this->user_town);
        $stmt->bindParam(':quota_start', $this->quota_start);
        $stmt->bindParam(':quota_end', $this->quota_end);
        $stmt->bindParam(':current_quota', $this->current_quota);
        $stmt->bindParam(':prefix', $this->prefix);
        $stmt->bindParam(':printer_name', $this->printer_name);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    function delete()
    {
        $query = "UPDATE " . $this->table_name . " SET delete_status = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
} 