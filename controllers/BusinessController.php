<?php
/**
 * Description of BusinessController
 *
 * @author akram
 */
class BusinessController
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
        $sql = "SELECT business_id id, name, phone, email FROM businesses WHERE is_deleted = 0 $likeConditionStr ORDER BY business_id DESC limit $offset,$limit";
        //echo $sql;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            //
            $countSql = "SELECT count(*) FROM businesses WHERE is_deleted = 0 " . $likeConditionStr;
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
        $sql = 'SELECT * FROM businesses WHERE is_deleted = 0 and email = \'' . $bodyParam['email'] . '\' ';
        try {
            $statement = $this->conn->prepare($sql);
            $statement->execute();
            $row = $statement->fetch();
            if (!empty($row)) {
                return [
                    'status' => 0,
                    'errorField' => 'email'
                ];
            }
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
        $data = [
            $bodyParam['name'],
            $bodyParam['phone'],
            $bodyParam['email'],
            1,
            date('Y-m-d h:i:s'),
            date('Y-m-d h:i:s'),
        ];
        $query = 'INSERT into businesses (name,phone,email,is_active, created_at, updated_at) VALUES(?,?,?,?,?,?)';
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
        $sql = 'SELECT * FROM businesses WHERE is_deleted = 0 and business_id=:business_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":business_id", $id);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function update($bodyParam, $id) {
        $sql = 'SELECT * FROM businesses WHERE is_deleted = 0 and email=:email and business_id !=:business_id';
        try {
            $statement = $this->conn->prepare($sql);
            $statement->bindParam(":business_id", $id);
            $statement->bindParam(":email", $bodyParam['email']);
            $statement->execute();
            $row = $statement->fetch();
            if (!empty($row)) {
                return [
                    'status' => 0,
                    'errorField' => 'email'
                ];
            }
        } catch (Exception $e) {
            echo "Query failed for email: " . $e->getMessage();
        }
        //
        $data = [
            'name' => $bodyParam['name'],
            'phone' => $bodyParam['phone'],
            'email' => $bodyParam['email'],
            'updated_at' => date("Y-m-d H:i:s"),
            'business_id' => $id,
        ];
        $query = 'UPDATE businesses SET name=:name, phone=:phone, email=:email, updated_at=:updated_at WHERE business_id=:business_id';
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
            'updated_at' => date("Y-m-d H:i:s"),
            'is_deleted' => 1,
            'business_id' => $id,
        ];
        $query = 'UPDATE businesses SET is_deleted=:is_deleted, updated_at=:updated_at WHERE business_id=:business_id';
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
