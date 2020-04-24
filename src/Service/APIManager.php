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
        $link = "https://api.themoviedb.org/3/discover/movie?api_key=".$this->secret;
        $response = $this->client->request('GET', $link);
        $content = $response->toArray();
        return $content["results"];
    }

    public function getMovie(int $id)
    {
        $link = "https://api.themoviedb.org/3/movie/".$id."?api_key=".$this->secret."&language=fr-FR";
        $response = $this->client->request('GET', $link);
        return $response->toArray();
    }

    public function getTrailer(int $id)
    {
        $links = "https://api.themoviedb.org/3/movie/".$id."/videos?api_key=".$this->secret."&language=fr-FR";
        $responses = $this->client->request('GET', $links);
        $trailer=  $responses->toArray();
        return $trailer['results'][0];
    }
}