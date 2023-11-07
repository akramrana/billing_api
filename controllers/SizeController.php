<?php

/**
 * Description of SizeController
 *
 * @author akram
 */
class SizeController
{
    public $db;
    private $conn;

    //put your code here
    public function __construct() {
        $this->db = new DB();
        $this->conn = $this->db->connect();
    }

    public function list($_page, $limit, $likes = []) {
        $offset = ($_page - 1) * $limit;
        $likeCondition = [];
        $likeConditionStr = "";
        if (!empty($likes)) {
            foreach ($likes as $key => $value) {
                if (isset($value)) {
                    $likeCondition[] = $key . ' like \'%' . $value . '%\'';
                }
            }
            if (!empty($likeCondition)) {
                $likeConditionStr = ' AND (' . implode(' OR ', $likeCondition) . ')';
            }
        }
        $sql = "SELECT size_id id, name FROM sizes WHERE is_deleted = 0 $likeConditionStr ORDER BY size_id DESC limit $offset,$limit";
        //echo $sql;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            //
            $countSql = "SELECT count(*) FROM sizes WHERE is_deleted = 0 " . $likeConditionStr;
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute();
            $numberOfRows = $countStmt->fetchColumn();
            $pageCount = ceil($numberOfRows / $limit);
            //
            return [
                'data' => $results,
                'total' => $pageCount,
            ];
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function create($bodyParam) {
        $data = [
            $bodyParam['name'],
            1,
        ];
        $query = 'INSERT into sizes (name,is_active) VALUES(?,?)';
        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($data);
            return [
                'status' => 1,
                'errorField' => ''
            ];
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function view($id) {
        $sql = 'SELECT * FROM sizes WHERE is_deleted = 0 and size_id=:size_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":size_id", $id);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function update($bodyParam, $id) {
        $data = [
            'name' => $bodyParam['name'],
            'size_id' => $id,
        ];
        $query = 'UPDATE sizes SET name=:name WHERE size_id=:size_id';
        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($data);
            return [
                'status' => 1,
                'errorField' => ''
            ];
        } catch (Exception $e) {
            echo "Query failed for update: " . $e->getMessage();
        }
    }

    public function delete($id) {
        $data = [
            'is_deleted' => 1,
            'size_id' => $id,
        ];
        $query = 'UPDATE sizes SET is_deleted=:is_deleted WHERE size_id=:size_id';
        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($data);
            return [
                'status' => 1,
                'errorField' => ''
            ];
        } catch (Exception $e) {
            echo "Query failed for delete: " . $e->getMessage();
        }
    }
}
