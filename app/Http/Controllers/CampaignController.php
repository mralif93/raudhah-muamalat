<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of all campaigns with pagination.
     */
    public function allCampaigns(Request $request)
    {
        $query = Campaign::query();
        
        // Filter by category if provided
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }
        
        // Search by title if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        // Sort options
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'goal':
                    $query->orderBy('goal_amount', 'desc');
                    break;
                case 'percent':
                    $query->orderByRaw('(raised_amount / goal_amount) DESC');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }
        
        // Only show active campaigns
        $query->where('status', 'active');
        
        // Paginate results
        $campaigns = $query->paginate(9)->withQueryString();
        
        return view('all-campaigns', compact('campaigns'));
    }
} 