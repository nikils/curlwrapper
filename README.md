Chaninable curl wrapper

Example:

<?php
$q = array('q' => 'google');
$c =  new Curl('http://www.google.com/');
$data = $c->verbose()->get($q);
var_dump($data);
?>

