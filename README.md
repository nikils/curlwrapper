Chainable curl wrapper

Example:
<pre>
$q = array('q' => 'google');
$c =  new Curl('http://www.google.com/');
$data = $c->verbose()->get($q);
var_dump($data);

</pre>

