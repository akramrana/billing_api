<?php

/**
 * Description of DeliveryController
 *
 * @author akram
 */
class DeliveryController
{

    public $db;
    private $conn;

    //put your code here
    public function __construct() {
        $this->db = new DB();
        $this->conn = $this->db->connect();
    }

    public function nextDeliveryNumber() {
        $sql = 'SELECT MAX(delivery_number) AS delivery_number FROM deliveries WHERE is_deleted = 0';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            if (!empty($row['delivery_number'])) {
                return $row['delivery_number'] + 1;
            } else {
                return 100001;
            }
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function list($_page, $limit, $likes = [], $wheres = []) {
        $offset = ($_page - 1) * $limit;
        $likeCondition = [];
        $likeConditionStr = "";
        $whereCondition = [];
        $whereConditionStr = "";
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
        if (!empty($wheres)) {
            foreach ($wheres as $key => $value) {
                if (isset($value)) {
                    $whereCondition[] = $key . ' = \'' . $value . '\'';
                }
            }
            if (!empty($whereCondition)) {
                $whereConditionStr = ' AND (' . implode(' AND ', $whereCondition) . ')';
            }
        }
        $sql = "SELECT delivery_id id, delivery_number, delivery_man, DATE_FORMAT(deliveries.created_at,'%Y-%m-%d %h:%i %p') created_at FROM deliveries "
                . "WHERE is_deleted = 0 $whereConditionStr $likeConditionStr "
                . "ORDER BY delivery_id DESC limit $offset,$limit";
        //echo $sql;
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            //
            $countSql = "SELECT count(*) FROM deliveries WHERE is_deleted = 0 " . $whereConditionStr . $likeConditionStr;
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
        //debugPrint($bodyParam);
        $data = [
            $bodyParam['deliveryNumber'],
            $bodyParam['deliveryMan'],
            1,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ];
        $query = 'INSERT into deliveries (delivery_number,delivery_man,is_active,created_at,updated_at) VALUES(?,?,?,?,?)';
        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($data);
            $id = $this->conn->lastInsertId();
            //
            if (!empty($bodyParam['itemsArray'])) {
                foreach ($bodyParam['itemsArray'] as $item) {
                    $orderId = null;
                    if (!empty($item['orderId'])) {
                        $orderId = $item['orderId'];
                    }
                    $itm = [
                        $item['colourId'],
                        $item['sizeId'],
                        $item['price'],
                        $item['quantity'],
                        $item['message'],
                        date('Y-m-d', strtotime($item['date'])),
                        $orderId,
                        $id,
                    ];
                    $iQuery = 'INSERT into delivery_items (colour_id,size_id,price,quantity,message,datetime,order_id,delivery_id) VALUES(?,?,?,?,?,?,?,?)';
                    $iStatement = $this->conn->prepare($iQuery);
                    $iStatement->execute($itm);
                }
            }
            return [
                'status' => 1,
                'errorField' => ''
            ];
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function view($id) {
        $sql = 'SELECT deliveries.* '
                . 'FROM deliveries '
                . 'WHERE deliveries.is_deleted = 0 and deliveries.delivery_id=:delivery_id';
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":delivery_id", $id);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $row = $stmt->fetch();
            //
            $iSql = 'SELECT delivery_items.*,colours.name as colour_name,sizes.name as size_name,orders.order_number '
                    . 'FROM delivery_items '
                    . 'INNER JOIN colours ON delivery_items.colour_id = colours.colour_id '
                    . 'INNER JOIN sizes ON delivery_items.size_id = sizes.size_id '
                    . 'LEFT JOIN orders ON delivery_items.order_id = orders.order_id '
                    . 'WHERE delivery_id=:delivery_id ORDER BY delivery_item_id ASC';
            //echo $iSql;
            $iStmt = $this->conn->prepare($iSql);
            $iStmt->bindParam(":delivery_id", $id);
            $iStmt->execute();
            $iStmt->setFetchMode(PDO::FETCH_ASSOC);
            $items = $iStmt->fetchAll();
            //
            $row['created_at'] = date('Y-m-d h:i A', strtotime($row['created_at']));
            $row['items'] = $items;
            return $row;
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function update($bodyParam, $id) {
        //debugPrint($bodyParam);
        $data = [
            'delivery_man' => $bodyParam['deliveryMan'],
            'updated_at' => date('Y-m-d H:i:s'),
            'delivery_id' => $id,
        ];
        $query = 'UPDATE deliveries SET delivery_man=:delivery_man, updated_at=:updated_at WHERE delivery_id=:delivery_id';
        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($data);
            //
            $dSql = "DELETE FROM delivery_items WHERE delivery_id=?";
            $dStmt = $this->conn->prepare($dSql);
            $dStmt->execute([$id]);
            //
            if (!empty($bodyParam['itemsArray'])) {
                foreach ($bodyParam['itemsArray'] as $item) {
                    $orderId = null;
                    if (!empty($item['orderId'])) {
                        $orderId = $item['orderId'];
                    }
                    $itm = [
                        $item['colourId'],
                        $item['sizeId'],
                        $item['price'],
                        $item['quantity'],
                        $item['message'],
                        date('Y-m-d', strtotime($item['date'])),
                        $orderId,
                        $id,
                    ];
                    $iQuery = 'INSERT into delivery_items (colour_id,size_id,price,quantity,message,datetime,order_id,delivery_id) VALUES(?,?,?,?,?,?,?,?)';
                    $iStatement = $this->conn->prepare($iQuery);
                    $iStatement->execute($itm);
                }
            }
            return [
                'status' => 1,
                'errorField' => ''
            ];
        } catch (Exception $e) {
            echo "Query failed: " . $e->getMessage();
        }
    }

    public function delete($id) {
        $data = [
            'updated_at' => date("Y-m-d H:i:s"),
            'is_deleted' => 1,
            'order_id' => $id,
        ];
        $query = 'UPDATE deliveries SET is_deleted=:is_deleted, updated_at=:updated_at WHERE delivery_id=:delivery_id';
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
