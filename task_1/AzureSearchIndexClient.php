<?php

declare(strict_types=1);

final readonly class AzureSearchIndexClient extends AbstractAzureClient implements SearchIndexClientInterface
{
    private const string INDEX_URL = 'https://%s.search.windows.net/indexes/%s?api-version=2023-11-01';
    private const string INDEX_DOC_URL = 'https://%s.search.windows.net/indexes/%s/docs/index?api-version=2023-11-01';
    private const string SEARCH_URL = 'https://%s.search.windows.net/indexes/%s/docs/search?api-version=2023-11-01';

    public function __construct(private string $azureSearchApiKey, private string $azureSearchService)
    {
    }

    /**
     * @throws JsonException
     */
    public function createIndex(array $indexDefinition): string
    {
        $url = sprintf(self::INDEX_URL, $this->azureSearchService, $indexDefinition['name']);
        $body = json_encode($indexDefinition, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        [$responseBody, $responseCode] = $this->executeRequest(
            'PUT',
            $url,
            $body,
            [
                'Content-Type: application/json',
                'api-key: '.$this->azureSearchApiKey,
                'Content-Length: '.strlen($body),
            ],
            null,
            'creating index'
        );

        if ($responseCode >= 400) {
            throw new RuntimeException(sprintf(
                'Azure Search Engine returned an error (PUT %s, HTTP %d): %s',
                $url,
                $responseCode,
                $responseBody
            ));
        }

        return sprintf(self::SEARCH_URL, $this->azureSearchService, $indexDefinition['name']);
    }

    /**
     * @throws JsonException
     */
    public function load(string $indexName, array $products): void
    {
        $url = sprintf(self::INDEX_DOC_URL, $this->azureSearchService, $indexName);
        $documents = array_map(
            fn (Product $product) => ['@search.action' => 'mergeOrUpload', ...$product->getIndexDocument()],
            array_values($products)
        );
        $body = json_encode(['value' => $documents], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        [$responseBody, $responseCode] = $this->executeRequest(
            'POST',
            $url,
            $body,
            [
                'Content-Type: application/json',
                'api-key: '.$this->azureSearchApiKey,
            ],
            60,
            'indexing documents'
        );

        if ($responseCode >= 400) {
            throw new RuntimeException(sprintf(
                'Azure Search returned an error (POST %s, HTTP %d): %s',
                $url,
                $responseCode,
                $responseBody
            ));
        }
    }

    public function deleteIndex(string $indexName): void
    {
        $url = sprintf(self::INDEX_URL, $this->azureSearchService, $indexName);
        [$responseBody, $responseCode] = $this->executeRequest(
            'DELETE',
            $url,
            null,
            [
                'api-key: '.$this->azureSearchApiKey,
            ],
            null,
            'deleting index'
        );

        if ($responseCode >= 400) {
            throw new RuntimeException(sprintf(
                'Azure Search returned an error (DELETE %s, HTTP %d): %s',
                $url,
                $responseCode,
                $responseBody
            ));
        }
    }

    /**
     * @throws JsonException
     */
    public function deleteDocuments(string $indexName, array $documentIds): void
    {
        if (empty($documentIds)) {
            return;
        }

        $url = sprintf(self::INDEX_DOC_URL, $this->azureSearchService, $indexName);
        $documents = array_map(
            fn (string $documentId) => ['@search.action' => 'delete', 'id' => $documentId],
            array_values($documentIds)
        );
        $body = json_encode(['value' => $documents], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        [$responseBody, $responseCode] = $this->executeRequest(
            'POST',
            $url,
            $body,
            [
                'Content-Type: application/json',
                'api-key: '.$this->azureSearchApiKey,
            ],
            null,
            'deleting documents'
        );

        if ($responseCode >= 400) {
            throw new RuntimeException(sprintf(
                'Azure Search returned an error when deleting documents (POST %s, HTTP %d): %s',
                $url,
                $responseCode,
                $responseBody
            ));
        }
    }
}
