<?php
/*
	This example will set up a loop which listens to changes made to a bucket and display the changes on the screen.

	This can be handy for storing changes locally to a database, or for notifying users of any changes that have been made.
	
	This should be run from the command-line, not from a web browser.
*/
	include("simperium.php");

	$simperium = new Simperium('my-app-id','my-api-key');
	$simperium->authorize('joe@example.com', 'secret');
	
	$cv = '';
	$numTodos = 0;
	$a = true;
	while( $a ){
		$changes = $simperium->todo2->changes($cv,true);
		foreach($changes as $change ){
			echo '<pre>'.print_r($change,true).'</pre><hr />';
			$cv = $change->cv;
		}
	}