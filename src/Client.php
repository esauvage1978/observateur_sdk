<?php

declare(strict_types=1);

namespace Observateur;

use Psr\Log\LoggerInterface;

final class Client
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.observateurcentral.fr',
        private readonly bool $failOpen = true,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log(string $severity, string $message, array $context = []): void
    {
        $this->post('/api/ingest/v1/logs', [
            'logs' => [[
                'severity' => strtoupper($severity),
                'message' => $message,
                'context' => $context,
                'timestamp' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
            ]],
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * @param list<array<string, mixed>> $metrics
     */
    public function metrics(array $metrics): void
    {
        $this->post('/api/ingest/v1/metrics', ['metrics' => $metrics]);
    }

    public function heartbeat(string $name): void
    {
        $this->post('/api/ingest/v1/heartbeat', ['name' => $name]);
    }

    public function deployment(string $version, ?string $commit = null): void
    {
        $this->post('/api/ingest/v1/deployments', ['version' => $version, 'commit' => $commit]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function post(string $path, array $payload): void
    {
        try {
            $body = json_encode($payload, \JSON_THROW_ON_ERROR);
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nX-Api-Key: {$this->apiKey}\r\n",
                    'content' => $body,
                    'timeout' => 2,
                    'ignore_errors' => true,
                ],
            ]);
            $result = @file_get_contents(rtrim($this->baseUrl, '/').$path, false, $context);
            if (false === $result && !$this->failOpen) {
                throw new \RuntimeException('Ingest request failed.');
            }
        } catch (\Throwable $exception) {
            $this->logger?->warning('Observateur ingest failed: '.$exception->getMessage());
            if (!$this->failOpen) {
                throw $exception;
            }
        }
    }
}
