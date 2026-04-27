<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use PDO;

class ImportNativeContacts extends Command
{
    protected $signature = 'crm:import-native {--db-path= : Path to the SQLite database}';
    protected $description = 'Import native contacts from SQLite database to MySQL';

    public function handle()
    {
        $dbPath = $this->option('db-path') ?: 'C:\Users\Esteban Selvaggi\Desktop\Tenanta\Marketing digital\Campañas\Contactos\contactos.db';
        
        if (!file_exists($dbPath)) {
            $this->error("Database file not found: {$dbPath}");
            return 1;
        }

        $this->info("Connecting to SQLite database at {$dbPath}...");

        try {
            $pdo = new PDO("sqlite:{$dbPath}");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Leer de la tabla principal "main"
            $stmt = $pdo->query("SELECT * FROM main");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->info("Found " . count($rows) . " contacts. Starting import...");

            $bar = $this->output->createProgressBar(count($rows));

            // Default tenant ID si se requiere en tu sistema multi-tenant
            $tenantId = 1;

            foreach ($rows as $row) {
                // Dividir el nombre completo si es necesario. (Asumimos "Título" o Nombre en la DB)
                $fullName = $row['Título'] ?? '';
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0] ?: 'Desconocido';
                $lastName = $nameParts[1] ?? '';

                // Tratar el campo Otros_Emails si está presente (tomar el primero si Email_Principal está vacío)
                $email = $row['Email_Principal'] ?? $row['Otros_Emails'] ?? null;
                $phone = $row['Teléfono'] ?? null;

                // Create or update by email/phone logic (assuming simple insert for now)
                Contact::updateOrCreate(
                    [
                        'email' => $email, // Llave de búsqueda principal
                    ],
                    [
                        'tenant_id' => $tenantId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone,
                        // Custom CRM fields (new ones)
                        'company' => $row['Empresa'] ?? null,
                        'job_title' => $row['Cargo'] ?? null,
                        'industry' => $row['Sector'] ?? null,
                        'activity' => $row['Actividad'] ?? null,
                        'linkedin_url' => $row['LinkedIn'] ?? null,
                        'maps_url' => $row['Google_Maps'] ?? null,
                        'address_details' => $row['Direccion'] ?? null,
                        'city' => $row['Ciudad'] ?? null,
                        'province' => $row['Provincia'] ?? null,
                        'country' => $row['Pais'] ?? null,
                        'deliverability_status' => $row['Entregabilidad'] ?? null,
                        'whatsapp_received' => $row['WhatsApp recepcionado'] ?? null,
                        'entity_type' => $row['Tipo_Entidad'] ?? null,
                        'assigned_sender' => $row['Remitente asignado'] ?? null,
                    ]
                );

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Contacts imported successfully!');

        } catch (\Exception $e) {
            $this->error("Error importing contacts: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
