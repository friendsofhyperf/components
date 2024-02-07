<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\ValidatedDTO\Datasets;

use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\HttpMessage\Upload\UploadedFile;

class ValidatedFileDTO extends ValidatedDTO
{
    public UploadedFile $file;

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [];
    }

    protected function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png',
        ];
    }
}
