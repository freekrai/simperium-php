<?php
	include("simperium.php");

	$simperium = new Simperium('my-app-id','my-api-key');
	$simperium->authorize('joe@example.com', 'secret');
	
	$mongohq_url = '';
	$dbname = '';
	
	$m = new Mongo( $mongohq_url );
	$db = $m->$dbname;  
	
	//	grab the last record and resume from there...
	$cv = $db->meta->fineOne(array(
		'_id' => 'cv',
	));
	$cv = $cv['cv'];

	$cv = '';
	$numTodos = 0;
	$a = true;
	while( $a ){
		$changes = $simperium->todo2->changes($cv,true);
		foreach($changes as $change ){
			echo '<pre>'.print_r($change,true).'</pre><hr />';
			$data = $change->d;
			$cv = $change->cv;
            # update mongo with the latest version of the data
			if( $data ){
				$data['_id'] = $change->id;
				$db->bucket->save( $data );
			}else{
				$db->bucket->remove( $change->id );
			}

            # persist the cv to mongo, so changes don't need to be
            # re-processed after restart
			$db->meta->save( array( '_id' => 'cv', 'cv' => $change->cv ) );
		}
	}