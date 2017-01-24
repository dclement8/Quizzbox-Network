<?php
namespace quizzbox;
use \Illuminate\Database\Capsule\Manager;

class AppInit
{
	public static function bootEloquent ($file)
	{
		$conf = parse_ini_file($file);
		$db = new Manager();
		
		$db->addConnection($conf);
		$db->setAsGlobal();
		$db->bootEloquent();
	}
}