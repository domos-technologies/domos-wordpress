<?php

namespace SchemaImmo\Rentable\Availability;

enum Status: string
{
    case Unavailable = 'unavailable';
    case UnavailableSoon = 'unavailable-soon';
    case Available = 'available';
    case AvailableSoon = 'available-soon';
}