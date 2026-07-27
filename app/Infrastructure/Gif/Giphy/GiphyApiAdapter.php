<?php

namespace App\Infrastructure\Gif\Giphy;

use App\Domain\Gif\Collections\GifCollection;
use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Exceptions\GifProviderException;
use App\Domain\Gif\Ports\GifProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use UnexpectedValueException;

final readonly class GiphyApiAdapter implements GifProvider
{
    public function __construct(
        private Factory $http,
        private GiphyGifMapper $mapper,
        private string $baseUrl,
        private string $apiKey,
        private int $timeoutSeconds,
        private int $connectTimeoutSeconds,
    ){
        if(trim($this->baseUrl) === ''){
            throw new InvalidArgumentException('The GIPHY base URL cannot be empty.');
        }

        if(trim($this->apiKey) === ''){
            throw new InvalidArgumentException('The GIPHY API key cannot be empty.');
        }

        if($this->timeoutSeconds < 1){
            throw new InvalidArgumentException('The GIPHY timeout must be greater than zero.');
        }

        if($this->connectTimeoutSeconds < 1){
            throw new InvalidArgumentException('The GIPHY connection timeout must be greater than zero.');
        }
    }

    public function search(
        string $query,
        int $limit,
        int $offset,
    ): GifCollection {
        try {
            $response = $this->request()->get($this->url('/gifs/search'), [
                'api_key' => $this->apiKey,
                'q' => $query,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (ConnectionException $e) {
            throw GifProviderException::connectionFailed($e);
        }

        $this->ensureSuccessfulResponse($response);

        $payload = $this->responsePayload($response);
        $this->ensureNotSyntheticResponse($payload);

        $data = $payload['data'] ?? null;
        if (!is_array($data)) {
            throw GifProviderException::invalidResponse('The "data" field must be an array.');
        }

        $gifs = [];
        foreach ($data as $index => $item) {
            if (!is_array($item)){
                throw GifProviderException::invalidResponse(
                    sprintf('the item at index %s must be an object.', (string) $index)
                );
            }

            try {
                $gifs[] = $this->mapper->fromArray($item);
            } catch (
                UnexpectedValueException | InvalidArgumentException $e
            ) {
                throw GifProviderException::invalidResponse(
                    reason: sprintf('the GIF at index %s could not be mapped.', (string) $index),
                    previous: $e
                );
            }
        }

        return new GifCollection($gifs);
    }

    public function getById(string $id): ?Gif
    {
      try {
            $response = $this->request()->get(
                $this->url(
                    sprintf(
                        '/gifs/%s',
                        rawurlencode($id),
                    )
                ),
                [
                    'api_key' => $this->apiKey,
                ],
            );
        } catch (ConnectionException $exception) {
            throw GifProviderException::connectionFailed(
                $exception
            );
        }

        if ($response->notFound()) {
            return null;
        }

        $this->ensureSuccessfulResponse($response);

        $payload = $this->responsePayload($response);
        $this->ensureNotSyntheticResponse($payload);

        $data = $payload['data'] ?? null;

        if ($data === []) {
            return null;
        }

        if (!is_array($data)) {
            throw GifProviderException::invalidResponse(
                'the "data" field must be an object.'
            );
        }

        try {
            return $this->mapper->fromArray($data);
        } catch (
            UnexpectedValueException |
            InvalidArgumentException $exception
        ) {
            throw GifProviderException::invalidResponse(
                reason: 'the requested GIF could not be mapped.',
                previous: $exception,
            );
        }
    }

    private function request(): PendingRequest
    {
        return $this->http
            ->acceptJson()
            ->connectTimeout(
                $this->connectTimeoutSeconds
            )
            ->timeout(
                $this->timeoutSeconds
            );
    }

    private function url(string $path): string
    {
        return sprintf(
            '%s/%s',
            rtrim($this->baseUrl, '/'),
            ltrim($path, '/'),
        );
    }
   
    private function ensureSuccessfulResponse(
        Response $response,
    ): void {
        if (!$response->failed()) {
            return;
        }

        $providerMessage = $response->json(
            'meta.msg'
        );

        throw GifProviderException::requestFailed(
            statusCode: $response->status(),
            providerMessage: is_string($providerMessage)
                ? $providerMessage
                : null,
        );
    }
    /**
     * @return array<string, mixed>
     */
    private function responsePayload(
        Response $response,
    ): array {
        $payload = $response->json();

        if (!is_array($payload)) {
            throw GifProviderException::invalidResponse(
                'the response body is not a JSON object.'
            );
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function ensureNotSyntheticResponse(
        array $payload,
    ): void {
        $status = $payload['meta']['status'] ?? null;
        $responseId = $payload['meta']['response_id'] ?? null;
        $data = $payload['data'] ?? null;

        $isSyntheticResponse =
            $status === 200
            && $responseId === ''
            && $data === []
            && !array_key_exists('pagination', $payload);

        if ($isSyntheticResponse) {
            throw GifProviderException::invalidResponse(
                'GIPHY returned a synthetic error response.'
            );
        }
    }
}

?>