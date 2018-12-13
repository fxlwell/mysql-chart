<?php
class Html_Tpl {
    private $html = '<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>HTML5 Canvas折线图表和柱形图表DEMO演示</title>
        <script type="text/javascript" src="jQuery.js"></script>
        <script type="text/javascript" src="jqplot.js"></script>
        </head>
        <body>
<div id="chart1"></div>
<div id="chart2"></div>

<script type="text/javascript">

var data = [[1,2,3,4,5,6,7,8,9],[1,2,3,4,5,6,7,8,9]];
var x = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16]; //定义X轴刻度值
var data_max = 30; //Y轴最大刻度
var line_title = ["A","B"]; //曲线名称

var y_label = "请求次数"; //Y轴标题
var x_label = "时间（分钟）"; //X轴标题
var title = "时间分布"; //统计图标标题
j.jqplot.diagram.base("chart1", data, line_title, "SQL", x, x_label, y_label, data_max, 1);
j.jqplot.diagram.base("chart2", data, line_title, "SQL", x, x_label, y_label, data_max, 1);

</script>
</body>
</html>';
    
    private $static_header = '<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>HTML5 Canvas折线图表和柱形图表DEMO演示</title>
        <script type="text/javascript" src="jQuery.js"></script>
        <script type="text/javascript" src="jqplot.js"></script>
        </head>
        <body>';
    private $div_count = 0;
    private $static_scripts = '<script type="text/javascript">';
    private $static_footer = '</script></body></html>';
    private $charDiv = '';
    private $scritps = '
        var y_label = "请求次数"; //Y轴标题
        var x_label = "时间（分钟）"; //X轴标题
        var title = "时间分布"; //统计图标标题
        ';

    private function setCharDiv(){
        $this->charDiv = $this->charDiv . "<div id='chart{$this->div_count}'></div>\n";
        $this->div_count++;
        return $this->div_count - 1;
    }
    private function setScripts($id, $xdata, $ydata){
        $var_x = "var x = [";
        foreach($xdata as $xd){
            $var_x = $var_x . "\"$xd\",";
        }
        $var_x = substr($var_x, 0, strlen($var_x) - 1);
        $var_x = $var_x . "];\n";

        $var_y = "var y = ["; 
        $var_t = "var t = [";
        $var_max = "var data_max = ";
        $d_max = 0;
        foreach($ydata as $title => $value){
            $tmp_y = "[".implode(',', $value)."],";
            $var_y = $var_y . $tmp_y;
            $tmp_array = array_values($value);
            $tmp_array[] = $d_max;
            $d_max = max($tmp_array);
            $var_t = $var_t . "'$title',";
        }

        $var_y = substr($var_y, 0, strlen($var_y) - 1); 
        $var_y = $var_y . "];\n";
        $var_t = substr($var_t, 0, strlen($var_t) - 1);
        $var_t = $var_t . "];\n";
        $var_max = $var_max . "$d_max;\n";
        $s_s = "j.jqplot.diagram.base('chart$id', y, t, 'SQL', x, x_label, y_label, data_max, 1);";
        $this->scritps = $this->scritps . $var_x . $var_y . $var_t . $var_max . $s_s . "\n";
    }
    public function addOneChart($x_data, $y_data){
        $id = $this->setCharDiv(); 
        $this->setScripts($id, $x_data, $y_data);
    }

    public function getHtml(){
        $html = $this->static_header . $this->charDiv . $this->static_scripts . $this->scritps . $this->static_footer;
        return $html;
    }
}
/*
$obj = new Html_Tpl();
$obj->addOneChart(array(1,2,3,4,5,6,7,8,9,10), array(
    'one' =>      array(2,4,6,8,10,12,14,16,18,20),
    'two' =>      array(2,4,6,1,1,2,14,16,48,20),
    'three' =>      array(2,4,6,1,10,2,14,1,12,50),
));
$obj->addOneChart(array(1,2,3,4,5,6,7,8,9,10), array(
    'four' =>      array(2,4,6,8,10,12,14,16,18,20),
    'five' =>      array(2,4,6,1,1,2,14,16,48,20),
    'six' =>      array(2,4,6,1,10,2,14,1,12,50),
));

$ret = $obj->getHtml();
echo $ret;
*/
