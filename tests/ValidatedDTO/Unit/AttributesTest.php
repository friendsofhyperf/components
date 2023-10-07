<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Tests\ValidatedDTO\Datasets\UserAttributesDTO;
use Hyperf\Validation\ValidationException;

beforeEach(function () {
    $this->subject_name = faker()->name;
    $this->subject_email = faker()->unique()->safeEmail;
});

it('throws exception when trying to instantiate a ValidatedDTO with invalid data using the Rules attribute')
    ->expect(fn () => new UserAttributesDTO([]))
    ->throws(ValidationException::class);

it('instantiates a ValidatedDTO validating its data using the Rules attribute and getting default values from the DefaultValue attribute', function () {
    $userDTO = new UserAttributesDTO([
        'name' => $this->subject_name,
        'email' => $this->subject_email,
    ]);

    expect($userDTO)->toBeInstanceOf(UserAttributesDTO::class)
        ->and($userDTO->validatedData)
        ->toBe([
            'name' => $this->subject_name,
            'email' => $this->subject_email,
            'active' => true,
        ])
        ->and($userDTO->validator->passes())
        ->toBeTrue();
});

it('maps the DTO data using the Map attribute', function () {
    $userDTO = new UserAttributesDTO([
        'user_name' => $this->subject_name,
        'email' => $this->subject_email,
    ]);

    expect($userDTO)->toBeInstanceOf(UserAttributesDTO::class)
        ->and($userDTO->validatedData)
        ->toBe([
            'name' => $this->subject_name,
            'email' => $this->subject_email,
            'active' => true,
        ])
        ->and($userDTO->validator->passes())
        ->toBeTrue()
        ->and($userDTO->toArray())
        ->toBe([
            'full_name' => $this->subject_name,
            'email' => $this->subject_email,
            'active' => true,
        ]);
});
