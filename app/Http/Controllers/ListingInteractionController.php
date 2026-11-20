<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListingInteractionController extends Controller
{
    public function participate(Listing $listing): RedirectResponse
    {
        return to_route('listings.show', $listing)->withFragment('interactions');
    }

    public function like(Request $request, Listing $listing): RedirectResponse
    {
        $listing->likes()->newPivotStatement()->insertOrIgnore([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
        ]);

        return to_route('listings.show', $listing)->withFragment('interactions');
    }

    public function unlike(Request $request, Listing $listing): RedirectResponse
    {
        $listing->likes()->detach($request->user()->id);

        return to_route('listings.show', $listing)->withFragment('interactions');
    }

    public function comment(Request $request, Listing $listing): RedirectResponse
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:1000']]);

        $listing->comments()->create(['body' => $data['comment'], 'user_id' => $request->user()->id]);

        return to_route('listings.show', $listing)->withFragment('comments');
    }
}
