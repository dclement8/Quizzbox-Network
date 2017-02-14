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
			foreach($_SESSION as $elementDeSession)
			{
				unset($elementDeSession);
			}
			
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
            $joueur = \quizzbox\model\joueur::where('pseudo', '=', $args['pseudo'])->first();
            if($joueur !== null && $joueur !== false) {
                if(password_verify($mdp, $joueur->motdepasse)) {
                    $_SESSION["login"] = $joueur->id;
                    return (new \quizzbox\view\quizzboxview($this))->render('connexionTraitement', $req, $resp, $args);
                }
                else
                    $_SESSION["message"] = 'Mot de passe incorrect !';
            }
            else
                $_SESSION["message"] = 'Joueur inexistant !';
        }
        return (new \quizzbox\view\quizzboxview($this))->render('connexionForm', $req, $resp, $args);
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
                                                $mdp = password_hash($mdp, PASSWORD_BCRYPT);
                                                $user = new \quizzbox\model\joueur();
                                                $user->pseudo = $args['pseudo'];
                                                $user->motdepasse = $mdp;
                                                $user->email = $args['email'];
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
		return (new \quizzbox\view\quizzboxview($this))->render('creer', $req, $resp, $args);
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
		}
		$_SESSION["message"] = 'Quizz supprimé';

		return (new \quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
	}
	
	public function supprimerJoueur(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if(\quizzbox\model\joueur::where('id', $id)->get()->toJson() != "[]")
		{
			\quizzbox\model\joueur::find($id)->scores()->detach();
			\quizzbox\model\joueur::destroy($id);
		}
		$_SESSION["message"] = 'Joueur supprimé';

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
		
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT); // ID = Token
		if(\quizzbox\model\quizz::where('tokenWeb', $id)->get()->toJson() != "[]")
		{
			$quizz = \quizzbox\model\quizz::where('tokenWeb', $id)->get();
			$questions = \quizzbox\model\question::where('id_quizz', $id)->get();
			
			$jsonQuestion = '[ ';
			foreach($questions as $uneQuestion)
			{
				$jsonQuestion .= '{ "id" : '.$uneQuestion->id.' , "ennonce" : "'.str_replace("'", "\'", $uneQuestion->ennonce).'" , "coefficient" : '.$uneQuestion->coefficient.' , "reponses" : [ ';
				
				$reponses = \quizzbox\model\question::where('id_quizz', $id)->where('id_question', $uneQuestion->id)->get();
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
			$jsonQuestion .= ' ]';
			
			$jsonQuizz = $quizz->toJson();
			$jsonQuizz = substr($jsonQuizz, 0, -1);
			$jsonQuizz = substr($jsonQuizz, 1);
			
			$json = '{ "quizz" : { '.$jsonQuizz.' } , "questions" : '.$jsonQuestion.' }';
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
			return (new \lbs\view\lbsview($arr))->render('getQuizzJSON', $req, $resp, $args);
		}
		else
		{
			return (new \lbs\view\lbsview($json))->render('getQuizzJSON', $req, $resp, $args);
		}
	}
	
	public function telechargerQuizz(Request $req, Response $resp, $args)
	{
		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
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
}
