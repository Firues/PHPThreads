About PHP Threads
==========

This class makes it easy to implement multithreading in PHP.<br/>
It works on any hosting, the only requirement is curl.<br/><br/>

<hr/>

<h4>Including the class</h4>
`require_once 'lib/Threads.php';`

<hr/>
<h4>Creating a Thread</h4>

    $Thread->Create(function(){
   	sleep(5);
   	echo 'Sleep 5';
    });
    
<hr/>

<h4>Starting Threads</h4>

`$Thread->Run();`

<hr/>

<h4>Starting Threads without printing anything from thread</h4>
`$Thread->Run(false);`
This code will run, but nothing will shown for user.<br/>
`print_r($Thread->Run(false));`
This code will run, and we get only responses from threads (return).
