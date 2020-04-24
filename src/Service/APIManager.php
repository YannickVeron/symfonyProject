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
        return $trailer['results'][0];
        if(! empty($trailer['results'][0])) { // take the first trailer
           return $trailer['results'][0]; 
        }else{
            return ["key" => "notrailer"];
        }
    }

    public function getSimilar(int $id)
    {
        $url = "https://api.themoviedb.org/3/movie/".$id."/similar?api_key=".$this->secret."&language=fr&page=1";
        $responseCategorie = $this->client->request('GET', $url);
        return $responseCategorie->toArray();
    }
}