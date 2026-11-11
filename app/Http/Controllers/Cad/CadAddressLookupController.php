<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Cad\AddressDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CadAddressLookupController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $q = trim($data['q'] ?? '');
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        // Escape LIKE wildcards; return only explicit administrator-imported data.
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
        $matches = AddressDirectory::query()
            ->where(function ($query) use ($escaped) {
                $query->where('street', 'like', '%'.$escaped.'%')
                    ->orWhere('city', 'like', '%'.$escaped.'%')
                    ->orWhere('postal', 'like', '%'.$escaped.'%');
            })
            ->orderBy('street')
            ->limit(20)
            ->get(['id', 'street', 'city', 'postal']);

        return response()->json(['results' => $matches]);
    }
}
