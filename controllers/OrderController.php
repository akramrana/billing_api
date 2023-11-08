<?php

/**
 * Description of OrderController
 *
 * @author akram
 */
class OrderController
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
        $sql = "SELECT orders.order_id id, order_number, orders.business_id, orders.created_at,delivery_time,businesses.name,temp.status_id,status.name as status_name "
                . "FROM orders "
                . "INNER JOIN businesses ON orders.business_id = businesses.business_id "
                . "LEFT JOIN (
                                SELECT t1.*
                                FROM order_status AS t1
                                LEFT OUTER JOIN order_status AS t2 ON t1.order_id = t2.order_id 
                                        AND (t1.status_date < t2.status_date 
                                         OR (t1.status_date = t2.status_date AND t1.order_status_id < t2.order_status_id))
                                WHERE t2.order_id IS NULL
                                ) as temp ON temp.order_id = orders.order_id "
                ."INNER JOIN status ON temp.status_id = status.status_id "
                . "WHERE orders.is_deleted = 0 $likeConditionStr ORDER BY orders.order_id DESC limit $offset,$limit";
        //echo $sql;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            //
            $countSql = "SELECT count(*) FROM orders WHERE is_deleted = 0 " . $likeConditionStr;
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

    public function nextOrderNumber() {
        $sql = 'SELECT MAX(order_number) AS order_number FROM orders WHERE is_deleted = 0';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            if (!empty($row['order_number'])) {
                return $row['order_number'] + 1;
            } else {
                return 100000001;
            }
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function create($bodyParam) {
        //debugPrint($bodyParam);
        $data = [
            $bodyParam['orderNumber'],
            $bodyParam['businessId'],
            1,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ];
        $query = 'INSERT into orders (order_number,business_id,is_processed,created_at,updated_at) VALUES(?,?,?,?,?)';
        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($data);
            $id = $this->conn->lastInsertId();
            //
            if (!empty($bodyParam['itemsArray'])) {
                foreach ($bodyParam['itemsArray'] as $item) {
                    $itm = [
                        $item['colourId'],
                        $item['sizeId'],
                        $item['finalPrice'],
                        $item['regularPrice'],
                        $item['quantity'],
                        $item['message'],
                        $id,
                    ];
                    $iQuery = 'INSERT into order_items (colour_id,size_id,price,regular_price,quantity,message,order_id) VALUES(?,?,?,?,?,?,?)';
                    $iStatement = $this->conn->prepare($iQuery);
                    $iStatement->execute($itm);
                }
            }
            //
            $sData = [
                $id,
                1,
                date('Y-m-d H:i:s'),
                'Added by System'
            ];
            $sQuery = 'INSERT into order_status (order_id,status_id,status_date,comment) VALUES(?,?,?,?)';
            $sStatement = $this->conn->prepare($sQuery);
            $sStatement->execute($sData);
            return [
                'status' => 1,
                'errorField' => ''
            ];
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }
}
