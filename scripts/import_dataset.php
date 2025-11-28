#!/usr/bin/env php
<?php

declare(strict_types = 1);

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;

require_once __DIR__ . '/../vendor/autoload.php';

$host = $argv[1] ?? $_ENV['ELASTICSEARCH_HOST'];

$client = ClientBuilder::create()
    ->setHosts([$host])
    ->build();

try {
    $client->indices()->delete(['index' => 'ecommerce']);
} catch (ClientResponseException) {}

$client->indices()->create([
    'index' => 'ecommerce',
]);

$client->indices()->putMapping([
    'index' => 'ecommerce',
    'body' => [
        'dynamic' => 'strict',
        'properties' => [
            'stock_code' => [
                'type' => 'keyword',
            ],
            'description' => [
                'type' => 'text',
            ],
            'quantity' => [
                'type' => 'integer',
            ],
            'invoice_date' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ],
            'unit_price' => [
                'type' => 'float',
            ],
            'customer_id' => [
                'type' => 'integer',
            ],
            'country' => [
                'type' => 'keyword',
            ],
        ],
    ],
]);

$file = new SplFileObject(
    implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'tests', 'dataset.csv']),
    'rb',
);

echo 'importing dataset... this will take a while' . PHP_EOL;

while (($line = $file->fgetcsv(',', escape: "\\")) !== false) {
    [
        $invoiceNumber,
        $stockCode,
        $description,
        $quantity,
        $invoiceDate,
        $unitPrice,
        $customerId,
        $country
    ] = $line;

    $client->index([
        'index' => 'ecommerce',
        'id' => $invoiceNumber,
        'body' => [
            'stock_code' => $stockCode,
            'description' => $description,
            'quantity' => $quantity,
            'invoice_date' => DateTime::createFromFormat('m/d/Y H:i', $invoiceDate)->format('Y-m-d H:i:s'),
            'unit_price' => $unitPrice,
            'customer_id' => $customerId,
            'country' => $country,
        ],
    ]);
}

echo 'dataset imported' . PHP_EOL;