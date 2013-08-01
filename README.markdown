# TMDb PHP API #

## TmDB API Version 3 support ##

I have starting working with the tmdb API and found out that this one is old and updated/forked from @glamorous with one or two added missed calls for getting directors and producers.

## Why this class ##

Because of the lack of a general and recent php class (api 2.1) for TMDb. The CakePHP class is outdated and doesn't provide you to search for people. With this class you can search and get Movie and People information.  
The second reason why this class is made is very simple: I love the work they do at [TMDb](http://themoviedb.org). They provide a great API so everyone can use there database to make cool applications.

## Why this fork ##
If you want to use this class with Codeigniter will you need to do some tweaks and changes, that could be a bit hard. This will make it easier with a stand-alone config-file for configuration.

## Requirements ##

- PHP 5.2.x or higher
- cURL
- TMDb API-key

## How to use ##

### Initialize the class ###

    <?php
		class Tmdb_test extends CI_Controller {

			function __construct() {
				parent::__construct();
				$this->load->library('tmdb');
			}

			function index() {
				$id = "tt0088247";
				$json = $this->tmdb->movieInfo($id);
				print_r(json_decode($json));
			}
		}
	?>

## License ##
This code is based on @glamorous TmDB v3 API lib.

 Licensed under BSD (http://www.opensource.org/licenses/bsd-license.php)
