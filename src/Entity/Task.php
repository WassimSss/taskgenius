<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaskRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]

    #[Assert\NotBlank(message : "Le titre de la tache est obligatoire")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le titre de la tache doit avoir au moins 3 caractères", maxMessage: "Le titre de la tache doit avoir au maximum 255 caractères")]

    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description de la tache est obligatoire")]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La priorité de la tache est obligatoire")]

    private ?string $priority = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de création de la tache est obligatoire")]
    private $creation_date = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de rendu de la tache est obligatoire")]
    #[Assert\GreaterThanOrEqual(
         propertyPath: "creation_date",
         message: "La date de rendu de la tache doit être postérieure ou égale à la date de création de la tache."
    )]
    private $due_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creation_date;
    }

    public function setCreationDate(?\DateTime $creation_date): static
    {
        $this->creation_date = $creation_date;

        return $this;
    }

    public function getDueDate(): \DateTime
    {
        return $this->due_date;
    }

    public function setDueDate(?\DateTime $due_date): static
    {
        $this->due_date = $due_date;

        return $this;
    }
}
