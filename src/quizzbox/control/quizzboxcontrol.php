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
	
	public function authentification(Request $req, Response $resp, callable $next)
	{
		// Middleware qui vérifie l'authentification
		/*
			Utilisation de la méthode "authentification" dans une route dans index.php :
			->setName('nomDeVotreRoute')->add('authentification'); // A placer à la fin d'un $app->get par exemple
		*/
		
		if(isset($_SESSION["login"]))
		{
			/* Bien ! . */
			return $next($req, $resp);
		}
		else
		{
			// Non-authentifié : retour à l'accueil
			$_SESSION["message"] = "Accès interdit : une authentification est requise pour accéder à la ressource demandée !";
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}
	
	public function authentificationAdmin(Request $req, Response $resp, callable $next)
	{
		// Middleware qui vérifie l'authentification Administrateur
		/*
			Utilisation de la méthode "authentificationAdmin" dans une route dans index.php :
			->setName('nomDeVotreRoute')->add('authentificationAdmin'); // A placer à la fin d'un $app->get par exemple
		*/
		
		if(isset($_SESSION["login"]))
		{
			if($_SESSION["login"] == "admin")
			{
				/* Bien ! . */
				return $next($req, $resp);
			}
			else
			{
				// Non-authentifié : retour à l'accueil
				$_SESSION["message"] = "Accès interdit : vous n'avez pas l'autorisation nécessaire pour accéder à la ressource demandée !";
				return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
			}
		}
		else
		{
			// Non-authentifié : retour à l'accueil
			$_SESSION["message"] = "Accès interdit : une authentification est requise pour accéder à la ressource demandée !";
			return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
		}
	}

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

    public function inscriptionForm(Request $req, Response $resp, $args) {
        $args['pseudo'] = '';
        $args['email'] = '';
        $this->message = '';
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
}
