<?php

declare(strict_types=1);

namespace Observateur;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

final class MonologHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly Client $client,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        try {
            $this->client->log($record->level->getName(), $record->message, $record->context);
        } catch (\Throwable) {
            // Fail-open: never break the host application.
        }
    }
}
