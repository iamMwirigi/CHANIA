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

    // Static helper to sanitize phone numbers
    public static function sanitizeNumber($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = '254' . substr($phone, 1);
        } elseif (strpos($phone, '7') === 0) {
            $phone = '254' . $phone;
        } elseif (strpos($phone, '254') === 0) {
            // Already correct
        } elseif (strpos($phone, '1') === 0) {
            $phone = '254' . $phone;
        } else {
            return null;
        }
        return $phone;
    }

    // Static helper to send SMS via Chania API
    public static function sendTextChania($recipient, $message, $senderID) {
        $baseUrl = "http://94.72.97.10/api/v2/SendSMS";
        $ch = curl_init($baseUrl);
        $data = array(
            'ApiKey' => '4zO2J0eeE74irbiK7gRlBzn/ovuptXNs9hhiXohnmHk=',
            'ClientId' => '1f7e7003-aef6-439f-a0dc-8e3af7b9b9a1',
            'SenderId' => $senderID,
            'Message' => $message,
            'MobileNumbers' => $recipient
        );
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Accept:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
} 