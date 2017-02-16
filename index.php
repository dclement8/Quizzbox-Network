<?php
session_start();
// Autoloaders
require_once("./vendor/autoload.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true ] ,
	'notFoundHandler' => function($c) {
		return (function($req, $resp) {
			$args = null;
			$resp = $resp->withStatus(404);
			
			$_SESSION["message"] = "Erreur 404 : la page que vous avez demandé est introuvable !";
			
			return (new quizzbox\control\quizzboxcontrol(null))->accueil($req, $resp, $args);
		});
	}
];
$c = new\Slim\Container($configuration);
$app = new \Slim\App($c);

// -------------------

/*$app->get('/',
	function (Request $req, Response $resp, $args)
	{
		return (new quizzbox\control\quizzboxcontrol($this))->exemple($req, $resp, $args);
	}
)->setName('exemple');*/

$app->get('/',
	function (Request $req, Response $resp, $args)
	{
		return (new quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, $args);
	}
)->setName('accueil');

$app->get('/categories',
	function (Request $req, Response $resp, $args)
	{
		return (new quizzbox\control\quizzboxcontrol($this))->afficherCategories($req, $resp, $args);
	}
)->setName('categories');

$app->get('/categories/{id}',
	function (Request $req, Response $resp, $args)
	{
		return (new quizzbox\control\quizzboxcontrol($this))->afficherQuizz($req, $resp, $args);
	}
)->setName('afficherCategories');

$app->get('/inscription', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->inscriptionForm($req, $resp, $args);
})->setName('inscriptionForm');

$app->post('/inscription', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->inscriptionTraitement($req, $resp, $args);
})->setName('inscriptionTraitement');

$app->get('/connexion', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->connexionForm($req, $resp, $args);
})->setName('connexionForm');

$app->post('/connexion', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->connexionTraitement($req, $resp, $args);
})->setName('connexionTraitement');

$app->get('/creer', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->creer($req, $resp, $args);
})->setName('creer')->add(new quizzbox\utils\authentification());

$app->post('/quizz/{id}/supprimer', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->supprimerQuizz($req, $resp, $args);
})->setName('supprimerQuizz')->add(new quizzbox\utils\authentificationAdmin());

$app->get('/profil/{id}', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->afficherProfil($req, $resp, $args);
})->setName('afficherProfil');

$app->post('/profil/{id}/supprimer', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->supprimerJoueur($req, $resp, $args);
})->setName('supprimerJoueur')->add(new quizzbox\utils\authentificationAdmin());

$app->get('/quizz/{id}', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->getQuizzJSON($req, $resp, $args);
})->setName('getQuizzJSON');

$app->get('/quizz/{id}/download', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->telechargerQuizz($req, $resp, $args);
})->setName('telechargerQuizz');

$app->put('/quizz/joueur/{joueur}/scores/{score}', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->envoiScore($req, $resp, $args);
})->setName('envoiScore');

$app->get('/recherche', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->rechercher($req, $resp, $args);
})->setName('rechercher');



$app->run();
