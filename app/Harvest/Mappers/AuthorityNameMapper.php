<?php

namespace App\Harvest\Mappers;

use App\AuthorityName;

class AuthorityNameMapper extends AbstractModelMapper
{
    protected $modelClass = AuthorityName::class;

    public function mapName(array $row) {
        return $row['name'];
    }

    public function mapPrefered() {
        return false;
    }
}