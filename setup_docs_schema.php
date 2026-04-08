<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Creating schema 'doc'...\n";
    DB::unprepared("CREATE SCHEMA IF NOT EXISTS doc AUTHORIZATION postgres;");

    // Table 1: doc.documents
    if (!Schema::hasTable('doc.documents')) {
        echo "Creating table 'doc.documents'...\n";
        DB::unprepared("
            CREATE TABLE doc.documents (
                doc_id SERIAL PRIMARY KEY,
                prj_id INT NOT NULL,
                doc_type VARCHAR(50) DEFAULT 'MPR',
                creator_id INT NOT NULL,
                current_owner_id INT NOT NULL,
                status VARCHAR(50) DEFAULT 'Draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
    } else {
        echo "Table 'doc.documents' already exists.\n";
    }

    // Table 2: doc.document_versions
    if (!Schema::hasTable('doc.document_versions')) {
        echo "Creating table 'doc.document_versions'...\n";
        DB::unprepared("
            CREATE TABLE doc.document_versions (
                ver_id SERIAL PRIMARY KEY,
                doc_id INT NOT NULL REFERENCES doc.documents(doc_id) ON DELETE CASCADE,
                version_no NUMERIC(5,2) DEFAULT 0.1,
                content_data JSONB,
                remarks TEXT,
                action_by INT,
                action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
    } else {
        echo "Table 'doc.document_versions' already exists.\n";
    }

    // Table 3: doc.document_history
    if (!Schema::hasTable('doc.document_history')) {
        echo "Creating table 'doc.document_history'...\n";
        DB::unprepared("
            CREATE TABLE doc.document_history (
                id SERIAL PRIMARY KEY,
                doc_id INT NOT NULL REFERENCES doc.documents(doc_id) ON DELETE CASCADE,
                from_user_id INT,
                to_user_id INT,
                action_type VARCHAR(100),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
    } else {
        echo "Table 'doc.document_history' already exists.\n";
    }

    echo "Done!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
