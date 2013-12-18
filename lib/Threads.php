<?php
	
	/*  PHP Threads by Andrzej Wielski  */
	/*    [ http://vk.com/wielski ]    */

	require_once('Closure.php');
	
	Class Thread {
		private $password = 'mypassword';
		
		public function __construct(){
			if($_SERVER['HTTP_PHPTHREADS']){
					$closure = $_POST['PHPThreads_Run'];
					$closure = $this->strcode(base64_decode($closure), $this->password);	
					
					$vars = $_POST['PHPThreads_Vars'];
					$vars = $this->strcode(base64_decode($vars), $this->password);	
					$vars = unserialize($vars);
					
					$session = $_POST['PHPThreads_Session'];
					$session = $this->strcode(base64_decode($session), $this->password);	
					$session = unserialize($session, true);
					
					$unserialized_closure = unserialize($closure);
					if(gettype($unserialized_closure) != 'object') return false;
					
					ob_start();
					$_SESSION = $session;
					if(is_array($vars)){
						$response = $unserialized_closure($vars);
					} else {
						$response = $unserialized_closure();
					}
					$echo = ob_get_contents();
					ob_end_clean();
					
					echo json_encode(array(
						'return' => $response,
						'echo' => $echo
					));
					die();
			}
		}
		
		public function Create($func, $variables = false){
			if(gettype($func) != 'object'){
				echo '<!--error--><br /><b>Threads Error</b>: Thread must be a function.<br />';
				return false;
			}
			$thread =  new SuperClosure($func);
			$serialized_closure = serialize($thread);
			$serialized_variables = serialize($variables);
			$this->threads[] = array(
				$serialized_closure,
				$serialized_variables
			);
		}
		
		public function Clear(){
			unset($this->threads);
		}
		
		public function Run($echo = true){
		
				if(!is_array($this->threads)) return false;
				
				$session = json_encode($_SESSION);
				session_write_close();
				
				//Start
				$cmh = curl_multi_init();
				$tasks = array();
				
				foreach ($this->threads as $i=>$thread) {
					$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($ch, CURLOPT_TIMEOUT, 30);
					curl_setopt($ch,CURLOPT_HTTPHEADER,
						array('PHPThreads: true')
					);
					curl_setopt($ch, CURLOPT_POST, 1);
					
					$Post = array(
						'PHPThreads_Run' => base64_encode($this->strcode($thread[0], $this->password)),
						'PHPThreads_Vars' => base64_encode($this->strcode($thread[1], $this->password)),
						'PHPThreads_Session' => base64_encode($this->strcode($session, $this->password))
					);
					
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($Post));
					
					$tasks[$i] = $ch;
					curl_multi_add_handle($cmh, $ch);
				}
				
				
				$active = null;
				do {
					$mrc = curl_multi_exec($cmh, $active);
				}
				while ($mrc == CURLM_CALL_MULTI_PERFORM);
				 
				while ($active && ($mrc == CURLM_OK)) {
					if (curl_multi_select($cmh) != -1) {
						do {
							$mrc = curl_multi_exec($cmh, $active);
							$info = curl_multi_info_read($cmh);
							if ($info['msg'] == CURLMSG_DONE) {
								$ch = $info['handle'];
								$url = array_search($ch, $tasks);
								
								$result = curl_multi_getcontent($ch);
								$curl_result = json_decode($result, true);
								
								if($echo) echo $curl_result['echo'];
								$resp[$url] = $curl_result['return'];
								
								curl_multi_remove_handle($cmh, $ch);
								curl_close($ch);							
							}
						}
						while ($mrc == CURLM_CALL_MULTI_PERFORM);
					}
				}
				
				curl_multi_close($cmh);
				session_start();
				
				$this->Clear(); //Clear Threads after run
				
				if(is_array($resp)) ksort($resp);
				return $resp;
				// End
				
		}
		
		
		private function strcode($str, $passw=""){
			$salt = "DfEQn8*#^2n!9jErF";
			$len = strlen($str);
			$gamma = '';
			$n = $len>100 ? 8 : 2;
			while( strlen($gamma)<$len ){
				$gamma .= substr(pack('H*', sha1($passw.$gamma.$salt)), 0, $n);
			}
			return $str^$gamma;
		} //Encode decode string by pass
		
		
	}
	
	$Thread = new Thread();
