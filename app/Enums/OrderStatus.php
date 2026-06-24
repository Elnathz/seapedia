<?php

namespace App\Enums;

enum OrderStatus: string
{
    case SedangDikemas = 'sedang_dikemas';
    case MenungguPengirim = 'menunggu_pengirim';
    case SedangDikirim = 'sedang_dikirim';
    case PesananSelesai = 'pesanan_selesai';
    case Dikembalikan = 'dikembalikan';
}
