<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Exception;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TwitchAuthService
{
    // Déclaration des propriétés privées pour les dépendances
    private HttpClientInterface $httpClient;
    private string $clientId;
    private string $clientSecret;
    private LoggerInterface $logger;
    private CacheInterface $cache;

    // Constructeur pour injecter les dépendances HttpClientInterface, clientId, clientSecret et LoggerInterface
    public function __construct(HttpClientInterface $httpClient, string $clientId, string $clientSecret, LoggerInterface $logger, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    // Méthode pour obtenir le token d'accès
    public function getAccessToken(): string
    {
        // On récupère d'abord le token depuis le cache, si le token n'est pas dans le cache ou a expiré, on exécute le callback pour obtenir un nouveau token
        return $this->cache->get('twitch_access_token', function (CacheItemInterface $item) {
            $item->expiresAfter(3600); // Expire après 1 heure

            // Envoi de la requête pour obtenir le token d'accès OAuth2 de Twitch
            $response = $this->httpClient->request('POST', 'https://id.twitch.tv/oauth2/token', [
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ],
                'timeout' => 5, // Temps maximum pour établir la connexion
                'max_duration' => 10, // Durée totale maximale de la requête, incluant le temps de connexion et le temps de réponse
            ]);

            // Conversion de la réponse en tableau
            $data = $response->toArray();

            // Vérification si le token d'accès est présent dans la réponse
            if (!isset($data['access_token'])) {
                // Log de l'erreur si le token d'accès n'est pas obtenu
                $this->logger->error('Failed to get access token', ['response' => $data]);
                // Lève une exception en cas d'échec
                throw new Exception('Failed to get access token');
            }

            return $data['access_token'];
        });
    }

    // Méthode pour obtenir l'ID client
    public function getClientId(): string
    {
        return $this->clientId;
    }
}
