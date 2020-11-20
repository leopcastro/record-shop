<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecordRepository::class)
 * @ORM\Table(name="record")
 */
class Record
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $artist;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private float $price;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $releasedYear;

    public function __construct(string $title, string $artist, float $price)
    {
        $this->title = $title;
        $this->artist = $artist;
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     */
    public function setArtist(string $artist): void
    {
        $this->artist = $artist;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return ?int
     */
    public function getReleasedYear(): ?int
    {
        return $this->releasedYear;
    }

    /**
     * @param ?int $releasedYear
     */
    public function setReleasedYear(?int $releasedYear): void
    {
        $this->releasedYear = $releasedYear;
    }
}
