<?php

namespace SchemaImmo\Estate;

use SchemaImmo\Arrayable;
use SchemaImmo\Contact;

class Social implements Arrayable
{
	/**
	 * @var Contact[]
	 */
	public array $contacts = [];

	public static function from(array $data): self
	{
		$social = new self;

		foreach ($data['contacts'] as $contact) {
			$social->contacts[] = Contact::from($contact);
		}

		return $social;
	}

	public function toArray(): array
	{
		return [
			'contacts' => array_map(
				fn (Contact $contact) => $contact->toArray(),
				$this->contacts
			),
		];
	}
}
