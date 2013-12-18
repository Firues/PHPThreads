<?php
session_start();
error_reporting(0); //Disable warnings from session
header('Content-Type: text/html; charset=UTF-8');
require_once 'lib/Threads.php';

$start = microtime(1); 

print_r($_SESSION);

$_SESSION['test'] = 'TEST';

$Thread->Create(function(){
	sleep(1);
	echo 'Sleep 1<br/>';
	return 'This threads don`t pring anything';
});

$Thread->Create(function(){
	sleep(5);
	echo 'Sleep 5<br/>';
});

$Thread->Create(function(){
	sleep(3);
	echo 'Sleep 3<br/>';
});

$Thread->Create(function(){
	sleep(6);
	echo 'Sleep 6<br/>';
});

$Thread->Create(function(){
	sleep(6);
	echo 'Sleep 6<br/>';
});

$Thread->Create(function(){
	sleep(6);
	echo 'Sleep 6<br/>';
});

$Thread->Create('string');

print_r($Thread->Run(false)); //Array with threads responses. This threads don't print anything, because here is false.


$finish = microtime(1); 
$totaltime = $finish - $start; 
$totaltime = (float)(round($totaltime)*1000)/1000;
echo '<br/><br/>runtime '.$totaltime.' sec.<br/><br/>'; 


$Thread->Create(function(){
	echo 'Another thread!<br/>';
});

$Thread->Create(function(){
	echo 'And more!<br/>';
	echo 'Get something from session: '.$_SESSION['test'].'<br/>';
});

$Thread->Run();

$_SESSION['test2'] = 'TEST 2!';