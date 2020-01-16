<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 */
class Country
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $alpha2;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $alpha3;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $nameEnGB;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $nameFrFr;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getAlpha2(): ?string
    {
        return $this->alpha2;
    }

    public function setAlpha2(string $alpha2): self
    {
        $this->alpha2 = $alpha2;

        return $this;
    }

    public function getAlpha3(): ?string
    {
        return $this->alpha3;
    }

    public function setAlpha3(string $alpha3): self
    {
        $this->alpha3 = $alpha3;

        return $this;
    }

    public function getNameEnGB(): ?string
    {
        return $this->nameEnGB;
    }

    public function setNameEnGB(string $nameEnGB): self
    {
        $this->nameEnGB = $nameEnGB;

        return $this;
    }

    public function getNameFrFr(): ?string
    {
        return $this->nameFrFr;
    }

    public function setNameFrFr(string $nameFrFr): self
    {
        $this->nameFrFr = $nameFrFr;

        return $this;
    }
}
