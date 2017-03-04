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
					if(!strstr($args['pseudo'], " ")) {
						if(!strstr($args['pseudo'], "@")) {
							if(!strstr($mdp, "@")) {
								if(strlen($args['email']) > 5) {
									if(strlen($args['email']) < 256) {
										if(!filter_var($args['email'], FILTER_VALIDATE_EMAIL) === false) {
											if($args['pseudo'] != "admin") {
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

																$_SESSION["message"] = 'Inscription effectuée';

																return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
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
								$_SESSION["message"] = 'Le mot de passe contient des caractères interdits !';
						}
						else
							$_SESSION["message"] = 'Le pseudo contient des caractères interdits !';
					}
					else
						$_SESSION["message"] = 'Le pseudo contient des caractères interdits !';
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

        function verifierContenu($json) {
            if((!isset($json->quizz->questions[0]->enonce) || $json->quizz->questions[0]->enonce == '') ||
                (!isset($json->quizz->questions[0]->reponses[0]) || $json->quizz->questions[0]->reponses[0] == '') ||
                (!isset($json->quizz->questions[0]->reponses[1]) || $json->quizz->questions[0]->reponses[1] == '') ||
                !isset($json->quizz->id_categorie) || $json->quizz->id_categorie == 0 || $json->quizz->id_categorie == '') {
                    return false;
            }
            for($i=0; $i < count($json->quizz->questions); $i++) {
                if(!isset($json->quizz->questions[$i]->enonce) || $json->quizz->questions[$i]->enonce == '' || !isset($json->quizz->questions[$i]->reponses)) {
                    return false;
                }
                $k = false;
                for($j=0; $j < count($json->quizz->questions[$i]->reponses); $j++) {
                    if(!isset($json->quizz->questions[$i]->reponses[$j]->nom) || $json->quizz->questions[$i]->reponses[$j]->nom == '') {
                        return false;
                    }
                    if($json->quizz->questions[$i]->reponses[$j]->estSolution == 1) {
                        $k = true;
                    }
                }
                if(count($json->quizz->questions[$i]->reponses) < 2 || !$k) {
                    return false;
                }
            }
            return true;
        }

        if(isset($_POST['json'])) {
            $json = json_decode($_POST['json']);
            if($json !== null) {
                $data['json'] = $_POST['json'];
                if(\quizzbox\model\categorie::where('id', '=', $json->quizz->id_categorie)->count() == 1) {
                    if(verifierContenu($json)) {
                        $quizz = new \quizzbox\model\quizz();
                        $quizz->nom = $json->quizz->nom;
                        $quizz->id_categorie = $json->quizz->id_categorie;
    					if($_SESSION["login"] != "admin")
    					{
    						$quizz->id_joueur = $_SESSION["login"];
    					}
                        $factory = new \RandomLib\Factory;
                        $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
                        $quizz->tokenWeb = $generator->generateString(32, 'abcdefghijklmnopqrstuvwxyz0123456789');
                        $quizz->save();

                        foreach($json->quizz->questions as $q) {
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

    public function modifierTraitement(Request $req, Response $resp, $args)
	{
        // TODO sécuriser variables et vérifier données
        $data['json'] = '';
        $data['categories'] = \quizzbox\model\categorie::orderBy('nom', 'ASC')->get();

        function verifierContenu($json) {
            if((!isset($json->quizz->questions[0]->enonce) || $json->quizz->questions[0]->enonce == '') ||
                (!isset($json->quizz->questions[0]->reponses[0]) || $json->quizz->questions[0]->reponses[0] == '') ||
                (!isset($json->quizz->questions[0]->reponses[1]) || $json->quizz->questions[0]->reponses[1] == '') ||
                !isset($json->quizz->id_categorie) || $json->quizz->id_categorie == 0 || $json->quizz->id_categorie == '') {
                    return false;
            }
            for($i=0; $i < count($json->quizz->questions); $i++) {
                if(!isset($json->quizz->questions[$i]->enonce) || $json->quizz->questions[$i]->enonce == '' || !isset($json->quizz->questions[$i]->reponses)) {
                    return false;
                }
                $k = false;
                for($j=0; $j < count($json->quizz->questions[$i]->reponses); $j++) {
                    if(!isset($json->quizz->questions[$i]->reponses[$j]->nom) || $json->quizz->questions[$i]->reponses[$j]->nom == '') {
                        return false;
                    }
                    if($json->quizz->questions[$i]->reponses[$j]->estSolution == 1) {
                        $k = true;
                    }
                }
                if(count($json->quizz->questions[$i]->reponses) < 2 || !$k) {
                    return false;
                }
            }
            return true;
        }

        if(isset($_POST['json'])) {
            $json = json_decode($_POST['json']);
            if($json !== null) {
                $data['json'] = $_POST['json'];
                if(\quizzbox\model\categorie::where('id', '=', $json->quizz->id_categorie)->count() == 1 && \quizzbox\model\quizz::where('id', '=', $json->quizz->id)->count() == 1) {
                    if(verifierContenu($json)) {

                        $oldQuestions = \quizzbox\model\question::where('id_quizz', '=', $json->quizz->id)->lists('id'); // On obtient un tableau contenant les id de toutes les questions
                        $newQuestions = []; // Tableau contenant les id des questions que l'on garde

                        $oldReponses = \quizzbox\model\reponse::where('id_quizz', '=', $json->quizz->id)->lists('id'); // On obtient un tableau contenant les id de toutes les réponses
                        $newReponses = []; // Tableau contenant les id des réponses que l'on garde

                        $quizz = \quizzbox\model\quizz::find($json->quizz->id);
						\quizzbox\model\quizz::find($json->quizz->id)->scores()->detach();
                        $quizz->nom = $json->quizz->nom;
                        $quizz->id_categorie = $json->quizz->id_categorie;
    					if($_SESSION["login"] != "admin")
    					{
    						$quizz->id_joueur = $_SESSION["login"];
    					}
                        $quizz->save();

                        foreach($json->quizz->questions as $q) {
                            $question = \quizzbox\model\question::find($q->id);
                            if($question === null) {
                                $question = new \quizzbox\model\question();
                            }
                            elseif($question->id_quizz != $quizz->id) {
                                // L'id ne correspond pas, on ne met pas cette question à jour
                                continue;
                            }
                            else {
                                $newQuestions[] = $q->id;
                            }
                            $question->enonce = $q->enonce;
                            $question->coefficient = $q->coefficient;
                            $question->id_quizz = $quizz->id;
                            $question->save();

                            foreach($q->reponses as $r) {
                                $reponse = \quizzbox\model\reponse::find($r->id);
                                if($reponse === null) {
                                    $reponse = new \quizzbox\model\reponse();
                                }
                                elseif($reponse->id_quizz != $quizz->id) {
                                    // L'id ne correspond pas, on ne met pas cette réponse à jour
                                    continue;
                                }
                                else {
                                    $newReponses[] = $r->id;
                                }
                                $reponse->nom = $r->nom;
                                $reponse->estSolution = $r->estSolution;
                                $reponse->id_question = $question->id;
                                $reponse->id_quizz = $quizz->id;
                                $reponse->save();
                            }
                        }

                        // On supprime les questions et réponses qui n'apparaissent pas dans le JSON transmis
                        $deleteQuestions = array_diff($oldQuestions, $newQuestions);
                        $deleteReponses = array_diff($oldReponses, $newReponses);

                        \quizzbox\model\reponse::whereIn('id', $deleteReponses)->delete();
                        \quizzbox\model\question::whereIn('id', $deleteQuestions)->delete();

                        $args['id'] = $quizz->id_categorie;
                        return (new \quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
                    }
                }
            }
        }
        // S'il y a un problème avec le JSON envoyé, on affiche à nouveau le formulaire
		return (new \quizzbox\view\quizzboxview($data))->render('modifierQuizz', $req, $resp, $args);
    }

	public function supprimerQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if(\quizzbox\model\quizz::where('id', $id)->get()->toJson() != "[]")
		{
			$idJoueur = \quizzbox\model\quizz::where('id', $id)->first()->id_joueur;
			if(($_SESSION["login"] == "admin") || ($_SESSION["login"] == $idJoueur))
			{
				\quizzbox\model\reponse::where('id_quizz', $id)->delete();
				\quizzbox\model\question::where('id_quizz', $id)->delete();
				\quizzbox\model\quizz::find($id)->scores()->detach();
				\quizzbox\model\quizz::destroy($id);

				$_SESSION["message"] = 'Quizz supprimé';
			}
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
			$lesQuizz = \quizzbox\model\quizz::where('id_joueur', $id)->get();
			foreach($lesQuizz as $unQuizz)
			{
				\quizzbox\model\reponse::where('id_quizz', $unQuizz->id)->delete();
				\quizzbox\model\question::where('id_quizz', $unQuizz->id)->delete();
				\quizzbox\model\quizz::find($unQuizz->id)->scores()->detach();
				\quizzbox\model\quizz::destroy($unQuizz->id);
			}
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
			$quizz = \quizzbox\model\quizz::where('tokenWeb', $id)->first();
			$idQuizz = $quizz->id;
			$questions = \quizzbox\model\question::where('id_quizz', $idQuizz)->get();

			$jsonQuestion = '[ ';
			$compteur = 0;
			foreach($questions as $uneQuestion)
			{
				$jsonQuestion .= '{ "enonce" : "'.str_replace('"', '\"', $uneQuestion->enonce).'" , "coefficient" : '.$uneQuestion->coefficient;
                if(isset($args['without_headers'])) {
                    $jsonQuestion .= ', "id": '.$uneQuestion->id;
                }
                $jsonQuestion .= ' , "reponses" : [ ';

				$reponses = \quizzbox\model\reponse::where('id_quizz', $idQuizz)->where('id_question', $uneQuestion->id)->get();
				$i = 1;
				foreach($reponses as $uneReponse)
				{
					$jsonQuestion .= ' { "nom" : "'.str_replace('"', '\"', $uneReponse->nom).'" , "estSolution" : '.$uneReponse->estSolution;
                    if(isset($args['without_headers'])) {
                        $jsonQuestion .= ', "id": '.$uneReponse->id;
                    }
                    $jsonQuestion .= ' } ';

					if($i != count($reponses))
					{
						$jsonQuestion .= ', ';
					}
					$i++;
				}
				$jsonQuestion .= ' ] } ';

				$compteur++;
				if($compteur != count($questions))
				{
					$jsonQuestion .= ',';
				}
			}
			$jsonQuestion .= ' ] }';

			$jsonQuizz = '{ "nom" : "'.str_replace('"', '\"', $quizz->nom).'" , "tokenWeb" : "'.$quizz->tokenWeb.'"';
            if(isset($args['without_headers'])) {
                $jsonQuizz .= ', "id": '.$quizz->id.', "id_categorie": '.$quizz->id_categorie;
            }

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

					if($jsonServeur == $jsonClient)
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
									$idQuizz = \quizzbox\model\quizz::where('tokenWeb', $args['id'])->first()->id;

									if(\quizzbox\model\joueur::find($lejoueur->id)->scores()->where("id_quizz", $idQuizz)->count() == 0)
									{
										$idAuteurQuizz = \quizzbox\model\quizz::where('tokenWeb', $args['id'])->first()->id_joueur;
										if($idAuteurQuizz != $lejoueur->id)
										{
											$config = parse_ini_file("conf/config.ini");
											$dsn = "mysql:host=".$config["host"].";dbname=".$config["database"];
											$db = new \PDO($dsn, $config["username"], $config["password"]);
											$db->query("SET CHARACTER SET utf8");

											$insert = "INSERT INTO scores VALUES(:score, NOW(), NULL, :joueur, :quizz)";
											$insert_prep = $db->prepare($insert);

											$idJoueur = $lejoueur->id;

											$insert_prep->bindParam(':score', $score, \PDO::PARAM_INT);
											$insert_prep->bindParam(':joueur', $idJoueur, \PDO::PARAM_INT);
											$insert_prep->bindParam(':quizz', $idQuizz, \PDO::PARAM_INT);

											$insert_prep->execute();

											$arr = array('success' => 'Score ajouté avec succès.');
											$resp = $resp->withStatus(201);
											return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
										}
										else
										{
											$arr = array('error' => "En tant qu'auteur de ce quizz, vous ne pouvez pas enregistrer votre score.");
											$resp = $resp->withStatus(400);
											return (new \quizzbox\view\quizzboxview($arr))->envoiScore($req, $resp, $args);
										}
									}
									else
									{
										$arr = array('error' => 'Vous avez déjà enregistré un score à ce quizz.');
										$resp = $resp->withStatus(400);
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

	public function nbQuizzCategoriesJSON(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);

		return (new \quizzbox\view\quizzboxview(\quizzbox\model\quizz::where('id_categorie', $id)->count()))->afficherCategoriesJSON($req, $resp, $args);
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

	public function creerCategorieForm(Request $req, Response $resp, $args)
	{
		return (new \quizzbox\view\quizzboxview(null))->render('creerCategorieForm', $req, $resp, $args);
	}

	public function creerCategorie(Request $req, Response $resp, $args)
	{
		$categorie = new \quizzbox\model\categorie();
		$categorie->nom = filter_var($_POST["categorieForm"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$categorie->description = filter_var($_POST["descriptionForm"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$categorie->save();

		$_SESSION["message"] = 'La catégorie a été crée !';
		return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
	}
}
