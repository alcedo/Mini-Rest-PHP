<?php 


// ==================================================================
//  The purpose of this class is to return various database
//  connection data. Yeap. Thats all. 
// 
//  The usage of classes would hopefully provide more contextual hint
//  as to which database we are dealing with instead of plain strings 
// ------------------------------------------------------------------

abstract class aDatabaseEnv
{

	
    // ##################################################################
    // Constants
	// ------------------------------------------------------------------
	const PROD = 'PROD';      
	const DEV  = 'DEV'; 
	const TEST = 'TEST';

	/**
	 * Determines the Environment Context
	 * Eg: LibrayEnv means the Databased is used for Storing lib books 
	 * @return string environment name 
	 */
	public function getEnv(){
		return $this->env;
	}

	/**
	 * Returns the Database Vendor Type
  	 * @return string vendor type (MYSQL, Sybase, MSSQL, Oracle etc)
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns the database hostname 
	 * @return string db hostname. eg: 123.132.33.12 or ec2.ap.gbl.sg etc
	 */
	public function getHostName(){
		return $this->host_name;
	}

	/**
	 * Returns the Database Name that is to be connected. 
	 * @return string database name. eg: my_db_name
	 */
	public function getDatabaseName(){
		return $this->db_name;
	}

	/**
	 * Returns the user id that is used for authentication 
	 * @return string user name 
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * Returns the password that is to be used for authentication 
	 * @return string pwd 
	 */
	public function getUserPwd(){
		return $this->user_pwd;
	}

	/**
	 * Returns whether database is in Production, Test Or Dev Mode. 
	 * @return string env mode 
	 */
	public function getMode(){
		return $this->mode;
	}


}


class DummyDatabase extends aDatabaseEnv
{		

		// ==================================================================
		//
		// Private variables
		//
		// ------------------------------------------------------------------

		protected $env;
		protected $type;
		protected $host_name;
		protected $db_name;
		protected $user_id;
		protected $user_pwd; 
		protected $mode; 

		public function setConnection () 
		{
			$this->env = "ENV"; 
			$this->mode = aDatabaseEnv::PROD; 

			$this->type 		=   "SQL";
			$this->host_name 	=   "amazon-ec2.ap.sg.com";
			$this->db_name 		=   "test_db";
			$this->user_id		=   "test_user";
			$this->user_pwd 	=   "test_pwd";

		}
		
}

/* 
$a = new DummyDatabase ();
$a->setConnection ();
echo $a->getEnv() . "<br />";
echo $a->getType() . "<br />";
echo $a->getHostName() . "<br />";
echo $a->getDatabaseName() . "<br />";
echo $a->getUserId() . "<br />";
echo $a->getUserPwd() . "<br />";
echo $a->getMode() . "<br />";

*/ 

?>