<?php
namespace quizzbox\control;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \quizzbox\AppInit;

// Connexion à la BDD
$connexion = new AppInit();
$connexion->bootEloquent("./conf/config.ini");

class quizzboxcontrol
{
    protected $c=null;

    public function __construct($c)
	{
        $this->c = $c;
    }


    /*public function exemple(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\view\quizzboxview(null))->render('exemple', $req, $resp, $args);
    }*/

	public function afficherCategories(Request $req, Response $resp, $args)
	{
		$categories = \quizzbox\model\categorie::orderBy('nom')->get();

		return (new \quizzbox\view\quizzboxview($categories))->render('afficherCategories', $req, $resp, $args);
    }

	public function afficherQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		$quizz = \quizzbox\model\quizz::where('id_categorie', $id)->orderBy('nom')->get();

		return (new \quizzbox\view\quizzboxview($quizz))->render('afficherQuizz', $req, $resp, $args);
    }

	public function accueil(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\control\quizzboxcontrol($this))->afficherCategories($req, $resp, $args);
    }

    public function connexionForm(Request $req, Response $resp, $args) {
        $args['pseudo'] = '';
		if(isset($_SESSION["login"]))
		{
			// Déconnexion et destruction de tous les éléments de session
			unset($_SESSION);
            session_destroy();

			$_SESSION["message"] = "Vous êtes à présent déconnecté !";
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
		else
		{
			return (new \quizzbox\view\quizzboxview($this))->render('connexionForm', $req, $resp, $args);
		}
    }

    public function connexionTraitement(Request $req, Response $resp, $args) {
        $args['pseudo'] = '';

        if(isset($_POST['pseudo']))
            $args['pseudo'] = filter_var($_POST['pseudo'], FILTER_SANITIZE_STRING);
        if(isset($_POST['mdp']))
            $mdp = $_POST['mdp'];

        if(!empty($args['pseudo']) && !empty($mdp)) {
			if($args['pseudo'] === "admin")
			{
				if($mdp === "174086")
				{
					$_SESSION["login"] = "admin";
					$_SESSION["message"] = 'Vous êtes connecté en tant qu\'administrateur !';
					return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
				}
				else
				{
					$_SESSION["message"] = 'Mot de passe incorrect !';
					return (new \quizzbox\view\quizzboxview($this))->render('connexionForm', $req, $resp, $args);
				}
			}
			else
			{
				$joueur = \quizzbox\model\joueur::where('pseudo', '=', $args['pseudo'])->first();
				if($joueur !== null && $joueur !== false) {
					if(password_verify(hash("sha256", $mdp), $joueur->motdepasse)) {
						$_SESSION["login"] = $joueur->id;
						$_SESSION["message"] = 'Vous êtes connecté !';
						return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
					}
					else
					{
						$_SESSION["message"] = 'Mot de passe incorrect !';
						return (new \quizzbox\view\quizzboxview($this))->render('connexionForm', $req, $resp, $args);
					}
				}
				else
				{
					$_SESSION["message"] = 'Joueur inexistant !';
					return (new \quizzbox\view\quizzboxview($this))->render('connexionForm', $req, $resp, $args);
				}
			}
        }
        else
		{
			return (new \quizzbox\view\quizzboxview($this))->render('connexionForm', $req, $resp, $args);
		}
    }

    public function inscriptionForm(Request $req, Response $resp, $args) {
        $args['pseudo'] = '';
        $args['email'] = '';
		return (new \quizzbox\view\quizzboxview($this))->render('inscriptionForm', $req, $resp, $args);
    }

    public function inscriptionTraitement(Request $req, Response $resp, $args) {
        $args['pseudo'] = '';
        $args['email'] = '';

        if(isset($_POST['pseudo']))
            $args['pseudo'] = filter_var($_POST['pseudo'], FILTER_SANITIZE_STRING);
        if(isset($_POST['email']))
            $args['email'] = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        if(isset($_POST['mdp']))
            $mdp = $_POST['mdp'];
        if(isset($_POST['mdpconfirm']))
            $mdpconfirm = $_POST['mdpconfirm'];

        if(!empty($args['pseudo']) && !empty($args['email']) && !empty($mdp) && !empty($mdpconfirm)) {
            if(strlen($args['pseudo']) > 2) {
                if(strlen($args['pseudo']) < 256) {
                    if(strlen($args['email']) > 5) {
                        if(strlen($args['email']) < 256) {
                            if(!filter_var($args['email'], FILTER_VALIDATE_EMAIL) === false) {
                                if(\quizzbox\model\joueur::where('pseudo', '=', $args['pseudo'])->count() == 0) {
                                    if(\quizzbox\model\joueur::where('email', '=', $args['email'])->count() == 0) {
                                        if($mdp == $mdpconfirm) {
                                            if(strlen($mdp) > 5) {
                                                /* Bien ! . */
                                                $mdp = password_hash(hash("sha256", $mdp), PASSWORD_BCRYPT);
                                                $user = new \quizzbox\model\joueur();
                                                $user->pseudo = $args['pseudo'];
                                                $user->motdepasse = $mdp;
                                                $user->email = $args['email'];
												$user->dateInscription = date("Y-m-d H:i:s");
                                                $user->save();
                                                return (new \quizzbox\view\quizzboxview($this))->render('inscriptionTraitement', $req, $resp, $args);
                                            }
                                            else
                                                $_SESSION["message"] = 'Mot de passe trop court !';
                                        }
                                        else
                                            $_SESSION["message"] = 'Les mots de passes sont différents !';
                                    }
                                    else
                                        $_SESSION["message"] = 'Email déjà pris !';
                                }
                                else
                                    $_SESSION["message"] = 'Pseudo déjà pris !';
                            }
                            else
                                $_SESSION["message"] = 'E-mail invalide !';
                        }
                        else
                            $_SESSION["message"] = 'E-mail trop long !';
                    }
                    else
                        $_SESSION["message"] = 'E-mail trop court !';
                }
                else
                    $_SESSION["message"] = 'Pseudo trop long !';
            }
            else
                $_SESSION["message"] = 'Pseudo trop court !';
        }
        // S'il y a une/des erreurs, on affiche à nouveau le formulaire
        return (new \quizzbox\view\quizzboxview($this))->render('inscriptionForm', $req, $resp, $args);
    }

    public function creer(Request $req, Response $resp, $args)
	{
        $data['json'] = '';
        $data['categories'] = \quizzbox\model\categorie::orderBy('nom', 'ASC')->get();
		return (new \quizzbox\view\quizzboxview($data))->render('creer', $req, $resp, $args);
    }

    public function creerTraitement(Request $req, Response $resp, $args)
	{
        // TODO sécuriser variables et vérifier données
        $data['json'] = '';
        $data['categories'] = \quizzbox\model\categorie::orderBy('nom', 'ASC')->get();

        if(isset($_POST['json'])) {
            $json = json_decode($_POST['json']);
            if($json !== null) {
                $data['json'] = $_POST['json'];
                if(\quizzbox\model\categorie::where('id', '=', $json->quizz->id_categorie)->count() == 1) {
                    $quizz = new \quizzbox\model\quizz();
                    $quizz->nom = $json->quizz->nom;
                    $quizz->id_categorie = $json->quizz->id_categorie;
                    $factory = new \RandomLib\Factory;
                    $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
                    $quizz->tokenWeb = $generator->generateString(32, 'abcdefghijklmnopqrstuvwxyz0123456789');
                    $quizz->save();

                    foreach($json->questions as $q) {
                        $question = new \quizzbox\model\question();
                        $question->enonce = $q->enonce;
                        $question->coefficient = $q->coefficient;
                        $question->id_quizz = $quizz->id;
                        $question->save();

                        foreach($q->reponses as $r) {
                            $reponse = new \quizzbox\model\reponse();
                            $reponse->nom = $r->nom;
                            $reponse->estSolution = $r->estSolution;
                            $reponse->id_question = $question->id;
                            $reponse->id_quizz = $quizz->id;
                            $reponse->save();
                        }
                    }
                    $args['id'] = $quizz->id_categorie;
                    return (new \quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
                }
            }
        }
        // S'il y a un problème avec le JSON envoyé, on affiche à nouveau le formulaire
		return (new \quizzbox\view\quizzboxview($data))->render('creer', $req, $resp, $args);
    }

    public function modifierQuizz(Request $req, Response $resp, $args)
	{
        $args['without_headers'] = true;
        $data['json'] = $this->getQuizzJSON($req, $resp, $args);
        $data['categories'] = \quizzbox\model\categorie::orderBy('nom', 'ASC')->get();
        if($data != '{"error":"quizz introuvable !"}') {
		    return (new \quizzbox\view\quizzboxview($data))->render('modifierQuizz', $req, $resp, $args);
        }
    }

	public function supprimerQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if(\quizzbox\model\quizz::where('id', $id)->get()->toJson() != "[]")
		{
			\quizzbox\model\reponse::where('id_quizz', $id)->delete();
			\quizzbox\model\question::where('id_quizz', $id)->delete();
			\quizzbox\model\quizz::find($id)->scores()->detach();
			\quizzbox\model\quizz::destroy($id);

			$_SESSION["message"] = 'Quizz supprimé';
		}
		else
		{
			$_SESSION["message"] = 'Quizz introuvable';
		}


		return (new \quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
	}

	public function supprimerJoueur(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if(\quizzbox\model\joueur::where('id', $id)->get()->toJson() != "[]")
		{
			\quizzbox\model\joueur::find($id)->scores()->detach();
			\quizzbox\model\joueur::destroy($id);
			$_SESSION["message"] = 'Joueur supprimé';
		}
		else
		{
			$_SESSION["message"] = 'Joueur introuvable';
		}

		return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
	}

	public function afficherProfil(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if(\quizzbox\model\joueur::where('id', $id)->get()->toJson() != "[]")
		{
			$joueur = \quizzbox\model\joueur::find($id);
			return (new \quizzbox\view\quizzboxview($joueur))->render('afficherProfil', $req, $resp, $args);
		}
		else
		{
			/* Oups ! . */
			$_SESSION["message"] = 'Ce joueur n\'existe pas !';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}

	public function getQuizz(Request $req, Response $resp, $args)
	{
		// Retourne une représentation JSON du Quizz passé en paramètre (via le token).

		$id = filter_var($args['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS); // ID = Token
		if(\quizzbox\model\quizz::where('tokenWeb', $id)->get()->toJson() != "[]")
		{
			$quizz = \quizzbox\model\quizz::where('tokenWeb', $id)->get();
			$idQuizz = $quizz[0]->id;
			$questions = \quizzbox\model\question::where('id_quizz', $idQuizz)->get();

			$jsonQuestion = '[ ';
			foreach($questions as $uneQuestion)
			{
				$jsonQuestion .= '{ "id" : '.$uneQuestion->id.' , "enonce" : "'.str_replace('"', '\"', $uneQuestion->enonce).'" , "coefficient" : '.$uneQuestion->coefficient.' , "reponses" : [ ';

				$reponses = \quizzbox\model\reponse::where('id_quizz', $idQuizz)->where('id_question', $uneQuestion->id)->get();
				$i = 1;
				foreach($reponses as $uneReponse)
				{
					$jsonQuestion .= ' { "id" : '.$uneReponse->id.' , "nom" : "'.str_replace("'", "\'", $uneReponse->nom).'" , "estSolution" : '.$uneReponse->estSolution.' } ';
					if($i != count($reponses))
					{
						$jsonQuestion .= ', ';
					}
					$i++;
				}
				$jsonQuestion .= ' ] }';
			}
			$jsonQuestion .= ' ] }';

			$jsonQuizz = $quizz->toJson();
			$jsonQuizz = substr($jsonQuizz, 0, -1);
			$jsonQuizz = substr($jsonQuizz, 1);
			$jsonQuizz = substr($jsonQuizz, 0, -1);

			$json = '{ "quizz" : '.$jsonQuizz.' , "questions" : '.$jsonQuestion.' }';
			return $json;
		}
		else
		{
			/* Oups ! . */
			return null;
		}
	}

	public function getQuizzJSON(Request $req, Response $resp, $args)
	{
		$json = (new \quizzbox\control\quizzboxcontrol($this))->getQuizz($req, $resp, $args);

		if($json == null)
		{
			$arr = array('error' => 'quizz introuvable !');
			$resp = $resp->withStatus(404);
			return (new \quizzbox\view\quizzboxview($arr))->getQuizzJSON($req, $resp, $args);
		}
        elseif(isset($args['without_headers'])) {
            return $json;
        }
		else
		{
			return (new \quizzbox\view\quizzboxview($json))->getQuizzJSON($req, $resp, $args);
		}
	}

	public function telechargerQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$json = (new \quizzbox\control\quizzboxcontrol($this))->getQuizz($req, $resp, $args);

		if($json == null)
		{
			$_SESSION["message"] = 'Le quizz n\'existe pas !';
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
		else
		{
			// Téléchargement du fichier Quizz

			$dir = "upload/";

			$nomFichier = 'quizzbox_'.\quizzbox\model\quizz::where('tokenWeb', $id)->first()->tokenWeb.'_'.time().'.quizz';
			$csv = new \SplFileObject($dir.$nomFichier, 'w');

			// Encode le JSON
			$csv->fwrite(base64_encode($json));

			header("Cache-Control: no-cache, must-revalidate");
			header("Cache-Control: post-check=0,pre-check=0");
			header("Cache-Control: max-age=0");
			header("Pragma: no-cache");
			header("Expires: 0");

			header("Content-Type: application/force-download");
			header('Content-Disposition: attachment; filename="'.$nomFichier.'"');

			$size = filesize($dir.$nomFichier);
			header("Content-Length: ".$size);

			readfile($dir.$nomFichier);
		}
	}

	public function envoiScore(Request $req, Response $resp, $args)
	{
		// On passe également en paramètre dans la requête, le JSON du quizz joué et on le compare avec celui de la base de données côté serveur pour vérifier l'intégrité des données avant envoi.

		// On authentifie le joueur par pseudo@motdepasse dans l'URL où le mot de passe est crypté en sha256 côté client.
		$joueur = filter_var($args['joueur'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$score = filter_var($args['score'], FILTER_SANITIZE_NUMBER_INT);

		$authentification = explode("@", $joueur);
		if(count($authentification) == 2) // Vérifier si il y a deux éléments : pseudo & mot de passe
		{
			if((json_decode($req->getBody())) == null)
			{
				// JSON invalide
				$arr = array('error' => 'Le JSON envoyé est formé de manière incorrect.');
				$resp = $resp->withStatus(400);
				return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
			}
			else
			{
				$jsonClient = json_decode($req->getBody());
				if(isset($jsonClient->quizz->tokenWeb))
				{
					$args['id'] = filter_var($jsonClient->quizz->tokenWeb, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // La méthode getQuizz a besoin d'un args['id']

					// Comparaison des JSON Client et Serveur
					$jsonServeur = json_decode((new \quizzbox\control\quizzboxcontrol($this))->getQuizz($req, $resp, $args));

					if($jsonServeur === $jsonClient)
					{
						if(\quizzbox\model\quizz::where('tokenWeb', $args['id'])->get()->toJson() != "[]")
						{
							// Intégrité du quizz vérifiée : vérifier pseudo & mot de passe.
							if(\quizzbox\model\joueur::where('pseudo', $authentification[0])->get()->toJson() != "[]")
							{
								$lejoueur = \quizzbox\model\joueur::where('pseudo', $authentification[0])->first();
								if(password_verify($authentification[1], $lejoueur->motdepasse))
								{
									// Authentification réussie !
									
									// Vérifier si le joueur n'a pas déjà joué au quizz
									$idQuizz = \quizzbox\model\quizz::where('tokenWeb', $id)->first()->id;
									
									if(\quizzbox\model\joueur::where('pseudo', $authentification[0])->scores()->where("id_quizz", $idQuizz)->count() > 0)
									{
										$scores = \quizzbox\model\quizz::where('tokenWeb', $id)->scores()->where("id_joueur", $lejoueur->id)->first();
										$scores->pivot->score = $score;
										$scores->save();

										$arr = array('success' => 'Score ajouté avec succès.');
										$resp = $resp->withStatus(201);
										return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
									}
									else
									{
										$arr = array('error' => 'Vous avez déjà enregistré un score à ce quizz.');
										$resp = $resp->withStatus(200);
										return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
									}
								}
								else
								{
									$arr = array('error' => 'Erreur d\'authentification.');
									$resp = $resp->withStatus(400);
									return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
								}
							}
							else
							{
								$arr = array('error' => 'Joueur introuvable.');
								$resp = $resp->withStatus(400);
								return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
							}
						}
						else
						{
							$arr = array('error' => 'Le quizz est introuvable sur le serveur.');
							$resp = $resp->withStatus(404);
							return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
						}
					}
					else
					{
						// Oups ! .
						$arr = array('error' => 'Erreur d\'intégrité du quizz.');
						$resp = $resp->withStatus(400);
						return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
					}
				}
				else
				{
					$arr = array('error' => 'Impossible de vérifier le quizz.');
					$resp = $resp->withStatus(400);
					return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
				}
			}
		}
		else
		{
			$arr = array('error' => 'Erreur d\authentification.');
			$resp = $resp->withStatus(400);
			return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
		}
	}

	public function rechercher(Request $req, Response $resp, $args)
	{
		if(isset($_GET["q"]))
		{
			if($_GET["q"] === "")
			{
				return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
			}
			else
			{
				$q = filter_var($_GET["q"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				$resultats = \quizzbox\model\quizz::where('nom', 'like', '%'.$q.'%')->get();

				return (new \quizzbox\view\quizzboxview($resultats))->render('rechercher', $req, $resp, $args);
			}
		}
		else
		{
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}

	public function afficherCategoriesJSON(Request $req, Response $resp, $args)
	{
		$categories = \quizzbox\model\categorie::orderBy('nom')->get()->toJson();

		return (new \quizzbox\view\quizzboxview($categories))->afficherCategoriesJSON($req, $resp, $args);
    }

	public function afficherQuizzJSON(Request $req, Response $resp, $args)
	{
		// Retourne [] si vide ou n'existe pas.
		
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		$quizz = \quizzbox\model\quizz::where('id_categorie', $id)->orderBy('nom')->get()->toJson();

		return (new \quizzbox\view\quizzboxview($quizz))->afficherQuizzJSON($req, $resp, $args);
    }
	
	public function getNbQuestionsQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		echo \quizzbox\model\question::where('id_quizz', $id)->count();
	}
}
