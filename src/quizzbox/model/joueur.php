<?php
namespace quizzbox\model;

class joueur extends \Illuminate\Database\Eloquent\Model
{
	// Database
	protected $table = 'joueur';
	protected $primaryKey = 'id';
	
	public $timestamps = false;
	
	
	// scores
	public function scores()
	{
		return $this->belongsToMany("\quizzbox\model\quizz", "scores", "id_joueur", "id_quizz")->withPivot("score", "dateHeure", "typeJeu");
	}
}