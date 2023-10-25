<?php

namespace SchemaImmo\Rentable;

enum TransactionType: string
{
	case Rent = 'rent';
	case Sale = 'sale';
}
