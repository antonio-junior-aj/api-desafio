<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"comment":"Índice da tabela"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8, options={"comment":"Tipo de pessoa: F- Física (tem CPF); J- Jurídica (tem CNPJ)"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=14, options={"comment":"CPF: 111.111.112-00(grava sem máscara); CNPJ: 55.238.879/0001-04(grava sem máscara)"})
     */
    private $cpf_cnpj;

    /**
     * @ORM\Column(type="boolean", options={"comment":"Boleano para adicionar a blacklist"})
     */
    private $blacklist;

    /**
     * @ORM\Column(type="text", nullable=true, options={"comment":"Razão para entrar na blacklist(opcional)"})
     */
    private $blacklist_reason;

    /**
     * @ORM\Column(type="integer", options={"comment":"Ordenação manual dos dados"})
     */
    private $order_number;

    /**
     * @ORM\Column(type="datetime", options={"comment":"Data de inserção"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment":"Data de alteração"})
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCpfCnpj(): ?string
    {
        return $this->cpf_cnpj;
    }

    public function setCpfCnpj(string $cpf_cnpj): self
    {
        $this->cpf_cnpj = $cpf_cnpj;

        return $this;
    }

    public function getBlacklist(): ?bool
    {
        return $this->blacklist;
    }

    public function setBlacklist(bool $blacklist): self
    {
        $this->blacklist = $blacklist;

        return $this;
    }

    public function getBlacklistReason(): ?string
    {
        return $this->blacklist_reason;
    }

    public function setBlacklistReason(?string $blacklist_reason): self
    {
        $this->blacklist_reason = $blacklist_reason;

        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->order_number;
    }

    public function setOrderNumber(int $order_number): self
    {
        $this->order_number = $order_number;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $update_at): self
    {
        $this->updated_at = $update_at;

        return $this;
    }
}
