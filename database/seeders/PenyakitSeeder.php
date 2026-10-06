<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenyakitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('penyakit')->insert([
            [
                'kode_penyakit' => 'P01',
                'nama_penyakit' => 'Anthracnose',
                'deskripsi' => 'Penyakit Antraknosa disebabkan oleh jamur Colletotrichum gloeosporioides. Gejalanya ditandai dengan munculnya bercak lingkaran kecil basah pada permukaan buah atau daun pepaya, yang lama-kelamaan melebar, menjadi cekung, dan berwarna cokelat kehitaman.',
                'rekomendasi_penanganan' => "1. Pemangkasan: Potong dan musnahkan daun atau bagian tanaman yang sakit untuk mengurangi sumber spora.\n2. Jaga Sanitasi Kebun: Bersihkan sisa-sisa tanaman di sekitar kebun.\n3. Pengaturan Jarak Tanam: Pastikan tanaman tidak terlalu rapat agar sirkulasi udara baik dan kelembapan berkurang.\n4. Aplikasi Fungisida: Semprotkan fungisida berbahan aktif tembaga hidroksida atau mankozeb sesuai dosis anjuran bila serangan meluas."
            ],
            [
                'kode_penyakit' => 'P02',
                'nama_penyakit' => 'Bacterial Spot',
                'deskripsi' => 'Bercak Bakteri disebabkan oleh patogen bakteri. Menyerang daun muda maupun tua, ditandai dengan bercak kecil bersudut basah (water-soaked) yang kemudian berubah menjadi cokelat tua atau hitam. Daun yang terserang parah akan menguning dan gugur.',
                'rekomendasi_penanganan' => "1. Sanitasi Kebun: Cabut dan bakar daun atau tanaman yang terinfeksi bakteri secara total.\n2. Hindari Penyiraman Atas (Overhead): Lakukan penyiraman langsung ke tanah untuk mencegah cipratan air menyebarkan bakteri ke daun lain.\n3. Disinfeksi Alat Pertanian: Bersihkan gunting atau pisau pemangkas menggunakan disinfektan setelah digunakan.\n4. Aplikasi Bakterisida: Gunakan bakterisida berbahan aktif tembaga jika infeksi menyebar di musim hujan."
            ],
            [
                'kode_penyakit' => 'P03',
                'nama_penyakit' => 'Curl',
                'deskripsi' => 'Penyakit Daun Keriting (Curl) disebabkan oleh Papaya Leaf Curl Virus (PaLCV) yang ditularkan oleh vektor kutu kebul (Bemisia tabaci). Gejalanya berupa daun mengeriting ke dalam atau ke luar, menebal, berkerut, dengan tulang daun yang menonjol dan tanaman menjadi kerdil.',
                'rekomendasi_penanganan' => "1. Pengendalian Vektor: Lakukan penyemprotan insektisida nabati atau kimia untuk membasmi kutu kebul.\n2. Eradikasi Tanaman Sakit: Cabut tanaman yang menunjukkan gejala keriting parah agar tidak menjadi sumber penularan bagi tanaman sehat di sekitarnya.\n3. Pemasangan Yellow Sticky Trap: Pasang perangkap lengket berwarna kuning di sekitar kebun untuk memantau dan menangkap hama vektor.\n4. Pemupukan Berimbang: Berikan pupuk nitrogen, fosfor, kalium, dan unsur mikro secara seimbang untuk memperkuat daya tahan tanaman."
            ],
            [
                'kode_penyakit' => 'P04',
                'nama_penyakit' => 'Ring Spot',
                'deskripsi' => 'Bercak Cincin (Ring Spot) disebabkan oleh Papaya Ringspot Virus (PRSV) yang ditularkan oleh kutu daun (Aphids). Gejala khas berupa pola cincin konsentris berwarna hijau tua pada buah, garis-garis gelap pada tangkai daun/batang, dan daun mengalami mosaik/distorsi parah.',
                'rekomendasi_penanganan' => "1. Penggunaan Bibit Bebas Virus: Gunakan bibit unggul yang bersertifikat bebas PRSV.\n2. Pengendalian Kutu Daun: Semprotkan minyak mineral ringan atau insektisida pengendali kutu daun.\n3. Pemusnahan Inang Alternatif: Bersihkan gulma di sekitar kebun yang bisa menjadi tanaman inang perantara bagi kutu daun.\n4. Isolasi Kebun: Buat pembatas fisik atau jarak isolasi yang cukup dari kebun pepaya tua yang sudah terinfeksi."
            ],
            [
                'kode_penyakit' => 'P05',
                'nama_penyakit' => 'Healthy',
                'deskripsi' => 'Tanaman Pepaya berada dalam kondisi sehat. Daun berwarna hijau segar merata, permukaan bersih dari bercak patogen, tulang daun berkembang normal, dan sirkulasi fotosintesis berjalan optimal.',
                'rekomendasi_penanganan' => "1. Perawatan Rutin: Lakukan penyiraman berkala sesuai kebutuhan tanah.\n2. Pemupukan Teratur: Berikan pupuk organik/kompos secara berkala untuk menjaga nutrisi tanah.\n3. Monitoring Berkala: Periksa permukaan bawah daun secara rutin untuk mendeteksi dini keberadaan hama pengisap.\n4. Pembersihan Gulma: Jaga kebersihan piringan pohon dari rumput liar agar tidak berebut nutrisi."
            ]
        ]);
    }
}
