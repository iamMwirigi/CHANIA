<?php
class Sms {
    private $conn;
    private $table = 'sms_outbox';

    public $id;
    public $member_id;
    public $phone_number;
    public $message;
    public $cost;
    public $status;
    public $date_sent;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log_sms() {
        $query = 'INSERT INTO ' . $this->table . ' SET member_id = :member_id, phone_number = :phone_number, message = :message, cost = :cost, status = :status';
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind data
        $stmt->bindParam(':member_id', $this->member_id);
        $stmt->bindParam(':phone_number', $this->phone_number);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':cost', $this->cost);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read_outbox() {
        $query = 'SELECT id, phone_number, message, cost, status, date_sent FROM ' . $this->table . ' ORDER BY date_sent DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
} 