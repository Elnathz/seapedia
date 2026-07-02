<?php

namespace Database\Factories;

use App\Models\AppReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppReview>
 */
class AppReviewFactory extends Factory
{
    protected $model = AppReview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Rizky Pratama', 'Aulia Rahma', 'Bima Satria', 'Dinda Mutiara', 'Kevin Wijaya',
            'Siti Aminah', 'Andi Firmansyah', 'Putri Larasati', 'Reza Kurniawan', 'Nadia Safitri',
            'Dimas Aditya', 'Ayu Lestari', 'Fajar Nugroho', 'Siska Amelia', 'Gilang Saputra',
            'Fikri Haikal', 'Tiara Andini', 'Ardiansyah', 'Nisa Sabyan', 'Iqbaal Ramadhan',
        ];

        $comments = [
            'Aplikasi belanja terbaik buat anak kampus! UI-nya sangat memanjakan mata, smooth banget kayak aplikasi e-commerce raksasa. Pokoknya mantap jiwa!',
            'Gila sih ini, barang yang dijual lengkap banget dan harganya emang disesuaikan sama kantong mahasiswa. Suka banget sama fitur pencarian dan filternya yang cepat tanpa loading lama.',
            'Mantap! Pengiriman dari toko kampus selalu on-time, apalagi voucher diskonnya sering banget diadain. Ngebantu banget buat ngirit uang bulanan.',
            'Pengalaman belanja jadi jauh lebih asik. Desain aplikasinya keren banget, transisinya mulus dan warna-warnanya nggak bikin sakit mata. Developer-nya jago banget nih!',
            'Sangat ngebantu buat cari kebutuhan dadakan waktu ngerjain tugas atau praktikum. Aplikasinya juga super ringan, nggak pernah crash walaupun dibuka di HP kentang.',
            'Fitur multi-gallery buat liat varian barang jelas banget. Jadi nggak takut salah beli warna atau ukuran. SEAPEDIA the best lah pokoknya.',
            'Super recommended! Seller-nya ramah-ramah, dan aplikasinya ngasih rekomendasi barang yang pas banget sama histori pencarian kita. Algoritmanya jalan banget.',
            'UX-nya juara! Gampang dipakai walau baru pertama kali install. Proses checkout sat-set, pilihan kurirnya juga banyak dan harganya transparan.',
            'Akhirnya ada marketplace khusus lingkungan kampus! Jadi gampang kalau mau beli makanan atau alat tulis tanpa harus keluar kosan. Luv banget sama SEAPEDIA <3',
            'Luar biasa, UI/UX-nya masterclass! Animasi saat pindah halamannya mulus banget, kayak bukan aplikasi tugas kampus biasa. Belanja di sini jadi nagih.',
        ];

        // Dominan bintang 5
        $rating = $this->faker->randomElement([4, 5, 5, 5, 5, 4, 3, 5, 5, 5]);

        return [
            'user_id' => null,
            'reviewer_name' => $this->faker->randomElement($names),
            'rating' => $rating,
            'comment' => $this->faker->randomElement($comments),
        ];
    }
}
