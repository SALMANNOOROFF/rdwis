public function storeMPR(Request $request)
{
    $user = Auth::user();
    $projectId = $request->project_id;
    
    // 1. Check if Document Already Exists for this Month/Project
    $document = Document::where('prj_id', $projectId)
                        ->where('status', '!=', 'Approved') // Agar open hai to wahi uthao
                        ->first();

    if (!$document) {
        // --- CREATE NEW DOCUMENT (Pehli dafa) ---
        $document = Document::create([
            'prj_id' => $projectId,
            'doc_type' => 'MPR',
            'creator_id' => $user->acc_id,
            'current_owner_id' => $user->acc_id, // Pehle mere paas hi rahega draft mein
            'status' => 'Draft'
        ]);
        
        $newVersionNo = '1.0';
    } else {
        // --- EXISTING DOCUMENT UPDATE ---
        // Permission Check: Kya mein owner hoon?
        if ($document->current_owner_id != $user->acc_id) {
            return response()->json(['error' => 'You do not have permission to edit this file right now. It is with ' . $document->currentOwner->acc_name]);
        }
        
        // Calculate new version number (Simple Logic)
        $lastVer = $document->latestVersion->version_no ?? '0.0';
        $newVersionNo = number_format((float)$lastVer + 0.1, 1);
    }

    // 2. CREATE NEW VERSION (Snapshot of Data)
    // Sara form data JSON mein save karalo
    $contentData = [
        'financial_progress' => $request->financial_progress,
        'physical_progress' => $request->physical_progress,
        'issues' => $request->issues,
        // Aur jo bhi fields hain...
    ];

    DocumentVersion::create([
        'doc_id' => $document->doc_id,
        'version_no' => $newVersionNo,
        'content_data' => $contentData,
        'remarks' => $request->remarks ?? 'Updated by Division',
        'action_by' => $user->acc_id,
        'action_date' => now()
    ]);

    // 3. HANDLE FORWARDING (Agar user ne "Send to SORD" click kiya)
    if ($request->action == 'forward_to_sord') {
        
        // SORD User ko dhoondo (Logic aapke User model se ayega)
        // Temporary logic: Find user with role SORD linked to this project/unit
        $sordUser = User::where('acc_username', 'nislam2')->first(); // Example logic

        if ($sordUser) {
            // Document ka owner change karo
            $document->current_owner_id = $sordUser->acc_id;
            $document->status = 'Pending Approval';
            $document->save();

            // History Log
            \DB::table('doc.document_history')->insert([
                'doc_id' => $document->doc_id,
                'from_user_id' => $user->acc_id,
                'to_user_id' => $sordUser->acc_id,
                'action_type' => 'Forwarded',
                'created_at' => now()
            ]);
        }
    }

    return redirect()->back()->with('success', 'MPR Updated Successfully!');
}