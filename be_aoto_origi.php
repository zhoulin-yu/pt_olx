<?php

function baseUrl()
{
    return 'https://www.autoscout24.be';
}

function country()
{
    return 'BE';
}

function priceEvaluation($priceEvaluation)
{
    switch ($priceEvaluation) {
        case 'TopPrice':
            return '[EXCELLENTE OFFRE] - ';
        case 'GoodPrice':
            return '[BONNE OFFRE] - ';
        case 'FairPrice':
            return '[OFFRE CORRECTE] - ';
    };
}

function fuelType($fuelType)
{
    switch ($fuelType) {
        case 'B':
            return 'Essence';
        case 'D':
            return 'Diesel';
        case 'E':
            return 'Electrique';
        case 'L':
            return 'GPL';
        case 'C':
            return 'GNV';
        case 'H':
            return 'Hydrogène';
        case 'M':
            return 'Ethanol';
        case '2':
            return 'Electrique/Essence';
        case '3':
            return 'Electrique/Diesel';
        default:
            return 'Autre';
    }
}

function contactPostalCode($postalCode)
{
    return "B{$postalCode}";
}
