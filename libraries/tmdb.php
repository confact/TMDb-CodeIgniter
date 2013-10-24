<?php
###########################
class Tmdb{
    #<CONSTANTS>
    #@var string url of API TMDB
    const _API_URL_ = "http://api.themoviedb.org/3/";

    #@var string Version of this class
    const VERSION = '0.0.2';

    #@var string API KEY
    private $_apikey;

    #@var string Default language
    private $_lang;

    #@var string url of TMDB images
    private $_imgUrl;
    #</CONSTANTS>
###############################################################################################################
    /**
     * Construct Class
     * @param string apikey
     * @param string language default is english
     */
    public function  __construct($apikey,$lang='en') {
        // Load config file
        $this->_obj =& get_instance();
        $this->_obj->load->config('tmdb');
        // Cache need to be fixed
        $this->_obj->load->driver('cache');

        //Assign Api Key
        $this->setApikey($this->_obj->config->item('tmdbapi'));

        //Setting Language
        $this->setLang($this->_obj->config->item('tmdbdefaultlang'));

        //Get Configuration
        $conf = $this->getConfig();
        if (empty($conf)){echo "Unable to read configuration, verify that the API key is valid";exit;}

        //set Images URL contain in config
        $this->setImageURL($conf);
    }//end of __construct

    /** Setter for the API-key
     * @param string $apikey
     * @return void
     */
    private function setApikey($apikey) {
        $this->_apikey = (string) $apikey;
    }//end of setApikey

    /** Getter for the API-key
     *  no input
     **  @return string
     */
    private function getApikey() {
        return $this->_apikey;
    }//end of getApikey

    /** Setter for the default language
     * @param string $lang
     * @return void
     **/
    public function setLang($lang="en") {
        $this->_lang = $lang;
    }//end of setLang

    /** Getter for the default language
     * no input
     * @return string
     **/
    public function getLang() {
        return $this->_lang;
    }//end of getLang

    /**
     * Set URL of images
     * @param  $config Configurarion of API
     * @return array
     */
    public function setImageURL($config) {
        $this->_imgUrl = (string) $config['images']["base_url"];
    } //end of setImageURL

    /** Getter for the URL images
     * no input
     * @return string
     */
    public function getImageURL($size="original") {
        return $this->_imgUrl . $size;
    }//end of getImageURL

    /**
     * movie Alternative Titles
     * http://api.themoviedb.org/3/movie/$id/alternative_titles
     * @param array  titles
     */
    public function movieTitles($idMovie) {
        $titleTmp = $this->movieInfo($idMovie,"alternative_titles",false);
        foreach ($titleTmp['titles'] as $titleArr){
            $title[]=$titleArr['title']." - ".$titleArr['iso_3166_1'];
        }
        return $title;
    }//end of movieTitles

    /**
     * movie translations
     * http://api.themoviedb.org/3/movie/$id/translations
     * @param array  translationsInfo
     */
    public function movieTrans($idMovie)
    {
        $transTmp = $this->movieInfo($idMovie,"translations",false);

        foreach ($transTmp['translations'] as $transArr){
            $trans[]=$transArr['english_name']." - ".$transArr['iso_639_1'];
        }
        return $trans;
    }//end of movieTrans

    /**
     * movie Trailer
     * http://api.themoviedb.org/3/movie/$id/trailers
     * @param array  trailerInfo
     */
    public function movieTrailer($idMovie) {
        $trailer = $this->movieInfo($idMovie,"trailers",false);
        return $trailer;
    } //movieTrailer


    /**
     * movie Detail
     * http://api.themoviedb.org/3/movie/$id
     * @param array  movieDetail
     */
    public function movieDetail($idMovie)
    {
        return $this->movieInfo($idMovie,"",false);
    }//end of movieDetail

    /**
     * movie Poster
     * http://api.themoviedb.org/3/movie/$id/images
     * @param array  moviePoster
     */
    public function moviePoster($idMovie)
    {
        $posters = $this->movieInfo($idMovie,"images",false);
        $posters =$posters['posters'];
        return $posters;
    }//end of movie Poster
    
    /**
     * movie Backdrops
     * http://api.themoviedb.org/3/movie/$id/images
     * @param array  moviePoster
     */
    public function movieBackdrops($idMovie)
    {
        $posters = $this->movieInfo($idMovie,"images",false);
        $posters =$posters['backdrops'];
        return $posters;
    }//end of movie Backdrops

    /**
     * movie Casting
     * http://api.themoviedb.org/3/movie/$id/casts
     * @param array  movieCast
     */
    public function movieCast($idMovie, $justNames = FALSE)
    {
        $castingTmp = $this->movieInfo($idMovie,"casts",false);
        $casting = array();
        if($justNames) {
            foreach ($castingTmp['cast'] as $castArr){
                $casting[]=array("name" => $castArr['name'], "id" => $castArr['id']);
            }
        }
        else {
            foreach ($castingTmp['cast'] as $castArr){
                $casting[$castArr['character']]=$castArr['name'];
            }
        }
        return $casting;
    }//end of movieCast


    /**
     * movie Crew
     * http://api.themoviedb.org/3/movie/$id/crew
     * @param array  movieCast
     */
    public function movieCrew($idMovie, $justNames = FALSE)
    {
        $castingTmp = $this->movieInfo($idMovie,"casts",false);
        $crew = array();
        if($justNames) {
            foreach ($castingTmp['crew'] as $castArr){
                $crew[]=array("name" => $castArr['name'], "id" => $castArr['id'], "job" => $castArr['job']);
            }
        }
        else {
            foreach ($castingTmp['crew'] as $castArr){
                $crew[$castArr['character']]=$castArr['name'];
            }
        }
        return $crew;
    }//end of movieCrew

    /**
     * Movie Info
     * http://api.themoviedb.org/3/movie/$id
     * @param array  movieInfo
     */
    public function movieInfo($idMovie,$option="",$print=false){
        $option = (empty($option))?"":"/" . $option;
        $params = "movie/" . $idMovie . $option;
        $movie= $this->_call($params,"");
        return $movie;
    }//end of movieInfo

    /**
     * Search Movie
     * http://api.themoviedb.org/3/search/movie?api_keyf&language&query=future
     * @param string  $peopleName
     */
    public function searchMovie($movieTitle, $year = false){
    	if(!$year) {
        	$movieTitle="query=".urlencode($movieTitle);
		}
		else {
			$movieTitle="query=".urlencode($movieTitle) . "&year=".urlencode($year);
		}
        return $this->_call("search/movie",$movieTitle,$this->_lang);
    }//end of searchMovie

    /**
     * Jobs list
     * http://api.themoviedb.org/3/job/list
     */
    public function jobs(){
        $params = "job/list";
        $jobs= $this->_call($params,"");
        return $jobs;
    }//end of jobs


    /**
     * Get Confuguration of API
     * configuration
     * http://api.themoviedb.org/3/configuration?apikey
     * @return array
     */
    public function getConfig() {
        return $this->_call("configuration","");
    }//end of getConfig

    /**
     * Latest Movie
     * http://api.themoviedb.org/3/latest/movie?api_key
     * @return array
     */
    public function latestMovie() {
        return $this->_call('latest/movie','');
    }
    /**
     * Now Playing Movies
     * http://api.themoviedb.org/3/movie/now-playing?api_key&language&page
     * @param integer $page
     */
    public function nowPlayingMovies($page=1) {
        return $this->_call('movie/now-playing', 'page='.$page);
    }

    /**
     * Makes the call to the API
     *
     * @param string $action	API specific function name for in the URL
     * @param string $text		Unencoded paramter for in the URL
     * @return string
     */
    private function _call($action,$text,$lang=""){
        // # http://api.themoviedb.org/3/movie/11?api_key=XXX
        $lang=(empty($lang))?$this->getLang():$lang;
        $url= Tmdb::_API_URL_.$action."?api_key=".$this->getApikey()."&language=".$lang."&".$text;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);

        $results = curl_exec($ch);
        $headers = curl_getinfo($ch);

        $error_number = curl_errno($ch);
        $error_message = curl_error($ch);

        curl_close($ch);
        // header('Content-Type: text/html; charset=iso-8859-1');
        $results = json_decode(($results),true);
        if($this->_obj->config->item('tmdbcache')) {
            $type = $this->_obj->config->item('tmdbcachetype');
            if($key) {
                $this->_obj->cache->$type->save($key, $results, 604800);
            }
        }
        return (array) $results;
    }//end of _call


} //end of class

?>
