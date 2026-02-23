<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportController extends Controller
{
    const FIELD_MAPPINGS = [
        'clients' => [
            'name' => ['name', 'nombre', 'company', 'empresa', 'razon_social'],
            'email' => ['email', 'correo', 'mail'],
            'phone' => ['phone', 'telefono', 'tel', 'celular', 'mobile'],
            'address' => ['address', 'direccion', 'domicilio'],
            'city' => ['city', 'ciudad'],
            'country' => ['country', 'pais'],
            'tax_id' => ['tax_id', 'cuit', 'rfc', 'nit', 'rut', 'dni'],
            'notes' => ['notes', 'notas', 'observaciones', 'comentarios'],
        ],
        'leads' => [
            'company_name' => ['company_name', 'company', 'empresa', 'razon_social'],
            'contact_name' => ['contact_name', 'name', 'nombre', 'contacto'],
            'email' => ['email', 'correo', 'mail'],
            'phone' => ['phone', 'telefono', 'tel', 'celular', 'mobile'],
            'position' => ['position', 'cargo', 'puesto', 'titulo'],
            'source' => ['source', 'origen', 'fuente'],
            'estimated_value' => ['estimated_value', 'value', 'valor', 'monto'],
            'notes' => ['notes', 'notas', 'observaciones', 'comentarios'],
        ],
    ];

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
            'type' => 'required|in:clients,leads',
        ]);

        $file = $request->file('file');
        $type = $request->get('type');

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $mappedFields = $this->autoMapFields($headers, $type);

        // Get preview (first 5 rows)
        $stmt = Statement::create()->limit(5);
        $records = $stmt->process($csv);
        $preview = [];
        foreach ($records as $record) {
            $preview[] = $record;
        }

        // Count total records
        $totalRecords = $csv->count();

        return response()->json([
            'success' => true,
            'data' => [
                'headers' => $headers,
                'mapped_fields' => $mappedFields,
                'preview' => $preview,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'type' => 'required|in:clients,leads',
            'field_mapping' => 'required|array',
            'duplicate_action' => 'required|in:skip,update,create',
            'duplicate_field' => 'required|string',
        ]);

        $file = $request->file('file');
        $type = $request->get('type');
        $fieldMapping = $request->get('field_mapping');
        $duplicateAction = $request->get('duplicate_action');
        $duplicateField = $request->get('duplicate_field');

        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0);

        $results = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $model = $type === 'clients' ? Client::class : Lead::class;

        return DB::transaction(function () use ($csv, $model, $fieldMapping, $duplicateAction, $duplicateField, &$results) {
            foreach ($csv as $index => $record) {
                $rowNumber = $index + 2; // +2 because index is 0-based and we skip header

                try {
                    $data = $this->mapRecordToData($record, $fieldMapping);

                    // Skip empty rows
                    if (empty(array_filter($data))) {
                        continue;
                    }

                    // Check for duplicates
                    $duplicateValue = $data[$duplicateField] ?? null;
                    $existing = null;

                    if ($duplicateValue) {
                        $existing = $model::where($duplicateField, $duplicateValue)->first();
                    }

                    if ($existing) {
                        if ($duplicateAction === 'skip') {
                            $results['skipped']++;
                            continue;
                        } elseif ($duplicateAction === 'update') {
                            $existing->update($data);
                            $results['updated']++;
                            continue;
                        }
                        // 'create' falls through to create a new record
                    }

                    // Add created_by
                    $data['created_by'] = auth('api')->id();

                    $model::create($data);
                    $results['imported']++;

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => $this->buildResultMessage($results),
                'data' => $results,
            ]);
        });
    }

    public function template(string $type): JsonResponse
    {
        if (!in_array($type, ['clients', 'leads'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de template inválido',
            ], 400);
        }

        $headers = array_keys(self::FIELD_MAPPINGS[$type]);

        $example = $type === 'clients' ? [
            'Acme Corp',
            'contacto@acme.com',
            '+54 11 1234-5678',
            'Av. Corrientes 1234',
            'Buenos Aires',
            'Argentina',
            '30-12345678-9',
            'Cliente potencial desde expo',
        ] : [
            'Acme Corp',
            'Juan Pérez',
            'juan@acme.com',
            '+54 11 1234-5678',
            'Gerente de TI',
            'web',
            '50000',
            'Interesado en servicios de desarrollo',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'headers' => $headers,
                'example' => array_combine($headers, $example),
            ],
        ]);
    }

    private function autoMapFields(array $headers, string $type): array
    {
        $mapping = [];
        $fieldMappings = self::FIELD_MAPPINGS[$type];

        foreach ($headers as $header) {
            $normalizedHeader = $this->normalizeHeader($header);
            $mappedField = null;

            foreach ($fieldMappings as $field => $aliases) {
                if (in_array($normalizedHeader, $aliases)) {
                    $mappedField = $field;
                    break;
                }
            }

            $mapping[$header] = $mappedField;
        }

        return $mapping;
    }

    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower($header);
        $header = str_replace([' ', '-', '.'], '_', $header);
        $header = preg_replace('/[^a-z0-9_]/', '', $header);
        return trim($header);
    }

    private function mapRecordToData(array $record, array $fieldMapping): array
    {
        $data = [];

        foreach ($fieldMapping as $csvField => $modelField) {
            if ($modelField && isset($record[$csvField])) {
                $value = trim($record[$csvField]);
                if ($value !== '') {
                    $data[$modelField] = $value;
                }
            }
        }

        return $data;
    }

    private function buildResultMessage(array $results): string
    {
        $parts = [];

        if ($results['imported'] > 0) {
            $parts[] = $results['imported'] . ' importado(s)';
        }
        if ($results['updated'] > 0) {
            $parts[] = $results['updated'] . ' actualizado(s)';
        }
        if ($results['skipped'] > 0) {
            $parts[] = $results['skipped'] . ' omitido(s)';
        }
        if (count($results['errors']) > 0) {
            $parts[] = count($results['errors']) . ' error(es)';
        }

        return 'Importación completada: ' . implode(', ', $parts);
    }
}
