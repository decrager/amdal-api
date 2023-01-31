<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    public function index(Request $request) // Datatable
    {
        $kegiatan = Kegiatan::join('user_pemrakarsa', 'kegiatan.id_pemrakarsa', 'user_pemrakarsa.id_pemrakarsa')
        ->join('kegiatan_lokasi', 'kegiatan.id_kegiatan', 'kegiatan_lokasi.id_kegiatan')
        ->join('idn_adm1', 'kegiatan_lokasi.id_prov', 'idn_adm1.id_1')
        ->join('idn_adm2', 'kegiatan_lokasi.id_kota', 'idn_adm2.id_2')
        ->select(
            'kegiatan.sid',
            'user_pemrakarsa.oss_nib',
            'user_pemrakarsa.pemrakarsa',
            'kegiatan.judul_kegiatan',
            'kegiatan.skala',
            'kegiatan.kewenangan',
            'kegiatan.tanggal_input',
            'kegiatan.jenisdokumen',
            'kegiatan.id_proyek',
            'kegiatan.jenis_risiko',
            'kegiatan.kbli',
            'kegiatan.file',
            'kegiatan.pkplh_doc',
            'kegiatan_lokasi.lokasi',
            'idn_adm1.name_1',
            'idn_adm2.name_2',
        );

        if ($request->kewenangan) {
            $kegiatan->where('kegiatan.kewenangan', $request->kewenangan);
        } if ($request->limit) {
            $kegiatan->take($request->limit);
        } if ($request->search) {
            $kegiatan->where('user_pemrakarsa.oss_nib', 'LIKE', '%' . $request->search . '%')
            ->orWhere('user_pemrakarsa.pemrakarsa', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.judul_kegiatan', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.skala', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.kewenangan', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.tanggal_input', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.jenisdokumen', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.id_proyek', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.jenis_risiko', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.kbli', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.file', 'LIKE', '%' . $request->search . '%')
            ->orWhere('kegiatan.pkplh_doc', 'LIKE', '%' . $request->search . '%')
            ->orWhere('idn_adm1.name_1', 'LIKE', '%' . $request->search . '%')
            ->orWhere('idn_adm2.name_2', 'LIKE', '%' . $request->search . '%');
        } if ($request->provinsi) {
            if ($request->search) {
                $kegiatan->orWhere('idn_adm1.name_1', 'LIKE', '%' . $request->provinsi . '%');
            } else {
                $kegiatan->where('idn_adm1.name_1', 'LIKE', '%' . $request->provinsi . '%');
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $kegiatan->get()
        ]);
    }

    public function statistik() // Statistik pertanggal
    {
        $statistik = DB::select(DB::raw("SELECT count(kegiatan) as jumlah, to_char(to_timestamp(tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') AS tanggal_record FROM kegiatan GROUP BY tanggal_record order by tanggal_record asc"));
        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $statistik
        ]);
    }

    public function uklupl_sppl() // Jumlah UKL-UPL MR dan SPPL
    {
        $jenis = DB::select(DB::raw("SELECT count(jenisdokumen) as jumlah, jenisdokumen FROM kegiatan where (jenisdokumen = 'UKL-UPL' and jenis_risiko = 'Menengah Rendah') or jenisdokumen = 'SPPL' GROUP BY jenisdokumen "));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $jenis
        ]);
    }

    public function uklupl_pusat() // Jumlah data UKL-UPL MR per kewenangan di Admin Pusat
    {
        $uklupl = DB::select(DB::raw("SELECT count(kewenangan) as jumlah, kewenangan FROM kegiatan
        where jenisdokumen = 'UKL-UPL' and jenis_risiko = 'Menengah Rendah'
        GROUP BY kewenangan"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $uklupl
        ]);
    }

    public function sppl_pusat() // Jumlah data SPPL per kewenangan di Admin Pusat
    {
        $sppl = DB::select(DB::raw("SELECT count(kewenangan) as jumlah, kewenangan FROM kegiatan
        where jenisdokumen = 'SPPL' 
        GROUP BY kewenangan"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $sppl
        ]);
    }

    public function jml_prov() // Jumlah UKL-UPL MR per provinsi di Admin Pusat
    {
        $prov = DB::select(DB::raw("SELECT i.name_1 as prov, count(i.name_1) as jumlah FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1  
        where jenisdokumen = 'UKL-UPL' and jenis_risiko = 'Menengah Rendah'
        GROUP BY prov"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $prov
        ]);
    }

    public function jml_kegiatan()
    {
        $kegiatan = DB::select(DB::raw("SELECT count(kegiatan) as jumlah, to_char(to_timestamp(tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') AS tanggal_record FROM kegiatan GROUP BY tanggal_record"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $kegiatan
        ]);
    }
}
