<?php

/**
 * Description of PaymentController
 *
 * @author akram
 */
class PaymentController
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
        $sql = "SELECT payments.payment_id id, payments.amount, payments.paymode, payments.order_id, payments.business_id, DATE_FORMAT(payments.created_at,'%d/%m/%Y %h:%i %p') created_at, orders.order_number, businesses.name business_name "
                . "FROM payments "
                . "INNER JOIN orders ON payments.order_id = orders.order_id "
                . "INNER JOIN businesses ON orders.business_id = businesses.business_id "
                . "WHERE payments.is_deleted = 0 AND orders.is_deleted = 0 $likeConditionStr "
                . "ORDER BY payments.payment_id DESC "
                . "limit $offset,$limit";
        //debugPrint($sql);
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            //
            $countSql = "SELECT count(*) FROM payments "
                    . "INNER JOIN orders ON payments.order_id = orders.order_id  "
                    . "INNER JOIN businesses ON orders.business_id = businesses.business_id "
                    . "WHERE payments.is_deleted = 0 " . $likeConditionStr;
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
        $sql = 'SELECT * FROM orders WHERE is_deleted = 0 and order_id=:order_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":order_id", $bodyParam['orderId']);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $row = $stmt->fetch();
            //
            $data = [
                $row['business_id'],
                $bodyParam['amount'],
                $bodyParam['paymode'],
                $bodyParam['orderId'],
                1
            ];
            $query = 'INSERT into payments (business_id,amount,paymode,order_id,is_active) VALUES(?,?,?,?,?)';
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
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function view($id) {
        $sql = 'SELECT * FROM payments WHERE is_deleted = 0 and payment_id=:payment_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":payment_id", $id);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function update($bodyParam, $id) {
        $sql = 'SELECT * FROM orders WHERE is_deleted = 0 and order_id=:order_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":order_id", $bodyParam['orderId']);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $row = $stmt->fetch();
            //
            $data = [
                'business_id' => $row['business_id'],
                'amount' => $bodyParam['amount'],
                'paymode' => $bodyParam['paymode'],
                'updated_at' => date('Y-m-d H:i:s'),
                'order_id' => $bodyParam['orderId'],
                'payment_id' => $id,
            ];
            $query = 'UPDATE payments SET business_id=:business_id,amount=:amount,paymode=:paymode,updated_at=:updated_at,order_id=:order_id '
                    . 'WHERE payment_id=:payment_id';
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
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
    
    public function delete($id) {
        $data = [
            'is_deleted' => 1,
            'payment_id' => $id,
        ];
        $query = 'UPDATE payments SET is_deleted=:is_deleted WHERE payment_id=:payment_id';
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
