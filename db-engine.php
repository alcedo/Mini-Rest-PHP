<?php


// For testing purposes !
class TestEcho 
{

	public function dumbEchoNoParams() 
	{
		echo " DumbEchoNoParams Executed ! ";
	}

	public function dumbEchoParam($params) 
	{
		echo " dumbEchoParam executed w :"   ;
		print_r ($params);
		echo (   json_encode($params)    ) ;
	} 

	public function dumbEchoParamDefault ($params = "no params") 
	{
		echo " dumbEchoParamDefault executed w :"   ;
		echo "default :" .  $params . "\n";
		print_r($params);
	}
}




?>
