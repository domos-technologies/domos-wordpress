<?php

namespace SchemaImmo;

use SchemaImmo\Estate\Address;

class Contact implements Arrayable
{
	public string $name;

	public ?string $role = null;
	public ?string $email = null;
	public ?string $phone = null;
	public ?string $mobile = null;

	public ?Image $avatar = null;
	public ?Address $address = null;

    public function __construct(
        string $name,
        ?string $role = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $mobile = null,
        ?Image $avatar = null,
        ?Address $address = null
    ) {
        $this->name = $name;
        $this->role = $role;
        $this->email = $email;
        $this->phone = $phone;
        $this->mobile = $mobile;
        $this->avatar = $avatar;
        $this->address = $address;
    }

    public static function from(array $data): self
	{
        if (!isset($data['name'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.name', 'Missing name');
        }

        if (!is_string($data['name'])) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.name', 'Name must be a string');
        }

        if (isset($data['role']) && !is_string($data['role']) && $data['role'] !== null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.role', 'Role must be a string');
        }

        if (isset($data['email']) && !is_string($data['email']) && $data['email'] !== null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.email', 'Email must be a string');
        }

        if (isset($data['phone']) && !is_string($data['phone']) && $data['phone'] !== null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.phone', 'Phone must be a string');
        }

        if (isset($data['mobile']) && !is_string($data['mobile']) && $data['mobile'] !== null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.mobile', 'Mobile must be a string');
        }

        if (isset($data['avatar']) && !is_array($data['avatar']) && $data['avatar'] !== null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.avatar', 'Avatar must be an array');
        }

        if (isset($data['address']) && !is_array($data['address']) && $data['address'] !== null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.address', 'Address must be an array');
        }

        $name = Sanitizer::nullify_string($data['name'] ?? null);

        if ($name === null) {
            throw new \SchemaImmo\Exceptions\InvalidDataException('contact.name', 'Name cannot be empty');
        }

		return new self(
            $name,
            Sanitizer::nullify_string($data['role'] ?? null),
            Sanitizer::nullify_string($data['email'] ?? null),
            Sanitizer::nullify_string($data['phone'] ?? null),
            Sanitizer::nullify_string($data['mobile'] ?? null),
            isset($data['avatar'])
                ? Image::from($data['avatar'])
                : null,
            isset($data['address'])
                ? Address::from($data['address'])
                : null
        );
	}

	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'role' => $this->role,
			'email' => $this->email,
			'phone' => $this->phone,
			'mobile' => $this->mobile,
			'avatar' => $this->avatar?->toArray(),
			'address' => $this->address?->toArray(),
		];
	}
}
