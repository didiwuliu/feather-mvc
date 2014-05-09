<?php

namespace Feather\Db;

abstract class AbstractAdapter {
    
    const FIELD_REPLACER = '?';
       
    //default configuration for the db connection
    protected $_config = array(
        'host'      => '',
        'port'      => 3306,
        'user'      => '',
        'password'  => '',
        'database'  => '',
        'charset'   => 'utf8'
    );

    //db connection
    protected $_connection = null;

    /*
    * Create a Db adapter instance
    *
    * @param array $config Database config
    */
    public function __construct($config) {
        $this->_config = array_merge($this->_config, $config);
    }

    /*
    * Execute a sql
    *
    * @param string $sql
    * @return mixed 
    */
    public function query($sql) {
        $this->connect();

        $result = $this->_query($sql);
        if ($result) {
            return $result;
        }

        $this->_throwDbException();
    }

    /*
    * Execute a sql
    *
    * @param string $sql
    * @param array $param
    * @return mixed
    */
    public function secureQuery($sql, $param = array()) {
        $this->connect();
        
        $finalSql = "";
        $remain = $sql;
        foreach ($param as $p) {
            $field = $this->escape($p);
            $replacePos = strpos($remain, self::FIELD_REPLACER);

            //no replacer for the param 
            if (empty($replacePos)) {
                break;
            }

            $finalSql .= substr($remain, 0, $replacePos)."'".$this->escape($p)."'";
            $remain =  substr($remain, $replacePos + 1);
            if (empty($remain)) {
                break;
            }
        }

        $result = $this->_query($finalSql);
        if (!$result) {
            return $result;
        }

        $this->_throwDbException();
    }

    /*
    * Connect to the DB server
    */
    abstract public function connect();

    /*
    * Release the connection to the DB server
    */
    abstract public function close();

    /*
    * Escape the special characters
    */ 
    abstract protected function escape($string);

    /*
    * Actually send the query to DB server
    */
    abstract protected function _query($sql);

    /*
    * throw the error of the DB server
    */
    abstract protected function _throwDbException();

    /*
    * the number of the affected row
    */
    abstract public function affectedRowsNum();

    /*
    * begin transaction
    */
    abstract public function beginTransaction();

    /*
    * commit the transaction
    */
    abstract public function commit();

    /*
    * rollback the transaction
    */
    abstract public function rollback();

}// END OF CLASS