<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of LoginController
 *
 * @author akram
 */
class LoginController
{

    public $db;
    private $conn;

    public function __construct() {
        $this->db = new DB();
        $this->conn = $this->db->connect();
    }

    public function login($bodyParam) {
        $sql = 'SELECT * FROM admins WHERE is_deleted = 0 and email = \'' . $bodyParam['email'] . '\' ';
        try {
            $statement = $this->conn->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            if (!empty($row)) {
                if (password_verify($bodyParam['password'], $row['password'])) {
                    $user = [
                        'id' => $row['name'],
                        'phone' => $row['phone'],
                        'email' => $row['email'],
                        'token' => $this->jwtToken($row),
                    ];
                    return [
                        'status' => 1,
                        'errorField' => '',
                        'user' => $user
                    ];
                } else {
                    return [
                        'status' => 0,
                        'errorField' => 'password',
                        'message' => 'Password did\'nt match'
                    ];
                }
            } else {
                return [
                    'status' => 0,
                    'errorField' => 'email',
                    'message' => 'E-mail does not exist'
                ];
            }
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    private function jwtToken($user) {
        $secret = '7c32d31dbdd39f2111da0b1dea59e94f3ed715fd8cdf0ca3ecf354ca1a2e3e30';
        //
        $today = date("Y-m-d H:i:s");
        $hours = strtotime('+2 hours', strtotime($today));
        // Create the token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        // Create the token payload
        $payload = json_encode([
            'id' => $user['admin_id'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'role' => 'admin',
            'exp' => $hours
        ]);
        // Encode Header
        $base64UrlHeader = base64UrlEncode($header);
        // Encode Payload
        $base64UrlPayload = base64UrlEncode($payload);
        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        // Encode Signature to Base64Url String
        $base64UrlSignature = base64UrlEncode($signature);
        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        return $jwt;
    }
}
