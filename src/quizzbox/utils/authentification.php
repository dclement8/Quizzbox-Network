<?php
namespace quizzbox\utils;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class authentification {
    // Middleware qui vérifie l'authentification
    /*
        Utilisation de "authentification" dans une route dans index.php :
        ->setName('nomDeVotreRoute')->add(new quizzbox\utils\authentification() ); // A placer à la fin d'un $app->get par exemple
    */

    public function __invoke(Request $req, Response $resp, callable $next)
    {
        if(isset($_SESSION["login"]))
        {
            /* Bien ! . */
            return $next($req, $resp);
        }
        else
        {
            // Non-authentifié : retour à l'accueil
            $_SESSION["message"] = "Accès interdit : une authentification est requise pour accéder à la ressource demandée !";
            return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, null);
        }
    }
}
