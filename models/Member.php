<?php
class Member {
    private $conn;
    private $table = 'member';

    public $id;
    public $name;
    public $phone_number;
    public $number;
    public $entry_code;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT id, name, phone_number FROM ' . $this->table . ' ORDER BY name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_one() {
        $query = 'SELECT id, name, phone_number FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->name = $row['name'];
            $this->phone_number = $row['phone_number'];
        }
    }
} 