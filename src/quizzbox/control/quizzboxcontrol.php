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
		//return (new \quizzbox\control\lbscontrol($this))->afficherCategories($req, $resp, $args);
    }
}
