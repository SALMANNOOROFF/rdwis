<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function index()
    {
        // Placeholder for list view
        return view('training.index');
    }

    public function create()
    {
        // View for raising a new training case
        return view('training.create');
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
