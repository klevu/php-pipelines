<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Stage;

use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesNotSupportedTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log implements PipelineInterface
{
    use StagesNotSupportedTrait;

    public const ARGUMENT_KEY_MESSAGE = 'message';
    public const ARGUMENT_KEY_LOG_LEVEL = 'logLevel';

    /**
     * @var LoggerInterface|null
     */
    private readonly ?LoggerInterface $logger;
    /**
     * @var string
     */
    private readonly string $identifier;
    /**
     * @var string
     */
    private string $message = '';
    /**
     * @var string
     */
    private string $logLevel = LogLevel::DEBUG;

    /**
     * @param LoggerInterface|null $logger
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidPipelineArgumentsException
     * @throws InvalidClassException
     */
    public function __construct(
        ?LoggerInterface $logger = null,
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
        $container = Container::getInstance();

        try {
            if (null === $logger) {
                $logger = $container->get(LoggerInterface::class);
                if ($logger && !($logger instanceof LoggerInterface)) {
                    throw new InvalidClassException(
                        identifier: LoggerInterface::class,
                        instance: $logger,
                    );
                }
            }
        } catch (ClassNotFoundException) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }
        /** @var LoggerInterface $logger */
        $this->logger = $logger;

        array_walk($stages, [$this, 'addStage']);
        if ($args) {
            $this->setArgs($args);
        }

        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param mixed[] $args
     * @return void
     * @throws InvalidPipelineArgumentsException
     */
    public function setArgs(array $args): void
    {
        if (array_key_exists(static::ARGUMENT_KEY_MESSAGE, $args)) {
            $this->message = $this->prepareMessageArgument(
                message: $args[static::ARGUMENT_KEY_MESSAGE],
                arguments: $args,
            );
        }

        if (array_key_exists(static::ARGUMENT_KEY_LOG_LEVEL, $args)) {
            $this->logLevel = $this->prepareLogLevelArgument(
                logLevel: $args[static::ARGUMENT_KEY_LOG_LEVEL],
                arguments: $args,
            );
        }
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): mixed {
        $this->logger?->log(
            level: $this->logLevel,
            message: $this->message,
            context: [
                'identifier' => $this->getIdentifier(),
                'payload' => $payload,
            ],
        );

        return $payload;
    }

    /**
     * @param mixed $message
     * @param mixed[]|null $arguments
     * @return string
     * @thorws InvalidPipelineArgumentsException
     */
    private function prepareMessageArgument(
        mixed $message,
        ?array $arguments,
    ): string {
        if (null === $message) {
            return '';
        }

        if ($message instanceof \Stringable) {
            $message = (string)$message;
        }

        if (!is_string($message)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Message argument (%s) must be null|string|%s; Received %s',
                    static::ARGUMENT_KEY_MESSAGE,
                    \Stringable::class,
                    get_debug_type($message),
                ),
            );
        }

        return $message;
    }

    /**
     * @param mixed $logLevel
     * @param mixed[]|null $arguments
     * @return string
     * @thorws InvalidPipelineArgumentsException
     */
    private function prepareLogLevelArgument(
        mixed $logLevel,
        ?array $arguments,
    ): string {
        if (null === $logLevel) {
            return LogLevel::DEBUG;
        }

        if (!is_string($logLevel)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Log Level argument (%s) must be null|string; Received %s',
                    static::ARGUMENT_KEY_LOG_LEVEL,
                    get_debug_type($logLevel),
                ),
            );
        }

        $validLogLevels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];
        if (!in_array($logLevel, $validLogLevels, true)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Log Level argument (%s) value is not a recognised PSR log level (Refer: %s): %s',
                    static::ARGUMENT_KEY_LOG_LEVEL,
                    LogLevel::class,
                    $logLevel,
                ),
            );
        }

        return $logLevel;
    }
}
