<?php

namespace SchemaImmo;

use SchemaImmo\Estate\Address;
use SchemaImmo\Estate\Certifications;
use SchemaImmo\Estate\Coordinates;
use SchemaImmo\Estate\Media;
use SchemaImmo\Estate\Location;
use SchemaImmo\Estate\Social;
use SchemaImmo\Estate\Texts;
use SchemaImmo\Estate\Usage;
use SchemaImmo\Media\Documents;

class Estate implements Arrayable
{
    public string $id;
	public string $slug;
    public string $name;
    public Address $address;

	/**
	 * @var array<string, bool|array> $features
	 */
	public array $features = [];

	/**
	 * @var Building[] $buildings
	 */
	public array $buildings = [];

	public Texts $texts;
	public Media $media;
	public Location $location;
	public Certifications $certifications;
	public Social $social;
	public Usage $usage;
	public ?WebExpose $expose = null;
	public Documents $docs;

	public function __construct(
		?string $id = null,
		?string $slug = null,
		?string $name = null,
		?Address $address = null,

		/** @var array<string, bool|array> $features */
		array $features = [],

		/** @var Building[] $buildings */
		array $buildings = [],

		?Texts $texts = null,
		?Media $media = null,
		?Location $location = null,
		?Certifications $certifications = null,
		?Social $social = null,
		?Usage $usage = null,
		?WebExpose $expose = null,
		?Documents $docs = null
	)
	{
		if ($id !== null) {
			$this->id = $id;
		}

		if ($slug !== null) {
			$this->slug = $slug;
		}

		if ($name !== null) {
			$this->name = $name;
		}

		if ($address !== null) {
			$this->address = $address;
		}

		$this->features = $features;
		$this->buildings = $buildings;

		$this->texts = $texts ?? new Texts;
		$this->media = $media ?? new Media;
		$this->location = $location ?? new Location;
		$this->certifications = $certifications ?? new Certifications;
		$this->social = $social ?? new Social;
		$this->usage = $usage ?? new Usage;
		$this->expose = $expose;
		$this->docs = $docs ?? new Documents;
	}

	public static function fake(): self
    {
        $estate = new self;
        $estate->id = '123';
		$estate->slug = 'elsenstrasse';
        $estate->name = 'Elsenstrasse';

        $estate->address = new Address;
        $estate->address->street = 'Elsenstrasse';
        $estate->address->number = '1';
        $estate->address->postal_code = '12345';
        $estate->address->city = 'Berlin';
        $estate->address->country = 'DE';

        $estate->address->coordinates = new Coordinates;
        $estate->address->coordinates->latitude = 52.123;
        $estate->address->coordinates->longitude = 13.123;

		$estate->media = new Media;
		$estate->media->thumbnail = new Image;
		$estate->media->thumbnail->src = 'https://example.com/image.jpg';

        return $estate;
    }

    public static function from(array $data): self
    {
        $estate = new self;
        $estate->id = $data['id'];
		$estate->slug = $data['slug'];
        $estate->name = $data['name'];

        $estate->address = Address::from($data['address']);

		if (isset($data['features'])) {
			$estate->features = $data['features'];
		}

		if (isset($data['buildings'])) {
			$estate->buildings = array_map(function (array $building) {
				return Building::from($building);
			}, $data['buildings']);
		}

		$estate->texts = isset($data['texts'])
			? Texts::from($data['texts'])
			: new Texts;

		$estate->media = isset($data['media'])
			? Media::from($data['media'])
			: new Media;

		$estate->location = isset($data['location'])
			? Location::from($data['location'])
			: new Location;

		$estate->certifications = isset($data['certifications'])
			? Certifications::from($data['certifications'])
			: new Certifications;

		$estate->social = isset($data['social'])
			? Social::from($data['social'])
			: new Social;

		$estate->usage = isset($data['usage'])
			? Usage::from($data['usage'])
			: new Usage;

		if (isset($data['expose'])) {
			$estate->expose = WebExpose::from($data['expose']);
		}

		if (isset($data['docs'])) {
			$estate->docs = Documents::from($data['docs']);
		}

        return $estate;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
			'slug' => $this->slug,
            'name' => $this->name,
            'address' => $this->address->toArray(),
			'usage' => $this->usage->toArray(),
			'features' => $this->features,
			'buildings' => array_map(function (Building $building) {
				return $building->toArray();
			}, $this->buildings),

			'texts' => $this->texts->toArray(),
			'media' => $this->media->toArray(),
			'location' => $this->location->toArray(),
			'certifications' => $this->certifications->toArray(),
			'social' => $this->social->toArray(),
			'docs' => $this->docs->toArray(),
			'expose' => $this->expose?->toArray(),
        ]);
    }
}
