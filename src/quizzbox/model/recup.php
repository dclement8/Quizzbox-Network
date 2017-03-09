<?php
namespace quizzbox\model;

class recup extends \Illuminate\Database\Eloquent\Model
{
	// Database
	protected $table = 'recup';
	protected $primaryKey = 'token';
	
	public $timestamps = false;
	
	
	// joueur ; recup
	public function joueurRecup()
	{
		return $this->belongsTo("\quizzbox\model\joueur","id_joueur");
	}
}