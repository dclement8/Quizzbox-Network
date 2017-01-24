<?php
namespace quizzbox\model;

class categorie extends \Illuminate\Database\Eloquent\Model
{
	// Database
	protected $table = 'categorie';
	protected $primaryKey = 'id';
	
	public $timestamps = false;
}