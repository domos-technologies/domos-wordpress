<?php

namespace SchemaImmo\Money;

enum Currency: string
{
	case Euro = 'EUR';
	case Dollar = 'USD';
	case Pound = 'GBP';
	case Yen = 'JPY';
}
