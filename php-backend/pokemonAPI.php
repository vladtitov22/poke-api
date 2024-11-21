<?php

class PokemonAPI {
    private $pdo;

    private const APP_URL = 'https://pokeapi.co/api/v2/pokemon/';

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        try {
            $this->pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()]);
            exit;
        }
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $pokemon = isset($_GET['pokemon']) ? $_GET['pokemon'] : null;
            $this->processRequest($pokemon);
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $pokemon = isset($input['pokemon']) ? $input['pokemon'] : null;
            $this->processRequest($pokemon);
        } else {
            echo json_encode(['error' => 'Methode nicht unterstützt. Bitte GET oder POST verwenden.']);
        }
    }

    private function processRequest($pokemon) {
        if (!$pokemon) {
            echo json_encode(['error' => 'Bitte geben Sie den Namen eines Pokémon an.']);
            return;
        }

        $cachedData = $this->getCache($pokemon);
        if ($cachedData) {
            echo $cachedData;
            return;
        }

        $data = $this->fetchFromAPI($pokemon);
        if ($data) {
            $this->saveCache($pokemon, $data);
            echo $data;
        } else {
            echo json_encode(['error' => 'Pokémon nicht gefunden.']);
        }
    }

    private function fetchFromAPI($pokemon) {
        $url = self::APP_URL . strtolower($pokemon);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);

            if (isset($data['name'])) {
                return json_encode([
                    'name' => $data['name'],
                    'weight' => $data['weight'],
                    'height' => $data['height'],
                    'abilities' => array_map(function ($ability) {
                        return $ability['ability']['name'];
                    }, $data['abilities']),
                    'images' => [
                        'front' => $data['sprites']['front_default'] ?? null,
                        'back' => $data['sprites']['back_default'] ?? null,
                        'shiny' => $data['sprites']['front_shiny'] ?? null,
                    ],
                ]);
            }
        }

        return null;
    }

    private function getCache($pokemon) {
        $stmt = $this->pdo->prepare("SELECT data FROM pokemon_cache WHERE name = :name AND TIMESTAMPDIFF(SECOND, last_updated, NOW()) < 3600");
        $stmt->execute(['name' => strtolower($pokemon)]);
        return $stmt->fetchColumn();
    }

    private function saveCache($pokemon, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO pokemon_cache (name, data) VALUES (:name, :data)
            ON DUPLICATE KEY UPDATE data = :data, last_updated = CURRENT_TIMESTAMP");
        $stmt->execute(['name' => strtolower($pokemon), 'data' => $data]);
    }
}
?>