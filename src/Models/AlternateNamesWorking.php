<?php

namespace Geonames\Models;

use Geonames\Console\AlternateName as AlternateNameConsole;

class AlternateNamesWorking extends AlternateName {

    protected $table = AlternateNameConsole::TABLE_WORKING;

}
