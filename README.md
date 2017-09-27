
Demon Keeper
------------

Demon keeper is a great way to start, stop, and check process form a web project.

It keeps track of a running script locking his process id into a visible file as well of logging the action in the .log file.

You can start a process and let it running asynchronous keeping track of that Demon through the log file or the `status` function. Or set the option `background` to false to run it synchronous and wait for the output.


Look how easy it is to use:

    <?php
    /* Run a Demon */
    
    require('./Source/DemonKeeper.php');

	use DemonKeeper\DemonKeeper;

    try {
		$script = new DemonKeeper('./Demo/Demon.php');
		
		if($script->isAlive()) {
			$script->stop();
		} else {
			$script->start();
		}
	} catch(DemonKeeperException $e) {
		print_r($e);
	} catch(Exception $e) {
		print_r($e);
	}

### PHP Version

PHP 5.3.0

### Options

- `hell`	   : `php` by default, but you can set the path/to/your/binary to choose the habitat of your Demon.
- `throw`	   : `true` by default, the code will throw an Exception or set it to false and check the _errors array.
- `background` : `true` by default, make the script run asynchronously.

Contribute
----------

- Issue Tracker: https://github.com/bogue89/Demonkeeper/issues
- Source Code: https://github.com/bogue89/Demonkeeper

Support
-------

If you are having issues or doubts, please let me know.

License
-------
The project is licensed under the MIT license.