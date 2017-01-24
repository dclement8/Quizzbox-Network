<?php
namespace quizzbox\model;

class question extends \Illuminate\Database\Eloquent\Model
{
	// Database
	protected $table = 'question';
	protected $primaryKey = 'id';
	
	public $timestamps = false;
	
	
	// quizz ; question
	public function quizzQuestion()
	{
		return $this->belongsTo("\quizzbox\model\quizz","id_quizz");
	}
}