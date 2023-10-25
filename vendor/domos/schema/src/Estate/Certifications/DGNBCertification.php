<?php

namespace SchemaImmo\Estate\Certifications;

use SchemaImmo\HasLabel;

enum DGNBCertification: string implements HasLabel
{
	case Platinum = 'platinum';
	case Gold = 'gold';
	case Silver = 'silver';
	case Bronze = 'bronze';


	public function label(): string
	{
		return match ($this) {
			DGNBCertification::Platinum => 'Platin',
			DGNBCertification::Gold => 'Gold',
			DGNBCertification::Silver => 'Silber',
			DGNBCertification::Bronze => 'Bronze',
		};
	}
}
