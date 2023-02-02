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
        ->leftJoin('kegiatan_lokasi', 'kegiatan.id_kegiatan', 'kegiatan_lokasi.id_kegiatan')
        ->leftJoin('idn_adm1', 'kegiatan_lokasi.id_prov', 'idn_adm1.id_1')
        ->leftJoin('idn_adm2', 'kegiatan_lokasi.id_kota', 'idn_adm2.id_2')
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
            $kegiatan->limit($request->limit)->offset($request->offset);
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
            // "total" => $total,
            "data" => $kegiatan->get()
        ]);
    }

    public function total()
    {
        $total = DB::select(DB::raw('SELECT count(kegiatan) FROM kegiatan'));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $total
        ]);
    }

    public function filteredTotal()
    {
        $total = Kegiatan::join('user_pemrakarsa', 'kegiatan.id_pemrakarsa', 'user_pemrakarsa.id_pemrakarsa')
        ->leftJoin('kegiatan_lokasi', 'kegiatan.id_kegiatan', 'kegiatan_lokasi.id_kegiatan')
        ->leftJoin('idn_adm1', 'kegiatan_lokasi.id_prov', 'idn_adm1.id_1')
        ->leftJoin('idn_adm2', 'kegiatan_lokasi.id_kota', 'idn_adm2.id_2')
        ->selectRaw('count(*)');

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $total->get()
        ]);
    }

    public function cluster()
    {
        $cluster = DB::select(DB::raw("SELECT cluster_kbli.cluster_formulir, count(kegiatan) AS total
        FROM cluster_kbli JOIN kegiatan
        ON kegiatan.kbli = ANY (cluster_kbli.list_kbli)
        WHERE jenisdokumen = 'UKL-UPL' AND jenis_risiko = 'Menengah Rendah'
        GROUP BY cluster_kbli.cluster_formulir"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $cluster
        ]);
    }

    public function statistik(Request $request) // Statistik pertanggal
    {
        $date_start = $request->start_date;
        $date_end = $request->end_date;

        if ($date_start AND $date_end) {
            $date = " AND to_char(to_timestamp(tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $date = "";
        }

        if ($request->perbulan == 1) {
            $statistik = DB::select(DB::raw("SELECT count(kegiatan) AS jumlah, to_char(to_timestamp(tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY-MM') AS bulan
            FROM kegiatan WHERE jenisdokumen = 'UKL-UPL' AND jenis_risiko = 'Menengah Rendah'
            ". $date ."
            GROUP BY bulan ORDER BY bulan ASC"));
        } else {
            $statistik = DB::select(DB::raw("SELECT count(kegiatan) as jumlah, to_char(to_timestamp(tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') AS tanggal_record
            FROM kegiatan WHERE jenisdokumen = 'UKL-UPL' AND jenis_risiko = 'Menengah Rendah'
            ". $date ."
            GROUP BY tanggal_record ORDER BY tanggal_record ASC"));
        }

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

    public function jml_prov(Request $request) // Jumlah UKL-UPL MR per provinsi di Admin Pusat
    {
        if ($request->dokumen) {
            $filter = " where jenisdokumen = '" . $request->dokumen . "' and jenis_risiko = 'Menengah Rendah' ";
        } else {
            $filter = " where (jenisdokumen = 'SPPL' or jenisdokumen = 'ULK-UPL') and jenis_risiko = 'Menengah Rendah' ";
        }

        $query = "SELECT i.name_1 as prov, count(i.name_1) as jumlah FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1" .
        $filter
        ."GROUP BY prov";

        $prov = DB::select(DB::raw($query));

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
