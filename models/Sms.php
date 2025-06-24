<?php
class Sms {
    private $conn;
    private $table = 'sms';

    public $id;
    public $sent_from;
    public $sent_to;
    public $package_id;
    public $text_message;
    public $af_cost;
    public $sent_time;
    public $sent_date;
    public $sms_characters;
    public $sent_status;
    public $pages;
    public $page_cost;
    public $cost;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT
                id,
                sent_to,
                text_message,
                cost,
                sent_status,
                sent_date,
                sent_time
            FROM
                ' . $this->table . '
            ORDER BY
                sent_date DESC, sent_time DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET sent_to = :sent_to, text_message = :text_message, sent_date = :sent_date, sent_time = :sent_time, sent_status = :sent_status, cost = :cost';

        $stmt = $this->conn->prepare($query);

        $this->sent_to = htmlspecialchars(strip_tags($this->sent_to));
        $this->text_message = htmlspecialchars(strip_tags($this->text_message));
        $this->sent_date = htmlspecialchars(strip_tags($this->sent_date));
        $this->sent_time = htmlspecialchars(strip_tags($this->sent_time));
        $this->sent_status = htmlspecialchars(strip_tags($this->sent_status));
        $this->cost = htmlspecialchars(strip_tags($this->cost));

        $stmt->bindParam(':sent_to', $this->sent_to);
        $stmt->bindParam(':text_message', $this->text_message);
        $stmt->bindParam(':sent_date', $this->sent_date);
        $stmt->bindParam(':sent_time', $this->sent_time);
        $stmt->bindParam(':sent_status', $this->sent_status);
        $stmt->bindParam(':cost', $this->cost);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }
} 