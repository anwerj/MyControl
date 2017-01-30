<?php

class mc {

    public static $log_path = '/var/www/html/core/log/';
    public static $var_path = '/var/www/html/core/var/';
    public static $config_path = '/var/www/html/core/config/';
    public static $alert_uri = 'http://localhost:3333';
    public static $trace_back = 1;

    /**
     * Prints the variable based on hashtag found
     *
     * <b>mc::pre($var1,[$var2...]);#VD#NP</b>
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

        //header('Content-Type: text/htnl');

        echo self::first_byte();

        if(self::$trace_back==1){
            $variables = func_get_args();
        }else{
            $varaibles_array = func_get_args();
            $variables = $varaibles_array[0];
        }

        if ($np)
            echo "<pre>";
        if(is_array($variables)){
            foreach ($variables as $key => $value) {
                if (isset($var_array[$key])) {
                    echo "<h3 style='background-color:#eee;padding: 5px;'>#$key : " . substr($var_array[$key], 0, 40) . "</h3>";
                }
                if ($vd)
                    var_dump($value);
                else
                    print_r($value);
            }
        }
        else{
                if ($vd)
                    var_dump($variables);
                else
                    print_r($variables);
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
     * <b>mc::pre($var1,[$var2...]);#WV</b>
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

    public static function cache($var,$data){

    }

    public static function read($var,$toArray = 0){
        $filename = self::$var_path.$var.".json";
        $return = '{}';
        if(file_exists($filename)){
            $return =  file_get_contents($filename);
        }
        if($toArray){
            return json_decode($return, true);
        }
        return $return;
    }
    public static function write($var,$data,$mode='w+'){
        $filename = self::$var_path.$var.".json";
        $f = fopen($filename, $mode);
        if(!is_string($data)){
            $data = json_encode($data);
        }
        if(!empty($data) && strlen($data)>10)
            return fputs($f, $data, strlen($data));

    }

    public static function append($var,$data){
        self::write($var, ' ','a+');
        return self::write($var, $data, 'a+');

    }



    public static function alert($text = 'OK!'){

        $calling = self::calling_line(self::$trace_back);
        $calling_line = $calling['line_number'];
        $calling_file = $calling['file'];
        $msg = $text."\n\n".$calling_file." : ".$calling_line;
        $msg = urlencode($msg);

        $sFile = file_get_contents("http://127.0.0.1:3333"."?text=".$msg);
    }

    private static function first_byte(){
        echo "<h3 style='background-color:#333;color:#ddd;padding:10px'>"
        . "Peak Memory: ". self::parse_memory() ." MB"
        . " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        . "Current Usage: ".self::parse_memory(0)." MB"
        . "</h3>";
    }

    public static function parse_memory($peak = 1){
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
        $filename = $file_start."-".self::clean($calling_line['line']);
        $file = fopen(self::$log_path.$filename, 'w+');
        if(self::string_has($calling_line['line'], '#SE'))
            $toPut = serialize($var);
        else
            $toPut = json_encode ($var);
        fputs($file, $toPut, strlen($toPut));
        fclose($file);
    }

    public static function calling_line($trace_back = 1,$die=0) {
        $trace = debug_backtrace();
        $back = $trace[$trace_back];
        $line = self::file_line($back['file'], $back['line']);
        $return = ['line'=>$line,'file'=>$back['file'],'line_number'=>$back['line']];
        if(empty($die))
          return $return;
        else
          self::pre ($return);
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

    public static function dot($array, $prepend = '',$dot='.')
	{
		$results = [];

		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$results = array_merge($results, static::dot($value, $prepend.$key.$dot,$dot));
			}
			else
			{
				$results[$prepend.$key] = $value;
			}
		}

		return $results;
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
    mc::$trace_back = 2;
    mc::pre($variables);
}
/**
 * Print the variables without killing script
 */
function mcprend(){
    $variables = func_get_args();
    mc::$trace_back = 2;
    mc::pre($variables);#ND
}
/**
 * Var_Dump the varaibles and kill the script
 */
function mcprevd(){
    $variables = func_get_args();
    mc::$trace_back = 2;
    mc::pre($variables);#VD
}
/**
 * Echo JSON encoded string for variables and kill the script
 */
function mcjs(){
    $variables = func_get_args();
    mc::$trace_back = 2;
    mc::js($variables);

}

/**
 * Echo JSON encoded string for variables with variable names and kill the script
 */
function mcjswv(){
    $variables = func_get_args();
    mc::$trace_back = 2;
    mc::js($variables);#WV

}

/**
 * Alert text with Ubutnu's notify-send
 */
function mcalert($text = 'OK'){
    mc::$trace_back = 2;
    mc::alert($text);#WV

}

/*
 *  Override PHP functions
 *  Comment these lines if you dont have runkit install
 *  These will save all outgoing curl requests.


 @runkit_function_remove('go_curl');
 runkit_function_rename('curl_exec','go_curl');
 runkit_function_add('curl_exec','$ch','return handle_curl($ch);');


function handle_curl($ch){

    $info = curl_getinfo($ch);
    $url = mc::clean($info['url']);
    $filename = mc::$var_path.'curl/'.$url.'.curl';

    if(mc::string_has($filename, 'localhost')){
      $response = go_curl($ch);
    }elseif(!file_exists($filename) || CURL_FORCE ){

      $file = fopen($filename, 'w+');
      $response = go_curl($ch);
      if($response){
        // CASE GOT RESPONSE
        fputs($file, $response, strlen($response));
      }else{
        // CASE NO RESPONSE

      }

    }else{

      $response = file_get_contents($filename);

    }

    return $response;
 }

 function initConfig(){
   $file = fopen(mc::$config_path.'config.cnf','a+');
   while(1){
     $line = fgets($file);
     $array = explode('=', $line);
     if(!isset($array[1])){
       break;
     }

     runkit_constant_add(strtoupper($array[0]),$array[1]);
   }

 }

 function initNetwork(){
  // If Internet Not Wo
//   if(!INTERNET_WORKING){
//     $content = @file_get_contents('https://www.googleapis.com/plus/v1/people');
//     if($content){
//       runkit_constant_add('INTERNET_WORKING',1);
//       fopen(mc::$config_path.'force.curl', 'w+');
//     }
//   }
 }
 initConfig();
 */