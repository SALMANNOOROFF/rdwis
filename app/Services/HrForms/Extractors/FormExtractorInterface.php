<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;

interface FormExtractorInterface
{
    /**
     * Extract live data from RDWIS for the specified case.
     *
     * @param HrCtrCase $case The contract case model
     * @param string $instanceKey e.g. 'main', 'cand_1', 'cand_2', 'cand_3'
     * @param array $existingManualData Existing user-entered manual values to preserve
     * @return array Contains ['live' => [...], 'manual' => [...], 'warnings' => [...], 'missing_fields' => [...]]
     */
    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array;
}
