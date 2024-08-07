<?php

namespace App\Service;

use Exception;
use InvalidArgumentException;
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
        $queryString = "fields id,name,cover.url,cover.image_id; where category = 0 & version_parent = null; limit 5;";
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

    public function getGameByIds(array $ids = []): array
    {
        // Obtention du token d'accès via le service d'authentification
        $accessToken = $this->authService->getAccessToken();
        // Log du token d'accès
        $this->logger->info('Access token obtained', ['access_token' => $accessToken]);

        foreach ($ids as $id) {
            if (!is_int($id)) {
                throw new InvalidArgumentException('All game IDs must be integers !');
            }
        }

        $idsString = implode(',', $ids);

        // Construction de la requête pour l'API IGDB
        $queryString = "fields id,name,cover.image_id,slug; limit 500; where id = ($idsString);";

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
    public function getGameDetails(string $gameId = null, string $gameName = null): array
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
            $queryString = "fields name,genres.name,platforms.name,screenshots.url,screenshots.image_id,cover.url,cover.image_id,involved_companies.company.name,involved_companies.developer; where id = $gameId;";
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

        // Je filtre les jeux pour ne garder que les développeurs (au booléen true)
        // J'utilise array_map pour applique une fonction à chaque élément du tableau
        $filteredData = array_map(function ($game) {
            // Je crée une nouvelle liste vide pour les développeurs de ce jeu
            $game['developers'] = [];

            // Je vérifie si le jeu a des compagnies impliquées
            if (isset($game['involved_companies'])) {
                // Pour chaque compagnie impliquée dans ce jeu
                foreach ($game['involved_companies'] as $company) {
                    // Je vérifie si cette compagnie est un développeur
                    if ($company['developer']) {
                        // Si oui, j'ajoute son nom à ma liste de développeurs
                        $game['developers'][] = $company['company']['name'];
                    }
                }
            }

            // Je supprime l'ancienne liste des compagnies impliquées, je n'en ai plus besoin
            unset($game['involved_companies']);

            // Je renvoie le jeu modifié
            return $game;
        }, $data);

        // Retourne les données filtrées
        return array_values($filteredData);
    }

    public function getGamesAndDetailsByIds(array $ids = []): array
    {
        // Obtention du token d'accès via le service d'authentification
        $accessToken = $this->authService->getAccessToken();
        // Log du token d'accès
        $this->logger->info('Access token obtained', ['access_token' => $accessToken]);

        // Vérification que tous les IDs sont des entiers
        foreach ($ids as $id) {
            if (!is_int($id)) {
                throw new InvalidArgumentException('All game IDs must be integers!');
            }
        }

        // Construction de la chaîne d'IDs pour la requête
        $idsString = implode(',', $ids);

        // Construction de la requête pour l'API IGDB
        $queryString = "fields id,name,cover.image_id,slug,genres.name,platforms.name,involved_companies.company.name,involved_companies.developer;
                        where id = ($idsString);
                        limit 500;";

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

        // Je filtre les jeux pour ne garder que les développeurs (au booléen true)
        // J'utilise array_map pour applique une fonction à chaque élément du tableau
        $filteredData = array_map(function ($game) {
            // Je crée une nouvelle liste vide pour les développeurs de ce jeu
            $game['developers'] = [];

            // Je vérifie si le jeu a des compagnies impliquées
            if (isset($game['involved_companies'])) {
                // Pour chaque compagnie impliquée dans ce jeu
                foreach ($game['involved_companies'] as $company) {
                    // Je vérifie si cette compagnie est un développeur
                    if ($company['developer']) {
                        // Si oui, j'ajoute son nom à ma liste de développeurs
                        $game['developers'][] = $company['company']['name'];
                    }
                }
            }

            // Je supprime l'ancienne liste des compagnies impliquées, je n'en ai plus besoin
            unset($game['involved_companies']);

            // Je renvoie le jeu modifié
            return $game;
        }, $data);

        // Retourne les données filtrées
        return array_values($filteredData);
    }
}
