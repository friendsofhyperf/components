<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\GrpcValidation\Annotation;

use DivisionByZeroError;
use Google\Protobuf\Internal\GPBDecodeException;
use Google\Protobuf\Internal\Message;
use Hyperf\Context\Context;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Request\FormRequest;
use JsonException;
use Psr\Container\ContainerInterface;

class ValidationAspect extends AbstractAspect
{
    public array $annotations = [
        Validation::class,
    ];

    public function __construct(protected ContainerInterface $container, protected ValidatorFactoryInterface $validatorFactory)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        [$rules, $messages, $resolve] = $this->getValidationRules($proceedingJoinPoint);
        $protobufMessage = $this->getProtobufMessage($proceedingJoinPoint);

        if (
            $rules
            && $protobufMessage
            && $data = $this->serializeToJsonArray($protobufMessage)
        ) {
            Context::set($protobufMessage::class, $data);
            $validator = $this->validatorFactory->make($data, $rules, $messages);
            Context::set(ValidatorInterface::class, $validator);

            if ($resolve && $validator->fails()) {
                throw new ValidationException($validator->errors()->first());
            }
        }

        return $proceedingJoinPoint->process();
    }

    protected function getValidationRules(ProceedingJoinPoint $proceedingJoinPoint): array
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Validation|null $annotation */
        $annotation = $metadata->method[Validation::class] ?? null;

        if (! $annotation) {
            return [null, null, false];
        }

        if ($annotation->formRequest && $this->container->has($annotation->formRequest)) {
            $scene = $annotation->scene ?: $proceedingJoinPoint->methodName;
            /** @var FormRequest $formRequest */
            $formRequest = $this->container->get($annotation->formRequest)->scene($scene);
            $rules = method_exists($formRequest, 'rules') ? $formRequest->rules() : [];
            $messages = $formRequest->messages();
        } else {
            $rules = $annotation->rules;
            $messages = $annotation->messages;
        }

        return [$rules, $messages, $annotation->resolve];
    }

    protected function getProtobufMessage(ProceedingJoinPoint $proceedingJoinPoint): ?Message
    {
        foreach ($proceedingJoinPoint->getArguments() as $argument) {
            if ($argument instanceof Message) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * @throws GPBDecodeException
     * @throws DivisionByZeroError
     */
    protected function serializeToJsonArray(Message $message): ?array
    {
        try {
            return json_decode($message->serializeToJsonString(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
    }
}
