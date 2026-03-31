<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    public function index()
    {
        // Placeholder for list view
        return view('training.index');
    }

    public function create()
    {
        $maxId = DB::table('pur.purcases')->max('pcs_id');
        $nextId = $maxId ? ($maxId + 1) : 1;

        $heads = DB::table('cen.heads')
                    ->select('hed_id', 'hed_name', 'hed_code')
                    ->orderBy('hed_name', 'asc')
                    ->get();

        return view('training.create', compact('nextId', 'heads'));
    }

    public function getNextMinuteNumber($headId)
    {
        $maxMinute = DB::table('pur.purcases')
                        ->where('pcs_hed_id', $headId)
                        ->max('pcs_minute');

        return response()->json([
            'next_minute' => $maxMinute ? ($maxMinute + 1) : 1,
            'last_minute' => $maxMinute ?? 0
        ]);
    }

    public function show($id)
    {
        // Placeholder for detail view
        return view('training.show', compact('id'));
    }

    public function indexBook()
    {
        // Dummy data for departmental books
        $books = [
            [
                'id' => 101,
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'department' => 'Information Technology',
                'copies' => 5,
                'available' => 3,
                'isbn' => '978-0132350884',
                'icon' => 'fas fa-book-open',
                'color' => '#f39c12'
            ],
            [
                'id' => 102,
                'title' => 'Project Management Body of Knowledge (PMBOK)',
                'author' => 'Project Management Institute',
                'department' => 'Project Management Office',
                'copies' => 2,
                'available' => 1,
                'isbn' => '978-1628256642',
                'icon' => 'fas fa-tasks',
                'color' => '#3498db'
            ],
            [
                'id' => 103,
                'title' => 'The Lean Startup',
                'author' => 'Eric Ries',
                'department' => 'Strategy & Planning',
                'copies' => 8,
                'available' => 5,
                'isbn' => '978-0307887894',
                'icon' => 'fas fa-rocket',
                'color' => '#e74c3c'
            ],
            [
                'id' => 104,
                'title' => 'Laravel Up & Running',
                'author' => 'Matt Stauffer',
                'department' => 'Information Technology',
                'copies' => 4,
                'available' => 2,
                'isbn' => '978-1492041214',
                'icon' => 'fab fa-laravel',
                'color' => '#ff2d20'
            ]
        ];

        return view('training.books_index', compact('books'));
    }

    public function requestBook(Request $request)
    {
        // Simulated borrowing notification
        return response()->json([
            'success' => true, 
            'message' => 'Your request to borrow this book has been sent to the holding department.',
            'simulated_status' => 'Pending Approval'
        ]);
    }

    public function createBook()
    {
        // View for raising a standalone book procurement case
        return view('training.books');
    }

    public function storeBook(Request $request)
    {
        // Placeholder for saving book data
        return response()->json(['success' => true, 'message' => 'Book procurement case raised successfully']);
    }

    public function indexLicense()
    {
        // Dummy data for institutional licenses
        $licenses = [
            [
                'id' => 1,
                'product' => 'Adobe Creative Cloud',
                'department' => 'Information Technology',
                'total_seats' => 10,
                'available' => 2,
                'expiry' => '2026-12-31',
                'icon' => 'fas fa-paint-brush',
                'color' => '#ff0000'
            ],
            [
                'id' => 2,
                'product' => 'Zoom Enterprise',
                'department' => 'Human Resources',
                'total_seats' => 50,
                'available' => 12,
                'expiry' => '2027-05-15',
                'icon' => 'fas fa-video',
                'color' => '#2D8CFF'
            ],
            [
                'id' => 3,
                'product' => 'Microsoft 365 Business',
                'department' => 'Administration',
                'total_seats' => 100,
                'available' => 8,
                'expiry' => '2026-08-20',
                'icon' => 'fab fa-microsoft',
                'color' => '#00a1f1'
            ],
            [
                'id' => 4,
                'product' => 'JetBrains All Products Pack',
                'department' => 'Research & Dev',
                'total_seats' => 5,
                'available' => 1,
                'expiry' => '2026-11-10',
                'icon' => 'fas fa-code',
                'color' => '#000000'
            ]
        ];

        return view('training.license_index', compact('licenses'));
    }

    public function requestLicense(Request $request)
    {
        // Simulated notification logic
        return response()->json([
            'success' => true, 
            'message' => 'Your request has been sent to the holding department. You will be notified once approved.',
            'simulated_creds' => [
                'user' => 'rdwis_user_' . rand(100,999),
                'pass' => '********'
            ]
        ]);
    }

    public function createLicense()
    {
        // View for raising a standalone license procurement case
        return view('training.license');
    }

    public function createPurchase()
    {
        // Dummy catalog for training related purchases
        $items = [
            (object)['id' => 1, 'title' => 'Multimedia Projector (4K)', 'category' => 'Hardware', 'last_price' => 85000, 'stock_qty' => 5],
            (object)['id' => 2, 'title' => 'High-End Developer Laptop', 'category' => 'Hardware', 'last_price' => 250000, 'stock_qty' => 12],
            (object)['id' => 3, 'title' => 'Digital Whiteboard (Interative)', 'category' => 'Hardware', 'last_price' => 120000, 'stock_qty' => 3],
            (object)['id' => 4, 'title' => 'Training Stationery Kit (Bulk)', 'category' => 'Stationery', 'last_price' => 1500, 'stock_qty' => 100],
            (object)['id' => 5, 'title' => 'VR Training Headset', 'category' => 'Hardware', 'last_price' => 95000, 'stock_qty' => 8],
            (object)['id' => 6, 'title' => 'AWS / Cloud Training Vouchers', 'category' => 'Certification', 'last_price' => 45000, 'stock_qty' => 50],
        ];

        return view('training.purchase', compact('items'));
    }

    public function storePurchase(Request $request)
    {
        // Placeholder for saving purchase data
        return response()->json(['success' => true, 'message' => 'Training purchase case raised successfully']);
    }

    public function storeLicense(Request $request)
    {
        // Placeholder for saving license data
        return response()->json(['success' => true, 'message' => 'License procurement case raised successfully']);
    }

    public function store(Request $request)
    {
        // Placeholder for saving data
        return response()->json(['success' => true, 'message' => 'Training case raised successfully']);
    }
}
