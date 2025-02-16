<?php

namespace Geonames\Repositories;

use Illuminate\Support\Collection;
use Geonames\Models\IsoLanguageCode;

class IsoLanguageCodeRepository {

    /**
     * @return Collection
     */
    public function all() {
        return IsoLanguageCode::all();
    }
}
