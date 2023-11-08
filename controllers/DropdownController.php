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
}
