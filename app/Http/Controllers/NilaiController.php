<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class NilaiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Lokasi file JSON
    |--------------------------------------------------------------------------
    */

    private function filePath()
    {
        return storage_path('app/nilai.json');
    }


    /*
    |--------------------------------------------------------------------------
    | Membaca data dari JSON
    |--------------------------------------------------------------------------
    */

    private function getData()
    {
        $file = $this->filePath();

        // Jika file belum ada, buat file kosong
        if (!File::exists($file)) {
            File::put($file, json_encode([]));
        }

        $content = File::get($file);

        $data = json_decode($content, true);

        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Menyimpan data ke JSON
    |--------------------------------------------------------------------------
    */

    private function saveData($data)
    {
        File::put(
            $this->filePath(),
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET /api/nilai
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $data = $this->getData();

        return response()->json([
            'status' => 'success',
            'total' => count($data),
            'data' => $data
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GET /api/nilai/{id}
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $data = $this->getData();

        foreach ($data as $item) {

            if ((int) $item['id'] === (int) $id) {

                return response()->json([
                    'status' => 'success',
                    'data' => $item
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'pesan' => 'Data tidak ditemukan'
        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | POST /api/nilai
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        | Validasi
        */

        $validated = $request->validate([
            'nama' => 'required|string',
            'mata_kuliah' => 'required|string',
            'nilai' => 'required|numeric|min:0|max:100'
        ]);


        /*
        | Ambil data lama
        */

        $data = $this->getData();


        /*
        | Membuat ID otomatis
        */

        if (empty($data)) {

            $id = 1;

        } else {

            $ids = array_column($data, 'id');

            $id = max($ids) + 1;
        }


        /*
        | Menentukan keterangan
        */

        $nilai = (float) $validated['nilai'];

        if ($nilai >= 60) {

            $keterangan = 'Lulus';

        } else {

            $keterangan = 'Tidak Lulus';
        }


        /*
        | Data baru
        */

        $dataBaru = [
            'id' => $id,
            'nama' => $validated['nama'],
            'mata_kuliah' => $validated['mata_kuliah'],
            'nilai' => $nilai,
            'keterangan' => $keterangan
        ];


        /*
        | Masukkan data ke array
        */

        $data[] = $dataBaru;


        /*
        | Simpan ke JSON
        */

        $this->saveData($data);


        /*
        | Response POST
        */

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data berhasil ditambahkan',
            'data' => $dataBaru
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | PUT /api/nilai/{id}
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        /*
        | Untuk PUT dan PATCH
        */

        $validated = $request->validate([
            'nama' => 'sometimes|required|string',
            'mata_kuliah' => 'sometimes|required|string',
            'nilai' => 'sometimes|required|numeric|min:0|max:100'
        ]);


        /*
        | Ambil data
        */

        $data = $this->getData();

        $ditemukan = false;

        $dataUpdate = null;


        /*
        | Cari ID
        */

        foreach ($data as $key => $item) {

            if ((int) $item['id'] === (int) $id) {

                $ditemukan = true;


                /*
                | Update nama
                */

                if (isset($validated['nama'])) {

                    $data[$key]['nama'] =
                        $validated['nama'];
                }


                /*
                | Update mata kuliah
                */

                if (isset($validated['mata_kuliah'])) {

                    $data[$key]['mata_kuliah'] =
                        $validated['mata_kuliah'];
                }


                /*
                | Update nilai
                */

                if (isset($validated['nilai'])) {

                    $nilai =
                        (float) $validated['nilai'];

                    $data[$key]['nilai'] =
                        $nilai;


                    /*
                    | Update keterangan
                    */

                    if ($nilai >= 60) {

                        $data[$key]['keterangan'] =
                            'Lulus';

                    } else {

                        $data[$key]['keterangan'] =
                            'Tidak Lulus';
                    }
                }


                $dataUpdate = $data[$key];

                break;
            }
        }


        /*
        | Jika tidak ditemukan
        */

        if (!$ditemukan) {

            return response()->json([
                'status' => 'error',
                'pesan' => 'Data tidak ditemukan'
            ], 404);
        }


        /*
        | Simpan
        */

        $this->saveData($data);


        /*
        | Response
        */

        return response()->json([
            'status' => 'success',
            'pesan' => 'Data berhasil diperbarui',
            'data' => $dataUpdate
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE /api/nilai/{id}
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $data = $this->getData();

        $dataBaru = [];

        $ditemukan = false;


        foreach ($data as $item) {

            if ((int) $item['id'] === (int) $id) {

                $ditemukan = true;

                continue;
            }

            $dataBaru[] = $item;
        }


        /*
        | Data tidak ditemukan
        */

        if (!$ditemukan) {

            return response()->json([
                'status' => 'error',
                'pesan' => 'Data tidak ditemukan'
            ], 404);
        }


        /*
        | Simpan data baru
        */

        $this->saveData($dataBaru);


        return response()->json([
            'status' => 'success',
            'pesan' => 'Data berhasil dihapus'
        ]);
    }
}