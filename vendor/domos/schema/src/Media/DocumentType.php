<?php

namespace SchemaImmo\Media;

enum DocumentType: string
{
	case Document = 'document';
	case Expose = 'expose';
	case EnergyCertificate = 'energy_certificate';
	case Contract = 'contract';
	case Floorplan = 'floorplan';
	case Invoice = 'invoice';
}