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


	private function menu($req, $resp, $args)
	{
		$html = "
			<li>
				<a href='/'>Accueil</a>
			</li>
		";

		// Vérifier l'authentification pour afficher la connexion/inscription ou le profil
		if(isset($_SESSION["login"]))
		{
			$html .= "
				<li>
					<a href='/profil'>Profil</a>
				</li>
			";
		}
		else
		{
			$html .= "
				<li>
					<a href='/connexion'>Connexion</a>
				</li>
				<li>
					<a href='/inscription'>Inscription</a>
				</li>
			";
		}

		return $html;
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
					<script src='js/jquery.min.js'></script>
					<script src='js/script.js'></script>
					<script src='js/coche.js'></script>
					<link rel='stylesheet' type='text/css' href='css/style.css'/>
				</head>
				<body>
					<header>
						<h1>
							Quizzbox
						</h1>
					</header>
					<ul id='menu'>
						".$this->menu($req, $resp, $args)."
					</ul>

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

		$moyenneDifficulte = $cumulCoefficients / (\quizzbox\model\question::where('id_quizz', $quizz->id)->count());

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
						<b>Description : </b>
						".$uneCategorie->description."
					</p>
					<p>
						<b>Nombre de quizz : </b>
						".\quizzbox\model\quizz::where('id_categorie', $uneCategorie->id)->count()."
					</p>
					<a class='button' href='./categories/".$uneCategorie->id."'>
						Consulter les quizz
					</a>
				</li>
			";
		}
		$html .= "</ul>";

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
							";

			if(isset($_SESSION["login"]))
			{
				if($_SESSION["login"] == "admin")
				{
					$html .= "
							<li>
								<form method='post' action='./quizz/".$unQuizz->id."/supprimer/'>
									<button type='submit'>Supprimer le quizz</button>
								</form>
							</li>
					";
				}
			}

			$html .= "
						</ul>
					</p>
				</li>
			";
		}
		$html .= "</ul>";

		return $html;
	}

	private function connexionForm($req, $resp, $args) {
		$html = <<<EOT
		<form method="post" action="connexion">
			<p><label for="pseudo">Pseudo :</label> <input type="text" name="pseudo" maxlength="255" value="{$args['pseudo']}" required/></p>
			<p><label for="mdp">Mot de passe :</label> <input type="password" name="mdp" maxlength="255" required/></p>
			<p><input type="submit" value="Connexion" /></p>
		</form>
EOT;
		return $html;
	}

	private function connexionTraitement($req, $resp, $args) {
		return 'Vous êtes connecté ! Redirection...';
	}

	private function inscriptionForm($req, $resp, $args) {
		$html = <<<EOT
		<form method="post" action="inscription">
			<p><label for="pseudo">Pseudo :</label> <input type="text" name="pseudo" maxlength="255" value="{$args['pseudo']}" required/></p>
			<p><label for="email">E-mail :</label> <input type="email" name="email" maxlength="256" required/></p>
			<p><label for="mdp">Mot de passe :</label> <input type="password" name="mdp" maxlength="255" required/></p>
			<p><label for="mdpconfirm">Confirmation :</label> <input type="password" name="mdpconfirm" maxlength="255" required/></p>
			<p><input type="submit" value="Inscription" /></p>
		</form>
EOT;
		return $html;
	}

	private function inscriptionTraitement($req, $resp, $args) {
		return 'Inscription effectuée avec succès !';
	}

	private function creer($req, $resp, $args) {
		return;
	}


	// -----------

	public function render($selector, $req, $resp, $args)
	{
		$html = $this->header($req, $resp, $args);

		switch($selector)
		{
			case "afficherCategories":
				$html .= $this->afficherCategories($req, $resp, $args);
				break;
			case "afficherQuizz":
				$html .= $this->afficherQuizz($req, $resp, $args);
				break;
			case "connexionForm":
				$html .= $this->connexionForm($req, $resp, $args);
				break;
			case "connexionTraitement":
				$html .= $this->connexionTraitement($req, $resp, $args);
				break;
			case "inscriptionForm":
				$html .= $this->inscriptionForm($req, $resp, $args);
				break;
			case "inscriptionTraitement":
				$html .= $this->inscriptionTraitement($req, $resp, $args);
				break;
			case "creer":
				$html .= $this->creer($req, $resp, $args);
				break;
		}

		$html .= $this->footer($req, $resp, $args);

		$resp->getBody()->write($html);
		return $resp;
	}
}
