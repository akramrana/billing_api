<?php
/**
 * Description of ColourController
 *
 * @author akram
 */
class ColourController
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
        $sql = "SELECT colour_id id, name FROM colours WHERE is_deleted = 0 $likeConditionStr ORDER BY colour_id DESC limit $offset,$limit";
        //echo $sql;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            //
            $countSql = "SELECT count(*) FROM colours WHERE is_deleted = 0 " . $likeConditionStr;
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
        $query = 'INSERT into colours (name,is_active) VALUES(?,?)';
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
        $sql = 'SELECT * FROM colours WHERE is_deleted = 0 and colour_id=:colour_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":colour_id", $id);
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
            'colour_id' => $id,
        ];
        $query = 'UPDATE colours SET name=:name WHERE colour_id=:colour_id';
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
            'colour_id' => $id,
        ];
        $query = 'UPDATE colours SET is_deleted=:is_deleted WHERE colour_id=:colour_id';
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
