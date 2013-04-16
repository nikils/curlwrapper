<?php

class Curl {
	private $handle;
	private $url;
	private $qparams;
	private $headers;
	private $rspheaders;
	private $rspbody;

	public function __construct($url = null)
	{
		if(!empty($url)) {
			$this->url = $url;
		}

		$this->handle = curl_init();
		$this->qparams = array();
		$this->headers = array();
		$this->rspheaders = array();
	}

	public function __call($name, $arguments)
	{
		$const = 'CURLOPT_'.strtoupper($name);
		if(defined($const)) {
			if(empty($arguments)) {
				$this->option(constant($const), true);
			} else {
				$this->option(constant($const), $arguments[0]);
			}
		}
		return $this;
	}

	public function queryParams($params)
	{
		$this->qparams = array_merge($this->qparams, $params);
		return $this;
	}

	public function queryParam($key, $value)
	{
		$this->qparams[$key] = $value;
		return $this;
	}

	public function headers($headers)
	{
		$this->headers = array_merge($this->headers, $headers);
		return $this;
	}

	public function header($header, $value)
	{
		$this->headers[] = $header.': '. $value;
		return $this;
	}

	public function mimetype($type)
	{
		return $this->header('Content-Type', $type);
	}

	public function oauth($value)
	{
		return $this->header('Authorization',  'OAuthSession '.$value);
	}

	public function option($key, $value)
	{
		curl_setopt($this->handle, $key, $value);
		return $this;
	}

	public function get($q = null)
	{
		if(!empty($q)) {
			$this->url .= '?'.http_build_str($q);
		}
		return $this->exec();
	}

	public function post($postdata)
	{
		$this->option(CURLOPT_POST, true)->option(CURLOPT_POSTFIELDS, $postdata);
		return $this->exec();
	}

	public function respHeader($header)
	{
		return $this->rspheaders[$header];
	}

	public function respHeaders($header)
	{
		return $this->rspheaders;
	}

	private function exec()
	{
		if(empty($this->url)) {
			return false;
		}
		$this->option(CURLOPT_URL, $this->url)->option(CURLOPT_HEADER, true)->option(CURLOPT_RETURNTRANSFER, true);
		if(!empty($this->headers)) {
			$this->option(CURLOPT_HTTPHEADER, $this->headers);
		}
		$response = curl_exec($this->handle);

		$pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
		preg_match_all($pattern, $response, $matches);
		$all_headers = array_pop($matches[0]);
		$headers = explode("\r\n", str_replace("\r\n\r\n", '', $all_headers));
		$this->rspbody = str_replace($all_headers, '', $response);

		$status = array_shift($headers);
		preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $status, $matches);
		$this->rspheaders['Http-Version'] = $matches[1];
		$this->rspheaders['Status-Code'] = $matches[2];
		$this->rspheaders['Status'] = $matches[2].' '.$matches[3];

		foreach ($headers as $header) {
			preg_match('#(.*?)\:\s(.*)#', $header, $matches);
			$this->rspheaders[$matches[1]] = $matches[2];
		}
		return $this->rspbody;
	}
}


?>
