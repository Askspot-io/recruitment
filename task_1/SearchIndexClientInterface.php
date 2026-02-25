<?php

declare(strict_types=1);

interface SearchIndexClientInterface
{
    public function createIndex(array $indexDefinition): string;

    public function load(string $indexName, array $products): void;

    public function deleteIndex(string $indexName): void;

    public function deleteDocuments(string $indexName, array $documentIds): void;
}
