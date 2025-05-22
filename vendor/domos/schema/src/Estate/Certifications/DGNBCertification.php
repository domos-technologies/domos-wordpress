<?php

namespace SchemaImmo\Estate\Certifications;

use SchemaImmo\HasLabel;

enum DGNBCertification: string implements HasLabel
{
	case Platinum = 'platinum';
	case Gold = 'gold';
	case Silver = 'silver';
	case Bronze = 'bronze';
    case PreliminaryBronze = 'preliminary_bronze';
    case PreliminarySilver = 'preliminary_silver';
    case PreliminaryGold = 'preliminary_gold';
    case PreliminaryPlatinum = 'preliminary_platinum';


	public function label(): string
	{
		return match ($this) {
			DGNBCertification::Platinum => 'Platin',
			DGNBCertification::Gold => 'Gold',
			DGNBCertification::Silver => 'Silber',
			DGNBCertification::Bronze => 'Bronze',
			DGNBCertification::PreliminaryBronze => 'Vorläufig Bronze',
			DGNBCertification::PreliminarySilver => 'Vorläufig Silber',
			DGNBCertification::PreliminaryGold => 'Vorläufig Gold',
			DGNBCertification::PreliminaryPlatinum => 'Vorläufig Platin',
		};
	}
}
