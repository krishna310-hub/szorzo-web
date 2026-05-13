<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SitemapRobotsController extends Controller
{
    public function index()
    {
        $robotsExists = file_exists(public_path('robots.txt'));
        $sitemapExists = file_exists(public_path('sitemap.xml'));

        return view('backend.sitemap.index', compact('robotsExists', 'sitemapExists'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'robots_file' => 'required|file|mimes:txt|max:1024', // 1MB
        ]);
    
        $file = $request->file('robots_file');
    
        $file->move(base_path(), 'robots.txt');
    
        return redirect()->back()
                         ->with('success', 'robots.txt uploaded successfully.');
    }

    public function downloadSitemap()
    {
        $filePath = base_path('sitemap.xml');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'sitemap.xml file not found.');
        }

        return response()->download($filePath, 'sitemap.xml', [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function downloadRobots()
    {
        $filePath = public_path('robots.txt');

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'robots.txt file not found.');
        }

        return response()->download($filePath, 'robots.txt', [
            'Content-Type' => 'text/plain',
        ]);
    }
}
