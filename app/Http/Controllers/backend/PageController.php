<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Pages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Validators\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PagesImport;
use App\Models\General;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PageController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('read', Pages::class);
        Artisan::call('optimize:clear');
        if ($request->ajax()) {
            $pages = Pages::latest();
    
            return DataTables::of($pages)
                ->addIndexColumn()
                ->filter(function ($query) {
                    if (request()->has('search') && $search = request('search')['value']) {
                        $query->where(function ($q) use ($search) {
                            $q->Where('page_code', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('url_slug', 'like', "%{$search}%")
                            ->orWhere('created_at', 'like', "%{$search}%");
                        });
                    }
                })
                ->editColumn('status', function ($row) {
                    return $row->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex gap-1">
                            <a href="'.route('admin.pages.edit', $row->id).'" class="btn btn-sm btn-primary" title="Edit">
                                <i class="bx bxs-edit"></i>
                            </a>
                            <button data-id="'.$row->id.'"
                                data-table-id="pages-table"
                                data-route="'.route('admin.pages.delete', $row->id).'" 
                                class="btn btn-sm btn-danger destroy" title="Delete">
                                <i class="bx bxs-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status','action','created_at'])
                ->make(true);
        }
        return view('backend.pages.index');
    }

    /** Create */
    public function create()
    {
        $this->authorize('create', Pages::class);
        return view('backend.pages.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Pages::class);
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'location'          => 'nullable|string|max:255',
            'category'          => 'nullable|string|max:255',
            'url'               => 'nullable|string|max:255',
            'banner_content'    => 'required|string|max:1500',
            'page_content'      => 'required|string|max:5000',
            'faqs'              => 'nullable|array',
            'faqs.*.question'   => 'required_with:faqs|string|max:255',
            'faqs.*.answer'     => 'required_with:faqs|string|max:1000',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
            'meta_keywords'     => 'nullable|string|max:255',
            'status'            => 'required|in:0,1',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:512',
        ]);

        $cleanText = function ($value) {
            return $value !== null
                ? trim(preg_replace('/[^A-Za-z0-9\s\-.,\'":()]/', '', $value))
                : null;
        };

        $faqs = null;

        if ($request->filled('faqs') && is_array($request->faqs)) {
            $faqItems = [];

            foreach ($request->faqs as $faq) {
                $question = $faq['question'] ?? null;
                $answer   = $faq['answer'] ?? null;

                if ($question || $answer) {
                    $faqItems[] = [
                        'question' => $question, 
                        'answer'   => $answer,   
                    ];
                }
            }

            if (!empty($faqItems)) {
                $faqs = json_encode(
                    $faqItems,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $destinationPath = public_path('backend/uploads/pages/banner_images');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $imagePath = 'backend/uploads/pages/banner_images/' . $fileName;
        }

        $baseSlug = Str::slug($request->url ?: $request->location);
        $slug = $baseSlug;
        $count = 1;

        while (Pages::where('url_slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        $lastId = Pages::max('id') ?? 0;

        Pages::create([
            'location'          => $cleanText($request->location),
            'category'          => $cleanText($request->category),
            'name'              => $cleanText($request->name),
            'url_slug'          => $slug,
            'banner_content'    => $request->banner_content,
            'page_content'      => $request->page_content,
            'faqs'              => $faqs,
            'meta_title'        => $cleanText($request->meta_title),
            'meta_description'  => $cleanText($request->meta_description),
            'meta_keyword'      => $cleanText($request->meta_keywords),
            'status'            => $request->status,
            'image'             => $imagePath,
            'page_code'         => '#GD' . ($lastId + 1),
        ]);

        $this->updateSitemap();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully');
    }


    public function edit($id)
    {
        $page = Pages::findOrFail($id);
        return view('backend.pages.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Pages::class);
        $page = Pages::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'url'               => 'required|string|max:255',
            'location'          => 'nullable|string|max:255',
            'category'          => 'nullable|string|max:255',
            'banner_content'    => 'required|string|max:1500',
            'page_content'      => 'required|string|max:5000',
            'faqs'              => 'nullable|array',
            'faqs.*.question'   => 'required_with:faqs|string|max:255',
            'faqs.*.answer'     => 'required_with:faqs|string|max:1000',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
            'meta_keywords'     => 'nullable|string|max:255',
            'status'            => 'required|in:0,1',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
        ]);

        $cleanText = function ($value) {
            return trim(
                preg_replace('/[^A-Za-z0-9\s\-.,\'":()]/', '', $value)
            );
        };

        // Process FAQs
        $faqs = null;
        if ($request->filled('faqs') && is_array($request->faqs)) {
            $faqItems = [];

            foreach ($request->faqs as $faq) {
                $question = $faq['question'] ?? null;
                $answer   = $faq['answer'] ?? null;

                if ($question || $answer) {
                    $faqItems[] = [
                        'question' => $question, 
                        'answer'   => $answer,   
                    ];
                }
            }

            if (!empty($faqItems)) {
                $faqs = json_encode(
                    $faqItems,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        if ($request->hasFile('image')) {
            if ($page->image) {
                if (File::exists($page->image)) {
                    unlink($page->image);
                }
            }

            $file = $request->file('image');
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $destinationPath = public_path('backend/uploads/pages/banner_images');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $imagePath = 'backend/uploads/pages/banner_images/' . $fileName;
            $page->image = $imagePath;
        }
        
        $baseSlug = Str::slug($request->url ?: $request->location);
        $slug = $baseSlug;
        
        if ($slug !== $page->url_slug) {
            $count = 1;
            while (
                Pages::where('url_slug', $slug)
                    ->where('id', '!=', $page->id)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $count++;
            }
        }
        
        // Update page
        $page->update([
            'location'         => $cleanText($request->location),
            'category'         => $cleanText($request->category),
            'name'             => $cleanText($request->name),
            'url_slug'         => $slug,
            'banner_content'   => $request->banner_content,
            'page_content'     => $request->page_content,
            'faqs'             => $faqs,
            'meta_title'       => $cleanText($request->meta_title),
            'meta_description' => $cleanText($request->meta_description),
            'meta_keyword'     => $cleanText($request->meta_keywords),
            'status'           => $request->status,
        ]);
        $this->updateSitemap();
        return redirect()->route('admin.pages.index')
                        ->with('success', 'Page updated successfully');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Pages::class);
        $page = Pages::findOrFail($id);
        if ($page->image) {
            if (File::exists($page->image)) {
                unlink($page->image);
            }
        }
        $page->delete();
        $this->updateSitemap();
        return response()->json(['success' => true]);
    }

    public function bulkUpload(Request $request)
    {
        $this->authorize('create', Pages::class);
        $request->validate([
            'sitemap_file' => 'required|mimes:csv,xlsx'
        ]);

        try {
            Excel::import(new PagesImport, $request->file('sitemap_file'));
            $this->updateSitemap();
            return back()->with('success', 'Pages imported successfully!');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return back()->with('error', implode(' | ', $errors));
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    protected function updateSitemap()
    {
        $this->authorize('sitemap', General::class);
        $pages = Pages::where('status', 1)->get();
        $pages = $pages->unique('url_slug');
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $url = $xml->addChild('url');
        $url->addChild('loc', url('/'));
        $url->addChild('lastmod', now()->toAtomString());
        $url->addChild('priority', '1.00');

        foreach ($pages as $page) {
            $url = $xml->addChild('url');
            $url->addChild('loc', url($page->url_slug));
            $url->addChild('lastmod', $page->updated_at->toAtomString());
            $url->addChild('priority', '0.80');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        $dom->save(base_path('sitemap.xml'));
    }

    public function deleteAll()
    {
        $this->authorize('delete', Pages::class);
        $pages = Pages::whereNotNull('image')->get();

        foreach ($pages as $page) {
            if ($page->image) {
                $path = public_path($page->image);

                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        Pages::truncate();
        $this->updateSitemap();
        return response()->json([
            'success' => true,
            'message' => 'All pages and images deleted successfully'
        ]);
    }

    public function downloadSample()
    {
        $this->authorize('create', Pages::class);
        $path = public_path('backend/sample.xlsx');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, 'sample.xlsx');
    }
}
