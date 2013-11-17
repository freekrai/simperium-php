<?php
	//	Simperium PHP library
	include("simperium.php");

	$simperium = new Simperium('my-app-id','my-api-key');
	$token = $simperium->authorize('joe@example.com', 'secret');

	//	echo '<hr />';
	echo '<h4>Add new data</h4>';
	$todo1_id = $simperium->generate_uuid();
	$simperium->todo2->post( $todo1_id,array('text'=>'Watch Star Wars with K', 'done'=>'False') );
	
	echo '<hr />';
	echo '<h4>Get Index List</h4>';
	$ret = $simperium->todo2->index();
	$simperium->_debug($ret);
	
	echo '<hr />';
	echo '<h4>Get Index list and include data</h4>';
	$ret = $simperium->todo2->index(true);
	$simperium->_debug($ret);
	
	echo '<hr />';
	echo '<h4>Get Index list and then grab data from document</h4>';
	foreach( $simperium->todo2->index()->index as $v ){
		echo $v->id.'<br />';
		$ret = $simperium->get( $v->id );
		$simperium->_debug($ret);
	}
	
	
	echo '<hr />';
	echo '<h4>Get item by unique id</h4>';
	$ret = $simperium->todo2->get('61a27242-e268-4951-89b8-1d42b379d353');
	$simperium->_debug($ret);
	
	echo '<hr />';
	echo '<h4>List All Buckets</h4>';
	$buckets = $simperium->buckets();
	$simperium->_debug( $buckets );