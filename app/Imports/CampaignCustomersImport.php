<?php
namespace App\Imports;

use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\City;
use App\Models\Customer;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CampaignCustomersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported   = 0;
    public int $updated    = 0;
    public int $skipped    = 0;
    public int $skippedAuthorized = 0;
    public array $errors   = [];

    private string $importBatch;
    private array $cityCache   = [];
    private array $deptCache   = [];

    public function __construct(private Campaign $campaign) {
        $this->importBatch = (string) Str::uuid();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 porque fila 1 es cabecera

            try {
                // Normalizar claves: minúsculas sin espacios
                $data = collect($row)->mapWithKeys(
                    fn($v, $k) => [Str::slug(trim((string)$k), '_') => trim((string)$v)]
                )->toArray();

                // Resolver columna de teléfono (acepta: telefono, celular, phone, movil, tel)
                $phone = $this->resolve($data, ['telefono','celular','phone','movil','tel','numero']);
                $phone = preg_replace('/\D/', '', $phone ?? '');

                if (empty($phone) || strlen($phone) < 7) {
                    $this->errors[] = "Fila {$rowNum}: teléfono inválido o vacío — se omitió.";
                    $this->skipped++;
                    continue;
                }

                // Campos opcionales
                $name      = $this->resolve($data, ['nombre','name','nombres','primer_nombre']) ?: null;
                $lastname  = $this->resolve($data, ['apellido','apellidos','lastname','primer_apellido']) ?: null;
                $email     = $this->resolve($data, ['email','correo','mail']) ?: null;
                $docType   = strtoupper($this->resolve($data, ['tipo_documento','tipo_doc','document_type']) ?: 'CC');
                $docNum    = $this->resolve($data, ['documento','cedula','document_number','numero_documento']) ?: null;
                $deptName  = $this->resolve($data, ['departamento','department','depto','dept']) ?: null;
                $cityName  = $this->resolve($data, ['ciudad','city']) ?: null;
                $deptId    = $deptName ? $this->resolveDepartment($deptName) : null;
                $cityId    = $cityName ? $this->resolveCity($cityName, $deptId) : null;

                // Buscar cliente existente por teléfono o documento
                $customer = null;
                if ($docNum) {
                    $customer = Customer::where('document_number', $docNum)->first();
                }
                if (!$customer) {
                    $customer = Customer::where('phone', $phone)->first();
                }

                if ($customer) {
                    // Actualizar datos faltantes sin sobreescribir existentes
                    $updates = [];
                    if (!$customer->name     && $name)     $updates['name']            = $name;
                    if (!$customer->lastname && $lastname)  $updates['lastname']        = $lastname;
                    if (!$customer->email    && $email)     $updates['email']           = $email;
                    if (!$customer->city_id  && $cityId)    $updates['city_id']         = $cityId;
                    if (!$customer->document_number && $docNum) {
                        $updates['document_number'] = $docNum;
                        $updates['document_type']   = $docType;
                    }
                    if (!empty($updates)) {
                        $customer->update($updates);
                    }
                    $this->updated++;
                } else {
                    $customer = Customer::create([
                        'document_type'   => $docType,
                        'document_number' => $docNum,
                        'name'            => $name  ?: 'Sin nombre',
                        'lastname'        => $lastname,
                        'email'           => $email,
                        'phone'           => $phone,
                        'city_id'         => $cityId,
                        'status'          => 'active',
                        'created_via'     => 'import',
                        'data_treatment_accepted'    => false,
                        'data_treatment_accepted_at' => null,
                    ]);
                    $this->imported++;
                }

                // Campaña de autorización: excluir clientes que ya tienen datos autorizados
                if ($this->campaign->type === 'autorizacion' && $customer->data_treatment_accepted) {
                    $this->skippedAuthorized++;
                    continue;
                }

                // Vincular a la campaña (ignorar si ya existe)
                CampaignCustomer::firstOrCreate(
                    ['campaign_id' => $this->campaign->id, 'customer_id' => $customer->id],
                    ['source' => 'import', 'import_batch' => $this->importBatch]
                );

            } catch (\Throwable $e) {
                $this->errors[] = "Fila {$rowNum}: " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    private function resolve(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                return $data[$key];
            }
        }
        return null;
    }

    private function resolveDepartment(string $name): ?int
    {
        $key = Str::slug($name);
        if (!isset($this->deptCache[$key])) {
            $dept = Department::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])->first();
            $this->deptCache[$key] = $dept?->id;
        }
        return $this->deptCache[$key];
    }

    private function resolveCity(string $name, ?int $departmentId = null): ?int
    {
        $key = Str::slug($name) . ($departmentId ? "_{$departmentId}" : '');
        if (!isset($this->cityCache[$key])) {
            $query = City::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%']);
            // If we have a department, narrow the search to avoid ambiguity
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            $city = $query->first();
            $this->cityCache[$key] = $city?->id;
        }
        return $this->cityCache[$key];
    }

    public function getImportBatch(): string
    {
        return $this->importBatch;
    }
}
