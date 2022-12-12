<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ValidatedDTO;

use FriendsOfHyperf\ValidatedDTO\Exception\InvalidJsonException;
use Hyperf\Command\Command;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\RequestInterface;

abstract class ValidatedDTO
{
    protected array $validatedData = [];

    /**
     * @throws ValidationException
     */
    public function __construct(array $data)
    {
        $validator = ApplicationContext::getContainer()
            ->get(ValidatorFactoryInterface::class)
            ->make(
                $data,
                $this->rules(),
                $this->messages(),
                $this->attributes()
            );

        $validator->fails() ? $this->failedValidation($validator) : $this->passedValidation($validator);
    }

    public function __get(string $name): mixed
    {
        return $this->validatedData[$name] ?? null;
    }

    /**
     * Creates a DTO instance from a valid JSON string.
     *
     * @throws InvalidJsonException|ValidationException
     */
    public static function fromJson(string $json): ValidatedDTO
    {
        $jsonDecoded = json_decode($json, true);

        if (! is_array($jsonDecoded)) {
            throw new InvalidJsonException();
        }

        return new static($jsonDecoded);
    }

    /**
     * Creates a DTO instance from a Request.
     * @param \Hyperf\HttpServer\Contract\RequestInterface $request
     * @throws ValidationException
     */
    public static function fromRequest(RequestInterface $request): ValidatedDTO
    {
        return new static($request->all());
    }

    /**
     * Creates a DTO instance from the given model.
     *
     * @throws ValidationException
     */
    public static function fromModel(Model $model): ValidatedDTO
    {
        return new static($model->toArray());
    }

    /**
     * Creates a DTO instance from the given command arguments.
     *
     * @throws ValidationException
     */
    public static function fromCommandArguments(Command $command): ValidatedDTO
    {
        return new static((fn () => $this->input->getArguments())->call($command));
    }

    /**
     * Creates a DTO instance from the given command options.
     *
     * @throws ValidationException
     */
    public static function fromCommandOptions(Command $command): ValidatedDTO
    {
        return new static((fn () => $this->input->getOptions())->call($command));
    }

    /**
     * Creates a DTO instance from the given command arguments and options.
     *
     * @throws ValidationException
     */
    public static function fromCommand(Command $command): ValidatedDTO
    {
        return new static(array_merge(
            (fn () => $this->input->getArguments())->call($command),
            (fn () => $this->input->getOptions())->call($command)
        ));
    }

    /**
     * Returns the DTO validated data in array format.
     */
    public function toArray(): array
    {
        return $this->validatedData;
    }

    /**
     * Returns the DTO validated data in a JSON string format.
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->validatedData, $options);
    }

    /**
     * Creates a new model with the DTO validated data.
     */
    public function toModel(string $model): Model
    {
        return new $model($this->validatedData);
    }

    /**
     * Defines the custom messages for validator errors.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Defines the custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Handles a passed validation attempt.
     */
    protected function passedValidation(ValidatorInterface $validator): void
    {
        $this->validatedData = $validator->validated();

        foreach ($this->defaults() as $key => $value) {
            if (! in_array($key, array_keys($this->rules()))) {
                continue;
            }

            $this->validatedData[$key] = $value;
        }
    }

    /**
     * Handles a failed validation attempt.
     *
     * @throws ValidationException
     */
    protected function failedValidation(ValidatorInterface $validator): void
    {
        throw new ValidationException($validator);
    }

    /**
     * Defines the default values for the properties of the DTO.
     */
    abstract protected function defaults(): array;

    /**
     * Defines the validation rules for the DTO.
     */
    abstract protected function rules(): array;
}
