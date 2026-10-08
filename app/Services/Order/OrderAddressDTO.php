<?php

namespace App\Services\Order;

class OrderAddressDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public string $state,
        public string $postalCode,
        public string $country,
        public ?string $phone,
        public string $type,
    ) {}

    public static function fromArray(array $data, string $type): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            addressLine1: $data['address_line_1'] ?? $data['address1'] ?? '',
            addressLine2: $data['address_line_2'] ?? $data['address2'] ?? null,
            city: $data['city'],
            state: $data['state'],
            postalCode: $data['postal_code'] ?? $data['postalCode'] ?? '',
            country: $data['country'],
            phone: $data['phone'] ?? null,
            type: $type,
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'phone' => $this->phone,
            'type' => $this->type,
        ];
    }
}