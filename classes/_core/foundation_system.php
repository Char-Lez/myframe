<?php
	//
	// foundation_system.php
	//
	class foundation_system
	{
		const INI_OPTIONAL = 1;
		//
		private $application_class_name;
		private $database;
		private $ini;
		//
		//
		public function __construct($application_class_name='')
		{
			try
			{
				//////////////////////////
				// Check argument count //
				//////////////////////////
				//
				$arg_count=func_num_args();
				switch ($arg_count)
				{
					case 0: {
						$application_class_name=get_application_name();
						//if ($application_class_name==='index')
						//{
						//	$application_class_name='foundation_application';
						//} // if ($application_class_name==='index')
					break; }
					//
					case 1: {
						$application_class_name=func_get_args(0);
					break; }
					//
					default: {
						throw new foundation_fault('Invalid argument count', $arg_count);
					break; }
				} // switch ($arg_count)
				//
				//
				//////////////////////
				// Check data types //
				//////////////////////
				//
				confirm_string($application_class_name);
				//
				//
				//////////////////
				// Sanity Check //
				//////////////////
				//
				confirm_path_safe($application_class_name);
				//
				//
				///////////////////////////////
				// Construct the application //
				///////////////////////////////
				//
				$this->database=FALSE;
				//
				$ini_file=$application_class_name.'.ini';
				$target='../'.$ini_file;
				//
				// Are we looking for index.ini?
				if ($application_class_name==='index')
				{
					// Yes, looking for index.ini
					//
					// Does the ini file exist?
					if (file_exists($target)!==TRUE)
					{
						// No, index.ini does not exist
						//
						// Let's try to get the installation sample ini
						//
						$template=new foundation_template('foundation_application.ini', foundation_template::CORE);
						//
						file_save($target, $template->render());
					} // if (file_exists($target)!==TRUE)
					//
					// Does the class file exist?
					$t2='../classes/'.$application_class_name.'.php';
					if (file_exists($t2)!==TRUE)
					{
						// No, index.php does not exist
						//
						$this->application_class_name='foundation_application';
					}
					else
					{
						// Yes, found it
						$this->application_class_name=$application_class_name;
					} // if (file_exists($t2)!==TRUE)
				} // if ($application_class_name==='index')
				//
				// Does the ini file exist now?
				if (file_exists($target)!==TRUE)
				{
					// No, ini file is missing
					throw new foundation_fault('Missing ini file', $ini_file);
				}
				else
				{
					// Yes, exists
					$this->ini=@parse_ini_file($target, FALSE, INI_SCANNER_TYPED);
					//
					// Did it parse?
					if ($this->ini===FALSE)
					{
						// No, did not parse
						throw new foundation_fault('Could not parse ini', $ini_file);
					} // if ($this->ini===FALSE)
				} // if (file_exists($target)!==TRUE)
				//
				return;
			}
			catch (Throwable $e)
			{
				throw new foundation_fault('Cannot create new Foundation application', '', $e);
			} // try
		} // __construct()
		//
		//
		public function get_ini()
		{
			try
			{
				$arg_count=func_num_args();
				switch ($arg_count)
				{
					case 0: {
						$result=$this->ini;
					break; }
					//
					case 1: {
						$ini=func_get_arg(0);
						//
						confirm_string($ini);
						confirm_array_element($ini, $this->ini);
						//
						$result=$this->ini[$ini];
					break; }
					//
					case 2: {
						$ini=func_get_arg(0);
						$optional=func_get_arg(1);
						//
						confirm_string($ini);
						confirm_int($optional);
						//
						if ($optional!==foundation_system::INI_OPTIONAL)
						{
							throw new foundation_fault('Unknown option', $optional);
						} // if ($optional!==foundation_application::INI_OPTIONAL)
						//
						if (array_key_exists($ini, $this->ini)!==TRUE)
						{
							$result=NULL;
						}
						else
						{
							$result=$this->ini[$ini];
						} // if (array_key_exists($ini, $this->ini)!==TRUE)
					break; }
					//
					default: {
						throw new foundation_fault('Invalid argument count', $arg_count);
					break; }
				} // switch ($arg_count)
				//
				return $result;
			}
			catch (Throwable $e)
			{
				throw new foundation_fault('Cannot return ini', '', $e);
			} // try
		} // get_ini()
		//
		//
		public function render()
		{
			try
			{
				//////////////////////////
				// Check argument count //
				//////////////////////////
				//
				$arg_count=func_num_args();
				confirm_args($arg_count, 0);
				//
				//
				////////////////////////////
				// Render the application //
				////////////////////////////
				//
				// Is there a POST command?
				if (array_key_exists('command', $_POST)===TRUE)
				{
					// Yes, has POST command
					$command=$_POST['command'];
				}
				else
				{
					// No, missing POST command
					// Is there a GET command?
					if (array_key_exists('command', $_GET)===TRUE)
					{
						// Yes, has GET command
						$command=$_GET['command'];
					}
					else
					{
						// No, missing GET command
						// Is there a COOKIE command?
						if (array_key_exists('command', $_COOKIE)===TRUE)
						{
							// Yes, has a COOKIE command
							$command=$_COOKIE['command'];
						}
						else
						{
							// No, missing COOKIE command
							// Go with default command
							$command='DISPLAY';
						} // if (array_key_exists('command', $_COOKIE)===TRUE)
					} // if (array_key_exists('command', $_GET)===TRUE)
				} // if (array_key_exists('command', $_POST)===TRUE)
				//
				// Create the application object
				$app=new $this->application_class_name;
				//
				// Does the command exist?
				if (method_exists($app, $command)!==TRUE)
				{
					// No, command is missing
					throw new foundation_fault("Invalid command [$command]", '');
				}
				else
				{
					// Ywa, command exists
					$response=$app->$command();
				} // if (method_exists($app, $command)!==TRUE)
				//
				//
				return $response;
			}
			catch (Throwable $e)
			{
				throw new foundation_fault('Cannot render', '', $e);
			} // try
		} // render()
	} // foundation_system
?>