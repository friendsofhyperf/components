<?php

declare(strict_types=1);
/**
 * This file is part of grpc-validation.
 *
 * @link     https://github.com/friendofhyperf/grpc-validation
 * @document https://github.com/friendofhyperf/grpc-validation/blob/main/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/friendofhyperf/grpc-validation/blob/main/LICENSE
 */
namespace FriendsOfHyperf\GrpcValidation\Annotation;

use Google\Protobuf\Internal\Message;
use Hyperf\Context\Context;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Request\FormRequest;
use Psr\Container\ContainerInterface;
use ReflectionProperty;

#[Aspect()]
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
            && $data = $this->convertProtobufMessageToArray($protobufMessage)
        ) {
            Context::set(get_class($protobufMessage), $data);
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
        /** @var Validation $annotation */
        $annotation = $metadata->method[Validation::class] ?? null;

        if (! $annotation) {
            return [null, null, false];
        }

        if ($annotation->formRequest && $this->container->has($annotation->formRequest)) {
            $scene = $annotation->scene ?: $proceedingJoinPoint->methodName;
            /** @var FormRequest $formRequest */
            $formRequest = $this->container->get($annotation->formRequest)->scene($scene);
            $rules = $formRequest->rules();
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

    protected function convertProtobufMessageToArray(Message $message): array
    {
        $array = [];
        $reflect = new \ReflectionObject($message);
        $props = $reflect->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($props as $prop) {
            $prop->setAccessible(true);

            $name = $prop->getName();
            $value = $prop->getValue($message);

            if ($value instanceof Message) {
                $array[$name] = $this->convertProtobufMessageToArray($value);
            } else {
                $array[$name] = $value;
            }
        }

        return $array;
    }
}
