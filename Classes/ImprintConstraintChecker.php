<?php

namespace gutesio\DataModelBundle\Classes;

class ImprintConstraintChecker
{
    private $imprintData;

    public function checkIfImprintIsComplete(array $imprintData)
    {
        $this->imprintData = $imprintData;
        switch ($imprintData['companyForm']) {
            case 'freelancer':
                return $this->checkFreelancerConstraints();
            case 'smallBusiness':
                return $this->checkSmallBusinessConstraints();
            case 'soleProprietor':
                return $this->checkSoleProprietorConstraints();
            case 'society':
                return $this->checkSocietyConstraints();
            case 'other':
                return $this->checkOtherConstraints();
            case 'noImprintRequired':
            default:
                return false;
        }
    }

    private function checkFreelancerConstraints()
    {
        return /*$this->checkField('inspectorate')
            && */$this->checkField('standeskammer')
            && $this->checkField('tradeID');
    }

    private function checkSmallBusinessConstraints()
    {
        return $this->checkField('owner');
    }

    private function checkSoleProprietorConstraints()
    {
        return /*$this->checkField('inspectorate')
            && */$this->checkField('owner')
            && $this->checkField('tradeID');
    }

    private function checkSocietyConstraints()
    {
        return /*$this->checkField('inspectorate')
            && */$this->checkField('owner')
            && $this->checkField('registryCourt')
            && $this->checkField('registerNumber');
    }

    private function checkOtherConstraints()
    {
        return /*$this->checkField('inspectorate')
            && */$this->checkField('owner')
            && $this->checkField('tradeID')
            && $this->checkField('registryCourt')
            && $this->checkField('registerNumber');
    }

    private function checkField(string $key)
    {
        return array_key_exists($key, $this->imprintData)
            && $this->imprintData[$key] !== ''
            && $this->imprintData[$key] !== null;
    }
}
