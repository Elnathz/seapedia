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
        $comments = [
            'Aplikasi belanja terbaik buat anak kampus! UI-nya sangat memanjakan mata dan responsif.',
            'Barang yang dijual lengkap dan harganya cocok banget di kantong mahasiswa. Suka banget sama fitur pencariannya yang cepat.',
            'Mantap! Pengiriman dari toko kampus selalu cepat, apalagi bisa pakai voucher diskon yang lumayan banget.',
            'Pengalaman belanja jadi jauh lebih mudah. Desain aplikasinya keren banget dan nggak ngebosenin.',
            'Sangat membantu buat cari kebutuhan dadakan waktu ngerjain tugas. Aplikasinya juga ringan dan nggak pernah nge-lag.',
            'Fitur multi-gallery buat liat varian barang sangat jelas. Nggak pernah kecewa belanja di SEAPEDIA.',
            'Super recommended! Seller-nya ramah dan aplikasinya ngasih rekomendasi barang yang pas banget sama yang lagi dicari.',
            'Gampang dipakai walau baru pertama kali install. Proses checkout gampang, apalagi pilihan kurirnya bervariasi.',
            'Akhirnya ada marketplace khusus lingkungan kampus! Harga transparan dan bisa langsung ambil di tempat kalau mau cepat.',
            'Luar biasa, UI/UX-nya juara! Animasi saat pindah halamannya mulus banget. Belanja jadi nagih.'
        ];

        // Biar lebih dominan bintang 4 atau 5 karena SEAPEDIA aplikasinya keren
        $rating = $this->faker->randomElement([4, 5, 5, 5, 4, 3, 5]);

        return [
            'user_id' => null,
            'reviewer_name' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'rating' => $rating,
            'comment' => $this->faker->randomElement($comments),
        ];
    }
}
