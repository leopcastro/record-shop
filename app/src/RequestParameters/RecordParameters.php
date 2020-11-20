<?php

declare(strict_types=1);

namespace App\RequestParameters;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RecordParameters implements Validatable
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(max="100")
     */
    private $title;

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(max="100")
     */
    private $artist;

    /**
     * @Assert\NotBlank
     *  @Assert\Regex(
     *     pattern="/^(\d+|\d+\.\d{1,2})$/",
     *     message="This values should be an integer or a float with 1 to 2 decimals"
     * )
     * @Assert\GreaterThan(0)
     */
    private $price;

    /**
     * @Assert\Regex(
     *     pattern="/^\d{1,4}$/",
     *     message="This values should be an integer with maximum 4 digits"
     * )
     * @Assert\Positive
     * @Assert\Callback({RecordParameters::class,"releasedYearValidation"})
     */
    private $releasedYear;

    public function __construct($title, $artist, $price, $releasedYear)
    {
        $this->title = $title;
        $this->artist = $artist;
        $this->price = $price;
        $this->releasedYear = $releasedYear;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * @return ?int
     */
    public function getReleasedYear(): ?int
    {
        if ($this->releasedYear) {
            return (int) $this->releasedYear;
        }

        return $this->releasedYear;
    }

    public static function releasedYearValidation($object, ExecutionContextInterface $context, $payload): void
    {
        if ((int) $object <= (int) date('Y')) {
            return;
        }

        $context->buildViolation('This value should be less or equal than current year')
            ->atPath($context->getPropertyPath())
            ->addViolation()
        ;
    }
}
