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

	public static function from(array $data): self
	{
		$contact = new self;

		$contact->name = $data['name'];
		$contact->role = $data['role'] ?? null;
		$contact->email = $data['email'] ?? null;
		$contact->phone = $data['phone'] ?? null;
		$contact->mobile = $data['mobile'] ?? null;

		$contact->avatar = isset($data['avatar'])
			? Image::from($data['avatar'])
			: null;

		$contact->address = isset($data['address'])
			? Address::from($data['address'])
			: null;

		return $contact;
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
