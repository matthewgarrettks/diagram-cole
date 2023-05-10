<?php

namespace App\Entity;

use App\Repository\ScrapeResponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScrapeResponseRepository::class)]
class FetchResponse
{

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column]
  private ?int $code = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $data = null;

  #[ORM\Column]
  private ?\DateTimeImmutable $updatedAt = null;

  /**
   * @param  int|null  $code
   * @param  string|null  $data
   */
  public function __construct(?int $code, ?string $data)
  {
    $this->code = $code;
    $this->data = $data;
  }


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

  public function getData(): ?string
  {
    return $this->data;
  }

  public function setData(?string $data): self
  {
    $this->data = $data;

    return $this;
  }

  public function getUpdatedAt(): ?\DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }

}
