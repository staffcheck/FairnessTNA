<?php
/*
 @version V5.20dev  ??-???-2014  (c) 2000-2014 John Lim (jlim#natsoft.com). All rights reserved.
 Released under both BSD license and Lesser GPL library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.
 Contribution by Frank M. Kromann <frank@frontbase.com>.
  Set tabs to 8.
*/

// security - hide paths
if (!defined('ADODB_DIR')) {
    die();
}

if (!defined("_ADODB_FBSQL_LAYER")) {
    define("_ADODB_FBSQL_LAYER", 1);

    class ADODB_fbsql extends ADOConnection
    {
        public $databaseType = 'fbsql';
        public $hasInsertID = true;
        public $hasAffectedRows = true;
        public $metaTablesSQL = "SHOW TABLES";
        public $metaColumnsSQL = "SHOW COLUMNS FROM %s";
        public $fmtTimeStamp = "'Y-m-d H:i:s'";
        public $hasLimit = false;

        public function ADODB_fbsql()
        {
        }

        public function _insertid()
        {
            return fbsql_insert_id($this->_connectionID);
        }

        public function _affectedrows()
        {
            return fbsql_affected_rows($this->_connectionID);
        }

        public function MetaDatabases()
        {
            $qid = fbsql_list_dbs($this->_connectionID);
            $arr = array();
            $i = 0;
            $max = fbsql_num_rows($qid);
            while ($i < $max) {
                $arr[] = fbsql_tablename($qid, $i);
                $i += 1;
            }
            return $arr;
        }

        // returns concatenated string
        public function Concat()
        {
            $s = "";
            $arr = func_get_args();
            $first = true;

            $s = implode(',', $arr);
            if (sizeof($arr) > 0) {
                return "CONCAT($s)";
            } else {
                return '';
            }
        }

        // returns true or false
        public function _connect($argHostname, $argUsername, $argPassword, $argDatabasename)
        {
            $this->_connectionID = fbsql_connect($argHostname, $argUsername, $argPassword);
            if ($this->_connectionID === false) {
                return false;
            }
            if ($argDatabasename) {
                return $this->SelectDB($argDatabasename);
            }
            return true;
        }

        // returns true or false
        public function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
        {
            $this->_connectionID = fbsql_pconnect($argHostname, $argUsername, $argPassword);
            if ($this->_connectionID === false) {
                return false;
            }
            if ($argDatabasename) {
                return $this->SelectDB($argDatabasename);
            }
            return true;
        }

        public function MetaColumns($table, $normalize = true)
        {
            if ($this->metaColumnsSQL) {
                $rs = $this->Execute(sprintf($this->metaColumnsSQL, $table));

                if ($rs === false) {
                    return false;
                }

                $retarr = array();
                while (!$rs->EOF) {
                    $fld = new ADOFieldObject();
                    $fld->name = $rs->fields[0];
                    $fld->type = $rs->fields[1];

                    // split type into type(length):
                    if (preg_match("/^(.+)\((\d+)\)$/", $fld->type, $query_array)) {
                        $fld->type = $query_array[1];
                        $fld->max_length = $query_array[2];
                    } else {
                        $fld->max_length = -1;
                    }
                    $fld->not_null = ($rs->fields[2] != 'YES');
                    $fld->primary_key = ($rs->fields[3] == 'PRI');
                    $fld->auto_increment = (strpos($rs->fields[5], 'auto_increment') !== false);
                    $fld->binary = (strpos($fld->type, 'blob') !== false);

                    $retarr[strtoupper($fld->name)] = $fld;
                    $rs->MoveNext();
                }
                $rs->Close();
                return $retarr;
            }
            return false;
        }

        // returns true or false
        public function SelectDB($dbName)
        {
            $this->database = $dbName;
            if ($this->_connectionID) {
                return @fbsql_select_db($dbName, $this->_connectionID);
            } else {
                return false;
            }
        }


        // returns queryID or false
        public function _query($sql, $inputarr = false)
        {
            return fbsql_query("$sql;", $this->_connectionID);
        }

        /*	Returns: the last error message from previous database operation	*/
        public function ErrorMsg()
        {
            $this->_errorMsg = @fbsql_error($this->_connectionID);
            return $this->_errorMsg;
        }

        /*	Returns: the last error number from previous database operation	*/
        public function ErrorNo()
        {
            return @fbsql_errno($this->_connectionID);
        }

        // returns true or false
        public function _close()
        {
            return @fbsql_close($this->_connectionID);
        }
    }

    /*--------------------------------------------------------------------------------------
         Class Name: Recordset
    --------------------------------------------------------------------------------------*/

    class ADORecordSet_fbsql extends ADORecordSet
    {
        public $databaseType = "fbsql";
        public $canSeek = true;

        public function ADORecordSet_fbsql($queryID, $mode = false)
        {
            if (!$mode) {
                global $ADODB_FETCH_MODE;
                $mode = $ADODB_FETCH_MODE;
            }
            switch ($mode) {
                case ADODB_FETCH_NUM:
                    $this->fetchMode = FBSQL_NUM;
                    break;
                case ADODB_FETCH_ASSOC:
                    $this->fetchMode = FBSQL_ASSOC;
                    break;
                case ADODB_FETCH_BOTH:
                default:
                    $this->fetchMode = FBSQL_BOTH;
                    break;
            }
            return $this->ADORecordSet($queryID);
        }

        public function _initrs()
        {
            global $ADODB_COUNTRECS;
            $this->_numOfRows = ($ADODB_COUNTRECS) ? @fbsql_num_rows($this->_queryID) : -1;
            $this->_numOfFields = @fbsql_num_fields($this->_queryID);
        }


        public function FetchField($fieldOffset = -1)
        {
            if ($fieldOffset != -1) {
                $o = @fbsql_fetch_field($this->_queryID, $fieldOffset);
                //$o->max_length = -1; // fbsql returns the max length less spaces -- so it is unrealiable
                $f = @fbsql_field_flags($this->_queryID, $fieldOffset);
                $o->binary = (strpos($f, 'binary') !== false);
            } elseif ($fieldOffset == -1) {    /*	The $fieldOffset argument is not provided thus its -1 	*/
                $o = @fbsql_fetch_field($this->_queryID);// fbsql returns the max length less spaces -- so it is unrealiable
                //$o->max_length = -1;
            }

            return $o;
        }

        public function _seek($row)
        {
            return @fbsql_data_seek($this->_queryID, $row);
        }

        public function _fetch($ignore_fields = false)
        {
            $this->fields = @fbsql_fetch_array($this->_queryID, $this->fetchMode);
            return ($this->fields == true);
        }

        public function _close()
        {
            return @fbsql_free_result($this->_queryID);
        }

        public function MetaType($t, $len = -1, $fieldobj = false)
        {
            if (is_object($t)) {
                $fieldobj = $t;
                $t = $fieldobj->type;
                $len = $fieldobj->max_length;
            }
            $len = -1; // fbsql max_length is not accurate
            switch (strtoupper($t)) {
                case 'CHARACTER':
                case 'CHARACTER VARYING':
                case 'BLOB':
                case 'CLOB':
                case 'BIT':
                case 'BIT VARYING':
                    if ($len <= $this->blobSize) {
                        return 'C';
                    }

                // so we have to check whether binary...
                case 'IMAGE':
                case 'LONGBLOB':
                case 'BLOB':
                case 'MEDIUMBLOB':
                    return !empty($fieldobj->binary) ? 'B' : 'X';

                case 'DATE':
                    return 'D';

                case 'TIME':
                case 'TIME WITH TIME ZONE':
                case 'TIMESTAMP':
                case 'TIMESTAMP WITH TIME ZONE':
                    return 'T';

                case 'PRIMARY_KEY':
                    return 'R';
                case 'INTEGER':
                case 'SMALLINT':
                case 'BOOLEAN':

                    if (!empty($fieldobj->primary_key)) {
                        return 'R';
                    } else {
                        return 'I';
                    }

                default:
                    return 'N';
            }
        }
    } //class
} // defined
