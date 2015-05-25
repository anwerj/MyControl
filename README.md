# mycontrol

MyControl is a simple initiative for PHP Developer who are just tired of using <b> print_r </b> and <b>var_dump</b>
It is mainly and only used in Debugging the app.

You can add this file in you <b style='color:#345'>php.ini</b> file in <b>auto_prepend_file</b> section.
Which will give you control to use code in every of your project. <br>
*Please read about auto_prepend_file and include_path conflict if your application defining include_path somewhere.<br>
To avoid the conflict you can add mycontrol/auto_prepend.php file in one of included path.

# API

<b>/mc:pre()</b><br/>
with various capabilities the function can provide more than print_f and var_dump
PHPDoc is provided with the function.
example:
<br>
<code>
  $var1 = array('MY'=>'Control',21034);
  $var2 = "This is string";
  $class = new \stdClass();
  \mc::pre($var1,$var2,$class,'One more string',['array']);
</code>

<br/>
<b>/mc::js()</b><br>
Print json encoded string for variable .
<br>

<b>/mc::dump($var,$title='')</b> <br>
In case you dont want the variable to print just dump in in a file , edit $log_path variable and you are good to go
<br>

# What else
Well that was just the beginnig <b>mycontrol</b> provides features which are just what a coder need.
You do not pass options to function but provide them in comment like <br>
<code>
  \mc::pre($var1,$var2,$class,'One more string',['array']);#ND
</code>
<br>
will not kill the script in the end , or
<br>
<code>
  \mc::pre($var1,$var2,$class,'One more string',['array']);#ND#VD
</code>
<br>
will use var_dump in place of print_r , also there is no killing this time.
<br>
<br>
There are many more features , you can find simply by PHPDocs provided.

There are some helper functions provided for those who just hate calling a class.
like
<br>
mcpre() same as \mc::pre() ,
<br>
mcprend() same as \mc::pre();#ND
<br>
mcprevd() same as \mc::pre();#VD
<br>
mcjs() same as \mc::js();
<br>
mcjswv() same as \mc::js();#WV
<br>

Hope you get used to it.

