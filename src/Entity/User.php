<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Validator\Constraints as Assert; 
use Symfony\Polyfill\Mbstring\Mbstring;

/**
 * User entity class
 */
#[ApiResource(
    paginationItemsPerPage: 5,
)]
#[ApiFilter(OrderFilter::class)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    /** 
     * Unique identifier of the user
     * @var \Symfony\Component\Uid\Uuid
     */
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    // using manual UUID v7 token creation workaround
    // (for more @see https://github.com/symfony/symfony/discussions/53331)
    // #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    // #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    /** 
     * User's first name
     * @var string
     */
    #[ORM\Column(length: 48)]
    #[ApiFilter(SearchFilter::class)]
    #[Assert\NotBlank(normalizer: [Mbstring::class, 'mb_trim'])]
    #[Assert\Length(max: 48)]
    #[Assert\Regex(
        // first name validation pattern accepting uppercase letter only starting names separated by single spaces
        // and consisting of Latin and/or non-Latin letters, apostrophe and dash symbols only.
        // (e.g. Miguel María, Антон Павлович, יעקב שבתאי)
        // (not supporting ideographic and other non-alphabetic symbols / writing systems e.g. Chinese, Japanese, ...)
        // 
        // @todo ensure that dash and apostrophe can't follow each other
        // @see https://en.wikipedia.org/wiki/List_of_writing_systems
        // @see https://www.regular-expressions.info/unicode.html#prop
        // @see https://www.quora.com/Do-Chinese-characters-have-letter-case-i-e-distinct-upper-and-lower-cases
        pattern: '/^(\p{Lu}[\p{L}\'-]*)( \p{Lu}[\p{L}\'-]*)*$/u',
        message: 'The "name" attribute accepts uppercase letter starting forenames containing letters, dash or apostrophe symbols only and separated by single space symbols.'
    )]
    private string $name;

    /**
     * User's surname
     * @var string
     */
    #[ORM\Column(length: 255)]
    #[ApiFilter(SearchFilter::class)]
    #[Assert\NotBlank(normalizer: [Mbstring::class, 'mb_trim'])]
    #[Assert\Length(max: 255)]
    private string $surname;

    /**
     * User's email
     * @var string
     */
    #[ORM\Column(length: 255, unique: true)]
    #[ApiFilter(SearchFilter::class)]
    #[Assert\NotBlank(normalizer: [Mbstring::class, 'mb_trim'])]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    private string $email;

    /**
     * Gender of the user
     * @var Gender
     */
    #[ORM\Column(enumType: Gender::class)]
    #[ApiFilter(SearchFilter::class)]
    #[Assert\NotBlank(normalizer: [Mbstring::class, 'mb_trim'])]
    private Gender $gender;

    /**
     * Set of roles assigned to the user
     * @var Role[]
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Role::class)]
    #[ApiFilter(SearchFilter::class)]
    #[Assert\Count(min: 1)]
    private array $roles = [];

    /**
     * Notes related to the user
     * @var string
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[ApiFilter(SearchFilter::class)]
    #[Assert\Length(max: 255)]
    private ?string $note = null;

    /**
     * Activity flag indicating user's
     * availability within system's boundary
     * @var bool
     */
    #[ORM\Column]
    #[ApiFilter(BooleanFilter::class)]
    #[Assert\NotBlank]
    #[Assert\Type('bool')]
    private bool $active;

    // public function __construct(string $name, string $surname, string $email, Gender $gender, ?string $note = null, bool $active, array $roles) {
    //     // picking up all the attribute values
    //     // from the constructor
    //     $this->name = $name;
    //     $this->surname = $surname;
    //     $this->email = $email;
    //     $this->gender = $gender;
    //     $this->note = $note;
    //     $this->active = $active;
    //     $this->roles = $roles;

    /**
     * Implicit constructor covering necessary entity class instance initializations
     */
    public function __construct()
    {
        // setting the ID explicitly 
        // to UUID v7
        // (due to symfony 6.4 ignores configs requesting usage of v7 and uses v6 instead anyway)
        // (for more info on this @see https://github.com/symfony/symfony/discussions/53331)
        $this->id = Uuid::v7();
    }

    /**
     * Get user's unique identifier
     * 
     * @return Uuid|null
     */
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * Set user's unique identifier
     * 
     * @param mixed $id
     * @return User
     */
    public function setId(?Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get name of the user
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name of the user
     * 
     * @param string $name
     * @return User
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get surname of the user
     * 
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * Set surname of the user
     * 
     * @param string $surname
     * @return User
     */
    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get user's email
     * 
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set user's email
     * 
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get user's gender
     * 
     * @return \App\Entity\Gender
     */
    public function getGender(): Gender
    {
        return $this->gender;
    }

    /**
     * Set user's gender
     * 
     * @param \App\Entity\Gender $gender
     * @return User
     */
    public function setGender(Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get roles of user
     * 
     * @return Role[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Set roles of user
     *
     * @param Role[] $roles
     * @return static
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get notes related to given user
     * 
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * Set notes notes related to given user
     * 
     * @param mixed $note
     * @return User
     */
    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Check user's availability indicating flag
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Set user's availability indicating flag
     * 
     * @param bool $active
     * @return User
     */
    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

}