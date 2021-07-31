<?php

namespace Akinuri\CI3_ORM\Base;

use Akinuri\Libraries\{
    Debug,
    Array2,
    String2,
};

class DatabaseGateway {
    
    
    /**
     * Stores the CodeIgniter database object.
     */
    protected $db = null;
    
    
    /**
     * Sets the database.
     */
    public function __construct() {
        $this->db = self::getDB();
    }
    
    
    /**
     * Returns the CodeIgniter database object.
     */
    public static function getDB() {
        return \get_instance()->db;
    }
    
    
    #region ==================== SELECT
    
    /**
     * Generic SELECT method.
     * 
     * WHERE processing may need further improvement.
     * 
     * @link https://dev.mysql.com/doc/refman/5.7/en/select.html
     * @link https://mariadb.com/kb/en/select/
     */
    public function select(array $params, string $customObj = null) {
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Eselect()
        if (!empty($params["select"])) {
            
            // "select" => "field1, field2, table.field3"
            // "select" => [
            //     "field1",
            //     "field2",
            //     "table.field3",
            // ]
            if (\is_array($params["select"])) {
                $params["select"] = \implode(", ", $params["select"]);
            }
            
            if (\str_starts_with($params["select"], "DISTINCT")) {
                $this->db->distinct();
                $params["select"] = \str_replace("DISTINCT ", "", $params["select"]);
            }
            
            $this->db->select($params["select"]);
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Efrom()
        if (!empty($params["from"])) {
            $this->db->from($params["from"]);
        } else {
            throw new \Exception("Missing 'from' parameter.");
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Ejoin()
        if (isset($params["join"])) {
            // "join" => [
            //     [table, condition, type],
            // ]
            foreach ($params["join"] as $join) {
                \call_user_func_array([$this->db, "join"], $join);
            }
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Ewhere()
        if (!empty($params["where"])) {
            if (\is_string($params["where"])) {
                // "where" => "field = 'value'"
                if (String2::includes($params["where"], [" = ", " IS "])) {
                    $params["where"] = [ $params["where"] ];
                }
            }
            else if (\is_array($params["where"])) {
                // "where" => [field, value]
                // "where" => [field => value]
                if (Array2::isAssociative($params["where"])) {
                    $params["where"] = [ $params["where"] ];
                }
            }
            \call_user_func_array([$this->db, "where"], $params["where"]);
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Eor_where()
        if (!empty($params["or_where"])) {
            if (Array2::isAssociative($params["or_where"])) {
                $params["or_where"] = [ $params["or_where"] ];
            }
            call_user_func_array([$this->db, "or_where"], $params["or_where"]);
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Ewhere_in()
        if (!empty($params["where_in"])) {
            if (Array2::isAssociative($params["where_in"])) {
                $params["where_in"] = [ $params["where_in"] ];
            }
            call_user_func_array([$this->db, "where_in"], $params["where_in"]);
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#:~:text=$this-%3Edb-%3Eorder_by()
        if (!empty($params["order_by"])) {
            if (\is_string($params["order_by"])) {
                // "order_by" => "field1 ASC, field2 DESC"
                $params["order_by"] = [
                    [$params["order_by"]]
                ];
            }
            else if (\is_array($params["order_by"])) {
                // "order_by" => [
                //     "field1" => "value1"
                //     "field2" => "value2"
                // ]
                if (Array2::isAssociative($params["order_by"])) {
                    $params["where"] = [ Array2::getEntries($params["order_by"]) ];
                }
            }
            foreach ($params["order_by"] as $order_by) {
                \call_user_func_array([$this->db, "order_by"], $order_by);
            }
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#limiting-or-counting-results
        $limit  = null;
        $offset = null;
        if (!empty($params["limit"])) {
            if (\is_numeric($params["limit"])) {
                $limit = $params["limit"];
            }
            else if (\is_array($params["limit"])) {
                $limit  = $params["limit"][0];
                $offset = $params["limit"][1] ?? null;
            }
            $this->db->limit($limit, $offset);
        }
        
        
        // https://codeigniter.com/userguide3/database/query_builder.html#selecting-data
        $result = $this->db->get();
        
        
        // https://codeigniter.com/userguide3/database/results.html
        if ($result) {
            if ($limit && $limit === 1) {
                if ($customObj) {
                    $result = $result->custom_row_object(0, $customObj);
                } else {
                    $result = $result->row_array();
                }
            } else {
                if ($customObj) {
                    $result = $result->custom_result_object(0, $customObj);
                } else {
                    $result = $result->result_array();
                    if (!empty($params["column"])) {
                        $result = \array_column($result, $params["column"]);
                    }
                }
            }
        }
        
        
        return $result;
    }
    
    #endregion
    
    
}