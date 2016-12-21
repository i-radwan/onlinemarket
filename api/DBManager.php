<?php

/**
 * Description of DBManager
 *
 * @author ibrahimradwan
 */

class DBManager {
    
    public function __construct() {
        $this->db_link = new mysqli(db_host, db_user, db_pass, db_name);
        if ($this->db_link->connect_errno > 0) {
            die('Unable to connect to database [' . $this->db_link->connect_error . ']');
        }
    }

    public function queryWithResult($query) {
        return $this->db_link->query($query);
    }

    /**
     * This function executes a query with no returned result
     * @param type $query
     * @return int number of affected rows, -1 if error happened
     */
    public function queryWithNoResult($query) {
        if ($result = $this->db_link->query($query)) {
            return $this->db_link->affected_rows;
        }        
        return -1;
    }

    public function getLastInsertID(){
        return $this->db_link->insert_id;
    }
    /**
     * This function returns a count of a query result
     * @param type $query
     * @return int result rows count, -1 if error
     */
    public function executeScalar($query) {
        if ($result = $this->db_link->query($query)) {
            return $result->fetch_assoc()['count'];
        }
        return -1;
    }

    public function __destruct() {
        $this->db_link->close();
    }

}
