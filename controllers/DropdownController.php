<?php
/**
 * Description of DropdownController
 *
 * @author akram
 */
class DropdownController
{

    public $db;
    private $conn;

    //put your code here
    public function __construct() {
        $this->db = new DB();
        $this->conn = $this->db->connect();
    }

    public function businessList() {
        $sql = 'SELECT * FROM businesses WHERE is_deleted = 0 ORDER BY business_id DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $listData = [];
            if (!empty($results)) {
                foreach ($results as $row) {
                    $d = [
                        'id' => $row['business_id'],
                        'name' => $row['name']
                    ];
                    array_push($listData, $d);
                }
            }
            return $listData;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
    
    public function colourList() {
        $sql = 'SELECT * FROM colours WHERE is_deleted = 0 ORDER BY colour_id DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $listData = [];
            if (!empty($results)) {
                foreach ($results as $row) {
                    $d = [
                        'id' => $row['colour_id'],
                        'name' => $row['name']
                    ];
                    array_push($listData, $d);
                }
            }
            return $listData;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
    
    public function sizeList() {
        $sql = 'SELECT * FROM sizes WHERE is_deleted = 0 ORDER BY size_id DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $listData = [];
            if (!empty($results)) {
                foreach ($results as $row) {
                    $d = [
                        'id' => $row['size_id'],
                        'name' => $row['name']
                    ];
                    array_push($listData, $d);
                }
            }
            return $listData;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
    
    public function statusList() {
        $sql = 'SELECT * FROM status ORDER BY status_id DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $listData = [];
            if (!empty($results)) {
                foreach ($results as $row) {
                    $d = [
                        'id' => $row['status_id'],
                        'name' => $row['name']
                    ];
                    array_push($listData, $d);
                }
            }
            return $listData;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
    
    public function orderList() {
        $sql = 'SELECT * FROM orders WHERE is_deleted = 0 ORDER BY order_id DESC';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            $listData = [];
            if (!empty($results)) {
                foreach ($results as $row) {
                    $d = [
                        'id' => $row['order_id'],
                        'name' => $row['order_number']
                    ];
                    array_push($listData, $d);
                }
            }
            return $listData;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
    
    public function paymodeList(){
        return [
            [
                'id' => 'CASH',
                'name' => 'CASH',
            ],
            [
                'id' => 'MOBILE_WALLET',
                'name' => 'MOBILE_WALLET',
            ],
            [
                'id' => 'CHEQUE',
                'name' => 'CHEQUE',
            ],
            [
                'id' => 'CARD',
                'name' => 'CARD',
            ],
            [
                'id' => 'ONLINE_TRANSFER',
                'name' => 'ONLINE_TRANSFER',
            ],
        ];
    }
}
