# mycontrol

MyControl is a simple initiative for PHP Developer who are just tired of using <b> print_r </b> and <b>var_dump</b>
It is mainly and only used in Debugging the app.

You can add this file in you <b style='color:#345'>php.ini</b> file in <b>auto_prepend_file</b> section.
Which will give you control to use code in every of your project.
*Please read about auto_prepend_file and include_path conflict if your application defining include_path somewhere.<br>
To avoid the conflict you can add mycontrol/auto_prepend.php file in one of included path.

# API

<b>/mc:pre()</b><br>
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


