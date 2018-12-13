<?php
include dirname(__FILE__) . '/sql_templet.php';
include dirname(__FILE__) . '/html_tpl.php';
class tSql{
    private $sql_tmpl;
    private $sql_tmpl_counter;
    private $file;
    private $ctime;
    private $var_path;
    private $html_path;
    private $discard_value;
    public function __construct($file, $now, $discard=500){
        if(!file_exists($file)){
            echo "file $file not exists\n";
            exit;
        }
        $var_path = dirname(__FILE__) . '/../var';
        @mkdir($var_path, 0700, true); 
        $this->var_path = $var_path;
        $this->html_path = dirname(__FILE__) . '/../web';
        $this->file = $file;
        $this->ctime = $now;
        $this->sql_tmpl = array();
        $this->sql_tmpl_counter = array();
        $this->discard_value = $discard;
    }
    private function system_exec($cmd){
         $ret = shell_exec($cmd);        
    }
    private function read_file(){
        $fp = fopen($this->file, 'r');
        $out = $this->var_path."/output_{$this->ctime}.tpl";
        while(!feof($fp)){
            $line = fgets($fp);
            $line = trim($line);
            if(empty($line)){
                continue;
            }
            $log = json_decode($line,true);
            $tpl = SQL_TEMPLET::transferSQLToTpl($log['sql']);
            $md5tpl = md5($tpl);
            $this->sql_tmpl[$md5tpl] = $tpl;
            if(!isset($this->sql_tmpl_counter[$md5tpl])){
                $this->sql_tmpl_counter[$md5tpl] = 0;
            }
            $this->sql_tmpl_counter[$md5tpl] ++;
            $stime = date("Y-m-d H:i", strtotime($log['time'])); //minute
            $tmp = array(
                'time' => $stime,
                'log'  => $md5tpl,
            );
            file_put_contents($out, json_encode($tmp)."\n", FILE_APPEND);
        }
        fclose($fp);
        return $out;
    } 
    public function split_sigle_sql($out){
        $minfileList = array();
        arsort($this->sql_tmpl_counter);
        foreach($this->sql_tmpl_counter as $md5tpl => $count){
            if($count < $this->discard_value){
                continue;
            }
            $f_name = $this->var_path."/{$md5tpl}_split.tpl";
            $this->system_exec("grep $md5tpl $out > $f_name"); 
            $minfileList[$f_name] = $f_name;
        }
        return $minfileList;
    }
    public function split_file($splitList){
        $ret = array();
        foreach($splitList as $index => $path){
            $md5sql = explode('_', basename($path))[0];
            $timeline = array();
            $fp = fopen($path,'r');
            while(!feof($fp)){
                $line = fgets($fp);
                $line = trim($line);
                if(empty($line)){
                    continue;
                }
                $log = json_decode($line,true);
                $t = $log['time'];
                if(!isset($timeline[$t])){
                    $timeline[$t] = 0;
                }
                $timeline[$t]++;
            }
            fclose($fp);
            $ret[$md5sql] = $timeline;
        }
        return $ret;
    }
    private function export_html($values){
        $obj = new Html_Tpl();
        $chunck_list = array_chunk($values, 5);
        $i = 0;
        foreach($chunck_list as $list){
            $x_data = current($list);
            $x_data = array_keys($x_data);
            $obj->addOneChart($x_data, $list);
            $i++;
            if($i >= 2){
                break;
            }
        }
        $ret = $obj->getHtml();
        echo $ret;
    }
    public function run(){
        $outfile = $this->read_file();
        $splitList = $this->split_sigle_sql($outfile);
        $values = $this->split_file($splitList);
        $this->export_html($values);
        exit;
    }
}

$obj = new tSql('/tmp/ice_query_sql_record_fe.sql.2018121123.log', '2018121123');
$obj->run();
