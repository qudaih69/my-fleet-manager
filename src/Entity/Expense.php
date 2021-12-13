<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseRepository::class)
 */
class Expense
{

     /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $expense_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $invoice_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $issued_on;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;


    /**
     * @ORM\Column(type="float")
     */
    private $value_ti;

    /**
     * @ORM\Column(type="float")
     */
    private $tax_rate;

    /**
     * @ORM\Column(type="float")
     */
    private $value_te;

    /**
     * @ORM\ManyToOne(targetEntity=GasStation::class, inversedBy="expenses", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $gas_station;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class, inversedBy="expenses", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getexpenseNumber(): ?string
    {
        return $this->expense_number;
    }

    public function setexpenseNumber(string $expense_number): self
    {
        $this->expense_number = $expense_number;

        return $this;
    }


    public function getInvoiceNumber(): ?string
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(string $invoice_number): self
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIssuedOn(): ?\DateTimeInterface
    {
        return $this->issued_on;
    }

    public function setIssuedOn(\DateTimeInterface $issued_on): self
    {
        $this->issued_on = $issued_on;

        return $this;
    }


    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }


    public function getValueTi(): ?float
    {
        return $this->value_ti;
    }

    public function setValueTi(float $value_ti): self
    {
        $this->value_ti = $value_ti;

        return $this;
    }

    public function getTaxRate(): ?float
    {
        return $this->tax_rate;
    }

    public function setTaxRate(float $tax_rate): self
    {
        $this->tax_rate = $tax_rate;

        return $this;
    }

    public function getValueTe(): ?float
    {
        return $this->value_te;
    }

    public function setValueTe(float $value_te): self
    {
        $this->value_te = $value_te;

        return $this;
    }

    public function getGasStation(): ?GasStation
    {
        return $this->gas_station;
    }

    public function setGasStation(?GasStation $gas_station): self
    {
        $this->gas_station = $gas_station;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }
}
