<?php
namespace quizzbox\view;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class quizzboxview
{
	protected $data = null ;

    public function __construct($data)
	{
        $this->data = $data;
    }

	private function getStatus() {
		if(array_key_exists('status', $this->data)) {
			if(is_numeric($this->data['status'])) {
				$status = $this->data['status'];
				unset($this->data['status']);
				return $status;
			}
		}
		return 400;
	}

	
	private function header($req, $resp, $args)
	{
		$html = "
			<!DOCTYPE html>
			<html lang='fr'>
				<head>
					<meta charset='UTF-8'>
					<meta name='viewport' content='width=device-width, initial-scale=1'>
					<title>Quizzbox</title>
					<script src='jquery.min.js'></script>
					<script src='script.js'></script>
					<link rel='stylesheet' type='text/css' href='css/style.css'/>
				</head>
				<body>
					<header>
						<h1>
							Quizzbox
						</h1>
					</header>
					
		";
		if(isset($_SESSION["message"]))
		{
			$html .= "<div id='message'>".filter_var($_SESSION["message"], FILTER_SANITIZE_FULL_SPECIAL_CHARS)."</div>";
			unset($_SESSION["message"]);
		}
		$html .= "
					
					<div id='content'>
		";
		
		return $html;
	}
	
	private function footer($req, $resp, $args)
	{
		$html = "
					</div>
					<footer>
						Quizzbox
					</footer>
				</body>
			</html>
		";
		
		return $html;
	}
	
	
	// -----------
	
	
    private function exemple($req, $resp, $args)
	{
		$html = "";
		return $html;
    }

	public function render($selector, $req, $resp, $args)
	{
		$html = $this->header($req, $resp, $args);
		
		switch($selector)
		{
			case "exemple":
				$this->resp = $this->exemple($req, $resp, $args);
				break;
		}
		
		$html .= $this->footer($req, $resp, $args);
		
		$resp->getBody()->write($html);
		return $resp;
	}
}
