<?php
// src/Service/APIManager.php
namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class APIManager
{
    private $client;
    private $secret;

    public function __construct($apiKey)
    {
        $this->client = HttpClient::create();
        $this->secret = $apiKey;
    }

    public function getDiscover()
    {
        $url = "https://api.themoviedb.org/3/discover/movie?api_key=".$this->secret;
        $response = $this->client->request('GET', $url);
        $content = $response->toArray();
        return $content["results"];
    }

    public function getMovie(int $id)
    {
        $url = "https://api.themoviedb.org/3/movie/".$id."?api_key=".$this->secret."&language=fr-FR";
        $response = $this->client->request('GET', $url);
        return $response->toArray();
    }

    public function getTrailer(int $id)
    {
        $url = "https://api.themoviedb.org/3/movie/".$id."/videos?api_key=".$this->secret."&language=fr-FR";
        $responses = $this->client->request('GET', $url);
        $trailer=  $responses->toArray();
        if(empty($trailer['results'])) { 
           return ["key" => "notrailer"];
        }
        return $trailer['results'][0]; // take the first trailer
        
    }

    public function getSimilar(int $id)
    {
        $url = "https://api.themoviedb.org/3/movie/".$id."/similar?api_key=".$this->secret."&language=fr&page=1";
        $responseCategorie = $this->client->request('GET', $url);
        return $responseCategorie->toArray();
    }

    public function getCategories() {
        $url = 'https://api.themoviedb.org/3/genre/movie/list?api_key='.$this->secret.'&language=fr-FR';
        $response = $this->client->request('GET', $url);
        $genres = $response->toArray();
        return $genres["genres"];
    }


    public function getListMovieByCategorie($genre) {
        $url = 'https://api.themoviedb.org/3/discover/movie?api_key='.$this->secret.'&with_genres='.$genre;
        $response = $this->client->request('GET', $url);
        $genres = $response->toArray();
        return $genres["results"];
    }




    
}