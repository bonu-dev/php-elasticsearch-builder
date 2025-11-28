#!/usr/bin/env php
<?php

declare(strict_types=1);

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;

require_once __DIR__ . '/../vendor/autoload.php';

$host = getenv('ELASTICSEARCH_HOST');
if ($host === false) {
    echo 'missing env ELASTICSEARCH_HOST' . PHP_EOL;
    exit(1);
}

$client = ClientBuilder::create()
    ->setHosts([$host])
    ->build();

try {
    $client->indices()->delete(['index' => 'spotify']);
} catch (ClientResponseException) {
}

$client->indices()->create([
    'index' => 'spotify',
]);

$client->indices()->putMapping([
    'index' => 'spotify',
    'body' => [
        'dynamic' => 'strict',
        'properties' => [
            'track_id' => [
                'type' => 'keyword',
            ],
            'track_name' => [
                'type' => 'text',
            ],
            'track_number' => [
                'type' => 'integer',
            ],
            'track_popularity' => [
                'type' => 'integer',
            ],
            'explicit' => [
                'type' => 'boolean',
            ],
            'artist_name' => [
                'type' => 'text',
            ],
            'artist_popularity' => [
                'type' => 'integer',
            ],
            'artist_followers' => [
                'type' => 'integer',
            ],
            'artist_genres' => [
                'type' => 'text', // @todo
            ],
            'album_id' => [
                'type' => 'keyword',
            ],
            'album_name' => [
                'type' => 'text',
            ],
            'album_release_date' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd',
            ],
            'album_total_tracks' => [
                'type' => 'integer',
            ],
            'album_type' => [
                'type' => 'keyword',
            ],
            'track_duration_min' => [
                'type' => 'float',
            ],
        ],
    ],
]);

$file = new SplFileObject(
    implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'tests', 'dataset.csv']),
    'rb',
);

echo 'importing dataset... this may take a while' . PHP_EOL;

while (($line = $file->fgetcsv(',', escape: "\\")) !== false) {
    [
        $trackId,
        $trackName,
        $trackNumber,
        $trackPopularity,
        $explicit,
        $artistName,
        $artistPopularity,
        $artistFollowers,
        $artistGenres,
        $albumId,
        $albumName,
        $albumReleaseDate,
        $albumTotalTracks,
        $albumType,
        $trackDurationMin,
    ] = $line;

    $client->index([
        'index' => 'spotify',
        'body' => [
            'track_id' => $trackId,
            'track_name' => $trackName,
            'track_number' => $trackNumber,
            'track_popularity' => $trackPopularity,
            'explicit' => $explicit === 'TRUE',
            'artist_name' => $artistName,
            'artist_popularity' => $artistPopularity,
            'artist_followers' => $artistFollowers,
            'artist_genres' => $artistGenres,
            'album_id' => $albumId,
            'album_name' => $albumName,
            'album_release_date' => $albumReleaseDate,
            'album_total_tracks' => $albumTotalTracks,
            'album_type' => $albumType,
            'track_duration_min' => $trackDurationMin,
        ],
    ]);
}

echo 'dataset imported' . PHP_EOL;
