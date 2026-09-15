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
     * Store an uploaded quotation document inside public/purquote/{dept}/
     * Creates folders if missing, preserves old files on re-upload, and syncs both public and storage disks.
     *
     * @param UploadedFile $file The uploaded quotation document
     * @param int|string $pcsId  Purchase Case ID
     * @param int|string $qteId  Quotation ID
     * @param string|null $dept  Optional department code (e.g. comm, proc)
     * @return string Relative path stored in DB (e.g. "purquote/comm/case_3002_quote_4554_1740000000.pdf")
     */
    public function storeQuote(UploadedFile $file, int|string $pcsId, int|string $qteId, ?string $dept = null): string
    {
        // 1. Resolve department code (e.g. comm, proc, avionics)
        if (empty($dept)) {
            $user = auth()->user();
            $area = strtolower(trim((string)($user?->acc_untarea ?? '')));
            if (str_contains($area, 'proc') || str_contains($area, 'prc')) {
                $dept = 'proc';
            } elseif ($user && $user->acc_unt_id) {
                $unit = DB::table('cen.units')->where('unt_id', $user->acc_unt_id)->first();
                $dept = $unit?->unt_namesh ?: ($unit?->unt_name ?: 'comm');
            } else {
                $pcsUnitId = DB::table('pur.purcases')->where('pcs_id', $pcsId)->value('pcs_unt_id');
                $unit = $pcsUnitId ? DB::table('cen.units')->where('unt_id', $pcsUnitId)->first() : null;
                $dept = $unit?->unt_namesh ?: ($unit?->unt_name ?: 'comm');
            }
        }
        $dept = strtolower(preg_replace('/[^a-z0-9_-]/i', '', $dept)) ?: 'comm';

        // 2. Ensure public/purquote/{dept} directory exists
        $publicDir = public_path("purquote" . DIRECTORY_SEPARATOR . $dept);
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0777, true);
        }

        // Also ensure storage/app/public/purquote/{dept} exists
        $storageDir = storage_path("app" . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "purquote" . DIRECTORY_SEPARATOR . $dept);
        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }

        // 3. Form clean filename with timestamp to preserve file history
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'pdf');
        $filename = "case_{$pcsId}_quote_{$qteId}_" . time() . ".{$ext}";
        if (file_exists($publicDir . DIRECTORY_SEPARATOR . $filename)) {
            $filename = "case_{$pcsId}_quote_{$qteId}_" . time() . "_" . mt_rand(100, 999) . ".{$ext}";
        }

        // Move to public/purquote/{dept}/
        $file->move($publicDir, $filename);

        // Copy to storage/app/public/purquote/{dept}/ so both locations are synced
        @copy($publicDir . DIRECTORY_SEPARATOR . $filename, $storageDir . DIRECTORY_SEPARATOR . $filename);

        return "purquote/{$dept}/{$filename}";
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
        if (str_starts_with($normalized, 'purquote/')) {
            return '/' . ltrim($normalized, '/');
        }
        return '/storage/' . ltrim($normalized, '/');
    }

    /**
     * Resolve physical file path across multiple candidate directories,
     * with transparent LAN remote fallback for multi-PC setups.
     *
     * @param string|null $relativePath
     * @return string|null
     */
    public function resolvePhysicalPath(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        $normalized = $this->normalizePath($relativePath);

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->path($normalized);
        }

        $candidates = [
            storage_path('app/public/' . $normalized),
            public_path('storage/' . $normalized),
            storage_path('app/' . $normalized),
            public_path($normalized),
            base_path($normalized),
        ];

        foreach ($candidates as $cand) {
            $standard = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cand);
            if (file_exists($standard) && is_file($standard)) {
                return $standard;
            }
        }

        // On-demand LAN fetch fallback for secondary PCs
        if ($this->tryFetchRemote($normalized)) {
            $localSaved = storage_path('app/public/' . $normalized);
            $standard = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $localSaved);
            if (file_exists($standard) && is_file($standard)) {
                return $standard;
            }
        }

        return null;
    }

    /**
     * Attempt to fetch a missing physical file from the primary server across the local network.
     * Saves the file permanently into storage/app/public/{normalized} on first access.
     *
     * @param string $normalized
     * @return bool
     */
    public function tryFetchRemote(string $normalized): bool
    {
        if (empty($normalized)) {
            return false;
        }

        $configuredHost = env('PRIMARY_STORAGE_HOST', env('REMOTE_STORAGE_HOST', ''));
        $hosts = array_unique(array_filter([
            $configuredHost,
            'http://192.168.1.159',
            'http://192.168.1.159:80',
            'http://rdwisv2.mil',
            'https://192.168.1.159:8443',
            'http://192.168.1.159:8000',
        ]));

        $currentUrl = config('app.url', '');

        foreach ($hosts as $host) {
            // Avoid querying ourselves if this machine is already 192.168.1.159
            if (str_contains($currentUrl, '192.168.1.159') && str_contains($host, '192.168.1.159')) {
                continue;
            }

            $targetUrl = rtrim($host, '/') . '/storage/' . ltrim($normalized, '/');

            try {
                $ctx = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 1,
                        'follow_location' => 1,
                        'header' => "User-Agent: RDWIS-Storage-Sync/2.0\r\n",
                    ],
                ]);

                $fileData = @file_get_contents($targetUrl, false, $ctx);
                if ($fileData !== false && strlen($fileData) > 0) {
                    $localDest = storage_path('app/public/' . $normalized);
                    $destDir = dirname($localDest);
                    if (!is_dir($destDir)) {
                        @mkdir($destDir, 0777, true);
                    }
                    if (@file_put_contents($localDest, $fileData) !== false) {
                        return true;
                    }
                }
            } catch (\Throwable $t) {
                // Remote fetch failed, try next candidate
            }
        }

        return false;
    }

    /**
     * Check if an attachment file physically exists on the public disk or candidates.
     *
     * @param string|null $relativePath
     * @return bool
     */
    public function exists(?string $relativePath): bool
    {
        return !is_null($this->resolvePhysicalPath($relativePath));
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

        $resolved = $this->resolvePhysicalPath($relativePath);
        if ($resolved) {
            return $resolved;
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
        $fullPath = $this->resolvePhysicalPath($relativePath);

        if (!$fullPath) {
            abort(404, 'Attachment file not found on storage disk. The physical file may not have been migrated or synced.');
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'txt'  => 'text/plain',
            'csv'  => 'text/csv',
        ];
        $mime = $mimeTypes[$ext] ?? (mime_content_type($fullPath) ?: 'application/octet-stream');
        $filename = $downloadName ?: basename($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Access-Control-Allow-Origin' => '*',
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
