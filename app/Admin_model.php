<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Admin_model extends Model
{
    public static function getUserInfo($logged_email)
    {
        return DB::table('users')->join('users_info', 'users.id', '=', 'users_info.id_user')
            ->select('users_info.*')->where('email', '=', $logged_email)->first();
    }

    public static function getTransaksiForCetak($id)
    {
        return DB::table('detail_transaksi')->select('barang.nama_barang', 'kategori.nama_kategori', 'servis.nama_servis', 'detail_transaksi.banyak', 'detail_transaksi.sub_total')
            ->join('barang', 'detail_transaksi.id_barang', '=', 'barang.id_barang')
            ->join('kategori', 'detail_transaksi.id_kategori', '=', 'kategori.id_kategori')
            ->join('servis', 'detail_transaksi.id_servis', '=', 'servis.id_servis')->where('detail_transaksi.id_transaksi', '=', $id)
            ->get();
    }

    public static function cekHarga($barang, $kategori, $servis)
    {
        return DB::table('daftar_harga')->where([
            'id_barang' => $barang,
            'id_kategori' => $kategori,
            'id_servis' => $servis
        ])->exists();
    }

    public static function cekMember($id)
    {
        return DB::table('users')->where([
            'id' => $id,
            'role' => 2
        ])->exists();
    }

    public static function getHarga($barang, $kategori, $servis)
    {
        return DB::table('daftar_harga')->where([
            'id_barang' => $barang,
            'id_kategori' => $kategori,
            'id_servis' => $servis
        ])->pluck('harga');
    }

    public static function simpanTransaksi($transaksi, $id_member, $total_harga)
    {
        $id_transaksi = DB::table('transaksi')->insertGetId([
            'tgl_masuk' => date('Y-m-d H:i:s'),
            'id_status' => 1,
            'id_user' => $id_member,
            'tgl_selesai' => null,
            'total_harga' => $total_harga
        ]);

        foreach ($transaksi as $key => $value) {
            DB::table('detail_transaksi')->insert([
                'id_transaksi' => $id_transaksi,
                'id_barang' => $transaksi[$key]['id_barang'],
                'id_kategori' => $transaksi[$key]['id_kategori'],
                'id_servis' => $transaksi[$key]['id_servis'],
                'banyak' => $transaksi[$key]['banyak'],
                'sub_total' => $transaksi[$key]['harga']
            ]);
        }

        $poin = DB::table('users_info')->where('id_user', '=', $id_member)->pluck('poin')[0];
        $poin += 1;
        DB::table('users_info')->where('id_user', '=', $id_member)->update([
            'poin' => $poin
        ]);

        return $id_transaksi;
    }

    public static function getRiwayatTransaksi()
    {
        return DB::table('transaksi')->join('users_info', 'transaksi.id_user', '=', 'users_info.id_user')
            ->select('transaksi.*', 'users_info.nama')->get();
    }

    public static function getDetailTransaksi($id)
    {
        return DB::table('detail_transaksi')->select('barang.nama_barang', 'kategori.nama_kategori', 'servis.nama_servis', 'detail_transaksi.banyak', 'detail_transaksi.sub_total')
            ->join('barang', 'detail_transaksi.id_barang', '=', 'barang.id_barang')
            ->join('kategori', 'detail_transaksi.id_kategori', '=', 'kategori.id_kategori')
            ->join('servis', 'detail_transaksi.id_servis', '=', 'servis.id_servis')->where('detail_transaksi.id_transaksi', '=', $id)
            ->get();
    }

    public static function tambahHarga($barang, $kategori, $servis, $harga)
    {
        DB::table('daftar_harga')->insert([
            'id_harga' => null,
            'id_barang' => $barang,
            'id_kategori' => $kategori,
            'id_servis' => $servis,
            'harga' => $harga
        ]);
    }

    public static function getSaranKomplain($tipe)
    {
        return DB::table('saran_komplain')->join('users_info', 'saran_komplain.id_user', '=', 'users_info.id_user')
            ->select('saran_komplain.id', 'users_info.nama')
            ->where([
                'tipe' => $tipe,
                'balasan' => NULL
            ])->get();
    }
}