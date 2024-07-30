<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    public function index(Request $request) // Datatable
    {
        $limit = "";
        if ($request->limit) {
            $limit .= " limit " . $request->limit . " offset " . $request->offset;
        }

        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getStripDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= "  AND (to_timestamp(kegiatan.tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '". $date_start ."' AND '". $date_end ."') ";
        } else {
            $dateFilter .= " AND (to_timestamp(kegiatan.tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '". $date['start'] ."' AND now()) ";
        }

        $filter = "";
        if ($request->provinsi && empty($request->kabkota)) {
            $filter = " AND (i.provinsi like '%" . $request->provinsi . "%')";
        } elseif ($request->provinsi && $request->kabkota) {
            $filter = " AND (i.provinsi like '%" . $request->provinsi . "%' and i2.kab_kota like '%" . $request->kabkota . "%') ";
        }

        $search = "";
        if ($request->search) {
            $search .= " WHERE oss_nib like '%". $request->search ."%'";
            $search .= " OR notelp like '%". $request->search ."%'";
            $search .= " OR email like '%". $request->search ."%'";
            $search .= " OR pemrakarsa like '%". $request->search ."%'";
            $search .= " OR judul_kegiatan like '%". $request->search ."%'";
            $search .= " OR skala like '%". $request->search ."%'";
            $search .= " OR kewenangan like '%". $request->search ."%'";
            $search .= " OR jenisdokumen like '%". $request->search ."%'";
            $search .= " OR jenis_risiko like '%". $request->search ."%'";
            $search .= " OR kbli like '%". $request->search ."%'";
            $search .= " OR name_1 like '%". $request->search ."%'";
            $search .= " OR name_2 like '%". $request->search ."%'";
        }

        $kegiatan = DB::select(DB::raw("SELECT kegiatan.sid, oss_nib as nib, kegiatan.kbli, notelp, email, pemrakarsa, judul_kegiatan, skala, kewenangan,
        to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd HH24:MI:ss') AS tanggal_input,
        jenisdokumen, id_proyek, jenis_risiko, kbli, file, pkplh_doc, kl.lokasi, id_izin, name_1 as prov, name_2 as kota,
        case when file is null then '-' else concat('<a class=\"btn btn-sm btn-success\" href="."https://amdal.menlhk.go.id/amdalnet', replace(file,'./assets', '/assets'), '"." target="."_blank"."><i class=\"fas fa-download\"></i></a>') end as file_url,
        case when pkplh_local_doc is null then '-' else concat('<a class=\"btn btn-sm btn-success\" href="."https://amdal.menlhk.go.id/amdalnet/assets/uploads/pkplh/', pkplh_local_doc, '"." target="."_blank"."><i class=\"fas fa-download\"></i></a>') end as pl_url
        from kegiatan
        inner join user_pemrakarsa on (kegiatan.id_pemrakarsa = user_pemrakarsa.id_pemrakarsa)
        and ((kegiatan.jenisdokumen = 'UKL-UPL' and kegiatan.jenis_risiko = 'Menengah Rendah') or kegiatan.jenisdokumen = 'SPPL')
        ". $dateFilter ."
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1 
        left join idn_adm2 AS i2 ON kl.id_kota = id_2 " . $filter . "
        " . $search . "
        ORDER BY kegiatan.sid desc" . $limit));
        
        // and (to_timestamp(tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '2021-08-01' AND now())
        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $kegiatan
        ]);
    }

    public function filteredTotal(Request $request)
    {
        #region Date Filter
        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getStripDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= " AND (to_timestamp(kegiatan.tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '". $date_start ."' AND '". $date_end ."')";
        } else {
            $dateFilter .= " AND (to_timestamp(kegiatan.tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '". $date['start'] ."' AND now())";
        }
        #endregion

        $filter = "";
        if ($request->provinsi && empty($request->kabkota)) {
            $filter = " AND (i.provinsi like '%" . $request->provinsi . "%')";
        } elseif ($request->provinsi && $request->kabkota) {
            $filter = " AND (i.provinsi like '%" . $request->provinsi . "%' and i2.kab_kota like '%" . $request->kabkota . "%') ";
        }

        $search = "";
        if ($request->search) {
            $search .= " WHERE oss_nib like '%". $request->search ."%'";
            $search .= " OR notelp like '%". $request->search ."%'";
            $search .= " OR email like '%". $request->search ."%'";
            $search .= " OR pemrakarsa like '%". $request->search ."%'";
            $search .= " OR judul_kegiatan like '%". $request->search ."%'";
            $search .= " OR skala like '%". $request->search ."%'";
            $search .= " OR kewenangan like '%". $request->search ."%'";
            $search .= " OR jenisdokumen like '%". $request->search ."%'";
            $search .= " OR jenis_risiko like '%". $request->search ."%'";
            $search .= " OR kbli like '%". $request->search ."%'";
            $search .= " OR name_1 like '%". $request->search ."%'";
            $search .= " OR name_2 like '%". $request->search ."%'";
        }

        // $filter = "";
        // if ($request->provinsi && empty($request->kabkota)) {
        //     $filter .= " WHERE i.provinsi LIKE '%" . $request->provinsi . "%' ";
        // } if ($request->kabkota && $request->kabkota) {
        //     $filter .= " WHERE (i.provinsi LIKE '%" . $request->provinsi . "%' AND j.kab_kota LIKE '%" . $request->kabkota . "%') ";
        // }

        $total = DB::select(DB::raw("SELECT count(kegiatan.*)
        FROM kegiatan
        inner join user_pemrakarsa on (kegiatan.id_pemrakarsa = user_pemrakarsa.id_pemrakarsa)
        and ((kegiatan.jenisdokumen = 'UKL-UPL' and kegiatan.jenis_risiko = 'Menengah Rendah') or kegiatan.jenisdokumen = 'SPPL')"
        . $dateFilter . "
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1 
        left join idn_adm2 AS i2 ON kl.id_kota = id_2
        ". $filter . $search));
        // ". $filter . "and (to_timestamp(tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '2021-08-01' AND now())"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $total
        ]);
    }

    public function total()
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

    public function cluster(Request $request)
    {
        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date['start'] . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date['now'] . "' ";
        }
        
        $filter = "";

        if ($request->provinsi) {
            $filter .= " AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            if ($request->kabkota) {
                $filter .= " AND i.provinsi LIKE '%" . $request->provinsi . "%' AND j.kab_kota LIKE '%" . $request->kabkota . "%' ";
            }
        }

        $cluster = DB::select(DB::raw("SELECT cluster_kbli.cluster_short, count(kegiatan) AS total
        FROM cluster_kbli JOIN kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1
        left join idn_adm2 AS j ON kl.id_kota = j.id_2
        ON kegiatan.kbli = ANY (cluster_kbli.list_kbli)
        WHERE jenisdokumen = 'UKL-UPL' AND jenis_risiko = 'Menengah Rendah' " . $filter . $dateFilter . "
        GROUP BY cluster_kbli.cluster_formulir, cluster_kbli.sid ORDER BY cluster_kbli.sid DESC"));

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
        $date = $this->getDate();

        if ($date_start AND $date_end) {
            $dateFilter = " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $dateFilter = " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date['start'] . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date['now'] . "' ";
        }

        $filter = "";

        if ($request->kewenangan == 'Pusat') {
            $filter .= "";
        } else if ($request->kewenangan != 'Pusat') {
            $filter .= "AND kegiatan.kewenangan LIKE '%" . $request->kewenangan . "%' ";
        }

        if ($request->provinsi) {
            $filter .= "AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            if ($request->kabkota) {
                $filter .= "AND j.kab_kota LIKE '%" . $request->kabkota . "%' ";
            }
        }

        if ($request->filterKewenangan == 'all') {
            $filter = "";
        } else if ($request->filterKewenangan == 'Pusat') {
            $filter = "AND kegiatan.kewenangan LIKE '%Pusat%' ";
        }

        if ($request->dokumen == 'UKL-UPL') {
            $dokumen = "kegiatan.jenisdokumen = 'UKL-UPL' AND";
        } else if ($request->dokumen == 'SPPL') {
            $dokumen = "kegiatan.jenisdokumen = 'SPPL' AND";
        } else {
            $dokumen = "";
        }

        if ($request->perbulan == 1) {
            $statistik = DB::select(DB::raw("SELECT count(*) AS jumlah, to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY-MM') AS bulan
            FROM kegiatan
            left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
            left join idn_adm1 AS i ON kl.id_prov = id_1
            left join idn_adm2 AS j ON kl.id_kota = j.id_2
            WHERE " . $dokumen . " kegiatan.jenis_risiko = 'Menengah Rendah'
            ". $dateFilter . $filter ."
            GROUP BY bulan ORDER BY bulan ASC"));
        } else {
            $statistik = DB::select(DB::raw("SELECT count(*) as jumlah, to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') AS tanggal_record
            FROM kegiatan
            left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
            left join idn_adm1 AS i ON kl.id_prov = id_1
            left join idn_adm2 AS j ON kl.id_kota = j.id_2
            WHERE kegiatan.jenisdokumen = 'UKL-UPL' AND kegiatan.jenis_risiko = 'Menengah Rendah'
            ". $dateFilter . $filter ."
            GROUP BY tanggal_record ORDER BY tanggal_record ASC"));
        }

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $statistik
        ]);
    }

    public function uklupl_sppl(Request $request) // Jumlah UKL-UPL MR dan SPPL
    {
        $filter = "";

        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date['start'] . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date['now'] . "' ";
        }

        if ($request->kewenangan) {
            $filter .= "AND kegiatan.kewenangan LIKE '%" . $request->kewenangan . "%' ";
        }
        if ($request->provinsi) {
            $filter .= "AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            if ($request->kabkota) {
                $filter .= "AND j.kab_kota LIKE '%" . $request->kabkota . "%' ";
            }
        }

        $jenis = DB::select(DB::raw("SELECT count(kegiatan.kewenangan) as jumlah, kegiatan.kewenangan FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1
        left join idn_adm2 AS j ON kl.id_kota = j.id_2
        where (kegiatan.jenisdokumen = 'UKL-UPL' and kegiatan.jenis_risiko = 'Menengah Rendah' " . $filter . $dateFilter . ") or kegiatan.jenisdokumen = 'SPPL' GROUP BY kegiatan.kewenangan"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $jenis
        ]);
    }

    public function uklupl_pusat(Request $request) // Jumlah data UKL-UPL MR per kewenangan di Admin Pusat
    {
        $filter = "";

        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date['start'] . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date['now'] . "' ";
        }

        if ($request->kewenangan) {
            $filter .= "AND kegiatan.kewenangan LIKE '%" . $request->kewenangan . "%' ";
        }
        if ($request->provinsi) {
            $filter .= "AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            if ($request->kabkota) {
                $filter .= "AND j.kab_kota LIKE '%" . $request->kabkota . "%' ";
            }
        }

        $uklupl = DB::select(DB::raw("SELECT count(kegiatan.kewenangan) as jumlah, kegiatan.kewenangan FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1
        left join idn_adm2 AS j ON kl.id_kota = j.id_2
        where kegiatan.jenisdokumen = 'UKL-UPL' " . $filter . $dateFilter . "and kegiatan.jenis_risiko = 'Menengah Rendah' GROUP BY kegiatan.kewenangan"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $uklupl
        ]);
    }

    public function sppl_pusat(Request $request) // Jumlah data SPPL per kewenangan di Admin Pusat
    {
        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date['start'] . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date['now'] . "' ";
        }

        $filter = "";

        if ($request->kewenangan) {
            $filter .= "AND kegiatan.kewenangan LIKE '%" . $request->kewenangan . "%' ";
        }
        if ($request->provinsi) {
            $filter .= "AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            if ($request->kabkota) {
                $filter .= "AND j.kab_kota LIKE '%" . $request->kabkota . "%' ";
            }
        }

        $sppl = DB::select(DB::raw("SELECT count(kegiatan.kewenangan) as jumlah, kegiatan.kewenangan FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1
        left join idn_adm2 AS j ON kl.id_kota = j.id_2
        where kegiatan.jenisdokumen = 'SPPL' " . $filter . $dateFilter . " GROUP BY kegiatan.kewenangan"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $sppl
        ]);
    }

    public function jml_prov(Request $request) // Jumlah UKL-UPL MR per provinsi di Admin Pusat
    {
        $date_start = $request->start_date;
        $date_end = $request->end_date;
        $date = $this->getDate();

        $dateFilter = "";
        if ($date_start AND $date_end) {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date_start . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date_end . "' ";
        } else {
            $dateFilter .= " AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') >= '" . $date['start'] . "' AND to_char(to_timestamp(kegiatan.tanggal_input,'dd/MM/YYYY HH24:MI:ss'),'YYYY/MM/dd') <= '" . $date['now'] . "' ";
        }

        if ($request->dokumen) {
            $filter = " WHERE kegiatan.jenisdokumen = '" . $request->dokumen . "' AND kegiatan.jenis_risiko = 'Menengah Rendah' ";
        } else {
            $filter = " WHERE (kegiatan.jenisdokumen = 'SPPL' or kegiatan.jenisdokumen = 'UKL-UPL') AND kegiatan.jenis_risiko = 'Menengah Rendah' ";
        }

        if ($request->dokumen && $request->kewenangan) {
            $filter .= " AND kegiatan.kewenangan LIKE '%" . $request->kewenangan . "%' ";
            if ($request->provinsi) {
                $filter .= " AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            }
        }

        $query = "SELECT i.name_1 as prov, count(i.name_1) as jumlah FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1" .
        $filter . $dateFilter
        ."GROUP BY prov ORDER BY jumlah DESC";

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

    public function test()
    {
        $data = $this->getDate();

        return $data;
    }

    public function uklupl_sppl_tot(Request $request)
    {
        $doc = "";
        if ($request->dokumen == "UKL-UPL") {
            $doc .= " WHERE (kegiatan.jenisdokumen = 'UKL-UPL' and kegiatan.jenis_risiko = 'Menengah Rendah') ";
        } elseif ($request->dokumen == "SPPL") {
            $doc .= " WHERE (kegiatan.jenisdokumen = 'SPPL') ";
        }

        $filter = "";
        if ($request->provinsi) {
            $filter .= "AND i.provinsi LIKE '%" . $request->provinsi . "%' ";
            if ($request->kabkota) {
                $filter .= "AND j.kab_kota LIKE '%" . $request->kabkota . "%' ";
            }
        }

        $total = DB::select(DB::raw("SELECT count(*) FROM kegiatan
        left join kegiatan_lokasi as kl on kegiatan.id_kegiatan = kl.id_kegiatan
        left join idn_adm1 AS i ON kl.id_prov = id_1
        left join idn_adm2 AS j ON kl.id_kota = j.id_2
        " . $doc . $filter . "
        and to_timestamp(tanggal_input,'DD/MM/YYYY HH24:MI:SS') BETWEEN '2021-08-01' AND now()"));

        return response()->json([
            "success" => true,
            "message" => "Data List",
            "data" => $total
        ]);
    }

    public function getDate()
    {
        $month = Carbon::now()->subMonths(3)->format('Y/m');
        $now = Carbon::now()->format('Y/m/d');

        $date = Carbon::now()->format('d');
        $subtract = $date - ($date - 1);

        $start = $month . "/0" . $subtract;

        $data = [
            'start' => $start,
            'now' => $now
        ];

        return $data;
    }

    public function getStripDate()
    {
        $month = Carbon::now()->subMonths(3)->format('Y-m');
        $now = Carbon::now()->format('Y-m-d');

        $date = Carbon::now()->format('d');
        $subtract = $date - ($date - 1);

        $start = $month . "-0" . $subtract;

        $data = [
            'start' => $start,
            'now' => $now
        ];

        return $data;
    }
}
