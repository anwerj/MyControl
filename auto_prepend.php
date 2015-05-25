<?php

class mc {
    
    protected static $log_path = '/var/www/html/core/log/';
    public static $trace_back = 1;

    /**
     * Prints the variable based on hashtag found
     *
     * <b>\mc::pre($var1,[$var2...]);#VD#NP</b>
     * 
     * @param #NP Disable pre tag
     * @param #VD Use var_dump in place of print_r
     * @param #ND Skip die in the end
     * @param #DW Die without calling function
     */
    public static function pre() {
        $calling = self::calling_line(self::$trace_back);
        $calling_line = $calling['line'];
        $var_array = self::parameter_array('pre', $calling_line);
        
        
        $calling = self::calling_line(1);
        $calling_line = $calling['line'];
        $np = !self::string_has($calling_line, '#NP');
        $vd = self::string_has($calling_line, '#VD');
        
        
        echo self::first_byte();
        
        if(self::$trace_back==1){
            $variables = func_get_args();
        }else{
            $varaibles_array = func_get_args();
            $variables = $varaibles_array[0];
        }
        
        if ($np)
            echo "<pre>";
        
        foreach ($variables as $key => $value) {
            if (isset($var_array[$key])) {
                echo "<h3 style='background-color:#eee;padding: 5px;'>#$key : " . substr($var_array[$key], 0, 40) . "</h3>";
            }
            if ($vd)
                var_dump($value);
            else
                print_r($value);
        }
        if ($np)
            echo "</pre>";
        echo "<h4 style='background-color:#ccc;padding: 5px;'>"
                    . $calling['file']." : ".$calling['line_number'] . "</h4>";
        if (!self::string_has($calling_line, '#ND')) {
            die();
        }
    }

// End of pre
    /**
     * Echo json_encoded variable based on hashtag found
     *
     * <b>\mc::pre($var1,[$var2...]);#WV</b>
     * 
     * @param #WV Returns with arguement's name
     */
    public static function js(){
        $calling = self::calling_line(self::$trace_back);
        $calling_line = $calling['line'];
        $var_array = self::parameter_array('pre', $calling_line);
        
        
        $calling = self::calling_line(1);
        $calling_line = $calling['line'];
        $wv = self::string_has($calling_line, '#WV');
        
        
        if(self::$trace_back==1){
            $variables = func_get_args();
        }else{
            $varaibles_array = func_get_args();
            $variables = $varaibles_array[0];
        }       
        
        if(!$wv){
            echo json_encode($variables);
            die();
        }
        
        $to_encode = [];
        foreach ($variables as $key => $value) {
            if (isset($var_array[$key])) {
                $to_encode[$var_array[$key]]=$value;
            }
            else{
                $to_encode[$key]=$value;
            }
        }
        echo json_encode($to_encode);
        die();
    }


    private static function first_byte(){
        echo "<h3 style='background-color:#333;color:#ddd;padding:10px'>"
        . "Peak Memory: ". self::parse_memory() ." MB"
        . " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        . "Current Usage: ".self::parse_memory(0)." MB"
        . "</h3>";
    }
    
    private static function parse_memory($peak = 1){
        if($peak)
            return round((memory_get_peak_usage()/(1024*1024)), 3);
        else
            return round((memory_get_usage()/(1024*1024)), 3);            
    }
    
    /**
     * Dump a variable using serielize or json_encode to log file
     * <b>Check $log_path first</b>
     * 
     * @param #SE  Uses serielize in place of json_encode
     * @param type $var Variable to Dump
     * @param type $title Title to file
     */

    public static function dump($var,$title='') {
        $calling_line = self::calling_line();
        $file_start = empty($title)?time():$title;
        $filename = $file_start."-".self::clean($calling_line);
        $file = fopen(self::$log_path.$filename, 'w+');
        if(self::string_has($calling_line, '#SE'))
            $toPut = serialize($var);
        else
            $toPut = json_encode ($var);
        fputs($file, $toPut, strlen($toPut));
        fclose($file);
    }

    private static function calling_line($trace_back = 1) {
        $trace = debug_backtrace();
        $back = $trace[$trace_back];
        $line = self::file_line($back['file'], $back['line']);
        return ['line'=>$line,'file'=>$back['file'],'line_number'=>$back['line']];
    }

    private static function file_line($file, $line) {
        $file = fopen($file, 'r');
        for ($i = 1; $i < $line; $i++)
            fgets($file);
        $line = fgets($file);
        return $line;
    }

    public static function clean($text, $l = 240) {
        $text = preg_replace('/[^\\pL\d]+/u', '-', $text);
        // Trim out extra -'s
        $text = trim($text, '-');
        // Convert letters that we have left to the closest ASCII representation
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Make text lowercase
        $text = strtolower($text);
        // Strip out anything we haven't been able to convert
        $text = preg_replace('/[^-\w]+/', '', $text);
        
        $text = substr($text, 0, $l);
        return $text;
    }

    public static function string_has($haystack, $needle) {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
        return false;
    }

    public static function start_with($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function end_with($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    private static function parameter_array($boundry, $string) {
        $str = '';
        if (self::string_has($string, "::" . $boundry)) {
            $str = substr($string, strpos($string, "::" . $boundry) + 2);
        } elseif (self::string_has($string, "->" . $boundry)) {
            $str = substr($string, strpos($string, "->" . $boundry) + 2);
        } else {
            $str = $string;
        }

        $output = array();
        preg_match('~\((.*?)\)\;~', $str, $output);
        if (empty($output[1])) {
            return [];
        }
        $re = "/([a-z]*(?:\\[[^]]*\\]|\\([^()]*\\)),?)|(?<=,)/";
        $output = preg_split($re, $output[1], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($output as $key=>$value){
            if(self::start_with($value, '$'))
                $value=  substr ($value, 1, strlen ($value));
            if(self::end_with($value, ','))
                $value = substr ($value, 0,  strlen ($value)-1);
            
            $output[$key]=$value;
        }
        return $output;
    }

}

/**
 * Print the varaibles and kill the script
 */

function mcpre(){
    $variables = func_get_args();
    \mc::$trace_back = 2;
    \mc::pre($variables);
}
/**
 * Print the variables without killing script
 */
function mcprend(){
    $variables = func_get_args();
    \mc::$trace_back = 2;
    \mc::pre($variables);#ND
}
/**
 * Var_Dump the varaibles and kill the script
 */
function mcprevd(){
    $variables = func_get_args();
    \mc::$trace_back = 2;
    \mc::pre($variables);#VD
}
/**
 * Echo JSON encoded string for variables and kill the script
 */
function mcjs(){
    $variables = func_get_args();    
    \mc::$trace_back = 2;
    \mc::js($variables);
    
}

/**
 * Echo JSON encoded string for variables with variable names and kill the script
 */
function mcjswv(){
    $variables = func_get_args();    
    \mc::$trace_back = 2;
    \mc::js($variables);#WV
    
}
