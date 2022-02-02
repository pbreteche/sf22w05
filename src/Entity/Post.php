<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"main"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max=120)
     * @Groups({"main"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Groups({"detail"})
     */
    private $body;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"main"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, fetch="EAGER")
     * @Groups({"main"})
     *
     * Si besoin, personnalisation de la colonne de jointure:
     * ORM\JoinColumn(name="category_id", nullable=false)
     */
    private $classedBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getClassedBy(): ?Category
    {
        return $this->classedBy;
    }

    public function setClassedBy(?Category $classedBy): self
    {
        $this->classedBy = $classedBy;

        return $this;
    }
}
