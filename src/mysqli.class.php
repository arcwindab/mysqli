<?php 
/**
 * @package arcwindab/mysqli
 * @link    https://github.com/arcwindab/mysqli/
 * @author  Tobias Jonson <suport@bot.arcwind.se>
 * @license https://github.com/arcwindab/mysqli/blob/main/LICENSE
 */

namespace arcwindab {
   class MySQLi {

      public  $DBh            = "";
      public  $result         = array();

      private $SQL_HOST       = "";
      private $SQL_USERNAME   = "";
      private $SQL_PASSWORD   = "";
      private $SQL_NAME       = "";
      private $SQL_PREFIX     = "";
      private $SQL_SOCKET     = "";

      public function __construct($SQL_HOST="",$SQL_USERNAME="",$SQL_PASSWORD="",$SQL_NAME="",$SQL_PREFIX="",$SQL_SOCKET="") {
         //Setting SQL 
         if(defined("AWM_SQL_HOST")) {
            $this->SQL_HOST      = constant("AWM_SQL_HOST");
         } else {
            $this->SQL_HOST      = $SQL_HOST;
            define("AWM_SQL_HOST",$this->SQL_HOST);
         }
         if(defined("AWM_SQL_USERNAME")) {
            $this->SQL_USERNAME  = constant("AWM_SQL_USERNAME");
         } else {
            $this->SQL_USERNAME  = $SQL_USERNAME;
            define("AWM_SQL_USERNAME",$this->SQL_USERNAME);
         }
         if(defined("AWM_SQL_PASSWORD")) {
            $this->SQL_PASSWORD  = constant("AWM_SQL_PASSWORD");
         } else {
            $this->SQL_PASSWORD  = $SQL_PASSWORD;
            define("AWM_SQL_PASSWORD",$this->SQL_PASSWORD);
         }
         if(defined("AWM_SQL_NAME")) {
            $this->SQL_NAME      = constant("AWM_SQL_NAME");
         } else {
            $this->SQL_NAME      = $SQL_NAME;
            define("AWM_SQL_NAME",$this->SQL_NAME);
         }
         if(defined("AWM_SQL_PREFIX")) {
            $this->SQL_PREFIX    = constant("AWM_SQL_PREFIX");
         } else {
            $this->SQL_PREFIX    = $SQL_PREFIX;
            define("AWM_SQL_PREFIX",$this->SQL_PREFIX);
         }
         if(defined("AWM_SQL_SOCKET")) {
            $this->SQL_SOCKET    = constant("AWM_SQL_SOCKET");
         } else {
            $this->SQL_SOCKET    = $SQL_SOCKET;
            define("AWM_SQL_SOCKET",$this->SQL_SOCKET);
         }

         return $this->DBh = $this->StartDBConnection();
      }
      public function __destruct() {
         $this->StopDBConnection();
      }

      public function StartDBConnection() {
         $DBh = false;
         if((isset($DBh)) && ($DBh!="")) { 

         } else {
            $DBh = "";
            $DBh = @mysqli_connect(AWM_SQL_HOST,AWM_SQL_USERNAME,AWM_SQL_PASSWORD,AWM_SQL_NAME) or die("SQL ERROR (".__LINE__."): Connection error: ".__LINE__);
            if (mysqli_connect_errno()) {
               trigger_error('SQL Connection error',E_USER_ERROR);
            }
            if($DBh=="") {
               trigger_error('SQL Connection error',E_USER_ERROR);
            }
         }

         $this->DBh = $DBh;
         return $DBh;
      }
      public function StopDBConnection(){
         if((isset($this->DBh)) && ($this->DBh!="")) { 
            @mysqli_close($this->DBh);
         }
      }
      private function verify($q,$allow,$caller) {
         if(strtolower(substr(trim($q), 0, strlen("drop"))) === strtolower("drop")) {
            trigger_error('Can not drop table',E_USER_ERROR);
         }
         if(strtolower(substr(trim($q), 0, strlen("trunkate"))) === strtolower("trunkate")) {
            trigger_error('Can not trunkate table',E_USER_ERROR);
         }
         if(strtolower(substr(trim($q), 0, strlen("alter"))) === strtolower("alter")) {
            trigger_error('Can not alter table',E_USER_ERROR);
         }
         if((strtolower(substr(trim($q), 0, strlen($allow))) === strtolower($allow)) && ((substr(trim($q), -1) === ';'))) {
            return $q;
         } else {
            if(strtolower(substr(trim($q), 0, strlen($allow))) !== strtolower($allow)) {
               trigger_error('SQL String type not match',E_USER_ERROR);
            } elseif(substr(trim($q), -1) !== ';') {
               trigger_error('SQL String end not match',E_USER_ERROR);
            } else {
               trigger_error('SQL Unknown Error',E_USER_ERROR);
            }
            return false;
         }
         return false;
      }
      public function escape($variable) {
         $old = array("  ");
         $new = array(" ");
         if(isset($this->DBh)) {
            $variable = $this->DBh->real_escape_string(str_replace($old,$new,trim(rawurldecode(rawurldecode($variable)))));
         }

         return $variable; 
      }
      public function insert($variable) {
         return $this->escape($variable); 
      }
      public function SQL($q,$allow="select") { 
         $arr     = array();
         $sel     = "Select";
         $prefix  = "[[DB]]";
         $caller = debug_backtrace()[0];

         if((is_array($q)) || (is_object($q))) {
            $query = "SELECT \n";
            $i=0;
            foreach($q as $v => $qu) {
               if(strpos($qu, $prefix) == false) {
                  trigger_error('SQL Missing prefix '.$prefix,E_USER_ERROR);
               }
               if($qu!="") {
                  $strpos = strpos($qu,$prefix);
                  if(substr($qu,($strpos-1),1) == "`") {
                     $qu = str_replace($prefix,constant("AWM_SQL_NAME")."`.`".constant("AWM_SQL_PREFIX")."",$qu);
                  } else {
                     $qu = str_replace($prefix,"`".constant("AWM_SQL_NAME")."`.`".constant("AWM_SQL_PREFIX")."",$qu);
                  }
               }
               $this->verify($qu,$allow,$caller);
               if($i!=0) {
                  $query .= ",\n";
               }
               $query .= "   (".trim($qu,";").") as `".$v."`";
               $i++;
            }
            $q = $query.";";
         } else {
            if(strpos($q, $prefix) == false) {
               trigger_error('SQL Missing prefix '.$prefix,E_USER_ERROR);
            }
            if($q!="") {
               $strpos = strpos($q,$prefix);
               if(substr($q,($strpos-1),1) == "`") {
                  $q = str_replace($prefix,constant("AWM_SQL_NAME")."`.`".constant("AWM_SQL_PREFIX")."",$q);
               } else {
                  $q = str_replace($prefix,"`".constant("AWM_SQL_NAME")."`.`".constant("AWM_SQL_PREFIX")."",$q);
               }
            }
            $this->verify($q,$allow,$caller);
         }
         if($q!="") {
            $this->verify($q,$allow,$caller);
            if(isset($this->DBh)) {} else {
               $this->DBh = $this->StartDBConnection();
            }
            $MySQLi[0]["Result"] = $this->DBh->query($q);

            if(strtolower(substr($q, 0, strlen($sel))) === strtolower($sel)) {
               if(!$MySQLi[0]["Result"]) {
                  trigger_error($this->DBh->error,E_USER_ERROR);
               } elseif($MySQLi[0]["Result"]->num_rows>0) {
                  while($MySQLi[0]["Rows"]=$MySQLi[0]["Result"]->fetch_object()){
                     $arr[] = $MySQLi[0]["Rows"];
                  }
                  return $arr;
               } elseif($MySQLi[0]["Result"]->num_rows==0) {
                  return array();
               }
            } else {
               if(!$MySQLi[0]["Result"]) {
                  trigger_error($this->DBh->error.'.<br><br><strong>Query</strong>:<br>'.$q,E_USER_ERROR);
                  return array();
               }
               return array();
            }
         }
         return array();
      }
      public function SQLBackup($tables = '*', $path = '', $title = '') {
         $return = "";
         if((is_array($tables)) || (is_object($tables))) {
            $t = $tables;
            $tables = array();
            foreach($t as $ta) {
               $tables[] = "`".constant("AWM_SQL_PREFIX").$ta."`";
            }
         } else {
            if($tables!='*') {
               $t = $tables;
               $tables = array();
               $tables[] = "`".constant("AWM_SQL_PREFIX").$t."`";
            }
            if($tables == '*') {
               $tables = array();
               $result = $this->DBh->query('SHOW TABLES');
               while($row = mysqli_fetch_row($result)) {
                  $tables[] = $row[0];
               }
            }
         } 

         foreach($tables as $table) {
            $result = $this->DBh->query('SELECT * FROM '.$table);
            if(!is_bool($result)) {
               $num_fields = mysqli_num_fields($result);

               $return.= '-- phpMyAdmin SQL Dump'."\n";
               $return.= '-- http://www.phpmyadmin.net'."\n"."\n";
               $return.= '-- Generation Time '.date("Y-m-d H:i:s e")."\n"."\n";
               $return.= '-- ArcWind\mysqli'."\n"."\n";
               $return.= '--'."\n";
               $return.= '-- Database: '.constant("AWM_SQL_NAME").''."\n";
               $return.= '--'."\n"."\n";
               $return.= '-- --------------------------------------------------------'."\n";
               $return.= '--'."\n";
               $return.= '-- Table structure for table '.$table."\n";
               $return.= '--'."\n"."\n";

               $return.= 'DROP TABLE IF EXISTS '.$table.';';
               $row2 = mysqli_fetch_row($this->DBh->query('SHOW CREATE TABLE '.$table));
               $return.= "\n\n".$row2[1].";\n\n";

               $return.= '--'."\n";
               $return.= '-- Dumping data for table '.$table."\n";
               $return.= '--'."\n"."\n";

               for ($i = 0; $i < $num_fields; $i++) {
                  while($row = mysqli_fetch_row($result)) {
                     $return.= 'INSERT INTO '.$table.' VALUES(';
                     for($j=0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n","\\n",$row[$j]);
                        if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                        if ($j < ($num_fields-1)) { $return.= ','; }
                     }
                     $return.= ");\n";
                  }
               }
               $return.="\n\n\n";
            }
         }
         if($path=="") {
            $path = __DIR__;
         }
         if($title!="") {
            $title = $title." ";
         }
         $paths = rtrim($path,"/").'/'.$this->toAscii($title).'db-backup-'.$this->toAscii(date("YmdHis")).'-'.(md5(implode(',',$tables))).'.sql';
         $handle = fopen($paths,'w+');
         fwrite($handle,$return);
         fclose($handle);
         return $paths;
      }

      public function toAscii($str, $replace=array(), $delimiter='-') {
         if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
         }

         $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
         $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
         $clean = strtolower(trim($clean, '-'));
         $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

         return $clean;
      }
   }
}
