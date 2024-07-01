<?php

namespace App\Service;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class IgdbApiService
{
    // Déclaration des propriétés privées pour les dépendances
    private HttpClientInterface $httpClient;
    private TwitchAuthService $authService;
    private LoggerInterface $logger;

    // Constructeur pour injecter les dépendances HttpClientInterface, TwitchAuthService et LoggerInterface
    public function __construct(HttpClientInterface $httpClient, TwitchAuthService $authService, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->authService = $authService;
        $this->logger = $logger;
    }

    // Méthode pour obtenir les jeux, avec un nom optionnel pour la recherche
    public function getGames(string $name = null): array
    {
        // Obtention du token d'accès via le service d'authentification
        $accessToken = $this->authService->getAccessToken();
        // Log du token d'accès
        $this->logger->info('Access token obtained', ['access_token' => $accessToken]);

        // Construction de la requête pour l'API IGDB
        $queryString = "fields id,name; limit 50;";
        if ($name) {
            $queryString = "search \"$name\"; " . $queryString;
        }

        // Envoi de la requête à l'API IGDB
        $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
            'headers' => [
                'Client-ID' => $this->authService->getClientId(),
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => $queryString,
        ]);

        // Traitement de la réponse de l'API
        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);

        // Vérification du statut de la réponse
        if ($statusCode !== 200) {
            // Log de l'erreur si le statut n'est pas 200
            $this->logger->error('Failed to fetch all games', [
                'status_code' => $statusCode,
                'response' => $data,
            ]);
            // Lève une exception en cas d'échec
            throw new Exception('Failed to fetch all games');
        }

        // Retourne les données obtenues
        return $data;
    }

    // Méthode pour obtenir les détails d'un jeu par ID
    public function getGameById(string $idGameApi = null): array
    {
        // Obtention du token d'accès via le service d'authentification
        $accessToken = $this->authService->getAccessToken();
        // Log du token d'accès
        $this->logger->info('Access token obtained', ['access_token' => $accessToken]);
    
        // Construction de la requête pour l'API IGDB
        $queryString = "fields id,name,cover.image_id,slug; where id = $idGameApi;";
    
        // Envoi de la requête à l'API IGDB
        $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
            'headers' => [
                'Client-ID' => $this->authService->getClientId(),
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => $queryString,
        ]);
    
        // Traitement de la réponse de l'API
        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);
    
        // Vérification du statut de la réponse
        if ($statusCode !== 200) {
            // Log de l'erreur si le statut n'est pas 200
            $this->logger->error('Failed to fetch game details', [
                'status_code' => $statusCode,
                'response' => $data,
            ]);
            // Lève une exception en cas d'échec
            throw new Exception('Failed to fetch game details');
        }
    
        // Retourne les données obtenues
        return $data;
    }

    // Méthode pour obtenir les détails d'un jeu par ID ou par nom
    public function getGameDetails(string $gameId = null, string $gameName = null)
    {
        // Vérification si l'un des paramètres est présent
        if ($gameId === null && $gameName === null) {
            throw new \InvalidArgumentException('Either gameId or gameName must be provided');
        }

        // Obtention du token d'accès via le service d'authentification
        $accessToken = $this->authService->getAccessToken();
        // Log du token d'accès
        $this->logger->info('Access token obtained', ['access_token' => $accessToken]);

        // Construction de la requête pour l'API IGDB en fonction des paramètres fournis
        $queryString = '';

        if ($gameId !== null) {
            $queryString = "fields name,genres.name,platforms.name,screenshots.url,screenshots.image_id,cover.url,cover.image_id; where id = $gameId;";
        } elseif ($gameName !== null) {
            $queryString = "fields *; where name ~ \"$gameName\";";
        }

        // Envoi de la requête à l'API IGDB
        $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
            'headers' => [
                'Client-ID' => $this->authService->getClientId(),
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => $queryString,
        ]);

        // Traitement de la réponse de l'API
        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);

        // Vérification du statut de la réponse
        if ($statusCode !== 200) {
            // Log de l'erreur si le statut n'est pas 200
            $this->logger->error('Failed to fetch game details', [
                'status_code' => $statusCode,
                'response' => $data,
            ]);
            // Lève une exception en cas d'échec
            throw new Exception('Failed to fetch game details');
        }

        // Retourne les données obtenues
        return $data;
    }
}