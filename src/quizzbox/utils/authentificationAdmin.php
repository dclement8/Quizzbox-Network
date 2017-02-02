<?php
namespace quizzbox\utils;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class authentificationAdmin {
    // Middleware qui vérifie l'authentification administrateur
    /*
        Utilisation de "authentificationAdmin" dans une route dans index.php :
        ->setName('nomDeVotreRoute')->add(new quizzbox\utils\authentificationAdmin() ); // A placer à la fin d'un $app->get par exemple
    */

    public function __invoke(Request $req, Response $resp, callable $next)
    {
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
                return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, null);
            }
        }
        else
        {
            // Non-authentifié : retour à l'accueil
            $_SESSION["message"] = "Accès interdit : une authentification est requise pour accéder à la ressource demandée !";
            return (new \quizzbox\control\quizzboxcontrol($this))->accueil($req, $resp, null);
        }
    }
}
