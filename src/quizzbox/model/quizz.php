<?php
namespace quizzbox\model;

class quizz extends \Illuminate\Database\Eloquent\Model
{
	// Database
	protected $table = 'quizz';
	protected $primaryKey = 'id';
	
	public $timestamps = false;
	
	
	// catégorie ; quizz
	public function categorieQuizz()
	{
		return $this->belongsTo("\quizzbox\model\categorie","id_categorie");
	}
	
	// scores
	public function scores()
	{
		return $this->belongsToMany("\quizzbox\model\joueur","scores","id_quizz","id_joueur")->withPivot("score", "dateHeure", "typeJeu");
	}
}