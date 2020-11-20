<?php

declare(strict_types=1);

namespace App\RequestParameters;

use Symfony\Component\Validator\Constraints as Assert;

class Pagination implements Validatable
{
    /**
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="It needs to be an integer"
     * )
     * @Assert\Positive
     */
    private $offset;

    /**
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="It needs to be an integer"
     * )
     * @Assert\LessThanOrEqual(100)
     * @Assert\Positive
     */
    private $limit;

    public function __construct($offset, $limit)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return (int) $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return (int) $this->limit;
    }
}
