<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileStorageService
{
    /**
     * Map of modules/tables to their slot configurations.
     */
    protected array $slotConfigurations = [
        'aud' => [
            'table' => 'aud.audattachments',
            'pk' => 'aat_id',
            'objtype' => 'aat_objtype',
            'objid' => 'aat_objid',
            'type' => 'aat_type',
            'path' => 'aat_path',
            'default_objtype' => 'rev',
            'subfolder' => 'aud',
        ],
        'ctc' => [
            'table' => 'hr.ctrcaseattachments',
            'pk' => 'cat_id',
            'objtype' => 'cat_objtype',
            'objid' => 'cat_objid',
            'type' => 'cat_type',
            'path' => 'cat_path',
            'default_objtype' => 'ctc',
            'subfolder' => 'hr',
        ],
        'emp' => [
            'table' => 'hr.empattachments',
            'pk' => 'eat_id',
            'objtype' => 'eat_objtype',
            'objid' => 'eat_objid',
            'type' => 'eat_type',
            'path' => 'eat_path',
            'default_objtype' => 'emp',
            'subfolder' => 'hr',
        ],
        'ina' => [
            'table' => 'ina.inaattachments',
            'pk' => 'iat_id',
            'objtype' => 'iat_objtype',
            'objid' => 'iat_objid',
            'type' => 'iat_type',
            'path' => 'iat_path',
            'default_objtype' => 'ina',
            'subfolder' => 'ina',
        ],
        'prj' => [
            'table' => 'prj.prjattachments',
            'pk' => 'jat_id',
            'objtype' => 'jat_objtype',
            'objid' => 'jat_objid',
            'type' => 'jat_type',
            'path' => 'jat_path',
            'default_objtype' => 'prj',
            'subfolder' => 'prj',
        ],
        'pur' => [
            'table' => 'pur.purattachments',
            'pk' => 'pat_id',
            'objtype' => 'pat_objtype',
            'objid' => 'pat_objid',
            'type' => 'pat_type',
            'path' => 'pat_path',
            'default_objtype' => 'pcs',
            'subfolder' => 'pur',
        ],
    ];

    /**
     * Store an uploaded file on the public disk.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $module One of: aud | hr | hr/photos | ina | prj | pur
     * @param string $prefix E.g. pcs-, ctc-, eat-, min-, pht-emp-, ctr-, mrr-, fs-, apl-, ntc-, ppr-, ppf-, wo-, rev-, mcc-, mx-
     * @param string $objectId The parent record identifier
     * @return string Relative path stored on disk (e.g. "pur/pcs-123.pdf")
     */
    public function store(UploadedFile $file, string $module, string $prefix, string $objectId): string
    {
        $normalizedModule = trim(str_replace('\\', '/', $module), '/');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');

        // Initial filename: {prefix}{objectId}.{ext}
        $filename = "{$prefix}{$objectId}.{$extension}";

        // Collision behavior (legacy ModifyFilePath starts counter at 3 -> -03, -04, etc.)
        if (Storage::disk('public')->exists("{$normalizedModule}/{$filename}")) {
            $n = 2;
            do {
                $n++;
                $candidate = "{$prefix}{$objectId}-" . sprintf('%02d', $n) . ".{$extension}";
            } while (Storage::disk('public')->exists("{$normalizedModule}/{$candidate}"));

            $filename = $candidate;
        }

        // Store file onto public disk
        Storage::disk('public')->putFileAs($normalizedModule, $file, $filename);

        // Return relative path only (e.g. pur/pcs-123.pdf)
        return "{$normalizedModule}/{$filename}";
    }

    /**
     * Generate the public URL for an attachment path.
     * Handles both forward slashes and legacy Windows backslashes transparently.
     *
     * @param string|null $relativePath Relative path stored in DB (e.g. "pur/pcs-123.pdf" or "\\pur\\min-pcs-231.pdf")
     * @return string|null Public asset URL or null if empty
     */
    public function url(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        $normalized = $this->normalizePath($relativePath);
        return Storage::disk('public')->url($normalized);
    }

    /**
     * Check if an attachment file physically exists on the public disk.
     *
     * @param string|null $relativePath
     * @return bool
     */
    public function exists(?string $relativePath): bool
    {
        if (empty($relativePath)) {
            return false;
        }

        $normalized = $this->normalizePath($relativePath);
        return Storage::disk('public')->exists($normalized);
    }

    /**
     * Get the absolute filesystem path for an attachment.
     *
     * @param string|null $relativePath
     * @return string|null
     */
    public function path(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        $normalized = $this->normalizePath($relativePath);
        return Storage::disk('public')->path($normalized);
    }

    /**
     * Delete an attachment file from the public disk if it exists.
     *
     * @param string|null $relativePath
     * @return bool
     */
    public function delete(?string $relativePath): bool
    {
        if (empty($relativePath)) {
            return false;
        }

        $normalized = $this->normalizePath($relativePath);
        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->delete($normalized);
        }

        return false;
    }

    /**
     * Return a BinaryFileResponse to view or download the attachment,
     * gracefully returning a 404 HTTP response if the physical file is not found on disk.
     *
     * @param string|null $relativePath
     * @param string|null $downloadName
     * @param bool $download
     * @return BinaryFileResponse
     */
    public function response(?string $relativePath, ?string $downloadName = null, bool $download = false): BinaryFileResponse
    {
        if (empty($relativePath) || !$this->exists($relativePath)) {
            abort(404, 'Attachment file not found on storage disk. The physical file may not have been migrated.');
        }

        $fullPath = $this->path($relativePath);
        $filename = $downloadName ?: basename($fullPath);

        return response()->file($fullPath, [
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Find or create an attachment slot row in one of the 6 slot tables.
     *
     * @param string $moduleOrTable 'aud' | 'ctc' | 'emp' | 'ina' | 'prj' | 'pur' or qualified table name
     * @param string $objType E.g. 'pcs', 'ctc', 'emp', 'prj', 'rev', 'ina', etc.
     * @param mixed $objId The parent record ID
     * @param string $type The attachment type (e.g. 'Quotation Document', 'CV', 'Approval', etc.)
     * @return object The slot record
     */
    public function findOrCreateSlot(string $moduleOrTable, string $objType, mixed $objId, string $type): object
    {
        $config = $this->resolveSlotConfig($moduleOrTable);

        $existing = DB::table($config['table'])
            ->where($config['objtype'], $objType)
            ->where($config['objid'], $objId)
            ->where($config['type'], $type)
            ->first();

        if ($existing) {
            return $existing;
        }

        $slotId = DB::table($config['table'])->insertGetId([
            $config['objtype'] => $objType,
            $config['objid'] => $objId,
            $config['type'] => $type,
            $config['path'] => null,
        ], $config['pk']);

        return DB::table($config['table'])
            ->where($config['pk'], $slotId)
            ->first();
    }

    /**
     * High-level helper: Store file and attach it directly to a slot table record.
     *
     * @param UploadedFile $file
     * @param string $moduleOrTable
     * @param string $prefix
     * @param mixed $objectId
     * @param string $type
     * @param string|null $objType
     * @return string Stored relative path
     */
    public function storeAndAttach(
        UploadedFile $file,
        string $moduleOrTable,
        string $prefix,
        mixed $objectId,
        string $type,
        ?string $objType = null
    ): string {
        $config = $this->resolveSlotConfig($moduleOrTable);
        $resolvedObjType = $objType ?: $config['default_objtype'];

        // Store physical file
        $path = $this->store($file, $config['subfolder'], $prefix, (string) $objectId);

        // Find or create slot
        $slot = $this->findOrCreateSlot($moduleOrTable, $resolvedObjType, $objectId, $type);

        // Update slot path
        DB::table($config['table'])
            ->where($config['pk'], $slot->{$config['pk']})
            ->update([$config['path'] => $path]);

        // Legacy behavior: If attaching an "Approval" file to a contract case (ctc),
        // also propagate the path to ctr_path2 for any matching hr.contracts
        if (in_array(strtolower($config['subfolder']), ['hr', 'ctc']) && strcasecmp($type, 'Approval') === 0) {
            DB::table('hr.contracts')
                ->where('ctr_ctc_id', $objectId)
                ->update(['ctr_path2' => $path]);
        }

        return $path;
    }

    /**
     * Normalize stored path string for cross-platform compatibility.
     *
     * @param string $path
     * @return string
     */
    public function normalizePath(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }

    /**
     * Resolve slot table configuration from shorthand key or table name.
     */
    protected function resolveSlotConfig(string $key): array
    {
        $normalized = strtolower(trim($key));

        // Direct key lookup
        if (isset($this->slotConfigurations[$normalized])) {
            return $this->slotConfigurations[$normalized];
        }

        // Lookup by table name or aliases
        $aliasMap = [
            'aud.audattachments' => 'aud',
            'audattachments' => 'aud',
            'rev' => 'aud',
            'hr.ctrcaseattachments' => 'ctc',
            'ctrcaseattachments' => 'ctc',
            'hr.empattachments' => 'emp',
            'empattachments' => 'emp',
            'ina.inaattachments' => 'ina',
            'inaattachments' => 'ina',
            'prj.prjattachments' => 'prj',
            'prjattachments' => 'prj',
            'project' => 'prj',
            'pur.purattachments' => 'pur',
            'purattachments' => 'pur',
            'pcs' => 'pur',
            'purchase' => 'pur',
        ];

        if (isset($aliasMap[$normalized]) && isset($this->slotConfigurations[$aliasMap[$normalized]])) {
            return $this->slotConfigurations[$aliasMap[$normalized]];
        }

        throw new \InvalidArgumentException("Unsupported slot table / module: '{$key}'");
    }
}
