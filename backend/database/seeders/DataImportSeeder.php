<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File; // Importante para manejar archivos
use App\Models\Testimonial;
use App\Models\Program;
use App\Models\News;

class DataImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $basePath = base_path();

        try {
            // Importación de Testimonios
            $this->importData('testimonials_backup.json', Testimonial::class, $basePath);
            
            // Importación de Programas
            $this->importData('programs_backup.json', Program::class, $basePath);
            
            // Importación de Noticias
            $this->importData('news_backup.json', News::class, $basePath);

            $this->command->info('✅ ¡Todos los datos e imágenes han sido importados exitosamente!');

        } catch (\Exception $e) {
            $this->command->error('❌ Ocurrió un error grave durante la importación: ' . $e->getMessage());
        }
    }

    /**
     * Función privada para importar datos y copiar imágenes.
     */
    private function importData(string $filename, string $modelClass, string $basePath)
    {
        $filePath = $basePath . '/' . $filename;
        $modelName = class_basename($modelClass);

        // 1. Verificar si existe el archivo JSON
        if (!File::exists($filePath)) {
            $this->command->warn("⚠️  ADVERTENCIA: Archivo {$filename} NO encontrado. Saltando la importación de {$modelName}.");
            return;
        }

        $this->command->info("🔄 Procesando {$modelName}...");
        
        $jsonContent = File::get($filePath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("❌ ERROR: El archivo {$filename} tiene un formato JSON inválido.");
            return;
        }
        
        // 2. Limpiar la tabla antes de insertar
        $modelClass::truncate(); 
        $importedCount = 0;

        foreach ($data as $item) {
            unset($item['id']); // Dejar que la BD asigne el ID

            // --- LÓGICA DE COPIA DE IMÁGENES ---
            if (isset($item['image']) && !empty($item['image'])) {
                // Ruta Fuente: database/seeders/images/testimonials/foto.png
                $sourcePath = database_path('seeders/images/' . $item['image']);
                
                // Ruta Destino: storage/app/public/testimonials/foto.png
                $destPath = storage_path('app/public/' . $item['image']);

                // Verificamos si tenemos la imagen original
                if (File::exists($sourcePath)) {
                    // Asegurar que la carpeta destino exista (ej. storage/app/public/testimonials)
                    $destDir = dirname($destPath);
                    if (!File::exists($destDir)) {
                        File::makeDirectory($destDir, 0755, true);
                    }

                    // Copiar el archivo
                    File::copy($sourcePath, $destPath);
                    $this->command->info("   📸 Imagen copiada: {$item['image']}");
                } else {
                    $this->command->warn("   ⚠️ Imagen no encontrada en seeders: {$item['image']}");
                }
            }
            // -----------------------------------

            $modelClass::create($item);
            $importedCount++;
        }
        $this->command->info("   -> Éxito: {$importedCount} registros de {$modelName} creados.");
    }
}