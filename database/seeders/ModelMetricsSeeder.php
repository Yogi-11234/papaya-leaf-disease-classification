<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder untuk tabel model_metrics_fold, dataset_distribusi, dan penyakit.
 *
 * Sumber data: hasil evaluasi 5-Fold Stratified Group Cross Validation
 * pada notebook papayaleaf_cnn_v3_augment_after_split.ipynb (Google Colab).
 * Angka di bawah ini adalah hasil eksekusi asli, BUKAN estimasi/dummy.
 *
 * File acuan (taruh di database/seeders/data/):
 * - fold_metrics_summary.csv
 * - final_summary.json
 */
class ModelMetricsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPenyakit();
        $this->seedFoldMetrics();
        $this->seedDatasetDistribusi();
    }

    private function seedPenyakit(): void
    {
        $penyakit = [
            [
                'kode_penyakit' => 'P01',
                'nama_penyakit' => 'Anthracnose',
                'deskripsi' => 'Bercak coklat kehitaman melingkar pada daun, dapat meluas dan menyebabkan daun menguning serta gugur pada kondisi lembap.',
                'rekomendasi_penanganan' => 'Pangkas dan musnahkan bagian daun yang terinfeksi, perbaiki drainase kebun, aplikasikan fungisida berbahan aktif mankozeb atau tembaga sesuai dosis anjuran, dan hindari penyiraman dari atas daun.',
            ],
            [
                'kode_penyakit' => 'P02',
                'nama_penyakit' => 'Bacterial Spot',
                'deskripsi' => 'Bercak kecil berair (water-soaked) berwarna coklat gelap dengan tepi kekuningan, dapat menyebar cepat pada kondisi hujan.',
                'rekomendasi_penanganan' => 'Kurangi kelembapan sekitar tanaman, buang daun terinfeksi, aplikasikan bakterisida berbahan tembaga, dan hindari kontak air antar tanaman saat penyiraman.',
            ],
            [
                'kode_penyakit' => 'P03',
                'nama_penyakit' => 'Curl',
                'deskripsi' => 'Daun mengeriting/menggulung tidak normal, sering diasosiasikan dengan infeksi virus yang ditularkan oleh vektor serangga (kutu kebul).',
                'rekomendasi_penanganan' => 'Kendalikan populasi vektor (kutu kebul) dengan insektisida nabati/kimia sesuai anjuran, cabut dan musnahkan tanaman yang terinfeksi berat untuk mencegah penyebaran, gunakan bibit bebas virus.',
            ],
            [
                'kode_penyakit' => 'P04',
                'nama_penyakit' => 'Ring Spot',
                'deskripsi' => 'Bercak cincin (ring spot) pada daun dan buah, disebabkan oleh Papaya Ringspot Virus (PRSV), ditularkan oleh kutu daun (aphid).',
                'rekomendasi_penanganan' => 'Gunakan varietas tahan/toleran PRSV bila tersedia, kendalikan populasi aphid vektor, musnahkan tanaman terinfeksi berat, dan lakukan rotasi tanam.',
            ],
            [
                'kode_penyakit' => 'P05',
                'nama_penyakit' => 'Healthy',
                'deskripsi' => 'Daun dalam kondisi sehat, tidak menunjukkan gejala bercak, klorosis, atau deformasi.',
                'rekomendasi_penanganan' => 'Tidak diperlukan penanganan khusus. Lanjutkan praktik budidaya dan pemantauan rutin.',
            ],
        ];

        foreach ($penyakit as $p) {
            DB::table('penyakit')->updateOrInsert(
                ['kode_penyakit' => $p['kode_penyakit']],
                $p
            );
        }
    }

    private function seedFoldMetrics(): void
    {
        $csvPath = database_path('seeders/data/fold_metrics_summary.csv');

        if (!file_exists($csvPath)) {
            $this->command->warn("File tidak ditemukan: {$csvPath}. Lewati seeding model_metrics_fold.");
            return;
        }

        $rows = array_map('str_getcsv', file($csvPath));
        $header = array_shift($rows); // ['fold','accuracy','precision','recall','f1']

        foreach ($rows as $row) {
            if (count($row) < 5) {
                continue;
            }
            [$fold, $accuracy, $precision, $recall, $f1] = $row;

            DB::table('model_metrics_fold')->updateOrInsert(
                ['fold' => (int) $fold],
                [
                    'accuracy'  => (float) $accuracy,
                    'precision' => (float) $precision,
                    'recall'    => (float) $recall,
                    'f1'        => (float) $f1,
                ]
            );
        }

        $this->command->info('model_metrics_fold seeded dari fold_metrics_summary.csv (' . count($rows) . ' fold).');

        // Info ringkasan (final_summary.json) - opsional disimpan sebagai cache/config,
        // TIDAK perlu tabel terpisah karena hanya dipakai untuk menampilkan
        // ringkasan rata-rata di dashboard (bisa dihitung ulang dari model_metrics_fold,
        // atau dibaca langsung dari file JSON saat request dashboard).
        $summaryPath = database_path('seeders/data/final_summary.json');
        if (file_exists($summaryPath)) {
            $summary = json_decode(file_get_contents($summaryPath), true);
            $this->command->info(sprintf(
                'Ringkasan: accuracy %.2f%% ± %.2f%% | best fold %d | worst fold %d',
                $summary['accuracy_mean'],
                $summary['accuracy_std'],
                $summary['best_fold'],
                $summary['worst_fold']
            ));
        }
    }

    private function seedDatasetDistribusi(): void
    {
        // Sesuai hasil eksekusi asli notebook (total 422 citra original)
        $distribusi = [
            ['kode_penyakit' => 'P01', 'jumlah_citra' => 97],  // Anthracnose
            ['kode_penyakit' => 'P02', 'jumlah_citra' => 71],  // Bacterial Spot
            ['kode_penyakit' => 'P03', 'jumlah_citra' => 103], // Curl
            ['kode_penyakit' => 'P04', 'jumlah_citra' => 74],  // Ring Spot
            ['kode_penyakit' => 'P05', 'jumlah_citra' => 77],  // Healthy
        ];

        foreach ($distribusi as $d) {
            DB::table('dataset_distribusi')->updateOrInsert(
                ['kode_penyakit' => $d['kode_penyakit']],
                $d
            );
        }
    }
}
