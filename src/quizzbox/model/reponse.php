<?php
namespace quizzbox\model;

class reponse extends \Illuminate\Database\Eloquent\Model
{
	// Database
	protected $table = 'reponse';
	protected $primaryKey = 'id';
	
	public $timestamps = false;
	
	
	// question ; reponse
	public function questionReponse()
	{
		return $this->belongsTo("\quizzbox\model\question","id_question");
	}
	
	// quizz ; reponse
	public function quizzReponse()
	{
		return $this->belongsTo("\quizzbox\model\quizz","id_quizz");
	}
}