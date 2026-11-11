<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cad\AddressDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CadAddressDirectoryController extends Controller
{
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'import_mode' => ['required', 'in:append,replace'],
        ]);

        $handle = fopen($validated['address_file']->getRealPath(), 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['address_file' => 'Unable to read the uploaded file.']);
        }

        try {
            $first = fgetcsv($handle);
            if (! $first) {
                throw ValidationException::withMessages(['address_file' => 'The CSV file is empty.']);
            }

            // CSV headers: street,city,postal. Also accept location/address.
            $headers = array_map(fn ($value) => strtolower(trim((string) $value, " \t\n\r\0\x0B\xEF\xBB\xBF")), $first);
            $streetIndex = array_search('street', $headers, true);
            if ($streetIndex === false) {
                $streetIndex = array_search('address', $headers, true);
            }
            if ($streetIndex === false) {
                $streetIndex = array_search('location', $headers, true);
            }
            if ($streetIndex === false) {
                throw ValidationException::withMessages(['address_file' => 'CSV requires a street, address, or location column.']);
            }
            $cityIndex = array_search('city', $headers, true);
            $postalIndex = array_search('postal', $headers, true);
            if ($postalIndex === false) {
                $postalIndex = array_search('postcode', $headers, true);
            }

            $rows = [];
            $seen = [];
            while (($data = fgetcsv($handle)) !== false) {
                if (count($rows) >= 10000) {
                    throw ValidationException::withMessages(['address_file' => 'Maximum 10,000 unique addresses per import.']);
                }
                $street = trim((string) ($data[$streetIndex] ?? ''));
                $city = $cityIndex === false ? '' : trim((string) ($data[$cityIndex] ?? ''));
                $postal = $postalIndex === false ? '' : trim((string) ($data[$postalIndex] ?? ''));
                if ($street === '') {
                    continue;
                }
                if (mb_strlen($street) > 255 || mb_strlen($city) > 255 || mb_strlen($postal) > 64) {
                    throw ValidationException::withMessages(['address_file' => 'An address exceeds the allowed field length.']);
                }
                $key = AddressDirectory::keyFor($street, $city, $postal);
                $seen[$key] = true;
                $rows[$key] = ['street' => $street, 'city' => $city ?: null, 'postal' => $postal ?: null, 'search_key' => $key];
            }
            if (! $rows) {
                throw ValidationException::withMessages(['address_file' => 'No valid addresses found in the CSV.']);
            }
            DB::transaction(function () use ($validated, $rows) {
                if ($validated['import_mode'] === 'replace') {
                    AddressDirectory::query()->delete();
                }
                foreach (array_chunk(array_values($rows), 250) as $chunk) {
                    AddressDirectory::query()->upsert($chunk, ['search_key'], ['street', 'city', 'postal']);
                }
            });
        } finally {
            fclose($handle);
        }

        return redirect()->route('admin.settings.cad')
            ->with('address_import_success', count($rows).' unique address records imported.');
    }
}
