<?php

namespace App\Service;

use App\Entity\Guest;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GuestService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateGuest(Guest $guest): ConstraintViolationListInterface
    {
        return $this->validator->validate($guest);
    }

    public function determineCountryByPhone(string $phone): ?string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $geoCoder = PhoneNumberOfflineGeocoder::getInstance();

        try {
            $phoneProto = $phoneUtil->parse($phone);
            if (!$phoneUtil->isValidNumber($phoneProto)) {
                throw new \InvalidArgumentException('Phone is invalid');
            }
            return $geoCoder->getDescriptionForNumber($phoneProto, 'en');
        } catch (NumberParseException $e) {
            throw new \InvalidArgumentException('Phone is invalid: ' . $e->getMessage());
        }
    }

    public function updateGuest(mixed $requestData, Guest $guest): void
    {
        if (isset($requestData['firstName'])) {
            $guest->setFirstName($requestData['firstName']);
        }
        if (isset($requestData['lastName'])) {
            $guest->setLastName($requestData['lastName']);
        }
        if (isset($requestData['email'])) {
            $guest->setEmail($requestData['email']);
        }
        if (isset($requestData['phone'])) {
            $guest->setPhone($requestData['phone']);
            $guest->setCountry($this->determineCountryByPhone($requestData['phone']));
        }
        if (isset($requestData['country'])) {
            $guest->setCountry($requestData['country']);
        }
    }
}