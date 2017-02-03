<?php
session_start();
// Autoloaders
require_once("./vendor/autoload.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true ]
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
		return (new quizzbox\control\quizzboxcontrol($this))->afficherCategories($req, $resp, $args);
	}
)->setName('afficherCategories');

$app->get('/inscription', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->inscriptionForm($req, $resp, $args);
})->setName('inscriptionForm');

$app->post('/inscription', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->inscriptionTraitement($req, $resp, $args);
})->setName('inscriptionTraitement');

$app->get('/creer', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->creer($req, $resp, $args);
})->setName('creer')->add(new quizzbox\utils\authentification());

$app->post('/quizz/{id}/supprimer', function (Request $req, Response $resp, $args) {
	return (new quizzbox\control\quizzboxcontrol($this))->supprimerQuizz($req, $resp, $args);
})->setName('supprimerQuizz')->add(new quizzbox\utils\authentificationAdmin());



$app->run();
