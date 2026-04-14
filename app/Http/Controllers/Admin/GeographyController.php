<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\PointOfSale;
use App\Models\Zone;
use Illuminate\Http\Request;

class GeographyController extends Controller
{
    public function index(Request $request)
    {
        $countries    = Country::with(['departments' => fn($q) => $q->withCount('cities')])->get();
        $departments  = Department::with('country')->withCount('cities')->orderBy('name')->get();
        $cities       = City::with('department')->withCount(['zones', 'pointsOfSale'])->orderBy('name')->get();
        $zones        = Zone::with('city')->orderBy('city_id')->orderBy('name')->get();

        $pointsOfSale = PointOfSale::with(['city.department', 'zone'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->city_id, fn($q) => $q->where('city_id', $request->city_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'countries'   => $countries->count(),
            'departments' => $departments->count(),
            'cities'      => $cities->count(),
            'zones'       => $zones->count(),
            'pos'         => PointOfSale::count(),
            'pos_active'  => PointOfSale::where('status', 'active')->count(),
        ];

        return view('admin.geography.index', compact(
            'countries', 'departments', 'cities', 'zones', 'pointsOfSale', 'stats'
        ));
    }

    public function storeCity(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:100',
            'code'          => 'nullable|string|max:10',
        ]);
        City::create($data + ['is_active' => true]);
        return back()->with('success', "Ciudad \"{$data['name']}\" creada correctamente.");
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'city_id'     => 'required|exists:cities,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        Zone::create($data + ['is_active' => true]);
        return back()->with('success', "Zona \"{$data['name']}\" creada correctamente.");
    }

    public function storePOS(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'code'         => 'required|string|max:30|unique:points_of_sale,code',
            'city_id'      => 'required|exists:cities,id',
            'zone_id'      => 'nullable|exists:zones,id',
            'address'      => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:100',
        ], [
            'code.unique' => 'Ya existe un punto de venta con ese código.',
        ]);
        PointOfSale::create($data + ['status' => 'active']);
        return back()->with('success', "Punto de venta \"{$data['name']}\" creado correctamente.");
    }

    public function togglePOS(PointOfSale $pos)
    {
        $pos->update(['status' => $pos->status === 'active' ? 'inactive' : 'active']);
        $label = $pos->status === 'active' ? 'activado' : 'desactivado';
        return back()->with('success', "Punto de venta \"{$pos->name}\" {$label}.");
    }

    public function destroyPOS(PointOfSale $pos)
    {
        $pos->delete();
        return back()->with('success', "Punto de venta \"{$pos->name}\" eliminado.");
    }
}
