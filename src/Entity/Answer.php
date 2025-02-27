<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\AnswerRepository;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Question $question = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $value = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Form $form = null;

    #[ORM\ManyToMany(targetEntity: AnswerOption::class)]
    #[ORM\JoinTable(name: "answer_selected_options")]
    private Collection $selectedOptions;

    public function __construct()
    {
        $this->selectedOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(?Form $form): static
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return Collection<int, AnswerOption>
     */
    public function getSelectedOptions(): Collection
    {
        return $this->selectedOptions;
    }

    public function addSelectedOption(AnswerOption $option): static
    {
        if (!$this->selectedOptions->contains($option)) {
            $this->selectedOptions->add($option);
        }

        return $this;
    }

    public function removeSelectedOption(AnswerOption $option): static
    {
        $this->selectedOptions->removeElement($option);
        return $this;
    }

    /**
     * ✅ Геттер для получения ОДНОГО варианта ответа (только для вопросов с radio!)
     */
    public function getSelectedOption(): ?AnswerOption
    {
        return $this->selectedOptions->first() ?: null;
    }

    /**
     * ✅ Сеттер для установки ТОЛЬКО ОДНОГО варианта ответа (только для вопросов с radio!)
     */
    public function setSelectedOption(?AnswerOption $option): static
    {
        $this->selectedOptions->clear(); // ✅ Убираем все старые значения (если были)
        if ($option) {
            $this->selectedOptions->add($option);
        }
        return $this;
    }
}
