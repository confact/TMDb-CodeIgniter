<?php
/**
 * A PHP wrapper for TMDb
 * 
 * @author confact <hakan@dun.se>
 * @author glamorous <hello@glamorous.be>
 * @project MMDb
 * @release <2.1>
 */
class Tmdb
{
    const _API_URL_ = "http://api.themoviedb.org/3/";
    const VERSION = '0.0.2';

    private $_apikey;
    private $_imgUrl;

    /**
     * Construct Class
     * 
     * @param string $apikey the apikey
     * 
     * @return void
     */

    public function __construct($apikey)
    {
        // Load config file
        $this->_obj = &get_instance();
        $this->_obj->load->config('tmdb');
        // Cache need to be fixed
        $this->_obj->load->driver('cache');

        //Assign Api Key
        $this->setApikey($this->_obj->config->item('tmdbapi'));

        //Get Configuration
        $conf = $this->getConfig();
        if (empty($conf)) {
            echo "Unable to read configuration, verify that the API key is valid";
            exit;
        }

        //set Images URL contain in config
        $this->setImageURL($conf);
    }

    /** 
     * Setter for the API-key
     * 
     * @param string $apikey
     * 
     * @return void
     */

    private function setApikey($apikey)
    {
        $this->_apikey = (string) $apikey;
    }

    /** 
     * Getter for the API-key
     *  
     * @return string
     */

    private function getApikey()
    {
        return $this->_apikey;
    }

    /**
     * Set URL of images
     * 
     * @param array $config configurarion of API
     * 
     * @return array
     */

    public function setImageURL($config)
    {
        $this->_imgUrl = (string) $config['images']["base_url"];
    }

    /**
     * Getter for the URL images
     * 
     * @param string $size
     * 
     * @return array
     */

    public function getImageURL($size = "original")
    {
        return $this->_imgUrl . $size;
    }

    /**
     * movie Alternative Titles
     * http://api.themoviedb.org/3/movie/$id/alternative_titles
     * 
     * @param string $idMovie
     * 
     * @return array
     */

    public function movieTitles($idMovie)
    {
        $titleTmp = $this->movieInfo($idMovie, "alternative_titles", false);
        foreach ($titleTmp['titles'] as $titleArr) {
            $title[] = $titleArr['title'] . " - " . $titleArr['iso_3166_1'];
        }

        return $title;
    }

    /**
     * movie translations
     * http://api.themoviedb.org/3/movie/$id/translations
     * 
     * @param string $idMovie
     * 
     * @return array
     */

    public function movieTrans($idMovie)
    {
        $transTmp = $this->movieInfo($idMovie, "translations", false);

        foreach ($transTmp['translations'] as $transArr) {
            $trans[] = $transArr['english_name'] . " - " . $transArr['iso_639_1'];
        }

        return $trans;
    }

    /**
     * movie Trailer
     * http://api.themoviedb.org/3/movie/$id/trailers
     * @param string $idMovie
     * 
     * @return array
     */

    public function movieTrailer($idMovie)
    {
        $trailer = $this->movieInfo($idMovie, "trailers", false);

        return $trailer;
    }

    /**
     * movie Detail
     * http://api.themoviedb.org/3/movie/$id
     * 
     * @param string $idMovie
     * 
     * @return array
     */
    public function movieDetail($idMovie)
    {
        return $this->movieInfo($idMovie, "", false);
    }

    /**
     * movie Poster
     * http://api.themoviedb.org/3/movie/$id/images
     * 
     * @param string $idMovie
     * 
     * @return array
     */
    public function moviePoster($idMovie)
    {
        $posters = $this->movieInfo($idMovie, "images", false);
        $posters = $posters['posters'];

        return $posters;
    }

    /**
     * movie Backdrops
     * http://api.themoviedb.org/3/movie/$id/images
     * 
     * @param string $idMovie
     * 
     * @return array
     */
    public function movieBackdrops($idMovie)
    {
        $posters = $this->movieInfo($idMovie, "images", false);
        $posters = $posters['backdrops'];

        return $posters;
    }

    /**
     * movie Casting
     * http://api.themoviedb.org/3/movie/$id/casts
     * 
     * @param string  $idMovie
     * @param boolean $justNames
     * 
     * @return array moviecast
     */
    public function movieCast($idMovie, $justNames = false)
    {
        $castingTmp = $this->movieInfo($idMovie, "casts", false);
        $casting = array();
        if ($justNames) {
            foreach ($castingTmp['cast'] as $castArr) {
                $casting[] = array("name" => $castArr['name'], "id" => $castArr['id']);
            }
        } else {
            foreach ($castingTmp['cast'] as $castArr) {
                $casting[$castArr['character']] = $castArr['name'];
            }
        }

        return $casting;
    }

    /**
     * movie Crew
     * http://api.themoviedb.org/3/movie/$id/crew
     * 
     * @param string $idMovie
     * @param string $justNames
     * 
     * @return array
     */
    public function movieCrew($idMovie, $justNames = false)
    {
        $castingTmp = $this->movieInfo($idMovie, "casts", false);
        $crew = array();
        if ($justNames) {
            foreach ($castingTmp['crew'] as $castArr) {
                $crew[] = array("name" => $castArr['name'], "id" => $castArr['id'], "job" => $castArr['job']);
            }
        } else {
            foreach ($castingTmp['crew'] as $castArr) {
                $crew[$castArr['character']] = $castArr['name'];
            }
        }

        return $crew;
    }

    /**
     * Movie Info
     * http://api.themoviedb.org/3/movie/$id
     * 
     * @param string  $idMovie
     * @param string  $option
     * @param boolean $print
     * 
     * @return array
     */

    public function movieInfo($idMovie, $option = "", $print = false)
    {
        $option = (empty($option)) ? "" : "/" . $option;
        $params = "movie/" . $idMovie . $option;
        $movie = $this->_call($params, "");

        return $movie;
    }

    /**
     * Search Movie
     * http://api.themoviedb.org/3/search/movie?api_keyf&language&query=future
     * 
     * @param string  $movieTitle
     * @param boolean $year
     * 
     * @return array
     */

    public function searchMovie($movieTitle, $year = false)
    {
        if (!$year) {
            $movieTitle = "query=" . urlencode($movieTitle);
        } else {
            $movieTitle = "query=" . urlencode($movieTitle) . "&year=" . urlencode($year);
        }

        return $this->_call("search/movie", $movieTitle);
    }

    /**
     * Jobs list
     * http://api.themoviedb.org/3/job/list
     * 
     * @return array
     */

    public function jobs()
    {
        $params = "job/list";
        $jobs = $this->_call($params, "");

        return $jobs;
    }

    /**
     * Get Confuguration of API
     * configuration
     * http://api.themoviedb.org/3/configuration?apikey
     * 
     * @return array
     */

    public function getConfig()
    {
        return $this->_call("configuration", "");
    }

    /**
     * Latest Movie
     * http://api.themoviedb.org/3/latest/movie?api_key
     * @return array
     */
    public function latestMovie()
    {
        return $this->_call('latest/movie', '');
    }

    /**
     * Now Playing Movies
     * http://api.themoviedb.org/3/movie/now-playing?api_key&language&page
     * 
     * @param int $page
     * 
     * @return array
     */

    public function nowPlayingMovies($page = 1)
    {
        return $this->_call('movie/now-playing', 'page=' . $page);
    }

    /**
     * Makes the call to the API
     *
     * @param string $action API specific function name for in the URL
     * @param string $text   Unencoded paramter for in the URL
     * 
     * @return array
     */

    private function _call($action, $text)
    {
        // # http://api.themoviedb.org/3/movie/11?api_key=XXX
        $url = Tmdb::_API_URL_ . $action . "?api_key=" . $this->getApikey() . "&" . $text;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);

        $results = curl_exec($ch);
        $headers = curl_getinfo($ch);

        curl_close($ch);
        // header('Content-Type: text/html; charset=iso-8859-1');
        $results = json_decode(($results), true);

        return (array) $results;
    }
}