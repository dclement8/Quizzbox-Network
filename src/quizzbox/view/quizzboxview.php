<?php
namespace quizzbox\view;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class quizzboxview
{
	protected $data = null ;
	protected $baseURL = null;

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
				<a href='".$this->baseURL."'>Accueil</a>
			</li>
		";

		// Vérifier l'authentification pour afficher la connexion/inscription ou le profil
		if(isset($_SESSION["login"]))
		{
			$html .= "
				<li>
					<a href='".$this->baseURL."/profil'>Profil</a>
				</li>
				<li>
					<a href='".$this->baseURL."/connexion'>Déconnexion</a>
				</li>
			";
		}
		else
		{
			$html .= "
				<li>
					<a href='".$this->baseURL."/connexion'>Connexion</a>
				</li>
				<li>
					<a href='".$this->baseURL."/inscription'>Inscription</a>
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
					<link rel='stylesheet' type='text/css' href='".$this->baseURL."/css/style.css'/>
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
					<script src='".$this->baseURL."/js/jquery.min.js'></script>
					<script src='".$this->baseURL."/js/main.js'></script>
					<script src='".$this->baseURL."/js/coche.js'></script>
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

		$moyenneDifficulte = 0;
		if(\quizzbox\model\question::where('id_quizz', $quizz->id)->count() != 0)
		{
			$moyenneDifficulte = $cumulCoefficients / (\quizzbox\model\question::where('id_quizz', $quizz->id)->count());
		}

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
					<a class='button' href='".$this->baseURL."/categories/".$uneCategorie->id."'>
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
							<li>
								<form method='get' action='".$this->baseURL."/quizz/".$unQuizz->tokenWeb."/download'>
									<button type='submit'>Télécharger le quizz</button>
								</form>
							</li>";

			if(isset($_SESSION["login"]))
			{
				if($_SESSION["login"] == "admin")
				{
					$html .= "
							<li>
								<form method='post' action='".$this->baseURL."/quizz/".$unQuizz->id."/supprimer'>
									<button type='submit'>Supprimer le quizz</button>
								</form>
							</li>
					";
				}
			}

			$html .= "
						</ul>
					</p>
					<h2>Classement des 10 meilleurs joueurs :</h2>
					<table class='classement'>
						<tr>
							<th>Position</td>
							<th>Joueur</td>
							<th>Score</td>
						</tr>
						";

			$scores = \quizzbox\model\quizz::find($unQuizz->id)->scores()->orderBy('score', 'DESC')->take(10)->get();
			$position = 1;
			foreach($scores as $unScore)
			{
				$html .= "
					<tr>
						<td>".$position."</td>
						<td>".\quizzbox\model\joueur::find($unScore->pivot->id_joueur)->first()->pseudo."</td>
						<td>".$unScore->pivot->score."</td>
					</tr>
				";
				$position++;
			}


			$html .= "
					</table>
			";

			if(isset($_SESSION["login"]))
			{
				if(\quizzbox\model\joueur::find($_SESSION["login"])->scores()->where("id_quizz", $unQuizz->id)->count() > 0)
				{
					$scores = \quizzbox\model\joueur::find($_SESSION["login"])->scores()->where("id_quizz", $unQuizz->id)->first();

					$html .= "<p>
						<b>Votre score sur ce quizz est de : </b>".$scores->pivot->score."
					</p>";
				}
			}

			$html .= "
				</li>
			";


		}
		$html .= "</ul>";

		return $html;
	}

	private function afficherProfil($req, $resp, $args)
	{
		$scores = \quizzbox\model\joueur::find($this->data->id)->scores()->orderBy('dateHeure', 'DESC')->get();

		// Récupérer le nombre de quizz joués par le joueur
		$nbQuizz = \quizzbox\model\joueur::find($this->data->id)->scores()->count();

		// Calcul du niveau moyen du joueur
		/*
			Le joueur doit avoir joué à au moins 5 quizz pour que l'on puisse déterminer son niveau.

			Méthode opératoire :
				- Comparer la somme des coefficients des quizz joués à la somme des scores du joueur
				- Si le cumul des scores du joueur est < 1/4 de la somme des coefficients des quizz joués alors niveau = Faible ; < 2/4 = Moyen ; < 3/4 = Bon ; <= 4/4 = Champion ; au delà c'est un tricheur X)
		*/
		$niveauJoueur = "Indéterminé";
		if($nbQuizz >= 5)
		{
			$cumulCoefficientsQuizzJoues = 0; // Etant le score maximal qu'il est possible d'obtenir
			foreach ($scores as $unScore)
			{
				$questions = \quizzbox\model\question::where('id_quizz', $unScore->pivot->id_quizz)->get();

				foreach($questions as $uneQuestion)
				{
					$cumulCoefficientsQuizzJoues += $uneQuestion->coefficient;
				}
			}

			$cumulScoreJoueur = 0;
			foreach ($scores as $unScore)
			{
				$cumulScoreJoueur += $unScore->pivot->score;
			}

			$niv = $cumulScoreJoueur / $cumulCoefficientsQuizzJoues;
			if($niv < (1/4))
			{
				$niveauJoueur = "Faible";
			}
			else
			{
				if($niv < (2/4))
				{
					$niveauJoueur = "Moyen";
				}
				else
				{
					if($niv < (3/4))
					{
						$niveauJoueur = "Bon";
					}
					else
					{
						if($niv <= 1)
						{
							$niveauJoueur = "Champion";
						}
						else
						{
							/* Faut aussi penser à tout X) ! . */
							$niveauJoueur = "Tricheur";
						}
					}
				}
			}
		}

		// Déterminer la catégorie de quizz la plus jouée par le joueur
		$categories = array();
		foreach ($scores as $unScore)
		{
			$categories[] = \quizzbox\model\quizz::find($unScore->pivot->id_quizz)->first()->id_categorie;
		}
		$nbCategories = array_count_values($categories);
		$leMax = 0;
		$idCategoriePlusJouee = 0;
		foreach ($nbCategories as $uneCategorie => $nbFoisJouee)
		{
			if($leMax < $nbFoisJouee)
			{
				$leMax = $nbFoisJouee;
				$idCategoriePlusJouee = $uneCategorie;
			}
		}
		$categoriePredilection = \quizzbox\model\categorie::find($idCategoriePlusJouee)->first();



		$html = "
			<ul class='profil'>
				<li>
					<b>".$this->data->pseudo."</b>
				</li>
				<li>
					<b>Inscrit le : </b>".$this->data->dateInscription."
				</li>
				<li>
					".$nbQuizz."<b> quizz joué(s)</b>
				</li>
				<li>
					<b>Niveau moyen du joueur : </b>".$niveauJoueur."
				</li>
				<li>
					<b>Dernier quizz joué : </b>".\quizzbox\model\quizz::find($scores[0]->pivot->id_quizz)->first()->nom.", <b>le :</b> ".$scores[0]->pivot->dateHeure."
				</li>
				<li>
					<b>Domaine de prédilection : </b><a href='".$this->baseURL."/categories/".$categoriePredilection->id."'>".$categoriePredilection->nom."</a>
				</li>";

		// Supprimer l'utilisateur
		if(isset($_SESSION["login"]))
		{
			if($_SESSION["login"] == "admin")
			{
				$html .= "
				<li>
					<form method='post' action='".$this->baseURL."/profil/".$this->data->id."/supprimer/'>
						<button type='submit'>Supprimer le joueur</button>
					</form>
				</li>";
			}
		}

		$html .= "
			</ul>
		";

		return $html;
	}

	private function connexionForm($req, $resp, $args) {
		$html = <<<EOT
	<form method="post" action="{$this->baseURL}/connexion">
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
		<form method="post" action="{$this->baseURL}/inscription">
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
		// Les questions et réponses sont stockées dans le input json avec le format JSON, voir main.js
		$html = <<<EOT
		<form method="post" id="formulaire" action="{$this->baseURL}/creer">
			<input type="hidden" name="json" id="json" />
			<p><label for="nom">Nom du quizz :</label> <input type="text" name="nom" maxlength="255" value="" required/></p>
			<p><label for="categorie">Catégorie :</label>
			<select name="categorie">
EOT;
		foreach($this->data as $categorie) {
			$html .= '<option>'.$categorie->nom.'</option>';
		}
		$html .= <<<EOT
			</select></p>
			<hr />
			<h3>Questions</h3>

			<div id="questions">
			</div>

			<p><input type="button" value="Ajouter une question" onclick="creer.ajouterQuestion()" /> <input type="button" value="Créer" onclick="creer.envoyer()" /></p>
		</form>
EOT;
		return $html;
	}

	private function getQuizzJSON($req, $resp, $args)
	{
		$json = "";

		if(is_array($this->data))
		{
			$json = json_encode($this->data);
			$resp = $resp->withHeader('Content-Type', 'application/json');
		}
		else
		{
			$json = $this->data;
			$resp = $resp->withStatus(200)->withHeader('Content-Type', 'application/json');
		}

		$resp->getBody()->write($json);
		return $resp;
	}


	// -----------

	public function render($selector, $req, $resp, $args)
	{
		$this->baseURL = $req->getUri()->getBasePath();

		$html = $this->header($req, $resp, $args);

		// Sélectionne automatiquement le sélecteur.
		$html .= $this->$selector($req, $resp, $args);

		/*switch($selector)
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
		}*/

		$html .= $this->footer($req, $resp, $args);

		$resp->getBody()->write($html);
		return $resp;
	}
}
