Mini-Rest-PHP
=============

Mini Restful PHP Listener. Useful for servers without mod_rewrite


<?php



// For testing purposes
class TestEcho 
{
  public function getBondTypes($msg="no msg")
	{
		// header("Cache-Control: no-cache, must-revalidate");
		// header("Expires: 0");

		echo "<br /> test: " . $msg;
	}

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
