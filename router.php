<?php

require_once ('./query-engine.php');
$r = new Gateway(FALSE);
$r->handle();





// ==================================================================
//
// This class waits for a client's instruction via GET / POST and 
// invokes the appropriate class and method as determined by the client
// You can think of this as a RESFUL server implementation, with the 
// exception that this server doesn't has mod_rewrite. Which means
// we can only obtain params from client via POST or GET query string
//
//
// *Note: All POST request from client must be in JSON format
//  {"class":"className", "method":"methodName", "param":"arg1,arg2,arg3"}
//  {"class":"className", "method":"methodName", "param": {"type":"my_type", "serial": "123", "country":"myCountry"}   }
//
// *All GET URL request from client should follow this format: 
//  gateway.php?class=className&method=methodName&param=arg1,arg2,arg3
//  gateway.php?class=className&method=methodName&param=type=my_type,serial=123,country=myCountry
//  
//  The params are supplied in this manner -> Class::Method(params_data_goes_here)
// ------------------------------------------------------------------
class Gateway 
{

    // ##################################################################
    // Constants
	// ------------------------------------------------------------------

	/**
	* These 3 variables are used as keys to extract data from _GET 
	* or from POST JSON data.
	*/
	const CLASS_KEY = 'class';      
	const METHOD_KEY = 'method'; 
	const PARAM_KEY = 'param';   


    // ##################################################################
    // Public variables 
	// ------------------------------------------------------------------

    /**
     * URL of the currently mapped service
     * @var string
     */
    public $url;

    /**
     * Http request method of the current request.
     * Any value between [GET, PUT, POST, DELETE]
     * @var string
     */
    public $request_method;

    /**
     * Stores the location of this file.
     * @var string
     */
    public $root_dir;

    /**
     * Data sent to the service
     * @var array
     */
    public $request_data = array();

    // ##################################################################
    // Protected variables 
	// ------------------------------------------------------------------

    /**
     * When set to FALSE, it will run in debug mode 
     * @var boolean
     */
    protected $production_mode;


    /**
     * Referenced to process handler via reflections
     * @var boolean
     */
    protected $engine;

    // ##################################################################
    // Private variables 
	// ------------------------------------------------------------------

   
	
	// ==================================================================
	//
	// Public functions
	//
	// ------------------------------------------------------------------

	/**
	 * Constructor
	 * @param boolean $production_mode When set to FALSE
	 */
	public function __construct($production_mode = FALSE)
	{
	    $this->production_mode = $production_mode;
	    $this->root_dir       = getcwd();
	}

	/**
	 * This implements the destructor.
	 */
	public function __destruct()
	{
	   // left empty for now 
	}

	/**
	 * Starts and listens to incoming request from client. 
	 * Returns appropriate response based on client's instructions
	 * You can think of this as the Main() function. 
	 */
	public function handle()
	{

		$this->request_method  = $this->getRequestMethod();
		$this->my_echo( "\n request_method is: " . $this->request_method );
		$this->request_data = $this->getRequestData();
		$client_request = $this->mapClassInvocation($this->request_data);
		$this->handleClassInvocation($client_request);
	}

	/**
	 * @param string $class name of the engine processor code 
	 * @param string $method specify a method to be invoked on the class 
	 * @param $params_array to be passed on to method 
	 * @throws todo: Throws Exception when supplied with invalid class name
	 */
	public function invokeClass($class_name, $method, $params_array)
	{
		$object = new $class_name(); //class_name case-insensitive
		$reflection = new ReflectionClass($object);
		if(!method_exists($class_name, $method)) {
			return "Error, class: " . $class_name . "::" . $method . " doesnt exist"; 
		}

		// Get desired method 
		$method = $reflection->getMethod($method);

 		$params_array = array_filter($params_array);
		if(!empty( $params_array )) { //invoke method with params 
			return $method->invoke($object, $params_array ); 
		}

		return $method->invoke($object); 
	}


	/**
	 * Compare two strings and remove the common
	 * sub string from the first string and return it
	 * @param string $first
	 * @param string $second
	 * @param string $char optional, set it as
	 * blank string for char by char comparison
	 * @return string
	 */
	public function removeCommonPath($first, $second, $char = '/')
	{
	    $first  = explode($char, $first);
	    $second = explode($char, $second);
	    while (count($second)) {
	        if ($first[0] == $second[0]) {
	            array_shift($first);
	        } else {
	            break;
	        }
	        array_shift($second);
	    }
	    return implode($char, $first);
	}

	// ==================================================================
	//
	// Protected functions
	//
	// ------------------------------------------------------------------

	/**
	 * Parses the request to figure out the http request type
	 * @return string which will be one of the following
	 * [GET, POST, PUT, DELETE]
	 * @example GET
	 */
	protected function getRequestMethod()
	{
	    $method = $_SERVER['REQUEST_METHOD'];
	    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) { // for older clients w/o PUT, UPDATE mthodds etc.
	        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
	    }
	    //support for HEAD request
	    if ($method == 'HEAD') {
	        $method = 'GET';
	    }
	    return $method;
	}

	/**
	 * Parses the request data and returns it
	 * @return array php data
	 */
	protected function getRequestData()
	{
	    $r = file_get_contents('php://input');
	     
	    if (empty($r) || is_null($r)) {
	    	
	        return $_GET;
	    }
	    
	    return is_null($r) ? array() : $r;
	}

	/**
	 * Determine which class should be invoked in accordance with 
	 * the request instructions from client. 
	 * All client request would be a POST JSON type, or GET type 
	 * Other type of request parse could be further added. But JSON is sufficient for now. 
	 * @param  payload $request_data from client 
	 * @return PHP array consisting of class, method and arguments to invoke
	 */
	protected function mapClassInvocation($request_data)
	{

		$invoke_list = array(
			Gateway::CLASS_KEY 	=> 'NoClassSpecified',
			Gateway::METHOD_KEY => 'NoMethodSpecified',
			Gateway::PARAM_KEY 	=> array(), 
		);

		// if GET request, data returned would be in PHP array obj 
		if($this->request_method == 'GET') {  
			$invoke_list[Gateway::CLASS_KEY]  = $_GET[Gateway::CLASS_KEY];
			$invoke_list[Gateway::METHOD_KEY] = $_GET[Gateway::METHOD_KEY];
			$invoke_list[Gateway::PARAM_KEY]  = explode(',' , $_GET[Gateway::PARAM_KEY]); //params are comma seperated 
			return $invoke_list; 
		}

		// if POST request, data returned in JSON string format 
		if($this->request_method == 'POST') { 
			$post_data = json_decode( $this->request_data , true);
			$invoke_list[Gateway::CLASS_KEY]  = $post_data[Gateway::CLASS_KEY];
			$invoke_list[Gateway::METHOD_KEY] = $post_data[Gateway::METHOD_KEY];
			$invoke_list[Gateway::PARAM_KEY]  = $post_data[Gateway::PARAM_KEY];

			// If param is not set, set it to be default value of empty array();
			if(!isset($invoke_list[Gateway::PARAM_KEY])) {
				$invoke_list[Gateway::PARAM_KEY] = array();
			}

			return $invoke_list; 
		}

		return $invoke_list; 
	}

	/**
	 * Takes in a request array and invoke the necessary class
	 * @param  payload $request_array from client 
	 * @return Data after invocation of the required class::method
	 */
	protected function handleClassInvocation($request_array)
	{
		
		$this->invokeClass(
				$request_array[Gateway::CLASS_KEY], 
				$request_array[Gateway::METHOD_KEY],
				$request_array[Gateway::PARAM_KEY]
			);

	}

	// ==================================================================
	//
	// private functions
	//
	// ------------------------------------------------------------------

	private function my_echo ($string)
	{
		if(!$this->production_mode) echo "DEBUG: " . $string  . " \n" ;
	}

	private function my_print_r($data)
	{
		if(!$this->production_mode) {
			echo "DEBUG: \n";
			print_r($data);
		}
	}

}


?>