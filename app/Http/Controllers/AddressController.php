<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Search addresses for auto-suggestions
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $addresses = Address::search($query, $limit);
        
        return response()->json($addresses->map(function($address) {
            return [
                'id' => $address->address_id,
                'text' => $address->address_name,
                'full_address' => $address->full_address
            ];
        }));
    }
    
    /**
     * Store new address (auto-created when user types new address)
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_name' => 'required|string|max:255',
            'full_address' => 'nullable|string'
        ]);
        
        try {
            // Use authenticated user ID if available, otherwise use 1 (admin)
            $createdBy = Auth::check() ? Auth::id() : 1;
            
            $address = Address::findOrCreate(
                $request->address_name,
                $request->full_address,
                $createdBy
            );
            
            return response()->json([
                'success' => true,
                'address_id' => $address->address_id,
                'address_name' => $address->address_name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create address: ' . $e->getMessage()
            ], 500);
        }
    }
}
