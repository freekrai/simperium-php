simperium-php
==============

This is an unofficial library for interacting with the Simperium API in PHP. It's based on the [Simperium Python library](https://github.com/Simperium/simperium-python).

Simperium is a simple way for developers to move data as it changes, instantly and automatically. This is the PHP library.

You can [sign up](http://simperium.com) for a hosted version of Simperium. There are Simperium libraries for [other languages](https://simperium.com/overview/) too.

This is not yet a full Simperium library for parsing diffs and changes. It's a wrapper for our [HTTP API](https://simperium.com/docs/http/) intended for scripting and basic backend development.

### License
The Simperium PHP library is available for free and commercial use under the MIT license.

### Getting Started
To get started, first log into [https://simperium.com](https://simperium.com) and
create a new application.  Copy down the new app's name, api key and admin key.

Install composer in your project:

    curl -s https://getcomposer.org/installer | php

Create a composer.json file in your project root:

	{
	    "require": {
	        "freekrai/simperium-php": "dev-master"
	    }
	}

Now, run composer and install the package:

	php composer.phar install

Add this line to your application’s index.php file:

	<?php
	require 'vendor/autoload.php';

If you are running this without Composer, then add this to your index.php file instead:

	require_once dirname(__FILE__) . './Simperium/Simperium.php';

We'll need to create a user to be able to store data:

	>>> $simperium = new Simperium\Simperium($yourappname,$yourapikey);
    >>> $token = $simperium->create('joe@example.com', 'secret');
    >>> echo $token
    '25c11ad089dd4c18b84f24bc18c58fe2'

We can now store and retrieve data from simperium.  Data is stored in buckets.
For example, we could store a list of todo items in a todo bucket.  When you
store items, you need to give them a unique identifier.  Uuids are usually a
good choice.

    >>> $todo1_id = $simperium->generate_uuid();
    >>> $simperium->todo->post($todo1_id,array('text' => 'Read general theory of love', 'done' => False));

We can retrieve this item:

    >>> $simperium->todo->get($todo1_id);
    {'text': 'Read general theory of love', 'done': False}

Store another todo:

    >>> $simperium->todo->post($simperium->generate_uuid(), array('text' => 'Watch battle royale', 'done'=> False) );

You can retrieve an index of all of a buckets items:

    >>> $simperium->todo->index();
    {
        'count': 2,
        'index': [
            {'id': 'f6b680f8504c4e31a0e54a95401ffca0', 'v': 1},
            {'id': 'c0d07bb7c46e48e693653425eca93af9', 'v': 1}],
        'current': '4f8507b8faf44720dfc432b1',}

Retrieve all the documents in the index:

    >>> foreach( $simperium->todo2->index()->index as $v ){
    >>> 	echo $v->id.'<br />';
    >>> 	$ret = $simperium->get( $v->id );
    >>> }
    [
        {'text': 'Read general theory of love', 'done': False},
        {'text': 'Watch battle royale', 'done': False}]

It's also possible to get the data for each document in the index with data=true:

    >>> $simperium->todo->index(true)
    {
        'count': 2,
        'index': [
            {'id': 'f6b680f8504c4e31a0e54a95401ffca0', 'v': 1,
                'd': {'text': 'Read general theory of love', 'done': False},},
            {'id': 'c0d07bb7c46e48e693653425eca93af9', 'v': 1,
                'd': {'text': 'Watch battle royale', 'done': False},}],
        'current': '4f8507b8faf44720dfc432b1'}

To update fields in an item, post the updated fields.  They'll be merged
with the current document:

    >>> $simperium->todo->post($todo1_id, array('done' => 'True'));
    >>> $simperium->todo->get($todo1_id)
    {'text': 'Read general theory of love', 'done': True}

Simperium items are versioned.  It's possible to go back in time and retrieve
previous versions of documents:

    >>> $simperium->todo->get($todo1_id, $version=1)
    {'text': 'Read general theory of love', 'done': False}

Of course, you can delete items:

    >>> $simperium->todo->delete($todo1_id)
    >>> echo $simperium->todo->index()->count
    1
