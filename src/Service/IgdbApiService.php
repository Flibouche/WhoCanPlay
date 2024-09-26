<?php

namespace App\Service;

use Exception;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    // Méthode pour obtenir les jeux
    public function getGames(?string $name = null): JsonResponse
    {
        $accessToken = $this->authService->getAccessToken();
        $this->logger->info('Access token obtained', ['access_token' => $accessToken]);

        // Je construis la requête pour l'API IGDB
        $queryString = "fields id,name,first_release_date,cover.image_id; where category = 0 & version_parent = null;";

        // Si un nom est fourni, je l'ajoute à la requête
        if ($name) {
            // Je convertis le nom en minuscules pour une recherche insensible à la casse
            $nameLower = strtolower($name);
            // Je recherche les jeux dont le nom ou un nom alternatif
            $queryString = "fields id,name,first_release_date,cover.image_id; 
                            where category = 0 & version_parent = null & 
                            (name ~ *\"$nameLower\"* | alternative_names.name ~ *\"$nameLower\"*);"; // ~ signifie "contient", * signifie "n'importe quel nombre de caractères"
        }

        $queryString .= " limit 5;"; // Je limite le nombre de résultats à 5

        $response = $this->httpClient->request('POST', 'https://api.igdb.com/v4/games', [
            'headers' => [
                'Client-ID' => $this->authService->getClientId(),
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'body' => $queryString,
        ]);

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);

        if ($statusCode !== 200) {
            $this->logger->error('Failed to fetch games', [
                'status_code' => $statusCode,
                'response' => $data,
                'query' => $queryString
            ]);
            throw new Exception('Failed to fetch games');
        }

        $formattedGames = array_map(function ($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'cover' => $game['cover'] ?? null,
                'date' => isset($game['first_release_date']) ? date('Y', $game['first_release_date']) : 'N/A',
            ];
        }, $data);

        // Tri des résultats pour mettre en premier ceux qui commencent exactement par la recherche
        if ($name) {
            usort($formattedGames, function ($currentGame, $comparisonGame) use ($name) {
                $nameStartPattern = '/^' . preg_quote($name, '/') . '/i';
                $currentGameStarts = preg_match($nameStartPattern, $currentGame['name']);
                $comparisonGameStarts = preg_match($nameStartPattern, $comparisonGame['name']);

                if ($currentGameStarts && !$comparisonGameStarts) {
                    return -1; // Le jeu actuel doit venir en premier
                } elseif (!$currentGameStarts && $comparisonGameStarts) {
                    return 1; // Le jeu de comparaison doit venir en premier
                }
                return 0; // Les deux jeux sont égaux en priorité
            });
        }

        $this->logger->info('Search results', [
            'query' => $name,
            'results_count' => count($formattedGames),
            'results' => $formattedGames
        ]);

        return new JsonResponse($formattedGames);
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
        $queryString = "fields id,name,cover.image_id,summary,slug; limit 500; where id = ($idsString);";

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

        if ($gameId !== null) {
            $queryString = "fields name,genres.name,platforms.name,screenshots.url,screenshots.image_id,cover.image_id,involved_companies.company.name,involved_companies.developer; where id = $gameId;";
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
