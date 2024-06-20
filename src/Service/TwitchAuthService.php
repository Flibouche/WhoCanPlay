<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Exception;

class TwitchAuthService
{
    // Déclaration des propriétés privées pour les dépendances
    private HttpClientInterface $httpClient;
    private string $clientId;
    private string $clientSecret;
    private LoggerInterface $logger;

    // Constructeur pour injecter les dépendances HttpClientInterface, clientId, clientSecret et LoggerInterface
    public function __construct(HttpClientInterface $httpClient, string $clientId, string $clientSecret, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
    }

    // Méthode pour obtenir le token d'accès
    public function getAccessToken(): string
    {
        // Envoi de la requête pour obtenir le token d'accès OAuth2 de Twitch
        $response = $this->httpClient->request('POST', 'https://id.twitch.tv/oauth2/token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ],
        ]);

        // Conversion de la réponse en tableau
        $data = $response->toArray();

        // Vérification si le token d'accès est présent dans la réponse
        if (isset($data['access_token'])) {
            return $data['access_token'];
        } else {
            // Log de l'erreur si le token d'accès n'est pas obtenu
            $this->logger->error('Failed to get access token', ['response' => $data]);
            // Lève une exception en cas d'échec
            throw new Exception('Failed to get access token');
        }
    }

    // Méthode pour obtenir l'ID client
    public function getClientId(): string
    {
        return $this->clientId;
    }
}
