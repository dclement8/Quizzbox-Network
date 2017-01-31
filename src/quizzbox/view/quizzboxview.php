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
	
	
	/* private function exemple($req, $resp, $args)
	{
		$html = "";
		return $html;
    }*/
	
	private function calculDifficulteQuizz($quizz)
	{
		// Un coefficient d'une question peut avoir comme valeur : 1, 2, 3, 4 ou 5
		// Plus le coefficient est élevé, plus la question est difficile.
		
		$questions = \quizzbox\model\question::where('id_quizz', $quizz->id)->get();
		
		$cumulCoefficients = 0;
		foreach($questions as $uneQuestion)
		{
			$cumulCoefficients = $cumulCoefficients + $uneQuestion->coefficient;
		}
		
		$moyenneDifficulte = $cumulCoefficients / (\quizzbox\model\question::where('id_quizz', $unQuizz->id)->count());
		
		$difficulte = "Facile"; // moyenneDifficulte < 2
		if($moyenneDifficulte >= 2)
		{
			if($moyenneDifficulte >= 3)
			{
				if($moyenneDifficulte >= 4)
				{
					$difficulte = "Très difficile";
				}
				else
				{
					$difficulte = "Difficile";
				}
			}
			else
			{
				$difficulte = "Moyen";
			}
		}
		
		return $difficulte;
	}
	
	
	private function afficherCategories($req, $resp, $args)
	{
		$html = "<ul class='elements'>";
		foreach($this->data as $uneCategorie)
		{
			$html .= "
				<li class='block'>
					<h1>
						".$uneCategorie->nom."
					</h1>
					<p>
						".$uneCategorie->description."
					</p>
					<a class='button' href='./categories/".$uneCategorie->id."'>
						Consulter les quizz
					</a>
				</li>
			";
		}
		$html = "</ul>";
		
		return $html;
	}
	
	private function afficherQuizz($req, $resp, $args)
	{
		$html = "<ul class='elements'>";
		foreach($this->data as $unQuizz)
		{
			$html .= "
				<li class='block'>
					<h1>
						".$unQuizz->nom."
					</h1>
					<p>
						<b>Détails :</b>
						<ul>
							<li>
								<b>Nombre de questions : </b>
								".\quizzbox\model\question::where('id_quizz', $unQuizz->id)->count()."
							</li>
							<li>
								<b>Difficulté évaluée : </b>
								".$this->calculDifficulteQuizz($unQuizz)."
							</li>
						</ul>
					</p>
				</li>
			";
		}
		$html = "</ul>";
		
		return $html;
	}

	
	// -----------
	
	public function render($selector, $req, $resp, $args)
	{
		$html = $this->header($req, $resp, $args);
		
		switch($selector)
		{
			case "afficherCategories":
				$this->resp = $this->afficherCategories($req, $resp, $args);
				break;
			case "afficherQuizz":
				$this->resp = $this->afficherQuizz($req, $resp, $args);
				break;
		}
		
		$html .= $this->footer($req, $resp, $args);
		
		$resp->getBody()->write($html);
		return $resp;
	}
}
