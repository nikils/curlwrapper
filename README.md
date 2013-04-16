Chaninable curl wrapper

Example:
<pre>
<?php
$q = array('q' => 'google');
$c =  new Curl('http://www.google.com/');
$data = $c->verbose()->get($q);
var_dump($data);
?>
</pre>

