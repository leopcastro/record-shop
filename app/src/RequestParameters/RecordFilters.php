<?php

declare(strict_types=1);

namespace App\RequestParameters;

use Symfony\Component\Validator\Constraints as Assert;

class RecordFilters implements Validatable
{
    /**
     * @Assert\Type("string")
     * @Assert\Length(max="100")
     *
     * @var string|null
     */
    private ?string $title;

    /**
     * @Assert\Type("string")
     * @Assert\Length(max="100")
     *
     * @var string|null
     */
    private ?string $artist;

    public function __construct(?string $title, ?string $artist)
    {
        $this->title = $title;
        $this->artist = $artist;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getArtist(): ?string
    {
        return $this->artist;
    }
}
