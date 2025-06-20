<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request)
    {
        $query = Faq::with('creator');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by category if provided
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }
        
        // Search by question and answer
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
        }
        
        // Default sorting by display order and creation date
        $query->orderBy('display_order', 'asc')->orderBy('created_at', 'desc');
        
        $faqs = $query->paginate(10)->withQueryString();
        $statuses = ['all' => 'All Statuses', 'active' => 'Active', 'inactive' => 'Inactive'];
        $categories = array_merge(['all' => 'All Categories'], Faq::getCategories());
        
        return view('admin.faqs.index', compact('faqs', 'statuses', 'categories'));
    }
    
    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        $categories = Faq::getCategories();
        return view('admin.faqs.create', compact('categories'));
    }
    
    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'required|string|in:' . implode(',', array_keys(Faq::getCategories())),
            'status' => 'required|in:active,inactive',
            'featured' => 'boolean',
            'display_order' => 'required|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Create FAQ
        $faq = Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'status' => $request->status,
            'featured' => $request->has('featured'),
            'display_order' => $request->display_order,
            'created_by' => Auth::id(),
        ]);
        
        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }
    
    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq)
    {
        $faq->load('creator');
        
        return view('admin.faqs.show', compact('faq'));
    }
    
    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq)
    {
        $categories = Faq::getCategories();
        return view('admin.faqs.edit', compact('faq', 'categories'));
    }
    
    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'required|string|in:' . implode(',', array_keys(Faq::getCategories())),
            'status' => 'required|in:active,inactive',
            'featured' => 'boolean',
            'display_order' => 'required|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update FAQ
        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->category = $request->category;
        $faq->status = $request->status;
        $faq->featured = $request->has('featured');
        $faq->display_order = $request->display_order;
        
        $faq->save();
        
        return redirect()->route('admin.faqs.show', $faq)
            ->with('success', 'FAQ updated successfully.');
    }
    
    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        
        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }
}
